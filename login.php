<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // IMPORTANTE: Los nombres en $_POST deben coincidir con el 'name' de los inputs
    $user_input = $_POST['user'] ?? '';
    $pass_input = $_POST['pass'] ?? '';

    // Credenciales quemadas para el avance del lunes
    $user_valido = "lcartagena@pizzeria.com.sv";
    $pass_valida = "123456";

    if ($user_input === $user_valido && $pass_input === $pass_valida) {
        $_SESSION['usuario_nombre'] = "Luis Cartagena";
        $_SESSION['usuario_rol'] = "Cajero - Sucursal central";
        $_SESSION['usuario_iniciales'] = "LC";
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Pizzeria El Salvador</title>
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <div class="login-brand-side">
            <div class="brand-content">
                <div class="brand-logo-container">
                    <img src="img/pizza.png" alt="Logo Pizzeria" class="pizza-icon">
                    <div class="brand-text">
                        <h1>Pizzeria El Salvador</h1>
                        <p>Sistema DTE - Facturacion electronica</p>
                    </div>
                </div>
                <p class="legal-text">
                    Autorizado por el Ministerio de Hacienda de El Salvador bajo la normativa de facturación electrónica 2026.
                </p>
            </div>

            <div class="brand-footer">
                <div class="status-pill">
                    <span class="dot"></span> Producción - MH conectado
                </div>
                <p class="footer-note">El nombre del usuario sesion queda registrado en cada DTE emitido.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="form-container">
                <h2>Iniciar sesion</h2>
                <p class="subtitle">Ingrese sus credenciales para acceder al sistema</p>

                <?php if ($error): ?>
                    <p style="color: #d32f2f; background: #fde8e7; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <?= $error ?>
                    </p>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>USUARIO / CORREO ELECTRÓNICO</label>
                        <input type="text" name="user" placeholder="lcartagena@pizzeria.com.sv" required>
                    </div>

                    <div class="form-group">
                        <label>CONTRASEÑA</label>
                        <div class="password-wrapper">
                            <input type="password" name="pass" id="passInput" placeholder="••••••••••••" required>
                            <button type="button" class="btn-show" onclick="togglePass()">Mostrar</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Ingresar al sistema</button>
                    
                    <div class="form-footer">
                        <a href="#">¿Olvido su contraseña? <span class="red-text">Contacte con el administrador</span></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('passInput');
            const btn = document.querySelector('.btn-show');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Ocultar';
            } else {
                input.type = 'password';
                btn.textContent = 'Mostrar';
            }
        }
    </script>
</body>
</html>