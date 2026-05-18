<?php
session_start();

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$pagina_activa = 'REPORTES';

// --- IMPORTAR CLASES ---
// Aseguramos la ruta hacia la carpeta class
require_once 'class/Database.php';
require_once 'class/ReporteModel.php';

// --- INICIAR CONEXIÓN ---
$database = new Database();
// Dependiendo de cómo esté hecho tu Database.php, llama al método que devuelve la conexión mysqli
$conn = $database->getConnection(); 

// Instanciamos el modelo pasándole la conexión
$reporteModel = new ReporteModel($conn);

// --- RANGO DE FECHAS (Filtros) ---
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');

// --- OBTENER DATOS USANDO EL MODELO ---
$emisorInfo = $reporteModel->getEmisor();
$sucursal_nombre = $emisorInfo['nombre'] ?? "Pizzería El Salvador — Sucursal Central";
$sucursal_codigo = $emisorInfo['cod_estable_mh'] ?? "0001"; 

$kpis = $reporteModel->getKpis($fecha_desde, $fecha_hasta);
$total_ventas       = $kpis['total_ventas'];
$cant_dtes_emitidos = $kpis['cant_dtes_emitidos'] ?? 0;
$cant_dtes_anulados = $kpis['cant_dtes_anulados'] ?? 0;
$total_iva_retenido = $kpis['total_iva_retenido'];

$resumen_dtes = $reporteModel->getResumenDtes($fecha_desde, $fecha_hasta);
$productos_top = $reporteModel->getProductosTop($fecha_desde, $fecha_hasta);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Ventas y DTEs</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>

<body>

    <div class="layout">
        <?php include 'navegacion.php'; ?>

        <div class="main-content">

            <div class="topbar">
                <h1>Panel de Reportes y Estadísticas</h1>
                <span class="company-name"><?= htmlspecialchars($sucursal_nombre) ?></span>
            </div>

            <div class="page-content">
                <div class="fe-container">
                    
                    <div class="fe-header-banner">
                        <div class="fe-header-left">
                            <span class="badge-fe">RP-01</span>
                            <div class="brand-text">
                                <h2 class="fe-title" style="font-size: 16px; margin: 0;"><?= htmlspecialchars($sucursal_nombre) ?></h2>
                            </div>
                        </div>
                        <div class="fe-header-right">
                            <span>Código Establecimiento MH: <strong class="fe-code"><?= htmlspecialchars($sucursal_codigo) ?></strong></span><br>
                            <span>Ecosistema Transmisor DTE</span>
                        </div>
                    </div>

                    <div class="fe-section" style="background: var(--bg-section);">
                        <form method="GET" action="">
                            <div class="grid-3" style="align-items: end;">
                                <div class="fe-group">
                                    <label>Fecha Desde</label>
                                    <input type="date" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
                                </div>
                                <div class="fe-group">
                                    <label>Fecha Hasta</label>
                                    <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
                                </div>
                                <div class="fe-group">
                                    <button type="submit" class="btn-add" style="margin: 0; width: 100%; height: 32px;">
                                        Filtrar Reporte
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="fe-section">
                        <div class="grid-4">
                            <div class="box-info" style="background: var(--white); border-color: var(--border);">
                                <h4 style="color: var(--navy); border-bottom-color: var(--border);">Ventas Totales</h4>
                                <span style="font-family: 'Courier New', monospace; font-size: 24px; font-weight: 900; color: var(--navy);">
                                    $<?= number_format($total_ventas, 2) ?>
                                </span>
                            </div>
                            
                            <div class="box-info" style="background: var(--white); border-color: var(--border);">
                                <h4 style="color: #27ae60; border-bottom-color: #27ae60;">DTEs Transmitidos</h4>
                                <span style="font-family: 'Courier New', monospace; font-size: 24px; font-weight: 900; color: #27ae60;">
                                    <?= $cant_dtes_emitidos ?>
                                </span>
                            </div>

                            <div class="box-info" style="background: var(--red-light); border-color: #e8b4ae;">
                                <h4 style="color: var(--red); border-bottom-color: var(--red);">Documentos Anulados</h4>
                                <span style="font-family: 'Courier New', monospace; font-size: 24px; font-weight: 900; color: var(--red);">
                                    <?= $cant_dtes_anulados ?>
                                </span>
                            </div>

                            <div class="box-info" style="background: var(--white); border-color: var(--border);">
                                <h4 style="color: #c8970a; border-bottom-color: #c8970a;">IVA Retenido (MH)</h4>
                                <span style="font-family: 'Courier New', monospace; font-size: 24px; font-weight: 900; color: #c8970a;">
                                    $<?= number_format($total_iva_retenido, 2) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="fe-section no-border">
                        <div class="grid-2">
                            
                            <div>
                                <div class="section-title">
                                    <span class="num">1</span> Distribución por Tipo de DTE
                                </div>
                                <div class="table-wrap">
                                    <table class="items-table" style="min-width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Cód.</th>
                                                <th style="text-align: left; padding-left: 10px;">Tipo de DTE</th>
                                                <th>Cantidad</th>
                                                <th>Total Gravado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($resumen_dtes)): ?>
                                                <tr><td colspan="4" style="text-align:center;">No hay datos para las fechas seleccionadas.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($resumen_dtes as $dte): ?>
                                                    <tr>
                                                        <td class="td-num"><?= htmlspecialchars($dte['tipo']) ?></td>
                                                        <td style="text-align: left; padding-left: 10px; font-weight: 600;"><?= htmlspecialchars($dte['nombre']) ?></td>
                                                        <td><?= $dte['cantidad'] ?></td>
                                                        <td style="font-family: 'Courier New', monospace; font-weight: 700; color: <?= $dte['total'] < 0 ? 'var(--red)' : 'var(--navy)' ?>;">
                                                            $<?= number_format($dte['total'], 2) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <div class="section-title">
                                    <span class="num">2</span> Menú Más Vendido (Unidades)
                                </div>
                                <div class="table-wrap">
                                    <table class="items-table" style="min-width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: left; padding-left: 10px;">Producto / Item</th>
                                                <th>Cantidad Vendida</th>
                                                <th>Monto Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($productos_top)): ?>
                                                <tr><td colspan="3" style="text-align:center;">No hay datos para las fechas seleccionadas.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($productos_top as $prod): ?>
                                                    <tr>
                                                        <td style="text-align: left; padding-left: 10px; font-weight: 600; color: var(--text-muted);"><?= htmlspecialchars($prod['nombre']) ?></td>
                                                        <td class="td-num"><?= (float)$prod['cantidad'] ?> uds.</td>
                                                        <td style="font-family: 'Courier New', monospace; font-weight: 700; color: var(--navy);">
                                                            $<?= number_format($prod['total'], 2) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="fe-section" style="padding-top: 0;">
                        <div class="nce-tabla-nota" style="margin-bottom: 0;">
                            <strong>Información de sincronización:</strong> Este reporte consolida tanto transacciones locales como los flujos de Notas de Crédito de Descuento/Devolución aplicados a Facturas y CCF en los rangos consultados.
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>