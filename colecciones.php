<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colecciones - GraciaShoes</title>
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
                    <a href="colecciones.php" class="active">COLECCIONES</a>
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
    </header>
    <?php if (!isset($_SESSION['usuario_id'])): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <div class="login-form-container">
                <h2>Iniciar Sesión</h2>
                <p>Accede a tu cuenta para una experiencia personalizada</p>
                
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

    <!-- Collections Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Nuestras Colecciones</h1>
                <p>Descubre las últimas tendencias en moda femenina</p>
            </div>

            <div class="collections-grid">
                <!-- Colección Primavera-Verano -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/concha-pica.png" alt="Colección Primavera-Verano">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Primavera-Verano 2024</h2>
                                <p>Colores vibrantes y diseños frescos para la temporada más luminosa del año</p>
                                <a href="tienda.php?coleccion=primavera-verano" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colección Otoño-Invierno -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/tens.jpg" alt="Colección Otoño-Invierno">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Otoño-Invierno 2024</h2>
                                <p>Elegancia y calidez en tonos tierra y texturas sofisticadas</p>
                                <a href="tienda.php?coleccion=otono-invierno" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colección Elegance -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/rosa.jpg" alt="Colección Elegance">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Elegance Collection</h2>
                                <p>Piezas atemporales para ocasiones especiales y eventos formales</p>
                                <a href="tienda.php?coleccion=elegance" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colección Casual Chic -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/leopard.jpg" alt="Colección Casual Chic">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Casual Chic</h2>
                                <p>Comodidad y estilo para el día a día de la mujer moderna</p>
                                <a href="tienda.php?coleccion=casual-chic" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colección Exclusive -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/pg.jpg" alt="Colección Exclusive">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Exclusive Limited</h2>
                                <p>Piezas únicas y limitadas para mujeres que buscan exclusividad</p>
                                <a href="tienda.php?coleccion=exclusive" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colección Accessories -->
                <div class="collection-item">
                    <div class="collection-image">
                        <img src="img/dorado.jpg" alt="Colección Accessories">
                        <div class="collection-overlay">
                            <div class="collection-content">
                                <h2>Signature Accessories</h2>
                                <p>Accesorios que complementan y elevan cualquier outfit</p>
                                <a href="tienda.php?coleccion=accessories" class="collection-btn">Ver Colección</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Categorías -->
            <div class="categories-section">
                <h2>Explora por Categoría</h2>
                <div class="categories-grid">
                    <div class="category-card" onclick="location.href='tienda.php?categoria=zapatos'">
                        <img src="img/cafe.jpg" alt="Zapatos">
                        <h3>Zapatos</h3>
                        <p>Desde elegantes tacones hasta cómodos flats</p>
                    </div>
                    <div class="category-card" onclick="location.href='tienda.php?categoria=bolsos'">
                        <img src="img/negro.jpg" alt="Bolsos">
                        <h3>Bolsos</h3>
                        <p>Carteras y bolsos para cada ocasión</p>
                    </div>
                    <div class="category-card" onclick="location.href='tienda.php?categoria=accesorios'">
                        <img src="img/accesi.jpg" alt="Accesorios">
                        <h3>Accesorios</h3>
                        <p>Detalles que marcan la diferencia</p>
                    </div>
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
                    <p><i class="fas fa-envelope"></i>  info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i>  +57 3116448364</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            openLoginModal();
        });
    </script>
    <?php endif; ?>
</body>
</html>