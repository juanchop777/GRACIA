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

// Database connection
$host = 'localhost';
$dbname = 'graciashoes';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch products
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen FROM productos");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error de conexión a la base de datos: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraciaShoes - Vista de Tienda (Admin)</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
     <link rel="icon" type="image/png" href="img/favicon.png">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 8px;
            text-align: center;
        }
        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
        .product-preview {
            margin-top: 20px;
        }
        .product-preview img {
            max-width: 100%;
            height: auto;
        }
        .cta-btn, .category-btn {
            cursor: pointer;
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="admin-index.php">
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height: 70px;">
                    </a>
                </div>
                <nav class="nav">
                    <a href="admin-index.php" class="active">INICIO</a>
                    <a href=>TIENDA</a>
                    <a href=>NOSOTROS</a>
                    <a href=>COLECCIONES</a>
                    <a href=>CONTACTO</a>
                    <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </nav>
                <div class="header-actions">
                    <?php if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] === 'ADMIN'): ?>
                        <a href="admin-dashboard.php" class="login-btn">VOLVER A PANEL ADMIN</a>
                    <?php else: ?>
                        <button class="login-btn" onclick="openLoginModal()" style="display: none;">INICIAR SESIÓN</button>
                    <?php endif; ?>
                    <div class="cart" onclick="openCartModal()" style="display: none;">
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
                        <?php if (!empty($products) && isset($products[0])): ?>
                            <button class="cta-btn" onclick="openModal(<?php echo $products[0]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="cta-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="img/bolso-pica.png" alt="Bolsos de diseño exclusivo" style="width:100vw; max-width:100vw; object-fit:cover;">
                    <div class="carousel-content">
                        <h2>Bolsos de Diseño Exclusivo</h2>
                        <p>Carteras y bolsos que combinan funcionalidad con diseño exclusivo para complementar tu elegancia natural.</p>
                        <?php if (!empty($products) && isset($products[1])): ?>
                            <button class="cta-btn" onclick="openModal(<?php echo $products[1]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="cta-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="carousel-slide">
                    <img src="img/acc-pica.png" alt="Accesorios femeninos" style="width:100vw; max-width:100vw; object-fit:cover;">
                    <div class="carousel-content">
                        <h2>Accesorios que Marcan la Diferencia</h2>
                        <p>Completa tu look con nuestros accesorios cuidadosamente seleccionados para realzar tu estilo único.</p>
                        <?php if (!empty($products) && isset($products[2])): ?>
                            <button class="cta-btn" onclick="openModal(<?php echo $products[2]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="cta-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
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
                        <?php if (!empty($products) && isset($products[0])): ?>
                            <button class="category-btn" onclick="openModal(<?php echo $products[0]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="category-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-category">
                    <img src="img/xc-removebg-preview.png" alt="Bolsos de diseño" style="max-width: 300px; height: auto; display: block; margin: 0 auto;">
                    <div class="category-content">
                        <h3>Bolsos</h3>
                        <p>Carteras y bolsos que combinan funcionalidad con diseño exclusivo</p>
                        <?php if (!empty($products) && isset($products[1])): ?>
                            <button class="category-btn" onclick="openModal(<?php echo $products[1]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="category-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-category">
                    <img src="img/gg.png" alt="Accesorios femeninos" style="max-width: 300px; height: auto; display: block; margin: 0 auto;">
                    <div class="category-content">
                        <h3>Accesorios</h3>
                        <p>Detalles que marcan la diferencia en tu look diario</p>
                        <?php if (!empty($products) && isset($products[2])): ?>
                            <button class="category-btn" onclick="openModal(<?php echo $products[2]['id']; ?>)">VISTA PREVIA</button>
                        <?php else: ?>
                            <button class="category-btn" disabled>VISTA PREVIA</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Login (Hidden for Admin) -->
    <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'ADMIN'): ?>
    <div id="loginModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">×</span>
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

    <!-- Modal del Carrito (Disabled for Admin) -->
    <div id="cartModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeCartModal()">×</span>
            <div class="cart-container">
                <h2>Carrito de Compras (Vista Solo)</h2>
                <div id="cart-items">
                    <p>Esta vista es solo para previsualización. No se permiten compras.</p>
                </div>
                <div class="cart-total">
                    <h3>Total: $<span id="cart-total">0.00</span></h3>
                </div>
                <div class="cart-actions">
                    <button class="btn btn-secondary" onclick="closeCartModal()">Cerrar</button>
                    <button class="btn btn-primary" disabled>Proceder al Pago (Deshabilitado)</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div class="product-preview">
                <img id="previewImage" src="" alt="Product Preview">
                <h2 id="previewTitle"></h2>
                <p id="previewDescription"></p>
                <p><strong>Precio:</strong> $<span id="previewPrice"></span></p>
                <p><strong>Stock:</strong> <span id="previewStock"></span></p>
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
                        <li><a href="admin-index.php">Inicio</a></li>
                        <li><a href="tienda.php">Tienda</a></li>
                        <li><a href="nosotros.php">Nosotros</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i> +57 3116448364</p>
                    <p><i class="fas fa-map-marker-alt"></i> Rivera Huila (centro arriba)</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 GraciaShoes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Override cart functionality for admin preview
        function openCartModal() {
            document.getElementById('cartModal').style.display = 'block';
        }
        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
        }
        function proceedToCheckout() {
            alert('Esta función está deshabilitada para administradores. Solo vista previa.');
        }

        // Carousel Functions
        let slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("carousel-slide");
            let dots = document.getElementsByClassName("dot");
            if (n > slides.length) { slideIndex = 1 }
            if (n < 1) { slideIndex = slides.length }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex - 1].style.display = "block";
            dots[slideIndex - 1].className += " active";
        }

        function prevSlide() {
            plusSlides(-1);
        }

        function nextSlide() {
            plusSlides(1);
        }

        // Modal Functions
        function openModal(productId) {
            <?php
            if (!empty($products)) {
                foreach ($products as $product) {
                    echo "if (productId == {$product['id']}) {";
                    echo "    document.getElementById('previewImage').src = '{$product['imagen']}' || 'img/default.png';";
                    echo "    document.getElementById('previewTitle').textContent = '{$product['nombre']}' || 'Sin nombre';";
                    echo "    document.getElementById('previewDescription').textContent = '{$product['descripcion']}' || 'Sin descripción';";
                    echo "    document.getElementById('previewPrice').textContent = '{$product['precio']}' ? parseFloat('{$product['precio']}').toFixed(2) : '0.00';";
                    echo "    document.getElementById('previewStock').textContent = '{$product['stock']}' || '0';";
                    echo "} ";
                }
                echo "else {";
                echo "    document.getElementById('previewImage').src = 'img/default.png';";
                echo "    document.getElementById('previewTitle').textContent = 'Producto no encontrado';";
                echo "    document.getElementById('previewDescription').textContent = 'No hay descripción disponible';";
                echo "    document.getElementById('previewPrice').textContent = '0.00';";
                echo "    document.getElementById('previewStock').textContent = '0';";
                echo "}";
            } else {
                echo "document.getElementById('previewImage').src = 'img/default.png';";
                echo "document.getElementById('previewTitle').textContent = 'Sin productos';";
                echo "document.getElementById('previewDescription').textContent = 'No hay productos disponibles';";
                echo "document.getElementById('previewPrice').textContent = '0.00';";
                echo "document.getElementById('previewStock').textContent = '0';";
            }
            ?>
            document.getElementById('previewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>