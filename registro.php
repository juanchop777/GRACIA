<?php
session_start();

$error = '';
$success = '';

// Manejar mensajes de la sesión
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
     <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height:70px;">
                    </a>
                </div>
                <nav class="nav">
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php">CONTACTO</a>
                </nav>
                <div class="header-actions">
                    <a href="index.php" class="login-btn">INICIAR SESIÓN</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Crear Cuenta</h1>
                <p>Únete a la familia GraciaShoes y disfruta de una experiencia de compra personalizada</p>
            </div>

            <div class="login-form-container" style="max-width: 500px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form action="procesar_registro.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasena">Confirmar contraseña</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Repite tu contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Crear Cuenta</button>
                </form>
                
                <div class="form-footer">
                    <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GraciaShoes</h3>
                    <p>Elegancia y comodidad en cada paso.</p>
                </div>
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="tienda.php">Tienda</a></li>
                        <li><a href="nosotros.php">Nosotros</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i> +57 3116448364</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
