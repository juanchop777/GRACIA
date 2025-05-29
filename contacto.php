<?php 
session_start();

$mensaje_enviado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí procesarías el formulario de contacto
    $mensaje_enviado = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - GraciaShoes</title>
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
                        <img src="img/gracie.png" alt="GraciaShoes" style="height:70px;">
                    </a>
                </div>
                <nav class="nav">
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php" class="active">CONTACTO</a>
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

    <!-- Contact Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Contáctanos</h1>
                <p>Estamos aquí para ayudarte con cualquier consulta</p>
            </div>

            <?php if ($mensaje_enviado): ?>
                <div class="alert alert-success">
                    ¡Gracias por tu mensaje! Te responderemos pronto.
                </div>
            <?php endif; ?>

            <div class="contact-content">
                <div class="contact-form-section">
                    <h2>Envíanos un Mensaje</h2>
                    <form action="contacto.php" method="POST" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono (Opcional)</label>
                            <input type="tel" id="telefono" name="telefono">
                        </div>
                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <select id="asunto" name="asunto" required>
                                <option value="">Selecciona un asunto</option>
                                <option value="consulta-producto">Consulta sobre producto</option>
                                <option value="pedido">Estado de pedido</option>
                                <option value="devolucion">Devolución o cambio</option>
                                <option value="sugerencia">Sugerencia</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mensaje">Mensaje</label>
                            <textarea id="mensaje" name="mensaje" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                    </form>
                </div>

                <div class="contact-info-section">
                    <h2>Información de Contacto</h2>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-details">
                                <h3>Dirección</h3>
                                <p>Rivera Huila<br>Centro Abajo</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-details">
                                <h3>Teléfono</h3>
                                <p>+57 3116448364</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p>dacar04@graciashoes.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <div class="contact-details">
                                <h3>Horarios</h3>
                                <p>Lunes a Viernes: 9:00 AM - 7:00 PM<br>
                                Sábados: 10:00 AM - 6:00 PM<br>
                                Domingos: 12:00 PM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="social-media">
                        <h3>Síguenos</h3>
                        <div class="social-links">
                            <a href="https://www.facebook.com/profile.php?id=61550890413231" target="_blank" class="social-link"><i class="fab fa-facebook"></i>
 Facebook</a>
                            <a href="https://www.instagram.com/graciashoes17/" target="_blank" class="social-link"><i class="fab fa-instagram"></i>
 Instagram</a>
                            <a href="https://wa.me/573116448364" target="_blank" class="social-link"><i class="fab fa-whatsapp"></i>
 WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa (placeholder) -->
            <div class="map-section">
                <h2>Nuestra Ubicación</h2>
                <div class="map-placeholder">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3985.127528108765!2d-75.25992462552962!3d2.7786044553197264!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3b6c1990ada923%3A0x419a2a615a3ac9fd!2sCl.%205%20%237-2%2C%20Centro%2C%20Rivera%2C%20Huila!5e0!3m2!1ses-419!2sco!4v1748541679342!5m2!1ses-419!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
</body>
</html>