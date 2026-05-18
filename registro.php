<?php
session_start();
require_once 'class/Database.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aqui recibimos los datos del form. Asegúrense de que los 'name' coincidan
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $pass = $_POST['pass'] ?? '';
    $pass_confirm = $_POST['pass_confirm'] ?? '';

    // Validaciones básicas para cuidar la integridad de nuestra BD
    if (empty($nombre) || empty($correo) || empty($pass) || empty($pass_confirm)) {
        $error = "Por favor, completa todos los campos para continuar.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Ese correo no parece válido, ¡revísalo bien!";
    } elseif ($pass !== $pass_confirm) {
        $error = "Las contraseñas no coinciden. Inténtalo de nuevo.";
    } elseif (strlen($pass) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres por seguridad.";
    } else {
        try {
            // Instanciamos nuestra conexión. La clase Database ya maneja el try-catch de la conexión inicial
            $db = new Database();
            $conn = $db->getConnection();

            // Verificamos si el correo ya existe para no tener usuarios duplicados
            $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Ya existe un usuario registrado con este correo electrónico.";
            } else {
                // Guardamos el usuario nuevo
                // Hasheamos la contraseña
                $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                
                // Verificamos si existe al menos un emisor en la base de datos
                // Como las tablas catálogo podrían estar vacías, esto evita el error de llave foránea
                $result_emisor = $conn->query("SELECT id_emisor FROM emisor LIMIT 1");
                
                if ($result_emisor && $result_emisor->num_rows > 0) {
                    $row_emisor = $result_emisor->fetch_assoc();
                    $id_emisor = $row_emisor['id_emisor'];
                } else {
                    // Si no hay emisor, creamos uno por defecto saltándonos las llaves foráneas temporalmente
                    // para que no falle si los catálogos están vacíos
                    $conn->query("SET FOREIGN_KEY_CHECKS=0");
                    $conn->query("INSERT INTO emisor (nit, nrc, nombre, tipo_establecimiento, codigo_actividad_economica, municipio, departamento, correo, telefono) 
                                  VALUES ('0000-000000-000-0', '000000-0', 'Pizzeria El Salvador (Default)', '01', '00000', '01', '01', 'info@pizzeria.com.sv', '2222-2222')");
                    $id_emisor = $conn->insert_id;
                    $conn->query("SET FOREIGN_KEY_CHECKS=1");
                }
                
                $insert_stmt = $conn->prepare("INSERT INTO usuario (id_emisor, nombre, correo, contrasena_hash) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("isss", $id_emisor, $nombre, $correo, $pass_hash);
                
                if ($insert_stmt->execute()) {
                    $success = "¡Usuario creado exitosamente! Ya puedes iniciar sesión.";
                } else {
                    $error = "Uy, hubo un problema al guardar el usuario en la base de datos.";
                }
            }
        } catch (Exception $e) {
            // Capturamos cualquier otro error de BD
            $error = "Error del sistema: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — Pizzeria El Salvador</title>
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
                <p class="footer-note">Crea tu cuenta para acceder al sistema de facturación.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="form-container">
                <h2>Crear usuario</h2>
                <p class="subtitle">Complete los datos para registrar un nuevo usuario en el sistema</p>

                <!-- Bloque para mostrar mensajes de error -->
                <?php if ($error): ?>
                    <p style="color: #d32f2f; background: #fde8e7; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <?= $error ?>
                    </p>
                <?php endif; ?>

                <!-- Bloque para mostrar mensaje de éxito -->
                <?php if ($success): ?>
                    <p style="color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <?= $success ?> <br><br>
                        <a href="login.php" style="color: #2e7d32; font-weight: bold; text-decoration: underline;">Ir al login</a>
                    </p>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>NOMBRE COMPLETO</label>
                        <input type="text" name="nombre" placeholder="Ej. Juan Pérez" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>CORREO ELECTRÓNICO</label>
                        <input type="email" name="correo" placeholder="correo@pizzeria.com.sv" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>CONTRASEÑA</label>
                        <div class="password-wrapper">
                            <input type="password" name="pass" id="passInput" placeholder="••••••••••••" required>
                            <button type="button" class="btn-show" onclick="togglePass('passInput', this)">Mostrar</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>CONFIRMAR CONTRASEÑA</label>
                        <div class="password-wrapper">
                            <input type="password" name="pass_confirm" id="passConfirmInput" placeholder="••••••••••••" required>
                            <button type="button" class="btn-show" onclick="togglePass('passConfirmInput', this)">Mostrar</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Registrarse</button>
                    
                    <div class="form-footer">
                        <a href="login.php">¿Ya tienes cuenta? <span class="red-text">Inicia sesión aquí</span></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para alternar la visibilidad de las contraseñas.
        // Recibe el ID del input y el botón para cambiar su texto dinámicamente.
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
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
