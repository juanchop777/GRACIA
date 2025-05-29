<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros - GraciaShoes</title>
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
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php" class="active">NOSOTROS</a>
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

    <!-- About Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Nuestra Historia</h1>
                <p>Conoce la pasión detrás de GraciaShoes</p>
            </div>

            <div class="about-content">
                <div class="about-section">
                    <div class="about-text">
                        <h2>Elegancia que Trasciende</h2>
                        <p>En GraciaShoes, creemos que cada mujer merece sentirse elegante y segura en cada paso que da. Desde nuestros inicios, nos hemos dedicado a curar una colección exclusiva de calzado, bolsos y accesorios que no solo complementan tu estilo, sino que lo elevan.</p>
                        <p>Nuestra pasión por la moda femenina y el compromiso con la calidad nos ha llevado a establecer relaciones con los mejores diseñadores y artesanos, asegurando que cada pieza en nuestra colección cumpla con los más altos estándares de elegancia y comodidad.</p>
                    </div>
                    <div class="about-image">
                        <img src="img/tia-pica.png" alt="Nuestra tienda">
                    </div>
                </div>

                <div class="values-section">
                    <h2>Nuestros Valores</h2>
                    <div class="values-grid">
                        <div class="value-item">
                            <div class="value-icon"><i class="fas fa-star-of-life"></i></div>
                            <h3>Elegancia</h3>
                            <p>Cada producto es seleccionado cuidadosamente para reflejar sofisticación y buen gusto.</p>
                        </div>
                        <div class="value-item">
                            <div class="value-icon"><i class="fas fa-gem"></i></div>
                            <h3>Calidad</h3>
                            <p>Trabajamos solo con materiales premium y artesanos expertos para garantizar durabilidad.</p>
                        </div>
                        <div class="value-item">
                            <div class="value-icon"><i class="fas fa-crown"></i></div>
                            <h3>Exclusividad</h3>
                            <p>Ofrecemos piezas únicas que te harán destacar con tu estilo personal.</p>
                        </div>
                        <div class="value-item">
                            <div class="value-icon"><i class="fas fa-handshake"></i></div>
                            <h3>Servicio</h3>
                            <p>Brindamos una experiencia de compra personalizada y excepcional.</p>
                        </div>
                    </div>
                </div>

                <div class="team-section">
                    <h2>Nuestro Equipo</h2>
                    <div class="team-grid">
                       
                        <div class="team-member">
                            <img src="img/danip.jpg" alt="Gerente-Diseñadora-Fundadora">
                            <h3>Daniela Cardoso</h3>
                            <p>Gerente de Experiencia al Cliente</p>
                            <p>Con más de 15 años de experiencia en moda femenina, Daniela lidera la visión de elegancia accesible.
                                Daniela se encarga de que cada cliente reciba el mejor servicio y atención personalizada.
                                Especialista en tendencias y diseño y asegura que cada colección esté a la vanguardia.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Login -->
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
</body>
</html>