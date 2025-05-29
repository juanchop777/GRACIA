<?php
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php"><h1>GraciaShoes</h1></a>
                </div>
                <nav class="nav">
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php">CONTACTO</a>
                </nav>
                <div class="header-actions">
                    <a href="dashboard.php" class="login-btn">MI CUENTA</a>
                    <div class="cart">
                        <span class="cart-icon">🛍️</span>
                        <span class="cart-count">(0)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="main-content">
        <div class="container">
            <div class="dashboard">
                <div class="dashboard-container">
                    <div class="dashboard-header">
                        <div class="dashboard-title">
                            <h1>Mi Cuenta</h1>
                            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                        </div>
                        <a href="logout.php" class="btn btn-secondary">Cerrar sesión</a>
                    </div>
                    
                    <div class="dashboard-grid">
                        <div class="dashboard-card">
                            <h2>Mi Perfil</h2>
                            <p>Gestiona tu información personal y preferencias</p>
                            <button class="btn btn-primary" onclick="location.href='perfil.php'">Ver perfil</button>
                        </div>
                        
                        <div class="dashboard-card">
                            <h2>Mis Pedidos</h2>
                            <p>Revisa el estado de tus pedidos y compras anteriores</p>
                            <button class="btn btn-primary" onclick="location.href='mis-pedidos.php'">Ver pedidos</button>
                        </div>
                        
                        <div class="dashboard-card">
                            <h2>Lista de Deseos</h2>
                            <p>Productos que has guardado para comprar más tarde</p>
                            <button class="btn btn-primary" onclick="location.href='lista-deseos.php'">Ver lista</button>
                        </div>
                        
                        <div class="dashboard-card">
                            <h2>Ir a la Tienda</h2>
                            <p>Explora nuestro catálogo completo de productos</p>
                            <button class="btn btn-primary" onclick="location.href='tienda.php'">Ver productos</button>
                        </div>
                        
                        <div class="dashboard-card">
                            <h2>Direcciones</h2>
                            <p>Gestiona tus direcciones de envío</p>
                            <button class="btn btn-primary" onclick="location.href='direcciones.php'">Gestionar direcciones</button>
                        </div>
                        
                        <div class="dashboard-card">
                            <h2>Métodos de Pago</h2>
                            <p>Administra tus tarjetas y métodos de pago</p>
                            <button class="btn btn-primary" onclick="location.href='metodos-pago.php'">Gestionar pagos</button>
                        </div>
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
                    <p>📧 info@graciashoes.com</p>
                    <p>📱 +57 3116448364</p>
                    <p>📍 Rivera Huila (centro arriba)</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 GraciaShoes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>