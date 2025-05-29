<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'contrasenas':
            $error = 'Las contraseñas no coinciden.';
            break;
        case 'correo_existe':
            $error = 'El correo electrónico ya está registrado.';
            break;
        case 'servidor':
            $error = 'Error del servidor. Por favor, inténtalo más tarde.';
            break;
        case 'datos':
            $error = 'Por favor, completa todos los campos.';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="header">
                <h1>Crear una cuenta</h1>
                <p>Regístrate para acceder a GraciaShoes</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="procesar_registro.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Tu nombre">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                </div>
                
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar contraseña</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" minlength="6" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Registrarse</button>
            </form>
            
            <div class="footer-links">
                <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>