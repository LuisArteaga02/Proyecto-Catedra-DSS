<?php
/**
 * navegacion.php
 * Componente reutilizable del sidebar para el Sistema DTE
 * 
 * Uso: <?php include 'navegacion.php'; ?>
 *
 * Variables opcionales que pueden definirse antes de incluir este archivo:
 *   $pagina_activa  → string: 'inicio' | 'historial' | 'reportes' | 'configuracion'
 *   $usuario_nombre → string: nombre completo del cajero
 *   $usuario_rol    → string: rol del usuario
 *   $usuario_iniciales → string: iniciales para el avatar (ej. "LC")
 */

// Valores por defecto
$pagina_activa      = $pagina_activa      ?? 'inicio';
$usuario_nombre     = $usuario_nombre     ?? 'Luis Cartagena';
$usuario_rol        = $usuario_rol        ?? 'Cajero - Sucursal central';
$usuario_iniciales  = $usuario_iniciales  ?? 'LC';

/**
 * Helper: devuelve 'active' si la página coincide con la activa.
 */
function nav_class(string $pagina, string $activa): string {
    return $pagina === $activa ? ' active' : '';
}
?>
<aside class="sidebar">

    <!-- Branding -->
    <div class="sidebar-brand">
        <div class="brand-logo-placeholder">
            <img src="https://placehold.co/28x28/c0392b/ffffff?text=P" alt="Logo Pizzeria" class="sidebar-img">
        </div>
        <div class="brand-text">
            <span class="brand-name">Pizzeria El Salvador</span>
            <span class="brand-sub">Sistema DTE</span>
        </div>
    </div>

    <!-- Pill de estado MH -->
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
        <a href="index.php" class="nav-item<?= nav_class('inicio', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=I" alt="Inicio" class="nav-img"></span>
            Inicio
        </a>
    </nav>

    <!-- Sección: Consultas -->
    <nav class="nav-section">
        <span class="nav-section-label">Consultas</span>
        <a href="historial.php" class="nav-item<?= nav_class('historial', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=H" alt="Historial" class="nav-img"></span>
            Historial de DTEs
        </a>
    </nav>

    <!-- Sección: Reportes -->
    <nav class="nav-section">
        <span class="nav-section-label">Reportes</span>
        <a href="reportes.php" class="nav-item<?= nav_class('reportes', $pagina_activa) ?>">
            <span class="nav-icon"><img src="https://placehold.co/16x16/888/fff?text=R" alt="Reportes" class="nav-img"></span>
            Reportes
        </a>
    </nav>

    <!-- Sección: Configuración -->
    <nav class="nav-section">
        <span class="nav-section-label">Configuracion</span>
        <a href="configuracion.php" class="nav-item<?= nav_class('configuracion', $pagina_activa) ?>">
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