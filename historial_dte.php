<?php
session_start();
require_once 'class/Database.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$pagina_activa = 'HISTORIAL';

// Conexión a la BD
$db = new Database();
$conn = $db->getConnection();

// Recuperar valores del filtro
$f_desde = $_GET['f_desde'] ?? '';
$f_hasta = $_GET['f_hasta'] ?? '';
$f_codigo = $_GET['f_codigo'] ?? '';
$f_receptor = $_GET['f_receptor'] ?? '';
$f_tipo = $_GET['f_tipo'] ?? '';
$f_estado = $_GET['f_estado'] ?? '';

// Construir la consulta dinámica
$sql = "SELECT f.*, r.nombre AS receptor_nombre, r.num_documento AS receptor_doc 
        FROM factura f 
        LEFT JOIN factura_vinculo fv ON f.id_factura = fv.id_factura
        LEFT JOIN receptor r ON fv.id_receptor = r.id_receptor
        WHERE 1=1";

$tipos_param = "";
$params = [];

if (!empty($f_desde)) {
    $sql .= " AND f.fecha_emision >= ?";
    $tipos_param .= "s";
    $params[] = $f_desde;
}
if (!empty($f_hasta)) {
    $sql .= " AND f.fecha_emision <= ?";
    $tipos_param .= "s";
    $params[] = $f_hasta;
}
if (!empty($f_codigo)) {
    $sql .= " AND f.codigo_generacion LIKE ?";
    $tipos_param .= "s";
    $params[] = "%" . $f_codigo . "%";
}
if (!empty($f_receptor)) {
    $sql .= " AND (r.nombre LIKE ? OR r.num_documento LIKE ?)";
    $tipos_param .= "ss";
    $params[] = "%" . $f_receptor . "%";
    $params[] = "%" . $f_receptor . "%";
}
if (!empty($f_tipo) && $f_tipo !== 'TODOS') {
    $sql .= " AND f.tipo_dte = ?";
    $tipos_param .= "s";
    $params[] = $f_tipo;
}
if (!empty($f_estado) && $f_estado !== 'TODOS') {
    $sql .= " AND f.estado_mh = ?";
    $tipos_param .= "s";
    $params[] = $f_estado;
}

