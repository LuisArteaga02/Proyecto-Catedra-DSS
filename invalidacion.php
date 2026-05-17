<?php
session_start();

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$pagina_activa = 'INVALIDACION';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalidar DTE — Pizzería El Salvador</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>

<body>

    <div class="layout">
        <?php include 'navegacion.php'; ?>

        <div class="main-content">

            <!-- TOPBAR ADAPTADO -->
            <div class="topbar">
                <h1>Evento de Invalidación de DTE</h1>
                <span class="company-name">Pizzería El Salvador S.A. de C.V.</span>
            </div>

            <!-- CONTENEDOR PRINCIPAL DEL FORMULARIO -->
            <div class="page-content">
                <div class="fe-container" style="max-width: 800px;">
                    
                    <!-- BANNER SUPERIOR CON ESTILO DTE -->
                    <div class="fe-header-banner" style="background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%); border-bottom: 3px solid var(--navy);">
                        <div class="fe-header-left">
                            <span class="badge-fe" style="background: var(--navy);">EV-05</span>
                            <div class="brand-text">
                                <h2 class="fe-title" style="font-size: 16px; margin: 0;">Anular Documento Electrónico</h2>
                            </div>
                        </div>
                        <div class="fe-header-right">
                            <span>Ministerio de Hacienda</span><br>
                            <span class="fe-code">Evento de Invalidación</span>
                        </div>
                    </div>

                    <!-- EXPLICACIÓN DEL PROCESO -->
                    <div class="fe-section" style="background: var(--bg-section); border-bottom: 1px solid var(--border);">
                        <p style="font-size: 12px; line-height: 1.5; color: var(--text-muted); margin: 0;">
                            Ingrese los datos requeridos por el Ministerio de Hacienda para invalidar un DTE que ya fue transmitido o firmado previamente. Esta operación generará un archivo de evento con estructura formal invalidando el identificador único del documento.
                        </p>
                    </div>

                    <!-- SECCIÓN DE NOTIFICACIONES / ALERTAS -->
                    <?php if (isset($_GET['exito'])): ?>
                        <div class="fe-section">
                            <div class="box-info" style="background: #e8f5e9; border-color: #a5d6a7; color: #1b5e20;">
                                <h4 style="color: #1b5e20; border-bottom: 1px dashed #a5d6a7;">¡DTE Invalidado con éxito!</h4>
                                <p style="font-size: 12px; margin-bottom: 6px;">El estado del documento ha cambiado a <strong>ANULADO</strong> y se ha generado el JSON del Evento de Invalidación de manera conforme.</p>
                                <span style="font-family: 'Courier New', monospace; font-size: 11px;">UUID del evento: <strong><?= htmlspecialchars($_GET['uuid'] ?? '') ?></strong></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="fe-section">
                            <div class="box-info">
                                <h4>Error al procesar la solicitud</h4>
                                <p style="font-size: 12px; margin: 0; color: var(--red-dark);"><?= htmlspecialchars($_GET['error']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- FORMULARIO PRINCIPAL -->
                    <form action="procesar_invalidacion.php" method="POST">
                        
                        <div class="fe-section">
                            <div class="grid-1">
                                <!-- CAMPO: CÓDIGO DE GENERACIÓN -->
                                <div class="fe-group">
                                    <label>Código de Generación (UUID del DTE) <span class="req">*</span></label>
                                    <input type="text" name="codigo_generacion" required 
                                           style="font-family: 'Courier New', monospace; font-weight: 600; letter-spacing: 0.5px;"
                                           placeholder="Ej. B44DB3DA-A612-DEEF-CF0C-8C200CBC1CE5">
                                    <span class="error-msg">Este campo es requerido</span>
                                    <p style="font-size: 11px; color: #7f8c8d; margin-top: 4px;">
                                        Si no encuentra el código, puede buscarlo directamente en el <a href="historial_dte.php" style="color: var(--navy); font-weight: 600; text-decoration: underline;">Historial de DTEs</a>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="fe-section">
                            <div class="grid-1">
                                <!-- CAMPO: RESPONSABLE -->
                                <div class="fe-group">
                                    <label>Nombre del Responsable de la Operación <span class="req">*</span></label>
                                    <input type="text" name="responsable" required 
                                           value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="fe-section">
                            <div class="grid-1">
                                <!-- CAMPO: MOTIVO -->
                                <div class="fe-group">
                                    <label>Motivo de Invalidación <span class="req">*</span></label>
                                    <textarea name="motivo_invalidacion" required rows="4"
                                              placeholder="Explique detalladamente el motivo de la anulación (ej. Error en asignación de ítems, el cliente desistió de la compra, duplicidad en transacciones, etc.)"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ADVERTENCIA LEGAL INTEGRADA -->
                        <div class="fe-section no-border">
                            <div class="nce-nota-legal" style="margin: 0;">
                                <strong>⚠️ ADVERTENCIA DE TRANSACCIÓN:</strong> Esta acción es completamente irreversible ante los servidores del Ministerio de Hacienda. El DTE seleccionado cambiará permanentemente su estado a "ANULADO".
                            </div>
                        </div>

                        <!-- ACCIONES DEL FORMULARIO CON BOTONES ORIGINALES -->
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='historial_dte.php';">
                                Cancelar
                            </button>
                            
                            <button type="submit" class="btn-save" 
                                    onclick="return confirm('¿Está totalmente seguro de que desea proceder a invalidar este DTE? Esta acción modificará los registros hacendarios y no se puede deshacer.');">
                                Proceder a Invalidar
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

</body>
</html>