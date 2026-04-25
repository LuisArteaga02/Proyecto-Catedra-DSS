<?php
session_start();
require_once 'class/Database.php';

// Si el usuario no ha iniciado sesión, no puede estar facturando.
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // ¡Importante! Iniciamos una transacción
        // Así nos aseguramos de que si ocurre un error a la mitad (por ejemplo, al guardar los detalles),
        // no se guarde una factura vacía o "rota" en la base de datos
        $conn->begin_transaction();

        $codigo_generacion = $_POST['codigo_generacion'] ?? '';
        
        /* 
         * Ignoramos el 'numero_control' que manda el HTML porque venía quemado.
         * En su lugar, buscamos la última factura en la BD para sumar +1 y generar 
         * un correlativo real, secuencial y único para que no haya error con la BD
         */
        $tipo_dte_default = '01'; // Factura Consumidor Final
        $sql_last_control = "SELECT numero_control FROM factura WHERE tipo_dte = ? ORDER BY id_factura DESC LIMIT 1";
        $stmt_last = $conn->prepare($sql_last_control);
        $stmt_last->bind_param("s", $tipo_dte_default);
        $stmt_last->execute();
        $result_last = $stmt_last->get_result();

        $nuevo_correlativo = 1;
        if ($row_last = $result_last->fetch_assoc()) {
            // El formato es: DTE-01-00000001-000000000000001
            $partes = explode('-', $row_last['numero_control']);
            if (count($partes) == 4) {
                // Tomamos la última parte y le sumamos 1
                $nuevo_correlativo = (int)$partes[3] + 1;
            }
        }
        
        // Rellenamos con ceros a la izquierda hasta tener 15 dígitos
        $correlativo_formateado = str_pad($nuevo_correlativo, 15, "0", STR_PAD_LEFT);
        $numero_control = "DTE-{$tipo_dte_default}-00000001-{$correlativo_formateado}";
        $fecha_generacion = $_POST['fecha_generacion'] ?? date('Y-m-d H:i:s');
        $partes_fecha = explode(' ', $fecha_generacion);
        $fecha_emision = $partes_fecha[0];
        $hora_emision = $partes_fecha[1] ?? '00:00:00';
        
        $condicion_pago = $_POST['condicion_pago'] ?? 1;
        
        // Valores de totales que el JavaScript ocultó en los inputs hidden
        $total_no_sujeto = $_POST['h_nosujeta'] ?? 0;
        $total_exento = $_POST['h_exenta'] ?? 0;
        $total_gravado = $_POST['h_gravada'] ?? 0;
        $sub_total = $_POST['h_subtotal'] ?? 0;
        $iva_retenido = $_POST['h_iva_retenido'] ?? 0;
        $monto_total = $_POST['h_total'] ?? 0;
        
        // Calculamos cuánto de la venta gravada corresponde al IVA (13%)
        $total_iva = $total_gravado - ($total_gravado / 1.13);

        /* 
         * Ya no perderemos los datos del cliente. Primero verificamos si ya existe por su DUI/NIT,
         * y si no, lo registramos. Luego lo vinculamos a la factura usando la tabla `factura_vinculo`.
         */
        $cliente_nombre = $_POST['cliente_nombre'] ?? 'Consumidor Final';
        $tipo_doc = $_POST['tipo_doc'] ?? '13'; // 13 es DUI por defecto en SV
        $cliente_doc = $_POST['cliente_doc'] ?? '';
        $cliente_nrc = $_POST['cliente_nrc'] ?? null;
        $cliente_email = $_POST['cliente_email'] ?? '';
        $cliente_tel = $_POST['cliente_tel'] ?? '';
        $cliente_direccion = $_POST['cliente_direccion'] ?? 'Sin dirección';
        
        // Extraer dep/mun de 'ubicacion' -> ej: "06-14"
        $ubicacion = $_POST['ubicacion'] ?? '06-14';
        $partes_ubi = explode('-', $ubicacion);
        $dir_departamento = $partes_ubi[0] ?? '06';
        $dir_municipio = $partes_ubi[1] ?? '14';
        
        $cod_actividad = !empty($_POST['actividad_receptor']) ? $_POST['actividad_receptor'] : '10005'; 
        
        $id_receptor = 0;
        
        if (!empty($cliente_doc) || !empty($cliente_nombre)) {
            $sql_buscar = "SELECT id_receptor FROM receptor WHERE num_documento = ? AND num_documento != '' LIMIT 1";
            $stmt_buscar = $conn->prepare($sql_buscar);
            $stmt_buscar->bind_param("s", $cliente_doc);
            $stmt_buscar->execute();
            $result_buscar = $stmt_buscar->get_result();
            
            if ($row_rec = $result_buscar->fetch_assoc()) {
                $id_receptor = $row_rec['id_receptor'];
            } else {
                $sql_insert_receptor = "INSERT INTO receptor (
                    tipo_documento, num_documento, nrc, nombre, dir_departamento, 
                    dir_municipio, dir_complemento, telefono, cod_actividad, correo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert_receptor);
                $stmt_insert->bind_param("ssssssssss", 
                    $tipo_doc, $cliente_doc, $cliente_nrc, $cliente_nombre, $dir_departamento, 
                    $dir_municipio, $cliente_direccion, $cliente_tel, $cod_actividad, $cliente_email
                );
                $stmt_insert->execute();
                $id_receptor = $stmt_insert->insert_id;
            }
        }
        
        // Insertamos la Factura
        $sql_factura = "INSERT INTO factura (
            codigo_generacion, numero_control, fecha_emision, hora_emision, condicion_pago,
            total_no_sujeto, total_exento, total_gravado, sub_total, iva_retenido,
            monto_total, total_iva, total_letras
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Total generado por sistema')";
        
        $stmt = $conn->prepare($sql_factura);
        $stmt->bind_param("ssssiddddddd", 
            $codigo_generacion, $numero_control, $fecha_emision, $hora_emision, $condicion_pago,
            $total_no_sujeto, $total_exento, $total_gravado, $sub_total, $iva_retenido,
            $monto_total, $total_iva
        );
        $stmt->execute();
        
        // Tomamos el ID de la factura que acabamos de insertar
        $id_factura = $conn->insert_id; 

        // Creamos el vínculo entre la factura y el receptor
        if ($id_receptor > 0) {
            $sql_vinculo = "INSERT INTO factura_vinculo (id_factura, id_receptor) VALUES (?, ?)";
            $stmt_vinculo = $conn->prepare($sql_vinculo);
            $stmt_vinculo->bind_param("ii", $id_factura, $id_receptor);
            $stmt_vinculo->execute();
        }

        // OBTENEMOS EL DETALLE DE ÍTEMS (Tabla 'factura_detalle')
        $ids_producto = $_POST['id_producto'] ?? []; // Ahora recibimos el ID real
        $cantidades = $_POST['cant'] ?? [];
        $precios = $_POST['precio'] ?? [];
        $descuentos = $_POST['descuento_item'] ?? [];
        $ventas_gravadas = $_POST['v_gravada'] ?? [];

        $sql_detalle = "INSERT INTO factura_detalle (
            id_factura, id_producto, num_item, cantidad, precio_unitario, descuento, venta_gravada
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conn->prepare($sql_detalle);

        $num_item = 1;
        // Recorremos los ítems recibidos (los array de inputs)
        for ($i = 0; $i < count($cantidades); $i++) {
            $id_producto = isset($ids_producto[$i]) ? (int)$ids_producto[$i] : 1; // Fallback por seguridad
            $cantidad = (float)$cantidades[$i];
            $precio_unitario = (float)$precios[$i];
            $descuento = isset($descuentos[$i]) ? (float)$descuentos[$i] : 0;
            $venta_gravada = isset($ventas_gravadas[$i]) ? (float)$ventas_gravadas[$i] : 0;
            
            // Ignoramos filas vacías o con precio/cantidad cero
            if ($cantidad > 0 && $precio_unitario > 0 && $id_producto > 0) {
                $stmt_detalle->bind_param("iiidddd", 
                    $id_factura, $id_producto, $num_item, 
                    $cantidad, $precio_unitario, $descuento, $venta_gravada
                );
                $stmt_detalle->execute();
                $num_item++;
            }
        }

        // Si llegamos hasta aquí sin que nada explotara, confirmamos todos los INSERTS
        $conn->commit();
        
        // Lo mandamos de regreso al inicio con un mensajito amigable
        header("Location: index.php?msg=factura_ok");
        exit();
        
    } catch (Exception $e) {
        // Si algo salió mal. Revertimos toda la operación (rollback) para no dejar datos basura.
        if (isset($conn)) {
            $conn->rollback();
        }
        
        // Redirigimos al formulario de vuelta pasándole el error
        header("Location: factura_fe.php?error=" . urlencode("No se pudo procesar la factura: " . $e->getMessage()));
        exit();
    }
} else {
    // Si alguien entra directo a procesar_factura.php sin enviar POST
    header("Location: index.php");
    exit();
}
?>
