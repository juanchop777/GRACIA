<?php
session_start();
require_once 'config.php';

// Obtener categoría seleccionada
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - GraciaShoes</title>
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
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height:70px;">
                    </a>
                </div>
                <nav class="nav">
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php" class="active">TIENDA</a>
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

    <!-- Tienda Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Nuestra Tienda</h1>
                <p>Descubre nuestra exclusiva colección de productos para la mujer moderna</p>
            </div>

            <!-- Filtros de Categoría -->
            <div class="category-filters">
                <button class="filter-btn <?php echo $categoria === 'todos' ? 'active' : ''; ?>" 
                        onclick="filterCategory('todos')">Todos los Productos</button>
                <button class="filter-btn <?php echo $categoria === 'zapatos' ? 'active' : ''; ?>" 
                        onclick="filterCategory('zapatos')">Zapatos</button>
                <button class="filter-btn <?php echo $categoria === 'bolsos' ? 'active' : ''; ?>" 
                        onclick="filterCategory('bolsos')">Bolsos</button>
                <button class="filter-btn <?php echo $categoria === 'accesorios' ? 'active' : ''; ?>" 
                        onclick="filterCategory('accesorios')">Accesorios</button>
            </div>

            <!-- Grid de Productos -->
            <div class="products-grid-shop">
                <!-- Zapatos -->
                <div class="product-item" data-category="zapatos">
                    <div class="product-image">
                        <img src="img/elegantes.jpg" alt="Zapatos Elegantes">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Zapatos Elegantes</h3>
                        <p class="product-price">$89.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('zapatos-elegantes', 'Zapatos Elegantes', 89.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="zapatos">
                    <div class="product-image">
                        <img src="img/zapatos-altos.jpg" alt="Tacones Altos">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Tacones Altos</h3>
                        <p class="product-price">$129.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('tacones-altos', 'Tacones Altos', 129.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="zapatos">
                    <div class="product-image">
                        <img src="img/floats.jpg" alt="Flats Cómodos">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Flats Cómodos</h3>
                        <p class="product-price">$69.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('flats-comodos', 'Flats Cómodos', 69.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <!-- Bolsos -->
                <div class="product-item" data-category="bolsos">
                    <div class="product-image">
                       <img src="img/bolsotejido.jpg" alt="Bolso Tejido">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Bolso tejido</h3>
                        <p class="product-price">$159.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('bolso-mano', 'Bolso de Mano Elegante', 159.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="bolsos">
                    <div class="product-image">
                        <img src="img/marmol.jpg" alt="Bolso marmol elegante"> 
                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Bolso marmol elegante</h3>
                        <p class="product-price">$99.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('cartera-crossbody', 'Cartera Crossbody', 99.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="bolsos">
                    <div class="product-image">
                        <img src="img/palma.jpg" alt="Bolso Palma de Iraca">
 
                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Bolso Palma de Iraca</h3>
                        <p class="product-price">$189.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('mochila-cuero', 'Mochila de Cuero', 189.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <!-- Accesorios -->
                <div class="product-item" data-category="accesorios">
                    <div class="product-image">
                        <img src="img/accesorios.jpg" alt="Set de perlas">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Set de perlas</h3>
                        <p class="product-price">$49.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('collar-dorado', 'Collar Dorado', 49.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="accesorios">
                    <div class="product-image">
                        <img src="img/acc-pica.png" alt="Set mar salado">

                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Set mar salado</h3>
                        <p class="product-price">$79.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('aretes-perla', 'Aretes de Perla', 79.99)">Añadir al Carrito</button>
                    </div>
                </div>

                <div class="product-item" data-category="accesorios">
                    <div class="product-image">
                        <img src="img/flores-removebg-preview-pica.png" alt="Set floral">
                        <div class="product-overlay">
                            <button class="quick-view-btn">Vista Rápida</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Set floral</h3>
                        <p class="product-price">$39.99</p>
                        <button class="add-to-cart-btn" onclick="addToCart('pulsera-elegante', 'Pulsera Elegante', 39.99)">Añadir al Carrito</button>
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
    <script>
        function filterCategory(category) {
            const products = document.querySelectorAll('.product-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Actualizar botones activos
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrar productos
            products.forEach(product => {
                if (category === 'todos' || product.dataset.category === category) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
            
            // Actualizar URL
            const url = new URL(window.location);
            url.searchParams.set('categoria', category);
            window.history.pushState({}, '', url);
        }
    </script>
</body>
</html>