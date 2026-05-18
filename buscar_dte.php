<?php
/* ============================================================
   buscar_dte.php — Endpoint AJAX para buscar el DTE original
   al crear una NCE. Devuelve JSON con cabecera e ítems.

   Parámetro GET: q = número_control | código_generación (UUID)
   ============================================================ */

session_start();
require_once 'class/Database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_nombre'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión no iniciada.']);
    exit();
}

$q = trim($_GET['q'] ?? '');
if (empty($q)) {
    echo json_encode(['ok' => false, 'msg' => 'Parámetro de búsqueda vacío.']);
    exit();
}

try {
    $db   = new Database();
    $conn = $db->getConnection();

    /*
     * 1. Buscar la CABECERA del DTE (FE o CCF)
     */
    $sql = "SELECT
                f.id_factura,
                f.tipo_dte,
                f.codigo_generacion,
                f.numero_control,
                f.fecha_emision,
                f.hora_emision,
                f.monto_total,
                f.estado_mh,
                f.sello_recibido,
                f.id_receptor,
                r.nombre        AS receptor_nombre,
                r.tipo_documento,
                r.num_documento
            FROM factura f
            LEFT JOIN receptor r ON r.id_receptor = f.id_receptor
            WHERE f.tipo_dte IN ('01', '03')
              AND (f.numero_control = ? OR f.codigo_generacion = ?)
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Armamos la primera parte del array de respuesta
        $data = [
            'ok'                => true,
            'id_factura'        => $row['id_factura'],
            'tipo_dte'          => $row['tipo_dte'],
            'codigo_generacion' => $row['codigo_generacion'],
            'numero_control'    => $row['numero_control'],
            'fecha_emision'     => $row['fecha_emision'],
            'hora_emision'      => $row['hora_emision'],
            'monto_total'       => $row['monto_total'],
            'estado_mh'         => $row['estado_mh'],
            'sello_recibido'    => $row['sello_recibido'],
            'id_receptor'       => $row['id_receptor'],
            'receptor_nombre'   => $row['receptor_nombre']  ?? '',
            'tipo_documento'    => $row['tipo_documento']   ?? '',
            'num_documento'     => $row['num_documento']    ?? '',
            'items'             => [] // Aquí guardaremos el detalle
        ];

        $id_factura_encontrada = $row['id_factura'];
        

        /*
         * 2. Buscar el DETALLE (Ítems) anexando la tabla Producto
         */
        $sql_items = "
            SELECT 
                fd.num_item,
                fd.cantidad,
                fd.precio_unitario,
                fd.descuento,
                p.id_producto,
                p.codigo_mh,
                p.product_name
            FROM factura_detalle fd
            INNER JOIN producto p ON fd.id_producto = p.id_producto
            WHERE fd.id_factura = ?
            ORDER BY fd.num_item ASC
        ";

        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $id_factura_encontrada);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        // Extraemos todos los ítems y los insertamos en el nodo 'items' de la respuesta
        while ($row_item = $result_items->fetch_assoc()) {
            $data['items'][] = [
                'num_item'        => $row_item['num_item'],
                'id_producto'     => $row_item['id_producto'],
                'codigo_mh'       => $row_item['codigo_mh'],
                'product_name'    => $row_item['product_name'],
                'cantidad'        => $row_item['cantidad'],
                'precio_unitario' => $row_item['precio_unitario'],
                'descuento'       => $row_item['descuento']
            ];
        }
        $stmt_items->close();

        // Enviamos el JSON consolidado
        echo json_encode($data);

    } else {
        echo json_encode([
            'ok'  => false,
            'msg' => 'No se encontró ningún DTE (FE o CCF) con ese número de control o UUID. '
                   . 'Verifique que el documento exista y sea de tipo 01 o 03.'
        ]);
        
    }

} catch (Exception $e) {
    echo json_encode([
        'ok'  => false,
        'msg' => 'Error al buscar el DTE: ' . $e->getMessage()
    ]);
}
?>