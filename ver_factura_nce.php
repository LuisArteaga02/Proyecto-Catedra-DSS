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
$json_dte = $generador->generarJSONNotaCredito($id_factura);
$dte = json_decode($json_dte, true);

if (isset($dte['error'])) {
    die("Error al recuperar el DTE: " . htmlspecialchars($dte['error']));
}

// URL de Validación para el QR exigido por Hacienda en El Salvador
$ambiente = $dte['identificacion']['ambiente'];
$codGen = $dte['identificacion']['codigoGeneracion'];
$fechaEmi = $dte['identificacion']['fecEmi'];
$url_mh = "https://admin.factura.gob.sv/consultaPublica?ambiente={$ambiente}&codGen={$codGen}&fechaEmi={$fechaEmi}";
<<<<<<< HEAD
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode($url_mh);
=======
<<<<<<< HEAD
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . urlencode($url_mh);
=======
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($url_mh);
>>>>>>> 53e73e9 (generacion de pdf de los dte funcionando)
>>>>>>> Dashboard
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Representación Impresa DTE — <?= $dte['identificacion']['numeroControl'] ?></title>
    <style>
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> Dashboard
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
<<<<<<< HEAD
=======
=======
/* ── Reset ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    background: #e5e7eb;
    color: #111;
    padding: 20px;
}

/* ── Botones (no se imprimen) ───────────────────────── */
.btn-bar {
    max-width: 900px;
    margin: 0 auto 10px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
.btn {
    padding: 7px 14px;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    font-size: 11px;
    cursor: pointer;
    text-decoration: none;
    color: #fff;
}
.btn-back  { background: #64748b; }
.btn-print { background: #b91c1c; }
.btn:hover { opacity: .88; }

/* ── Documento ──────────────────────────────────────── */
.doc {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #bbb;
}

/* ════════════════════════════════════
   CABECERA: Logo | Centro | Empresa
   ════════════════════════════════════ */
.cab {
    display: grid;
    grid-template-columns: 160px 1fr 230px;
    min-height: 130px;
    background: #fff;
    border-bottom: 1px solid #ddd;
}

.cab-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    border-right: 1px solid #e0e0e0;
}
.cab-logo img {
    max-width: 130px;
    max-height: 115px;
    object-fit: contain;
}

.cab-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 10px 6px 6px;
    text-align: center;
}
.cab-center .doc-tipo-top {
    font-size: 12px;
    font-weight: bold;
    color: #111;
    margin-bottom: 1px;
    text-transform: uppercase;
}
.cab-center .doc-subtipo {
    font-size: 11.5px;
    font-weight: bold;
    color: #111;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.cab-center img {
    border: 1px solid #aaa;
    padding: 2px;
    background: #fff;
}

.cab-right {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 10px 12px;
    text-align: right;
    border-left: 1px solid #e0e0e0;
}
.empresa-nombre {
    font-size: 22px;
    font-weight: 900;
    color: #cc2200;
    font-style: italic;
    margin-bottom: 4px;
}
.empresa-dir {
    font-size: 10px;
    color: #cc2200;
    margin-bottom: 3px;
}
.empresa-tel {
    font-size: 10.5px;
    color: #111;
    font-weight: bold;
}

/* ── Barra de códigos ───────────────────────────────── */
.codigos-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 5px 12px;
}
.codigos-left { display: flex; flex-direction: column; gap: 2px; }
.codigos-right { display: flex; flex-direction: column; gap: 2px; text-align: right; align-items: flex-end; }
.cod-lbl {
    font-size: 10px;
    color: #1a56c4;
    line-height: 1.6;
}
.cod-lbl span { color: #111; }

/* ════════════════════════════════════
   EMISOR / RECEPTOR
   ════════════════════════════════════ */
.er-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.emisor-block {
    background: #fdf5a0;
    padding: 0;
    border-right: 2px solid #fff;
}
.receptor-block {
    background: #b84010;
    padding: 0;
}

.er-titulo {
    font-size: 14px;
    font-weight: 900;
    color: #1a1a8c;
    padding: 6px 10px 3px;
    letter-spacing: .5px;
}
.receptor-block .er-titulo { color: #fdf5a0; }

.er-body { padding: 2px 10px 8px; }

.er-fila { margin-bottom: 3px; }
.er-fila .lbl {
    font-size: 10px;
    color: #1a1a8c;
    display: block;
}
.receptor-block .er-fila .lbl { color: #fdf5a0; }
.er-fila .val {
    font-size: 10.5px;
    color: #111;
    display: block;
}
.receptor-block .er-fila .val { color: #fff; }

/* ════════════════════════════════════
   DETALLE DE PRODUCTOS (original)
   ════════════════════════════════════ */
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
    border: 1px solid #000000;
}
.items-table td {
    padding: 6px 4px;
    border: 1px solid #000000;
    background: #fdf5a0;
}
.text-right { text-align: right; }
.text-center { text-align: center; }
.resumen-container {
    width: 100%;
    margin-top: 15px;
    padding: 0 0 14px;
    background: #fdf5a0;
}
.resumen-table {
    width: 40%;
    float: right;
    border-collapse: collapse;
}
.resumen-table td {
    
    padding: 5px 4px;
    border: 1px solid #000000;
    background: #fdf5a0;
    
}
.total-row {
    background: #f1f5f9;
    font-weight: bold;
    font-size: 12px;
}
.clearfix { clear: both; }
.footer-area {
    margin-top: 20px;
    border-top: 1px dashed #cbd5e1;
    padding-top: 15px;
    padding-bottom: 20px;
}

/* ── Print ──────────────────────────────────────────── */
@media print {
    body { background: #fff; padding: 0; }
    .btn-bar { display: none; }
    .doc { border: none; max-width: 100%; }
}
>>>>>>> 53e73e9 (generacion de pdf de los dte funcionando)
>>>>>>> Dashboard
    </style>
</head>
<body>

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> Dashboard
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
                <strong>Motivo por nota de credito</strong><br>
                <br>
                <span style="text-transform: uppercase; font-weight:bold;"><?= htmlspecialchars($dte['extension']['observaciones']) ?></span>
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
<<<<<<< HEAD
=======
=======
    <div class="btn-bar">
        <a href="index.php" class="btn btn-back">◀ Volver</a>
        <button onclick="window.print();" class="btn btn-print">🖨 Imprimir / PDF</button>
    </div>

    <div class="doc">

        <!-- ══════════════════════════════════════════
             CABECERA
        ══════════════════════════════════════════ -->
        <div class="cab">
            <div class="cab-logo">
                <img src="img/logo_factura.png" alt="Logo"
                     onerror="this.style.display='none'">
            </div>
            <div class="cab-center">
                <div class="doc-tipo-top">DOCUMENTO TRIBUTARIO ELECTRÓNICO</div>
                <div class="doc-subtipo">NOTA DE CRÉDITO</div>
                <img src="<?= $qr_api ?>" width="100" height="100" alt="QR MH">
            </div>
            <div class="cab-right">
                <div class="empresa-nombre">PIZZERIA,  S.A. DE C.V.</div>
                <div class="empresa-dir">departamento 06, municipio: 14, Calle Principal #123, San Salvador</div>
                <div class="empresa-tel">TEL:2222-0000</div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             BARRA CÓDIGOS
        ══════════════════════════════════════════ -->
        <div class="codigos-bar">
            <div class="codigos-left">
                <div class="cod-lbl">Código de Generación: <span><?= htmlspecialchars($codGen) ?></span></div>
                <div class="cod-lbl">Numero de control: <span><?= htmlspecialchars($dte['identificacion']['numeroControl']) ?></span></div>
                <div class="cod-lbl">Sello de recepcion: <span><?= htmlspecialchars($dte['identificacion']['selloRecibido'] ?? '') ?></span></div>
            </div>
            <div class="codigos-right">
                <div class="cod-lbl">Modelo de Facturacion: <span><?= htmlspecialchars($dte['identificacion']['tipoModelo'] ?? '') ?></span></div>
                <div class="cod-lbl">Tipo de transmision: <span><?= htmlspecialchars($dte['identificacion']['tipoOperacion'] ?? '') ?></span></div>
                <div class="cod-lbl">Fecha y hora de generacion: <span><?= $dte['identificacion']['fecEmi'] ?> <?= $dte['identificacion']['horEmi'] ?></span></div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             EMISOR / RECEPTOR
        ══════════════════════════════════════════ -->
        <div class="er-grid">

            <div class="emisor-block">
                <div class="er-titulo">EMISOR</div>
                <div class="er-body">
                   <div class="er-fila"><span class="lbl">Nombre o razón social:</span><span class="val">PIZZERIA, S.A. DE C.V.</span></div>
                    <div class="er-fila"><span class="lbl">NIT:</span><span class="val"><?= htmlspecialchars($dte['emisor']['nit']) ?></span></div>
                    <div class="er-fila"><span class="lbl">NRC:</span><span class="val"><?= htmlspecialchars($dte['emisor']['nrc']) ?></span></div>
                    <div class="er-fila"><span class="lbl">Actividad económica:</span><span class="val"><?= htmlspecialchars($dte['emisor']['descActividad']) ?></span></div>
                   <div class="er-fila"><span class="lbl">Dirección:</span><span class="val">departamento 06, municipio: 14, Calle Principal #123, San Salvador</span></div>
                    <div class="er-fila"><span class="lbl">Número de teléfono:</span><span class="val"><?= htmlspecialchars($dte['emisor']['telefono']) ?></span></div>
                    <div class="er-fila"><span class="lbl">Correo electrónico:</span><span class="val"><?= htmlspecialchars($dte['emisor']['correo']) ?></span></div>
                </div>
            </div>

            <div class="receptor-block">
                <div class="er-titulo">RECEPTOR</div>
                <div class="er-body">
                    <div class="er-fila"><span class="lbl">Nombre o razón social:</span><span class="val"><?= htmlspecialchars($dte['receptor']['nombre']) ?></span></div>
                    <div class="er-fila"><span class="lbl">DUI/Documento:</span><span class="val"><?= htmlspecialchars($dte['receptor']['numDocumento'] ?? '---') ?></span></div>
                    <div class="er-fila"><span class="lbl">Dirección:</span><span class="val"><?= htmlspecialchars($dte['receptor']['direccion']['complemento'] ?? 'Sin Dirección') ?></span></div>
                    <div class="er-fila"><span class="lbl">Número de teléfono:</span><span class="val"><?= htmlspecialchars($dte['receptor']['telefono'] ?? '---') ?></span></div>
                    <div class="er-fila"><span class="lbl">Correo electrónico:</span><span class="val"><?= htmlspecialchars($dte['receptor']['correo'] ?? '---') ?></span></div>
                    <div class="er-fila"><span class="lbl">Condición de pago:</span><span class="val"><?= $dte['resumen']['condicionOperacion'] === 1 ? 'Contado' : 'Crédito' ?></span></div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             DETALLE DE PRODUCTOS (original nota de crédito)
        ══════════════════════════════════════════ -->
        <div style="padding: 0 14px;">
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
                    <br><br>
                    <strong>Motivo por nota de crédito:</strong><br>
                    <span style="text-transform: uppercase; font-weight:bold;"><?= htmlspecialchars($dte['extension']['observaciones']) ?></span>
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

                </tr>
            </table>
        </div>

    </div><!-- /.doc -->
>>>>>>> 53e73e9 (generacion de pdf de los dte funcionando)
>>>>>>> Dashboard
</body>
</html>