$sql .= " ORDER BY f.fecha_emision DESC, f.hora_emision DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($tipos_param, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$facturas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $facturas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de DTEs</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<div class="layout">
    <?php include 'navegacion.php'; ?>

    <div class="main-content" style="background-color: #fff;">
        
        <div class="historial-topbar">
            <h1>Consultas de DTEs</h1>
            <span class="company-name">Pizzeria El Salvador S.A de C.V.</span>
        </div>

        <form method="GET" action="historial_dte.php" class="filters-container" id="formFiltros">
            <!-- Campos ocultos para mantener el estado de las píldoras -->
            <input type="hidden" name="f_tipo" id="f_tipo" value="<?= htmlspecialchars($f_tipo) ?>">
            <input type="hidden" name="f_estado" id="f_estado" value="<?= htmlspecialchars($f_estado) ?>">

            <div class="filters-grid">
                <div class="filter-group">
                    <label>Desde:</label>
                    <input type="date" name="f_desde" value="<?= htmlspecialchars($f_desde) ?>">
                </div>
                <div class="filter-group">
                    <label>Hasta:</label>
                    <input type="date" name="f_hasta" value="<?= htmlspecialchars($f_hasta) ?>">
                </div>
                <div class="filter-group" style="flex: 1.5;">
                    <label>Codigo de generacion:</label>
                    <input type="text" name="f_codigo" placeholder="Ej. A1B2C3D4..." value="<?= htmlspecialchars($f_codigo) ?>">
                </div>
                <div class="filter-group" style="flex: 1.5;">
                    <label>Receptor</label>
                    <input type="text" name="f_receptor" placeholder="DUI o Nombre" value="<?= htmlspecialchars($f_receptor) ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-buscar">Buscar</button>
                    <a href="historial_dte.php" class="btn-limpiar" style="text-decoration:none; display:flex; align-items:center;">Limpiar</a>
                </div>
            </div>
            
            <div class="pills-row">
                <span class="pills-label">Tipo:</span>
                <button type="button" class="pill-btn <?= empty($f_tipo) || $f_tipo == 'TODOS' ? 'active red' : '' ?>" onclick="setFilter('f_tipo', 'TODOS')">Todos</button>
                <button type="button" class="pill-btn <?= $f_tipo == '01' ? 'active red' : '' ?>" onclick="setFilter('f_tipo', '01')">FE - 01</button>
                <button type="button" class="pill-btn <?= $f_tipo == '03' ? 'active red' : '' ?>" onclick="setFilter('f_tipo', '03')">CCF - 03</button>
                <button type="button" class="pill-btn <?= $f_tipo == '05' ? 'active red' : '' ?>" onclick="setFilter('f_tipo', '05')">NCE - 05</button>
                
                <span class="pills-label" style="margin-left: 20px;">Estado:</span>
                <button type="button" class="pill-btn <?= empty($f_estado) || $f_estado == 'TODOS' ? 'active green' : '' ?>" onclick="setFilter('f_estado', 'TODOS')">Todos los estados</button>
                <button type="button" class="pill-btn <?= $f_estado == 'ACEPTADO' ? 'active green' : '' ?>" onclick="setFilter('f_estado', 'ACEPTADO')">Aceptados</button>
                <button type="button" class="pill-btn <?= $f_estado == 'CONTINGENCIA' ? 'active green' : '' ?>" onclick="setFilter('f_estado', 'CONTINGENCIA')">Contingencia</button>
                <button type="button" class="pill-btn <?= $f_estado == 'ANULADO' ? 'active green' : '' ?>" onclick="setFilter('f_estado', 'ANULADO')">Anulados</button>
            </div>
        </form>

        <div class="results-info">Mostrando <?= count($facturas) ?> resultados</div>
        
        <div class="table-header">
            <div class="sortable">Fecha</div>
            <div>Tipo</div>
            <div>Receptor</div>
            <div>Codigo generacion</div>
            <div>Monto</div>
            <div>Estado MH</div>
        </div>

        <?php if(empty($facturas)): ?>
            <div style="padding: 30px; text-align: center; color: #888;">No hay facturas registradas aún.</div>
        <?php else: ?>
            <?php foreach ($facturas as $index => $fac): 
                $tipoLabel = ($fac['tipo_dte'] == '01') ? 'FE - 01' : 'CCF - 03';
                $badgeClass = ($fac['estado_mh'] == 'ACEPTADO') ? 'badge-aceptado' : 'badge-pendiente';
                
                // Formateamos fecha
                $fechaEmi = date("Y-m-d", strtotime($fac['fecha_emision']));
            ?>
            
            <div class="row-item" onclick="toggleDetails(<?= $index ?>)">
                <div><?= htmlspecialchars($fechaEmi) ?></div>
                <div class="td-tipo"><?= $tipoLabel ?></div>
                <div class="td-receptor">
                    <?= htmlspecialchars($fac['receptor_nombre'] ?? 'Consumidor Final') ?>
                    <span class="dui">DUI/NIT: <?= htmlspecialchars($fac['receptor_doc'] ?? 'N/A') ?></span>
                </div>
                <div class="td-codigo"><?= htmlspecialchars(substr($fac['codigo_generacion'], 0, 18)) ?>...</div>
                <div class="td-monto">$<?= number_format($fac['monto_total'], 2) ?></div>
                <div><span class="<?= $badgeClass ?>"><?= htmlspecialchars($fac['estado_mh']) ?></span></div>
            </div>

            <!-- Detalles expandidos -->
            <div class="expanded-details" id="details-<?= $index ?>">
                <h4>DETALLES:</h4>
                <div class="details-grid">
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Estado del documento:</span>
                            <?= htmlspecialchars($fac['estado_mh']) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Descripcion del estado:</span>
                            <?= htmlspecialchars($fac['descripcion_msg'] ?? 'Registrado internamente') ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tipo de DTE: Factura</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha y hora de generacion:</span>
                            <?= htmlspecialchars($fac['fecha_emision'] . ' ' . $fac['hora_emision']) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Codigo de generacion:</span>
                            <?= htmlspecialchars($fac['codigo_generacion']) ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Sello de recepcion:</span>
                            <?= htmlspecialchars($fac['sello_recibido'] ?? 'Pendiente de transmisión') ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Numero de control:</span>
                            <?= htmlspecialchars($fac['numero_control']) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Monto total:</span>
                            $<?= number_format($fac['monto_total'], 2) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">IVA de las operaciones:</span>
                            $<?= number_format($fac['total_iva'], 2) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">IVA retenido:</span>
                            $<?= number_format($fac['iva_retenido'], 2) ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Retencion renta:</span>
                            $<?= number_format($fac['retencion_renta'], 2) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total valores no afectos:</span>
                            $0.00
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total a pagar:</span>
                            $<?= number_format($fac['monto_total'], 2) ?>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Otros tributos:</span>
                            $0.00
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<script>
    // Pequeño script para hacer que los detalles se desplieguen al darle clic a la fila
    function toggleDetails(index) {
        const detailsDiv = document.getElementById('details-' + index);
        if (detailsDiv.classList.contains('show')) {
            detailsDiv.classList.remove('show');
        } else {
            document.querySelectorAll('.expanded-details.show').forEach(el => el.classList.remove('show'));
            detailsDiv.classList.add('show');
        }
    }

    // Script para actualizar los campos ocultos cuando se hace click y enviar el form
    function setFilter(fieldId, value) {
        document.getElementById(fieldId).value = value;
        document.getElementById('formFiltros').submit();
    }
</script>

</body>
</html>
