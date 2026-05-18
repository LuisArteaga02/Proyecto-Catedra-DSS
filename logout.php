<?php
// 1. Iniciar la sesión para poder manipularla
session_start();

// 2. Limpiar todas las variables de sesión
$_SESSION = array();

// 3. Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no solo los datos de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruir la sesión.
session_destroy();

// 5. Redirigir al usuario al login (o a la página de inicio)
header("Location: login.php");
exit();
?>