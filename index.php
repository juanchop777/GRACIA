<?php
session_start();

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'credenciales':
            $error = 'Credenciales incorrectas. Por favor, inténtalo de nuevo.';
            break;
        case 'servidor':
            $error = 'Error del servidor. Por favor, inténtalo más tarde.';
            break;
    }
}

$success = '';
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $success = 'Cuenta creada exitosamente. Ya puedes iniciar sesión.';
}

if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success = 'Has cerrado sesión correctamente.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraciaShoes - Elegancia en Cada Paso</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height: 70px;">
                    </a>
                </div>
                <nav class="nav">
                    <a href="index.php" class="active">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php">CONTACTO</a>
                </nav>
                <div class="header-actions">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'ADMIN'): ?>
                            <a href="admin-dashboard.php" class="login-btn">PANEL ADMIN</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="login-btn">MI CUENTA</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="login-btn" onclick="openLoginModal()">INICIAR SESIÓN</button>
                    <?php endif; ?>
                    <div class="cart" onclick="openCartModal()">
                        <i class="fas fa-shopping-cart"></i>

                        <span class="cart-count" id="cart-count">(0)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section con Carrusel -->
    <section class="hero hero-fullwidth" id="inicio" style="width:100vw; position:relative; left:50%; right:50%; margin-left:-50vw; margin-right:-50vw; padding:0;">
        <div class="carousel-container" style="width:100%; max-width:100vw;">
            <div class="carousel">
                <div class="carousel-slide active">
                    <img src="img/grass-pica.png" alt="Colección de zapatos elegantes" style="width:100vw; max-width:100vw; object-fit:cover;">
                    <div class="carousel-content">
                        <h2>Elegancia en Cada Paso</h2>
                        <p>Descubre nuestra exclusiva colección de calzado, bolsos y accesorios diseñados para la mujer moderna que valora el estilo y la comodidad.</p>
                        <button class="cta-btn" onclick="location.href='tienda.php?categoria=zapatos'">EXPLORAR COLECCIÓN</button>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="img/bolso-pica.png" alt="Bolsos de diseño exclusivo" style="width:100vw; max-width:100vw; object-fit:cover;">
                    <div class="carousel-content">
                        <h2>Bolsos de Diseño Exclusivo</h2>
                        <p>Carteras y bolsos que combinan funcionalidad con diseño exclusivo para complementar tu elegancia natural.</p>
                        <button class="cta-btn" onclick="location.href='tienda.php?categoria=bolsos'">VER BOLSOS</button>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="img/acc-pica.png" alt="Accesorios femeninos" style="width:100vw; max-width:100vw; object-fit:cover;">
                    <div class="carousel-content">
                        <h2>Accesorios que Marcan la Diferencia</h2>
                        <p>Completa tu look con nuestros accesorios cuidadosamente seleccionados para realzar tu estilo único.</p>
                        <button class="cta-btn" onclick="location.href='tienda.php?categoria=accesorios'">VER ACCESORIOS</button>
                    </div>
                </div>
            </div>
            <button class="carousel-btn prev" onclick="prevSlide()">❮</button>
            <button class="carousel-btn next" onclick="nextSlide()">❯</button>
            <div class="carousel-dots">
                <span class="dot active" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
            </div>
        </div>
    </section>

    <!-- Secciones de Productos -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Descubre tu estilo perfecto...</h2>
            
            <div class="products-grid">
                <div class="product-category">
                    <img src="img/gr.png" alt="Zapatos elegantes" style="max-width: 300px; height: auto; display: block; margin: 0 auto;">
                    <div class="category-content">
                        <h3>Calzado</h3>
                        <p>Desde tacones sofisticados hasta flats cómodos para el día a día</p>
                        <button class="category-btn" onclick="location.href='tienda.php?categoria=zapatos'">VER COLECCIÓN</button>
                    </div>
                </div>
                
                <div class="product-category">
                    <img src="img/xc-removebg-preview.png" alt="Bolsos de diseño" style="max-width: 300px; height: auto; display: block; margin: 0 auto;">
                    <div class="category-content">
                        <h3>Bolsos</h3>
                        <p>Carteras y bolsos que combinan funcionalidad con diseño exclusivo</p>
                        <button class="category-btn" onclick="location.href='tienda.php?categoria=bolsos'">VER COLECCIÓN</button>
                    </div>
                </div>
                
                <div class="product-category">
                    <img src="img/gg.png" alt="Accesorios femeninos" style="max-width: 300px; height: auto; display: block; margin: 0 auto;">
                    <div class="category-content">
                        <h3>Accesorios</h3>
                        <p>Detalles que marcan la diferencia en tu look diario</p>
                        <button class="category-btn" onclick="location.href='tienda.php?categoria=accesorios'">VER COLECCIÓN</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Login -->
    <?php if (!isset($_SESSION['usuario_id'])): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <div class="login-form-container">
                <h2>Iniciar Sesión</h2>
                <p>Accede a tu cuenta para una experiencia personalizada</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form action="procesar_login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                </form>
                
                <div class="form-footer">
                    <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                    <a href="recuperar-contrasena.php">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal del Carrito -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCartModal()">&times;</span>
            <div class="cart-container">
                <h2>Carrito de Compras</h2>
                <div id="cart-items">
                    <p>Tu carrito está vacío</p>
                </div>
                <div class="cart-total">
                    <h3>Total: $<span id="cart-total">0.00</span></h3>
                </div>
                <div class="cart-actions">
                    <button class="btn btn-secondary" onclick="closeCartModal()">Seguir Comprando</button>
                    <button class="btn btn-primary" onclick="proceedToCheckout()">Proceder al Pago</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GraciaShoes</h3>
                    <p>Elegancia y comodidad en cada paso. Descubre la colección perfecta para tu estilo único.</p>
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
                    <p><i class="fas fa-envelope"></i>  info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i>  +57 3116448364</p>
                    <p><i class="fas fa-map-marker-alt"></i>  Rivera Huila (centro arriba)</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 GraciaShoes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>