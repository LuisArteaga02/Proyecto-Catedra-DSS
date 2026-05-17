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
    <title>Invalidar DTE</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>

<div class="layout">
    <?php include 'navegacion.php'; ?>

    <div class="main-content" style="background-color: #f8f9fa;">
        
        <div class="historial-topbar" style="margin-bottom: 20px;">
            <h1>Evento de Invalidación de DTE</h1>
            <span class="company-name">Pizzeria El Salvador S.A de C.V.</span>
        </div>

        <div style="max-width: 700px; margin: 30px auto; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
            <div style="background: #dc3545; color: white; padding: 25px 30px;">
                <h2 style="margin: 0; font-size: 22px; display: flex; align-items: center; gap: 10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Anular Documento Electrónico
                </h2>
                <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; line-height: 1.5;">Ingrese los datos requeridos por el Ministerio de Hacienda para invalidar un DTE (Tipo de Evento 05) que ya fue transmitido o firmado previamente.</p>
            </div>
            
            <?php if (isset($_GET['exito'])): ?>
            <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px 20px; margin: 25px 30px 0 30px; border-radius: 6px; font-size: 15px;">
                <strong style="display: block; margin-bottom: 5px;">¡DTE Invalidado con éxito!</strong>
                El estado del documento ha cambiado a ANULADO y se ha generado el JSON del Evento de Invalidación.<br>
                <span style="font-size: 13px; color: #383d41; margin-top: 5px; display: inline-block;">UUID del evento: <strong><?= htmlspecialchars($_GET['uuid'] ?? '') ?></strong></span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px 20px; margin: 25px 30px 0 30px; border-radius: 6px; font-size: 15px;">
                <strong>Error al procesar:</strong> <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <?php endif; ?>

            <form action="procesar_invalidacion.php" method="POST" style="padding: 35px;">
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Código de Generación (UUID del DTE) <span style="color:#dc3545">*</span></label>
                    <input type="text" name="codigo_generacion" required style="width: 100%; padding: 14px; border: 1px solid #ced4da; border-radius: 6px; font-family: monospace; font-size: 15px; box-sizing: border-box;" placeholder="Ej. B44DB3DA-A612-DEEF-CF0C-8C200CBC1CE5">
                    <small style="color: #6c757d; display: block; margin-top: 6px;">Si no lo recuerda, puede encontrar este código en la sección de <a href="historial_dte.php" style="color: #0d6efd;">Historial de DTEs</a>.</small>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Nombre del Responsable <span style="color:#dc3545">*</span></label>
                    <input type="text" name="responsable" required style="width: 100%; padding: 14px; border: 1px solid #ced4da; border-radius: 6px; font-size: 15px; box-sizing: border-box;" placeholder="Nombre completo de quien autoriza la anulación">
                </div>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Motivo de Invalidación <span style="color:#dc3545">*</span></label>
                    <textarea name="motivo_invalidacion" required style="width: 100%; padding: 14px; border: 1px solid #ced4da; border-radius: 6px; font-size: 15px; resize: vertical; min-height: 120px; box-sizing: border-box; font-family: inherit;" placeholder="Explique claramente el motivo (ej. Error en montos, el cliente desistió de la compra, documento duplicado, etc.)"></textarea>
                </div>
                
                <div style="background: #fff3cd; color: #856404; padding: 16px 20px; border-radius: 6px; border-left: 5px solid #ffeeba; margin-bottom: 30px; font-size: 14px; line-height: 1.5;">
                    <strong style="display: block; margin-bottom: 4px;">⚠️ Advertencia:</strong> 
                    Esta acción es irreversible. El DTE seleccionado cambiará su estado interno a "ANULADO" y se generará un archivo JSON con el <strong>Evento de Invalidación</strong> correspondiente.
                </div>

                <div style="display: flex; justify-content: flex-end; align-items: center;">
                    <a href="historial_dte.php" style="padding: 12px 24px; color: #6c757d; text-decoration: none; font-weight: 600; margin-right: 15px; transition: color 0.2s;" onmouseover="this.style.color='#333'" onmouseout="this.style.color='#6c757d'">Cancelar</a>
                    <button type="submit" style="background: #dc3545; color: white; border: none; padding: 14px 28px; border-radius: 6px; font-weight: 600; font-size: 16px; cursor: pointer; transition: background 0.2s; box-shadow: 0 2px 4px rgba(220,53,69,0.3);" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                        Proceder a Invalidar
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

</body>
</html>
