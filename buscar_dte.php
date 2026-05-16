<?php
/* ============================================================
   buscar_dte.php — Endpoint AJAX para buscar el DTE original
   al crear una NCE. Devuelve JSON.

   Parámetro GET: q = número_control | código_generación (UUID)
   Respuesta:
     { ok: true,  id_factura, tipo_dte, codigo_generacion,
       numero_control, fecha_emision, monto_total, estado_mh,
       sello_recibido, id_receptor, receptor_nombre,
       tipo_documento, num_documento }
     { ok: false, msg: "..." }
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
     * La NCE solo puede referenciar FE (01) y CCF (03).
     * Buscamos por número de control O por código de generación (UUID).
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
        echo json_encode([
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
        ]);
    } else {
        echo json_encode([
            'ok'  => false,
            'msg' => 'No se encontró ningún DTE (FE o CCF) con ese número de control o UUID. '
                   . 'Verifique que el documento exista y sea de tipo 01 o 03.'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'ok'  => false,
        'msg' => 'Error al buscar el DTE: ' . $e->getMessage()
    ]);
}
?>