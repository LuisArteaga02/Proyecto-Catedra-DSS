<?php
session_start();
require_once 'class/Database.php';
require_once 'class/GeneradorDTE.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: invalidacion.php");
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $codigo_generacion_orig = trim($_POST['codigo_generacion'] ?? '');
    $motivo = trim($_POST['motivo_invalidacion'] ?? '');
    $responsable = trim($_POST['responsable'] ?? '');
    
    if (empty($codigo_generacion_orig) || empty($motivo) || empty($responsable)) {
        throw new Exception("Todos los campos son obligatorios.");
    }
    
    // Buscar la factura
    $stmt = $conn->prepare("SELECT id_factura, estado_mh FROM factura WHERE codigo_generacion = ?");
    $stmt->bind_param("s", $codigo_generacion_orig);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        throw new Exception("No se encontró ningún DTE con el código de generación proporcionado.");
    }
    
    $factura = $res->fetch_assoc();
    $id_factura = $factura['id_factura'];
    $stmt->close();
    
    if ($factura['estado_mh'] === 'ANULADO') {
        throw new Exception("Este DTE ya se encuentra anulado.");
    }
    
    $conn->begin_transaction();
    
    // Generar el UUID para el evento de invalidación
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    $uuid_evento = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    $uuid_evento = strtoupper($uuid_evento);
    
    // Insertar el evento 
    $tipo_evento = '05';
    $estado_evento = 'PENDIENTE'; // Hasta que Hacienda lo selle
    
    $stmt_ev = $conn->prepare("INSERT INTO factura_evento (id_factura, tipo_evento, descripcion, responsable, codigo_generacion_r, estado_evento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_ev->bind_param("isssss", $id_factura, $tipo_evento, $motivo, $responsable, $uuid_evento, $estado_evento);
    $stmt_ev->execute();
    $id_evento = $stmt_ev->insert_id;
    $stmt_ev->close();
    
    // Actualizar el estado de la factura a anulado
    $nuevo_estado = 'ANULADO';
    $msg = 'Invalidado internamente. UUID Evento: ' . $uuid_evento;
    $stmt_upd = $conn->prepare("UPDATE factura SET estado_mh = ?, descripcion_msg = ? WHERE id_factura = ?");
    $stmt_upd->bind_param("ssi", $nuevo_estado, $msg, $id_factura);
    $stmt_upd->execute();
    $stmt_upd->close();
    
    $conn->commit();
    
    // generar JSON con la estructura oficial requerida por el MH
    $generador = new GeneradorDTE();
    $json_invalidacion = $generador->generarJSONInvalidacion($id_factura, $id_evento);
    
    // 6. Guardar el archivo JSON en la carpeta de firmados
    if (!is_dir('dtes_firmados')) {
        mkdir('dtes_firmados', 0777, true);
    }
    $ruta_archivo = "dtes_firmados/" . $uuid_evento . "_invalidacion.json";
    file_put_contents($ruta_archivo, $json_invalidacion);
    
    // Redirigir con mensaje de éxito a la misma vista
    header("Location: invalidacion.php?exito=1&uuid=" . urlencode($uuid_evento));
    exit();
    
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_error == null) {
        $conn->rollback();
    }
    header("Location: invalidacion.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
