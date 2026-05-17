<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$id_factura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_factura) {
    die("ID de factura inválido o no proporcionado.");
}

require_once 'class/GeneradorDTE.php';
$generador = new GeneradorDTE();

// Obtenemos el JSON estructurado que genera tu clase
$json_dte = $generador->generarJSONConsumidorFinal($id_factura);
$dte = json_decode($json_dte, true);

if (isset($dte['error'])) {
    die("Error al recuperar el DTE: " . htmlspecialchars($dte['error']));
}

// URL de Validación para el QR exigido por Hacienda en El Salvador
$ambiente = $dte['identificacion']['ambiente'];
$codGen = $dte['identificacion']['codigoGeneracion'];
$fechaEmi = $dte['identificacion']['fecEmi'];
$url_mh = "https://admin.factura.gob.sv/consultaPublica?ambiente={$ambiente}&codGen={$codGen}&fechaEmi={$fechaEmi}";
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode($url_mh);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Representación Impresa DTE — <?= $dte['identificacion']['numeroControl'] ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
            font-size: 11px;
        }
        .dte-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-left {
            width: 55%;
            vertical-align: top;
        }
        .header-right {
            width: 45%;
            vertical-align: top;
        }
        .empresa-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 5px 0;
        }
        .dte-box {
            border: 2px solid #1e3a8a;
            border-radius: 6px;
            padding: 12px;
            background-color: #f8fafc;
        }
        .dte-box-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background: #1e3a8a;
            color: #fff;
            padding: 4px;
            margin: -12px -12px 10px -12px;
            border-radius: 4px 4px 0 0;
            text-transform: uppercase;
        }
        .section-title {
            background: #e2e8f0;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-left: 4px solid #1e3a8a;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background: #1e3a8a;
            color: #fff;
            padding: 6px 4px;
            font-size: 10px;
            border: 1px solid #1e3a8a;
        }
        .items-table td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .resumen-container {
            width: 100%;
            margin-top: 15px;
        }
        .resumen-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .resumen-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
        }
        .total-row {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 12px;
        }
        .clearfix { clear: both; }
        .footer-area {
            margin-top: 30px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
        }
        .btn-print-box {
            max-width: 800px;
            margin: 10px auto;
            text-align: right;
        }
        .btn-accion {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-accion:hover { background: #059669; }
        @media print {
            body { background: #fff; padding: 0; }
            .dte-wrapper { border: none; box-shadow: none; padding: 0; }
            .btn-print-box { display: none; }
        }
    </style>
</head>
<body>

    <div class="btn-print-box">
        <a href="index.php" class="btn-accion" style="background:#64748b;">Volver al Inicio</a>
        <button onclick="window.print();" class="btn-accion">Imprimir Documento (PDF)</button>
    </div>

    <div class="dte-wrapper">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="empresa-title"><?= htmlspecialchars($dte['emisor']['nombreComercial'] ?? $dte['emisor']['nombre']) ?></div>
                    <strong><?= htmlspecialchars($dte['emisor']['nombre']) ?></strong><br>
                    NIT: <?= htmlspecialchars($dte['emisor']['nit']) ?> | NRC: <?= htmlspecialchars($dte['emisor']['nrc']) ?><br>
                    Actividad: <?= htmlspecialchars($dte['emisor']['descActividad']) ?><br>
                    Dirección: <?= htmlspecialchars($dte['emisor']['direccion']['complemento']) ?>, <?= htmlspecialchars($dte['emisor']['direccion']['municipio']) ?>, <?= htmlspecialchars($dte['emisor']['direccion']['departamento']) ?><br>
                    Teléfono: <?= htmlspecialchars($dte['emisor']['telefono']) ?> | Correo: <?= htmlspecialchars($dte['emisor']['correo']) ?>
                </td>
                <td class="header-right">
                    <div class="dte-box">
                        <div class="dte-box-title">Documento Tributario Electrónico</div>
                        <table style="width:100%; border-collapse:collapse; font-size:10px;">
                            <tr>
                                <td style="padding:2px 0;"><strong>TIPO DOCUMENTO:</strong></td>
                                <td>FACTURA ELECTRÓNICA (01)</td>
                            </tr>
                            <tr>
                                <td style="padding:2px 0;"><strong>CÓDIGO GENERACIÓN:</strong></td>
                                <td style="font-family:monospace; font-size:11px; font-weight:bold; color:#b91c1c;"><?= $dte['identificacion']['codigoGeneracion'] ?></td>
                            </tr>
                            <tr>
                                <td style="padding:2px 0;"><strong>NÚMERO DE CONTROL:</strong></td>
                                <td style="font-family:monospace; font-size:11px;"><?= $dte['identificacion']['numeroControl'] ?></td>
                            </tr>
                            <tr>
                                <td style="padding:2px 0;"><strong>AMBIENTE:</strong></td>
                                <td><?= $dte['identificacion']['ambiente'] === '00' ? 'PRUEBAS' : 'PRODUCCIÓN' ?></td>
                            </tr>
                            <tr>
                                <td style="padding:2px 0;"><strong>FECHA/HORA EMI:</strong></td>
                                <td><?= $dte['identificacion']['fecEmi'] ?> <?= $dte['identificacion']['horEmi'] ?></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">Receptor / Cliente</div>
        <table class="info-table">
            <tr>
                <td style="width:12%"><strong>Nombre:</strong></td>
                <td style="width:53%"><?= htmlspecialchars($dte['receptor']['nombre']) ?></td>
                <td style="width:15%"><strong>DUI/Documento:</strong></td>
                <td style="width:20%"><?= htmlspecialchars($dte['receptor']['numDocumento'] ?? '---') ?></td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td><?= htmlspecialchars($dte['receptor']['direccion']['complemento'] ?? 'Sin Dirección') ?></td>
                <td><strong>Teléfono:</strong></td>
                <td><?= htmlspecialchars($dte['receptor']['telefono'] ?? '---') ?></td>
            </tr>
            <tr>
                <td><strong>Correo:</strong></td>
                <td><?= htmlspecialchars($dte['receptor']['correo'] ?? '---') ?></td>
                <td><strong>Cond. Pago:</strong></td>
                <td><?= $dte['resumen']['condicionOperacion'] === 1 ? 'Contado' : 'Crédito' ?></td>
            </tr>
        </table>

        <div class="section-title">Detalle de Productos</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%">N°</th>
                    <th style="width:12%">Código</th>
                    <th style="width:45%">Descripción</th>
                    <th style="width:8%">Cantidad</th>
                    <th style="width:10%">Precio Unit.</th>
                    <th style="width:8%">Descto.</th>
                    <th style="width:12%">Ventas Gravadas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dte['cuerpoDocumento'] as $item): ?>
                <tr>
                    <td class="text-center"><?= $item['numItem'] ?></td>
                    <td style="font-family:monospace;"><?= htmlspecialchars($item['codigo']) ?></td>
                    <td><?= htmlspecialchars($item['descripcion']) ?></td>
                    <td class="text-center"><?= number_format($item['cantidad'], 2) ?></td>
                    <td class="text-right">$<?= number_format($item['precioUni'], 2) ?></td>
                    <td class="text-right">$<?= number_format($item['montoDescu'], 2) ?></td>
                    <td class="text-right">$<?= number_format($item['ventaGravada'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="resumen-container">
            <div style="width: 55%; float:left; font-size:10px; margin-top:5px;">
                <strong>VALOR EN LETRAS:</strong><br>
                <span style="text-transform: uppercase; font-weight:bold;"><?= htmlspecialchars($dte['resumen']['totalLetras']) ?></span>
            </div>
            
            <table class="resumen-table">
                <tr>
                    <td>Subtotal Ventas:</td>
                    <td class="text-right">$<?= number_format($dte['resumen']['subTotalVentas'], 2) ?></td>
                </tr>
                <tr>
                    <td>IVA Incluido (13%):</td>
                    <td class="text-right">$<?= number_format($dte['resumen']['tributos'][0]['valor'], 2) ?></td>
                </tr>
                <tr>
                    <td>Retenciones / Descuentos:</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL A PAGAR:</td>
                    <td class="text-right">$<?= number_format($dte['resumen']['totalPagar'], 2) ?></td>
                </tr>
            </table>
            <div class="clearfix"></div>
        </div>

        <table class="footer-area" style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:75%; vertical-align:middle; padding-right:15px; color:#475569; font-size:10px; line-height:14px;">
                    <p style="margin:0 0 6px 0;"><strong>INFORMACIÓN DE CONTROL TRIBUTARIO:</strong></p>
                    Esta es la representación gráfica e impresa de un Documento Tributario Electrónico emitido de conformidad con las normativas del Ministerio de Hacienda de El Salvador.<br>
                    Puede verificar la autenticidad de este documento tributario escaneando el código QR adjunto o ingresando directamente al portal de consultas públicas del MH con el Código de Generación provisto en el encabezado.
                </td>
                <td style="width:25%; text-align:center; vertical-align:middle;">
                    <img src="<?= $qr_api ?>" alt="QR de Validación MH" style="border:1px solid #cbd5e1; padding:4px; background:#fff;">
                </td>
            </tr>
        </table>

    </div>
</body>
</html>