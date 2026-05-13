<?php
/**
 * navegacion.php
 * Componente reutilizable del sidebar para el Sistema DTE
 * 
 * Se invoca con: <?php include 'navegacion.php'; ?>
 *
 * La logica de este sidebar aún se falta establecer, asi que es solo un ejemplo
 */

// Valores desde la sesión activa, con fallback a los ejemplos por defecto
$pagina_activa      = $pagina_activa      ?? 'board';
$usuario_nombre     = $_SESSION['usuario_nombre'] ?? 'Luis Cartagena';
$usuario_rol        = $_SESSION['usuario_rol'] ?? 'Cajero - Sucursal central';
$usuario_iniciales  = $_SESSION['usuario_iniciales'] ?? 'LC';

/**
 * Helper: esto devuelve 'active' si la página coincide con la activa. Osea, que el glow en el sidebar coincida
 */
function nav_class(string $pagina, string $activa): string {
    return $pagina === $activa ? ' active' : '';
}
?>

<aside class="sidebar">

    <!-- Branding -->
    <div class="sidebar-brand">
        <div>
            <img src="img/pizza.png" alt="Logo Pizzeria" class="sidebar-img">
        </div>
        <div class="brand-text">
            <span class="brand-name">Pizzeria El Salvador</span>
            <span class="brand-sub">Sistema DTE</span>
        </div>
    </div>

    <!-- Pill de estado MH || Esto es para ver si el sistema esta conectado con la API de Hacienda -->
    <span class="status-pill">
        <span class="dot"></span>
        Producción - MH conectado
    </span>

    <!-- Usuario activo -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?= htmlspecialchars($usuario_iniciales) ?>
            <span class="online-dot"></span>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($usuario_nombre) ?></span>
            <span class="user-role"><?= htmlspecialchars($usuario_rol) ?></span>
        </div>
    </div>

    <!-- Sección: Panel -->
    <nav class="nav-section">
        <span class="nav-section-label">Panel</span>
        <a href="index.php" class="nav-item<?= nav_class('BOARD', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=I" alt="Inicio" class="nav-img"></span>
            Inicio
        </a>
    </nav>

    <!-- Sección: Facturación -->
    <nav class="nav-section">
        <span class="nav-section-label">Facturación</span>

        <a href="factura_fe.php" class="nav-item<?= nav_class('FE', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=F" alt="Nueva factura" class="nav-img"></span>
            <span class="nav-item-text">Factura consumidor - FE 01</span>
        </a>

        <a href="factura_ccf.php" class="nav-item<?= nav_class('CCF', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=C" alt="Credito fiscal" class="nav-img"></span>
            <span class="nav-item-text">Crédito fiscal - CCF 03</span>
        </a>

        <a href="index.php" class="nav-item<?= nav_class('NCE', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=N" alt="Nota de credito" class="nav-img"></span>
            <span class="nav-item-text">Nota de crédito - NCE 05</span>
        </a>

        <a href="index.php" class="nav-item<?= nav_class('INVALIDACION', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=X" alt="Invalidar DTE" class="nav-img"></span>
            <span class="nav-item-text">Invalidar DTE</span>
        </a>
    </nav>

    <!-- Sección: Consultas -->
    <nav class="nav-section">
        <span class="nav-section-label">Consultas</span>
        <a href="historial_dte.php" class="nav-item<?= nav_class('HISTORIAL', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=H" alt="Historial" class="nav-img"></span>
            Historial de DTEs
        </a>
    </nav>

    <!-- Sección: Reportes -->
    <nav class="nav-section">
        <span class="nav-section-label">Reportes</span>
        <a href="index.php" class="nav-item<?= nav_class('REPORTES', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=R" alt="Reportes" class="nav-img"></span>
            Reportes
        </a>
    </nav>

    <!-- Sección: Configuración -->
    <nav class="nav-section">
        <span class="nav-section-label">Configuracion</span>
        <a href="index.php" class="nav-item<?= nav_class('CONFIGURACION', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=C" alt="Configuracion" class="nav-img"></span>
            Configuracion
        </a>
    </nav>

    <!-- Espaciador -->
    <div class="sidebar-spacer"></div>

    <!-- Cerrar sesión -->
    <div class="sidebar-logout">
        <a href="logout.php" class="nav-item">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=X" alt="Cerrar sesion" class="nav-img"></span>
            Cerrar sesion
        </a>
    </div>

</aside>