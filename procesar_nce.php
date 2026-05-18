<?php
/* ============================================================
   procesar_nce.php — Procesador de Nota de Crédito Electrónica (DTE Tipo 05)
   Pizzeria El Salvador — Sistema de Facturación Electrónica

   Flujo de BD:
   1. INSERT IGNORE en cat_tipo_dte para el código '05'
   2. INSERT en factura (tipo_dte = '05', monto_total negativo)
   3. INSERT en factura_detalle por cada ítem de ajuste
   4. INSERT en factura_evento vinculando la NCE al DTE original
   5. INSERT en factura_pago (forma de devolución)
   ============================================================ */

session_start();
require_once 'class/Database.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

try {
    $db   = new Database();
    $conn = $db->getConnection();
    $conn->begin_transaction();

    /* ============================================================
       0. GARANTIZAR QUE cat_tipo_dte TIENE EL CÓDIGO '05' (NCE)
          El dump inicial no lo incluye — INSERT IGNORE lo añade
          sin romper nada si ya existe.
       ============================================================ */
    $conn->query(
        "INSERT IGNORE INTO cat_tipo_dte (codigo, nombre, descripcion)
         VALUES ('05', 'Nota de Crédito', 'Nota de Crédito Electrónica')"
    );

    /* ============================================================
       1. METADATOS DEL DTE
       ============================================================ */
    $tipo_dte          = '05';
    $codigo_generacion = trim($_POST['codigo_generacion'] ?? '');

    /* Número de control secuencial para tipo_dte = '05' */
    $stmt_last = $conn->prepare(
        "SELECT numero_control FROM factura
         WHERE tipo_dte = ?
         ORDER BY id_factura DESC LIMIT 1"
    );
    $stmt_last->bind_param("s", $tipo_dte);
    $stmt_last->execute();
    $result_last = $stmt_last->get_result();

    $nuevo_correlativo = 1;
    if ($row_last = $result_last->fetch_assoc()) {
        $partes = explode('-', $row_last['numero_control']);
        if (count($partes) == 4) {
            $nuevo_correlativo = (int)end($partes) + 1;
        }
    }
    $stmt_last->close();

    $correlativo_fmt = str_pad($nuevo_correlativo, 15, '0', STR_PAD_LEFT);
    $numero_control  = "DTE-{$tipo_dte}-00000001-{$correlativo_fmt}";

    /* Fecha y hora */
    $fecha_generacion = trim($_POST['fecha_generacion'] ?? date('Y-m-d H:i:s'));
    $partes_fecha     = explode(' ', $fecha_generacion);
    $fecha_emision    = $partes_fecha[0];
    $hora_emision     = $partes_fecha[1] ?? '00:00:00';

    /* ============================================================
       2. DATOS DEL DOCUMENTO RELACIONADO (DTE original)
       ============================================================ */
    $id_factura_original     = (int)trim($_POST['id_factura_original']     ?? 0);
    $tipo_dte_original       = trim($_POST['tipo_dte_original']            ?? '');
    $codigo_gen_original     = trim($_POST['codigo_gen_original']          ?? '');
    $numero_control_original = trim($_POST['numero_control_original']      ?? '');
    $fecha_emision_original  = trim($_POST['fecha_emision_original']       ?? '');
    $id_receptor_original    = (int)trim($_POST['id_receptor_original']    ?? 0) ?: null;

    if ($id_factura_original <= 0 || empty($numero_control_original)) {
        throw new Exception(
            "Debe seleccionar y confirmar el DTE original antes de emitir la NCE."
        );
    }

    /* Verificar que el DTE original existe y es tipo 01 o 03 */
    $stmt_ver = $conn->prepare(
        "SELECT id_factura, tipo_dte, estado_mh, fecha_emision
         FROM factura
         WHERE id_factura = ? AND tipo_dte IN ('01','03')
         LIMIT 1"
    );
    $stmt_ver->bind_param("i", $id_factura_original);
    $stmt_ver->execute();
    $res_ver = $stmt_ver->get_result();
    if (!($row_ver = $res_ver->fetch_assoc())) {
        throw new Exception(
            "El DTE original (ID: {$id_factura_original}) no fue encontrado o no es de tipo FE/CCF."
        );
    }
    $stmt_ver->close();

    /* Verificar plazo de 90 días */
    $fecha_original_dt = new DateTime($row_ver['fecha_emision']);
    $hoy_dt            = new DateTime($fecha_emision);
    $dias_diff         = (int)$fecha_original_dt->diff($hoy_dt)->format('%a');
    if ($dias_diff > 90) {
        throw new Exception(
            "Han transcurrido {$dias_diff} días desde la emisión del DTE original. "
            . "El plazo máximo para emitir una NCE es de 90 días."
        );
    }

    /* ============================================================
       3. MOTIVO Y TIPO DE AJUSTE
       ============================================================ */
    $tipo_ajuste       = trim($_POST['tipo_ajuste']        ?? 'devolucion_parcial');
    $descripcion_motivo = trim($_POST['descripcion_motivo'] ?? '');
    $observaciones     = trim($_POST['observaciones']      ?? '');
    $forma_devolucion  = trim($_POST['forma_devolucion']   ?? '01');

    if (empty($descripcion_motivo)) {
        throw new Exception("La descripción del motivo del ajuste es obligatoria.");
    }

    /* ============================================================
       4. TOTALES — vienen de los campos hidden (valores positivos)
          Se guardan en la BD como negativos para reflejar el ajuste.
       ============================================================ */
    $h_gravada  = abs((float)($_POST['h_gravada']  ?? 0));
    $h_subtotal = abs((float)($_POST['h_subtotal'] ?? 0));
    $h_iva      = abs((float)($_POST['h_iva']      ?? 0));
    $h_total    = abs((float)($_POST['h_total']    ?? 0));

    if ($h_total <= 0) {
        throw new Exception("El total a devolver debe ser mayor a \$0.00.");
    }

    /* Guardamos negativos en la tabla — la NCE reduce el débito fiscal */
    $total_gravado   = -round($h_gravada,  2);
    $sub_total       = -round($h_subtotal, 2);
    $total_iva       = -round($h_iva,      2);
    $monto_total     = -round($h_total,    2);

    $total_no_sujeto = 0.00;
    $total_exento    = 0.00;
    $iva_retenido    = 0.00;
    $retencion_renta = 0.00;
    $condicion_pago  = 1;    /* int, igual que factura original */

    $total_letras = 'Total generado por sistema';

    /* ============================================================
       5. INSERT EN factura (tipo_dte = '05')
       ============================================================ */
    $id_receptor_nce = $id_receptor_original ?: null;
<<<<<<< HEAD
    $sello_falso = "TEST-" . substr(md5(uniqid()), 0, 15);
=======
>>>>>>> Dashboard

    $sql_factura = "INSERT INTO factura (
        tipo_dte,    id_receptor,
        codigo_generacion, numero_control,
        fecha_emision, hora_emision, condicion_pago,
        total_no_sujeto, total_exento, total_gravado,
        sub_total, iva_retenido, retencion_renta,
<<<<<<< HEAD
        monto_total, total_iva, total_letras, estado_mh, sello_recibido
=======
        monto_total, total_iva, total_letras
>>>>>>> Dashboard
    ) VALUES (
        ?, ?,
        ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
<<<<<<< HEAD
        ?, ?, ?, 'ACEPTADO', ?
=======
        ?, ?, ?
>>>>>>> Dashboard
    )";

    $stmt_fac = $conn->prepare($sql_factura);
    $stmt_fac->bind_param(
        "sisssssdddddddds",
        $tipo_dte,        $id_receptor_nce,
        $codigo_generacion, $numero_control,
        $fecha_emision,   $hora_emision,   $condicion_pago,
        $total_no_sujeto, $total_exento,   $total_gravado,
        $sub_total,       $iva_retenido,   $retencion_renta,
<<<<<<< HEAD
        $monto_total,     $total_iva,      $total_letras, $sello_falso
=======
        $monto_total,     $total_iva,      $total_letras
>>>>>>> Dashboard
    );
    $stmt_fac->execute();
    $id_factura_nce = $conn->insert_id;
    $stmt_fac->close();

    /* ============================================================
       6. INSERT EN factura_detalle — ítems de ajuste
          precio_unitario = precio original del ítem (con IVA, igual FE)
          venta_gravada   = valor del ajuste (negativo en la NCE)
          iva_item y precio_venta son GENERATED — no se insertan
       ============================================================ */
    $ids_producto = $_POST['id_producto']     ?? [];
    $cantidades   = $_POST['cant']            ?? [];
    $precios      = $_POST['precio_unit']     ?? [];
    $descuentos   = $_POST['descuento_item']  ?? [];
    $descs_ajuste = $_POST['desc_ajuste']     ?? [];
    $codigos_item = $_POST['codigo_item']     ?? [];

    if (empty($cantidades)) {
        throw new Exception("Debe incluir al menos un ítem en la nota de crédito.");
    }

    /*
     * Para la NCE no hay producto FK obligatorio — el ítem puede ser una
     * descripción libre. Usamos id_producto = 1 (producto genérico / primero)
     * como fallback cuando no se selecciona uno del catálogo.
     */
    $sql_primer_prod = "SELECT id_producto FROM producto ORDER BY id_producto LIMIT 1";
    $res_prod        = $conn->query($sql_primer_prod);
    $id_prod_fallback = $res_prod ? (int)$res_prod->fetch_assoc()['id_producto'] : 1;

    $sql_detalle = "INSERT INTO factura_detalle (
        id_factura, id_producto, num_item,
        cantidad, precio_unitario, descuento,
        venta_gravada
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt_det = $conn->prepare($sql_detalle);
    $num_item  = 1;

    for ($i = 0; $i < count($cantidades); $i++) {
        $id_producto     = isset($ids_producto[$i]) && (int)$ids_producto[$i] > 0
                           ? (int)$ids_producto[$i] : $id_prod_fallback;
        $cantidad        = (float)($cantidades[$i]   ?? 0);
        $precio_unitario = (float)($precios[$i]      ?? 0);
        $descuento       = (float)($descuentos[$i]   ?? 0);

        if ($cantidad <= 0 || $precio_unitario <= 0) continue;

        /* Venta gravada del ajuste — negativa en la NCE */
        $venta_gravada_sv = -round(max(0, ($cantidad * $precio_unitario) - $descuento), 2);

        $stmt_det->bind_param(
            "iiidddd",
            $id_factura_nce, $id_producto, $num_item,
            $cantidad,       $precio_unitario, $descuento,
            $venta_gravada_sv
        );
        $stmt_det->execute();
        $num_item++;
    }
    $stmt_det->close();

    if ($num_item === 1) {
        throw new Exception(
            "No se encontraron ítems válidos. "
            . "Verifique que cada fila tenga cantidad y precio mayor a 0."
        );
    }

    /* ============================================================
       7. INSERT EN factura_evento — vínculo NCE ↔ DTE original
          tipo_evento = '05' = Nota de Crédito (según CHECK de la tabla)
          descripcion = motivo del ajuste
          responsable = usuario en sesión
          codigo_generacion_r = UUID de la NCE recién creada
       ============================================================ */
    $responsable_evento = $_SESSION['usuario_nombre'] ?? 'Sistema';

    $stmt_ev = $conn->prepare(
        "INSERT INTO factura_evento
         (id_factura, tipo_evento, descripcion, responsable, codigo_generacion_r)
         VALUES (?, '05', ?, ?, ?)"
    );
    $stmt_ev->bind_param(
        "isss",
        $id_factura_original,
        $descripcion_motivo,
        $responsable_evento,
        $codigo_generacion
    );
    $stmt_ev->execute();
    $stmt_ev->close();

    /* ============================================================
       8. INSERT EN factura_pago — forma de devolución
          Misma lógica que el CCF: INSERT IGNORE en cat_medio_pago primero.
       ============================================================ */
    $tabla_pago_existe = $conn->query(
        "SELECT 1 FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'factura_pago'
         LIMIT 1"
    )->num_rows > 0;

    if ($tabla_pago_existe) {
        $nombres_pago = [
            '01' => 'Efectivo',           '02' => 'Tarjeta Débito',
            '03' => 'Tarjeta Crédito',    '04' => 'Cheque',
            '05' => 'Transferencia Crédito', '06' => 'Transferencia Débito',
            '07' => 'Nota de Crédito',    '08' => 'Dinero Electrónico',
            '99' => 'Otro',
        ];

        $nombre_forma = $nombres_pago[$forma_devolucion] ?? 'Otro';

        $stmt_cat_pago = $conn->prepare(
            "INSERT IGNORE INTO cat_medio_pago (codigo, nombre) VALUES (?, ?)"
        );
        $stmt_cat_pago->bind_param("ss", $forma_devolucion, $nombre_forma);
        $stmt_cat_pago->execute();
        $stmt_cat_pago->close();

        /* Monto a devolver — positivo en factura_pago (es la cantidad que sale) */
        $monto_devolucion = round($h_total, 2);
        $ref_devolucion   = 'NCE-' . $numero_control;

        $stmt_pago = $conn->prepare(
            "INSERT INTO factura_pago
             (id_factura, codigo_pago, monto, referencia)
             VALUES (?, ?, ?, ?)"
        );
        $stmt_pago->bind_param(
            "isds",
            $id_factura_nce, $forma_devolucion, $monto_devolucion, $ref_devolucion
        );
        $stmt_pago->execute();
        $stmt_pago->close();
    }

    /* ============================================================
       9. COMMIT Y REDIRECCIÓN
       ============================================================ */
    $conn->commit();

    require_once 'class/GeneradorDTE.php';
        $generador = new generadorDTE();
        $json_final_dte = $generador->generarJSONNotaCredito($id_factura_nce);
        file_put_contents("dtes_firmados/" . $codigo_generacion . ".json", $json_final_dte);
        
        // Lo mandamos de regreso al inicio con un mensajito amigable
        header("Location: ver_factura_nce.php?id=" . $id_factura_nce);
    exit();

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    header("Location: factura_nce.php?error=" . urlencode(
        "No se pudo procesar la NCE: " . $e->getMessage()
    ));
    exit();
}
?>