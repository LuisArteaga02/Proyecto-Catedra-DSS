<?php
/* ============================================================
   procesar_ccf.php — Procesador del Comprobante de Crédito Fiscal (DTE Tipo 03)
   Pizzeria El Salvador — Sistema de Facturación Electrónica

   Diferencias clave respecto a procesar_factura.php (FE):
   ─ tipo_dte  = '03'  (no '01')
   ─ El receptor es contribuyente: NIT + NRC + actividad son obligatorios
   ─ precio_unitario llega como precio NETO (sin IVA), 8 decimales
   ─ venta_gravada = (qty × precio_neto) − descuento   (calculado en JS y re-verificado aquí)
   ─ iva_item = venta_gravada × 0.13                   (calculado en JS y re-verificado aquí)
   ─ total_iva  = h_iva  (viene directo del JS, no se recalcula como total_gravado / 1.13)
   ─ Se guardan iva_retenido y retencion_renta como campos separados
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
       0. GARANTIZAR QUE EL CATÁLOGO TIENE EL CÓDIGO '36' (NIT)
          cat_tipo_documento solo trae '13' (DUI) del dump inicial.
          INSERT IGNORE añade '36' si no existe, sin tocar nada si ya existe.
          Esto evita el error de FK al registrar un receptor contribuyente.
       ============================================================ */
    $conn->query(
        "INSERT IGNORE INTO cat_tipo_documento (codigo, nombre)
         VALUES ('36', 'NIT')"
    );

    /* ============================================================
       1. METADATOS DEL DTE
       ============================================================ */
    $tipo_dte          = '03';
    $codigo_generacion = trim($_POST['codigo_generacion'] ?? '');

    /* Número de control secuencial para tipo_dte = '03' */
    $sql_last = "SELECT numero_control FROM factura
                 WHERE tipo_dte = ?
                 ORDER BY id_factura DESC LIMIT 1";
    $stmt_last = $conn->prepare($sql_last);
    $stmt_last->bind_param("s", $tipo_dte);
    $stmt_last->execute();
    $result_last = $stmt_last->get_result();

    $nuevo_correlativo = 1;
    if ($row_last = $result_last->fetch_assoc()) {
        // Formato idéntico al FE: DTE-03-00000001-000000000000001 = 31 chars (varchar(31))
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
       2. DATOS DEL RECEPTOR CONTRIBUYENTE
       ============================================================ */
    $receptor_nit            = trim($_POST['receptor_nit']            ?? '');
    $receptor_nrc            = trim($_POST['receptor_nrc']            ?? '');
    $receptor_nombre         = trim($_POST['receptor_nombre']         ?? '');
    $receptor_email          = trim($_POST['receptor_email']          ?? '');
    $receptor_tel            = trim($_POST['receptor_tel']            ?? '');
    $receptor_direccion      = trim($_POST['receptor_direccion']      ?? '');
    $receptor_cod_actividad  = trim($_POST['receptor_cod_actividad']  ?? '');
    $receptor_desc_actividad = trim($_POST['receptor_desc_actividad'] ?? '');

    $dir_departamento = !empty($_POST['dir_departamento'])
        ? str_pad(trim($_POST['dir_departamento']), 2, '0', STR_PAD_LEFT)
        : null;
    $dir_municipio = !empty($_POST['dir_municipio'])
        ? str_pad(trim($_POST['dir_municipio']), 2, '0', STR_PAD_LEFT)
        : null;

    /* Validación del servidor */
    if (empty($receptor_nit) || empty($receptor_nrc) || empty($receptor_nombre)) {
        throw new Exception("NIT, NRC y Nombre del receptor son obligatorios para el CCF.");
    }
    if (empty($receptor_cod_actividad)) {
        throw new Exception("La actividad económica del receptor es obligatoria para el CCF.");
    }
    if (empty($dir_departamento) || empty($dir_municipio)) {
        throw new Exception("Departamento y municipio del receptor son obligatorios para el CCF.");
    }

    /* ============================================================
       2b. GARANTIZAR QUE LA ACTIVIDAD ECONÓMICA EXISTE EN EL CATÁLOGO
           El SELECT del formulario usa los datos de cat_actividad_economica,
           pero si el catálogo está incompleto, la FK fk_act fallaría.
           INSERT IGNORE la añade con descripción mínima si no existe.
       ============================================================ */
    $stmt_act = $conn->prepare(
        "INSERT IGNORE INTO cat_actividad_economica (codigo, descripcion) VALUES (?, ?)"
    );
    $stmt_act->bind_param("ss", $receptor_cod_actividad, $receptor_desc_actividad);
    $stmt_act->execute();
    $stmt_act->close();

    /* ============================================================
       3. UPSERT DEL RECEPTOR
          Buscar por NIT. Si existe → actualizar. Si no → insertar.
          tipo_documento = '36' (NIT), ya garantizado en el paso 0.
       ============================================================ */
    $id_receptor  = null;
    $tipo_doc_nit = '36';

    $stmt_buscar = $conn->prepare(
        "SELECT id_receptor FROM receptor
         WHERE num_documento = ? AND num_documento != ''
         LIMIT 1"
    );
    $stmt_buscar->bind_param("s", $receptor_nit);
    $stmt_buscar->execute();
    $result_buscar = $stmt_buscar->get_result();

    if ($row_rec = $result_buscar->fetch_assoc()) {
        /* Contribuyente ya existe — actualizar */
        $id_receptor = $row_rec['id_receptor'];

        $stmt_upd = $conn->prepare(
            "UPDATE receptor SET
                nrc              = ?,
                nombre           = ?,
                dir_departamento = ?,
                dir_municipio    = ?,
                dir_complemento  = ?,
                telefono         = ?,
                cod_actividad    = ?,
                correo           = ?
             WHERE id_receptor = ?"
        );
        $stmt_upd->bind_param(
            "ssssssssi",
            $receptor_nrc,          $receptor_nombre,
            $dir_departamento,      $dir_municipio,
            $receptor_direccion,    $receptor_tel,
            $receptor_cod_actividad, $receptor_email,
            $id_receptor
        );
        $stmt_upd->execute();
        $stmt_upd->close();

    } else {
        /* Contribuyente nuevo — insertar */
        $stmt_ins = $conn->prepare(
            "INSERT INTO receptor (
                tipo_documento, num_documento, nrc,
                nombre, dir_departamento, dir_municipio,
                dir_complemento, telefono, cod_actividad, correo
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_ins->bind_param(
            "ssssssssss",
            $tipo_doc_nit,          $receptor_nit,         $receptor_nrc,
            $receptor_nombre,       $dir_departamento,     $dir_municipio,
            $receptor_direccion,    $receptor_tel,
            $receptor_cod_actividad, $receptor_email
        );
        $stmt_ins->execute();
        $id_receptor = $stmt_ins->insert_id;
        $stmt_ins->close();
    }
    $stmt_buscar->close();

    /* ============================================================
       4. CONDICIÓN DE PAGO Y TOTALES (desde campos hidden del JS)
       ============================================================ */
    $condicion_pago  = trim($_POST['condicion_pago'] ?? '01');

    $total_no_sujeto = (float)($_POST['h_nosujeta']        ?? 0);
    $total_exento    = (float)($_POST['h_exenta']          ?? 0);
    $total_gravado   = (float)($_POST['h_gravada']         ?? 0);
    $desc_global     = (float)($_POST['h_desc_global']     ?? 0);
    $sub_total       = (float)($_POST['h_subtotal']        ?? 0);
    $total_iva       = (float)($_POST['h_iva']             ?? 0);
    $iva_retenido    = (float)($_POST['h_iva_retenido']    ?? 0);
    $retencion_renta = (float)($_POST['h_retencion_renta'] ?? 0);
    $monto_total     = (float)($_POST['h_total']           ?? 0);

    if ($monto_total <= 0) {
        throw new Exception("El total a pagar debe ser mayor a \$0.00.");
    }

    /* Re-verificación del total en el servidor */
    $monto_verificado = round($sub_total + $total_iva - $iva_retenido - $retencion_renta, 2);
    if (abs($monto_verificado - $monto_total) > 0.01) {
        throw new Exception(
            "Inconsistencia en totales detectada por el servidor. " .
            "Calculado: \${$monto_verificado} | Recibido: \${$monto_total}. " .
            "Recargue el formulario e intente de nuevo."
        );
    }

    $total_letras = 'Total generado por sistema';

    /* ============================================================
       5. INSERCIÓN EN TABLA factura (tipo_dte = '03')
       ============================================================ */
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
    // Tipos: s=tipo_dte, i=id_receptor, i=... NO — id_receptor es int pero viene de insert_id
    // Columnas:  tipo_dte(s), id_receptor(i),
    //            codigo_generacion(s), numero_control(s),
    //            fecha_emision(s), hora_emision(s), condicion_pago(s),
    //            total_no_sujeto(d), total_exento(d), total_gravado(d),
    //            sub_total(d), iva_retenido(d), retencion_renta(d),
    //            monto_total(d), total_iva(d), total_letras(s)
    // = s i s s s s s d d d d d d d d s  →  16 vars, 16 tipos
    $stmt_fac->bind_param(
        "sisssssdddddddds",
        $tipo_dte,        $id_receptor,
        $codigo_generacion, $numero_control,
        $fecha_emision,   $hora_emision,    $condicion_pago,
        $total_no_sujeto, $total_exento,    $total_gravado,
        $sub_total,       $iva_retenido,    $retencion_renta,
<<<<<<< HEAD
        $monto_total,     $total_iva,       $total_letras, $sello_falso
=======
        $monto_total,     $total_iva,       $total_letras
>>>>>>> Dashboard
    );
    $stmt_fac->execute();
    $id_factura = $conn->insert_id;
    $stmt_fac->close();

    /* ============================================================
       6. DETALLE DE ÍTEMS
       ============================================================ */
    $ids_producto    = $_POST['id_producto']    ?? [];
    $cantidades      = $_POST['cant']           ?? [];
    $precios_neto    = $_POST['precio_neto']    ?? [];
    $descuentos      = $_POST['descuento_item'] ?? [];

    if (empty($cantidades)) {
        throw new Exception("Debe incluir al menos un ítem en el comprobante.");
    }

    // iva_item y precio_venta son columnas GENERATED ALWAYS — MySQL las calcula solo.
    // No se incluyen en el INSERT o MySQL lanza error.
    $sql_detalle = "INSERT INTO factura_detalle (
        id_factura, id_producto, num_item,
        cantidad, precio_unitario, descuento,
        venta_gravada
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt_det = $conn->prepare($sql_detalle);
    $num_item = 1;

    for ($i = 0; $i < count($cantidades); $i++) {
        $id_producto     = (int)($ids_producto[$i]   ?? 0);
        $cantidad        = (float)($cantidades[$i]   ?? 0);
        $precio_unitario = (float)($precios_neto[$i] ?? 0);   // precio neto, 8 decimales
        $descuento       = (float)($descuentos[$i]   ?? 0);

        /* Saltamos filas vacías */
        if ($cantidad <= 0 || $id_producto <= 0 || $precio_unitario <= 0) {
            continue;
        }

        /*
         * Re-calcular en el servidor usando el precio neto con todos sus decimales.
         * Redondear a 2 decimales solo al guardar, igual que lo hace el JS.
         */
        $venta_gravada_sv = round(($cantidad * $precio_unitario) - $descuento, 2);
        if ($venta_gravada_sv < 0) $venta_gravada_sv = 0.00;

        $stmt_det->bind_param(
            "iiidddd",
            $id_factura,       $id_producto, $num_item,
            $cantidad,         $precio_unitario, $descuento,
            $venta_gravada_sv
        );
        $stmt_det->execute();
        $num_item++;
    }
    $stmt_det->close();

    if ($num_item === 1) {
        throw new Exception(
            "No se encontraron ítems válidos. " .
            "Verifique que cada fila tenga cantidad, precio y producto seleccionado."
        );
    }

    /* ============================================================
       7. FORMAS DE PAGO (tabla factura_pago — si existe en el esquema)
       ============================================================ */
    $codigos_pago  = $_POST['forma_pago_codigo']  ?? [];
    $montos_pago   = $_POST['forma_pago_monto']   ?? [];
    $periodos_pago = $_POST['forma_pago_periodo'] ?? [];

    $tabla_pago_existe = $conn->query(
        "SELECT 1 FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'factura_pago'
         LIMIT 1"
    )->num_rows > 0;

    if ($tabla_pago_existe && !empty($codigos_pago)) {
        // Columnas reales de factura_pago: id_factura, codigo_pago, monto, referencia
        // codigo_pago tiene FK a cat_medio_pago — garantizamos que el código exista
        $sql_pago  = "INSERT INTO factura_pago
                      (id_factura, codigo_pago, monto, referencia)
                      VALUES (?, ?, ?, ?)";
        $stmt_pago = $conn->prepare($sql_pago);

        $stmt_cat_pago = $conn->prepare(
            "INSERT IGNORE INTO cat_medio_pago (codigo, nombre) VALUES (?, ?)"
        );

        // Nombres legibles para los códigos del formulario
        $nombres_pago = [
            '01' => 'Billetes y Monedas', '02' => 'Tarjeta Débito',
            '03' => 'Tarjeta Crédito',   '04' => 'Cheque',
            '05' => 'Transferencia Crédito', '06' => 'Transferencia Débito',
            '07' => 'Vales',             '08' => 'Dinero Electrónico',
            '09' => 'Tarjeta Prepago',   '10' => 'Pago Móvil',
            '11' => 'Bitcoin',           '99' => 'Otros',
        ];

        for ($i = 0; $i < count($codigos_pago); $i++) {
            $cod_pago = trim($codigos_pago[$i] ?? '');
            $mto_pago = (float)($montos_pago[$i] ?? 0);
            $ref_pago = trim($periodos_pago[$i] ?? ''); // referencia (texto libre)

            if (empty($cod_pago) || $mto_pago <= 0) continue;

            // Garantizar que el código existe en cat_medio_pago antes de usarlo como FK
            $nombre_pago = $nombres_pago[$cod_pago] ?? 'Otro';
            $stmt_cat_pago->bind_param("ss", $cod_pago, $nombre_pago);
            $stmt_cat_pago->execute();

            $stmt_pago->bind_param("isds",
                $id_factura, $cod_pago, $mto_pago, $ref_pago
            );
            $stmt_pago->execute();
        }
        $stmt_cat_pago->close();
        $stmt_pago->close();
    }

    /* ============================================================
       8. COMMIT Y REDIRECCIÓN
       ============================================================ */
    $conn->commit();

     require_once 'class/GeneradorDTE.php';
        $generador = new generadorDTE();
        $json_final_dte = $generador->generarJSONConsumidorFinal($id_factura);
        file_put_contents("dtes_firmados/" . $codigo_generacion . ".json", $json_final_dte);
        
        // Lo mandamos de regreso al inicio con un mensajito amigable
        header("Location: ver_factura_dte.php?id=" . $id_factura);
        
    exit();

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    header("Location: factura_ccf.php?error=" . urlencode(
        "No se pudo procesar el CCF: " . $e->getMessage()
    ));
    exit();
}
?>