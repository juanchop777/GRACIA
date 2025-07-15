<?php
session_start();
require_once 'config.php';

// Obtener categoría seleccionada
$categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';

// Verificar si existe la columna 'imagen' en la tabla productos
function verificarColumnaImagen($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Obtener productos y categorías de la base de datos
try {
    $pdo = conectarDB();
    $tieneColumnaImagen = verificarColumnaImagen($pdo);
    
    // Verificar si hay productos en la base de datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock > 0");
    $total_productos_db = $stmt->fetch()['total'];
    
    // Obtener todas las categorías
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
    // Obtener productos con información de categoría
    if ($total_productos_db > 0) {
        if ($tieneColumnaImagen) {
            $stmt = $pdo->query("
                SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.stock > 0
                ORDER BY p.nombre
            ");
        } else {
            $stmt = $pdo->query("
                SELECT p.*, c.nombre as categoria_nombre, NULL as imagen
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.stock > 0
                ORDER BY p.nombre
            ");
        }
        $productos_db = $stmt->fetchAll();
    } else {
        $productos_db = [];
    }
    
} catch (Exception $e) {
    $categorias = [];
    $productos_db = [];
    $tieneColumnaImagen = false;
    $total_productos_db = 0;
}

// Función para obtener el slug de la categoría
function getCategorySlug($categoria_nombre) {
    if (!$categoria_nombre) return 'otros';
    return strtolower(str_replace(' ', '', $categoria_nombre));
}

// Decidir qué productos mostrar
$usar_productos_db = $total_productos_db > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - GraciaShoes</title>
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
                    <a href="tienda.php" class="active">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php">CONTACTO</a>
                </nav>
                <div class="header-actions">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="admin-dashboard.php" class="login-btn">PANEL ADMIN</a>
                        <?php else: ?>
                            <a href="tablero.php" class="login-btn">MI CUENTA</a>
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
                <?php if (!$usar_productos_db): ?>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Mostrando catálogo de demostración
                    </p>
                <?php endif; ?>
            </div>

            <!-- Filtros de Categoría -->
            <div class="category-filters">
                <button class="filter-btn <?php echo $categoria_filtro === 'todos' ? 'active' : ''; ?>" 
                        onclick="filterCategory('todos')">Todos los Productos</button>
                
                <?php if ($usar_productos_db): ?>
                    <?php 
                    $categorias_unicas = [];
                    foreach ($productos_db as $producto) {
                        if ($producto['categoria_nombre'] && !in_array($producto['categoria_nombre'], $categorias_unicas)) {
                            $categorias_unicas[] = $producto['categoria_nombre'];
                        }
                    }
                    ?>
                    
                    <?php foreach ($categorias_unicas as $cat_nombre): ?>
                        <?php $slug = getCategorySlug($cat_nombre); ?>
                        <button class="filter-btn <?php echo $categoria_filtro === $slug ? 'active' : ''; ?>" 
                                onclick="filterCategory('<?php echo $slug; ?>')">
                            <?php echo htmlspecialchars($cat_nombre); ?>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <button class="filter-btn <?php echo $categoria_filtro === 'zapatos' ? 'active' : ''; ?>" 
                            onclick="filterCategory('zapatos')">Zapatos</button>
                    <button class="filter-btn <?php echo $categoria_filtro === 'bolsos' ? 'active' : ''; ?>" 
                            onclick="filterCategory('bolsos')">Bolsos</button>
                    <button class="filter-btn <?php echo $categoria_filtro === 'accesorios' ? 'active' : ''; ?>" 
                            onclick="filterCategory('accesorios')">Accesorios</button>
                <?php endif; ?>
            </div>

            <!-- Grid de Productos -->
            <div class="products-grid-shop">
                <?php if ($usar_productos_db): ?>
                    <?php if (empty($productos_db)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 0;">
                            <h3>No hay productos disponibles</h3>
                            <p>Vuelve pronto para ver nuestras novedades.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($productos_db as $producto): ?>
                            <?php 
                            $categoria_slug = $producto['categoria_nombre'] ? getCategorySlug($producto['categoria_nombre']) : 'otros';
                            $imagen_src = $tieneColumnaImagen && $producto['imagen'] ? 'img/' . $producto['imagen'] : 'img/placeholder.jpg';
                            ?>
                            <div class="product-item" data-category="<?php echo $categoria_slug; ?>">
                                <div class="product-image">
                                    <img src="<?php echo $imagen_src; ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         onerror="this.src='img/placeholder.jpg'">
                                    <div class="product-overlay">
                                        <button class="quick-view-btn" onclick="viewProduct('<?php echo htmlspecialchars($producto['id']); ?>')">Vista Rápida</button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="product-price">$<?php echo number_format($producto['precio'], 2); ?></p>
                                    <?php if ($producto['stock'] > 0): ?>
                                        <button class="add-to-cart-btn" 
                                                onclick="addToCart('<?php echo htmlspecialchars($producto['id']); ?>', '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['precio']; ?>)">
                                            Añadir al Carrito
                                        </button>
                                    <?php else: ?>
                                        <button class="add-to-cart-btn" style="background-color: #ccc; cursor: not-allowed;" disabled>
                                            Agotado
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Productos hardcodeados (catálogo de demostración) -->
                    <div class="product-item" data-category="zapatos">
                        <div class="product-image">
                            <img src="img/elegantes.jpg" alt="Zapatos Elegantes">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('zapatos-elegantes')">Vista Rápida</button>
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
                                <button class="quick-view-btn" onclick="viewProduct('tacones-altos')">Vista Rápida</button>
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
                                <button class="quick-view-btn" onclick="viewProduct('flats-comodos')">Vista Rápida</button>
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
                                <button class="quick-view-btn" onclick="viewProduct('bolso-tejido')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Bolso Tejido</h3>
                            <p class="product-price">$159.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('bolso-tejido', 'Bolso Tejido', 159.99)">Añadir al Carrito</button>
                        </div>
                    </div>

                    <div class="product-item" data-category="bolsos">
                        <div class="product-image">
                            <img src="img/marmol.jpg" alt="Bolso Marmol Elegante"> 
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('bolso-marmol')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Bolso Marmol Elegante</h3>
                            <p class="product-price">$99.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('bolso-marmol', 'Bolso Marmol Elegante', 99.99)">Añadir al Carrito</button>
                        </div>
                    </div>

                    <div class="product-item" data-category="bolsos">
                        <div class="product-image">
                            <img src="img/palma.jpg" alt="Bolso Palma de Iraca">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('bolso-palma')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Bolso Palma de Iraca</h3>
                            <p class="product-price">$189.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('bolso-palma', 'Bolso Palma de Iraca', 189.99)">Añadir al Carrito</button>
                        </div>
                    </div>

                    <!-- Accesorios -->
                    <div class="product-item" data-category="accesorios">
                        <div class="product-image">
                            <img src="img/accesorios.jpg" alt="Set de Perlas">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('set-perlas')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Set de Perlas</h3>
                            <p class="product-price">$49.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('set-perlas', 'Set de Perlas', 49.99)">Añadir al Carrito</button>
                        </div>
                    </div>

                    <div class="product-item" data-category="accesorios">
                        <div class="product-image">
                            <img src="img/acc-pica.png" alt="Set Mar Salado">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('set-mar-salado')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Set Mar Salado</h3>
                            <p class="product-price">$79.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('set-mar-salado', 'Set Mar Salado', 79.99)">Añadir al Carrito</button>
                        </div>
                    </div>

                    <div class="product-item" data-category="accesorios">
                        <div class="product-image">
                            <img src="img/flores-removebg-preview-pica.png" alt="Set Floral">
                            <div class="product-overlay">
                                <button class="quick-view-btn" onclick="viewProduct('set-floral')">Vista Rápida</button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>Set Floral</h3>
                            <p class="product-price">$39.99</p>
                            <button class="add-to-cart-btn" onclick="addToCart('set-floral', 'Set Floral', 39.99)">Añadir al Carrito</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de Login -->
    <?php if (!isset($_SESSION['usuario_id'])): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">×</span>
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

    <!-- Modal de Vista Rápida de Producto -->
    <div id="productModal" class="modal">
        <div class="modal-content" style="max-width: 900px; background: #fff; border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
            <span class="close" onclick="closeProductModal()" style="color: #8b7355; font-size: 1.8rem; cursor: pointer; position: absolute; right: 1.5rem; top: 1.5rem;">×</span>
            <div id="product-detail-container" style="display: flex; flex-wrap: wrap; padding: 2.5rem; gap: 2rem;">
                <div class="loading" style="width: 100%; text-align: center; padding: 2rem;"><p>Cargando información del producto...</p></div>
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
                    <p><i class="fas fa-envelope"></i> info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i> +57 3116448364</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        function filterCategory(category) {
            const products = document.querySelectorAll('.product-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            products.forEach(product => {
                if (category === 'todos' || product.dataset.category === category) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
            
            const url = new URL(window.location);
            url.searchParams.set('categoria', category);
            window.history.pushState({}, '', url);
        }

        function viewProduct(productId) {
            console.log('Intentando cargar producto con ID:', productId);
            if (!productId || productId.trim() === '') {
                console.error('ID de producto vacío o inválido:', productId);
                return;
            }
            const modal = document.getElementById('productModal');
            const container = document.getElementById('product-detail-container');
            
            modal.style.display = 'block';
            container.innerHTML = '<div class="loading" style="width: 100%; text-align: center; padding: 2rem;"><p>Cargando información del producto...</p></div>';
            
           

            const fetchUrl = 'get-product.php?id=' + encodeURIComponent(productId);
            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        console.error('Respuesta no OK para ID', productId, ':', response.status, response.statusText);
                        throw new Error('Error en la respuesta: ' + response.status + ' - ' + response.statusText);
                    }
                    return response.json();
                })
                .then(product => {
                    console.log('Datos recibidos para ID', productId, ':', product);
                    if (product.error) throw new Error('Error del servidor: ' + product.error);
                    if (typeof product !== 'object' || product === null) throw new Error('Datos inválidos recibidos para ID ' + productId);
                    let imageSrc = 'img/placeholder.jpg';
                    let stock = parseInt(product.stock) || 0;
                    if (product.imagen) imageSrc = 'img/' + product.imagen;
                    container.innerHTML = `
                        <div style="flex: 1; min-width: 300px; padding: 1.5rem; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <img src="${imageSrc}" alt="${product.nombre || 'Producto Desconocido'}" 
                                 style="width: 100%; height: auto; border-radius: 8px; object-fit: cover;" 
                                 onerror="this.src='img/placeholder.jpg'">
                        </div>
                        <div style="flex: 2; min-width: 400px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h2 style="color: #2c3e50; margin: 0 0 1rem; font-size: 2rem; font-weight: 600;">${product.nombre || 'Producto Desconocido'}</h2>
                                <p style="color: #8b7355; font-size: 1.8rem; font-weight: bold; margin: 0.5rem 0;">$${parseFloat(product.precio || 0).toFixed(2)}</p>
                                <p style="color: #7f8c8d; line-height: 1.6; margin: 1rem 0;">${product.descripcion || 'No hay descripción disponible.'}</p>
                                <p style="color: #2c3e50; font-weight: 500;"><strong>Categoría:</strong> ${product.categoria_nombre || 'Sin categoría'}</p>
                                <p style="color: #2c3e50; font-weight: 500;"><strong>Disponibilidad:</strong> ${stock > 0 ? 'En stock (' + stock + ' unidades)' : 'Agotado'}</p>
                            </div>
                            <div style="margin-top: 1.5rem;">
                                ${stock > 0 ? `
                                    <button class="btn btn-primary" style="width: 100%; background: #8b7355; color: white; border: none; padding: 1rem; border-radius: 5px; font-size: 1.1rem; cursor: pointer; transition: background 0.3s;"
                                            onmouseover="this.style.background='#938887ff'" 
                                            onmouseout="this.style.background='#8b7355'"
                                            onclick="addToCart('${product.id}', '${product.nombre || 'Producto Desconocido'}', ${parseFloat(product.precio || 0)}); closeProductModal();">
                                        Añadir al Carrito
                                    </button>
                                ` : `
                                    <button class="btn btn-primary" style="width: 100%; background: #bdc3c7; color: #7f8c8d; border: none; padding: 1rem; border-radius: 5px; font-size: 1.1rem; cursor: not-allowed;" disabled>
                                        Agotado
                                    </button>
                                `}
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error al cargar el producto con ID', productId, ':', error);
                    const demoProduct = demoProducts[productId] || { id: productId, nombre: 'Producto Desconocido', precio: 0, imagen: 'img/placeholder.jpg', descripcion: 'No disponible.', stock: 0, categoria_nombre: 'Sin categoría' };
                    let imageSrc = demoProduct.imagen;
                    let stock = demoProduct.stock;
                    container.innerHTML = `
                        <div style="flex: 1; min-width: 300px; padding: 1.5rem; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <img src="${imageSrc}" alt="${demoProduct.nombre}" 
                                 style="width: 100%; height: auto; border-radius: 8px; object-fit: cover;" 
                                 onerror="this.src='img/placeholder.jpg'">
                        </div>
                        <div style="flex: 2; min-width: 400px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h2 style="color: #2c3e50; margin: 0 0 1rem; font-size: 2rem; font-weight: 600;">${demoProduct.nombre}</h2>
                                <p style="color: #e74c3c; font-size: 1.8rem; font-weight: bold; margin: 0.5rem 0;">$${parseFloat(demoProduct.precio).toFixed(2)}</p>
                                <p style="color: #7f8c8d; line-height: 1.6; margin: 1rem 0;">${demoProduct.descripcion}</p>
                                <p style="color: #2c3e50; font-weight: 500;"><strong>Categoría:</strong> ${demoProduct.categoria_nombre}</p>
                                <p style="color: #2c3e50; font-weight: 500;"><strong>Disponibilidad:</strong> ${stock > 0 ? 'En stock (' + stock + ' unidades)' : 'Agotado'}</p>
                            </div>
                            <div style="margin-top: 1.5rem;">
                                ${stock > 0 ? `
                                    <button class="btn btn-primary" style="width: 100%; background: #e74c3c; color: white; border: none; padding: 1rem; border-radius: 5px; font-size: 1.1rem; cursor: pointer; transition: background 0.3s;"
                                            onmouseover="this.style.background='#c0392b'" 
                                            onmouseout="this.style.background='#e74c3c'"
                                            onclick="addToCart('${demoProduct.id}', '${demoProduct.nombre}', ${demoProduct.precio}); closeProductModal();">
                                        Añadir al Carrito
                                    </button>
                                ` : `
                                    <button class="btn btn-primary" style="width: 100%; background: #bdc3c7; color: #7f8c8d; border: none; padding: 1rem; border-radius: 5px; font-size: 1.1rem; cursor: not-allowed;" disabled>
                                        Agotado
                                    </button>
                                `}
                            </div>
                        </div>
                        <p style="color: #e74c3c; text-align: center; width: 100%;">Error: ${error.message}</p>
                    `;
                });
        }
        
        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function addToCart(productId, productName, productPrice) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id: productId, name: productName, price: productPrice, quantity: 1 });
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
        }

        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            document.getElementById('cart-count').textContent = '(' + cart.reduce((sum, item) => sum + item.quantity, 0) + ')';
        }

        function openCartModal() {
            const modal = document.getElementById('cartModal');
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');

            if (cart.length === 0) {
                cartItems.innerHTML = '<p>Tu carrito está vacío</p>';
                cartTotal.textContent = '0.00';
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <div style="margin-bottom: 1.2rem; display: flex; justify-content: space-between; align-items: center; background: #faf8f6; border-radius: 8px; padding: 0.8rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                        <div style="flex: 1; font-weight: 500; color: #2c3e50;">
                            <span style="font-size: 1.05rem;">${item.name}</span>
                            <span style="color: #7f8c8d; font-size: 0.95rem; margin-left: 0.5rem;">$${item.price.toFixed(2)} c/u</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <button style="background: #ffffffff; border: none; border-radius: 4px; padding: 0.2rem 0.6rem; font-size: 1.1rem; cursor: pointer; color: #333;" onclick="updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                            <span style="min-width: 1.5rem; text-align: center; font-size: 1.1rem;">${item.quantity}</span>
                            <button style="background: #ffffffff; border: none; border-radius: 4px; padding: 0.2rem 0.6rem; font-size: 1.1rem; cursor: pointer; color: #333;" onclick="updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                            <button style="background: #e74c3c; border: none; border-radius: 4px; padding: 0.3rem 0.7rem; margin-left: 0.rem; cursor: pointer; color: white; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;" onclick="removeFromCart('${item.id}')" title="Eliminar">
                                <i class='fas fa-trash'></i>
                            </button>
                            <span style="font-weight: 600; color: #a67c52;  font-size: 1.1rem;">$${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                    </div>
                `).join('');
                const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
                cartTotal.textContent = total.toFixed(2);
            }
            modal.style.display = 'block';
        }

        function updateQuantity(productId, newQuantity) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const item = cart.find(item => item.id === productId);
            if (item) {
                if (newQuantity > 0) {
                    item.quantity = newQuantity;
                } else {
                    cart = cart.filter(item => item.id !== productId);
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                openCartModal();
                updateCartCount();
            }
        }

        function removeFromCart(productId) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart = cart.filter(item => item.id !== productId);
            localStorage.setItem('cart', JSON.stringify(cart));
            openCartModal();
            updateCartCount();
        }

        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
        }

                function openLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        window.onload = updateCartCount;
    </script>
    <?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            openLoginModal();
        });
    </script>
    <?php endif; ?>
</body>
</html>