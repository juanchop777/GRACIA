<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!estaLogueado()) {
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
$usuario = obtenerUsuarioActual();
if (!$usuario) {
    header('Location: logout.php');
    exit;
}

// Obtener pedidos del usuario
$pedidos = obtenerPedidos($usuario['id'], 10);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - GraciaShoes</title>
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
                    <a href="dashboard.php" class="login-btn">MI CUENTA</a>
                    <a href="logout.php" class="login-btn">CERRAR SESIÓN</a>
                    <div class="cart" onclick="openCartModal()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cart-count">(0)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Mi Cuenta</h1>
                    <p>Bienvenido/a, <?php echo htmlspecialchars($usuario['nombre']); ?></p>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Información Personal -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-user"></i> Información Personal</h2>
                    <p>Gestiona tu información de cuenta</p>
                    <div style="margin-top: 1rem;">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                        <p><strong>Rol:</strong> <?php echo ucfirst($usuario['rol']); ?></p>
                    </div>
                </div>

             
                <!-- Acciones Rápidas -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
                    <p>Accesos directos a funciones importantes</p>
                    <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="tienda.php" class="btn btn-secondary">Explorar Tienda</a>
                        <a href="colecciones.php" class="btn btn-secondary">Ver Colecciones</a>
                        <a href="contacto.php" class="btn btn-secondary">Contactar Soporte</a>
                        <a href="pedidos.php" class="btn btn-secondary">Ver Mis Pedidos</a>
                    </div>
                </div>
            </div>

            <!-- Tabla de Pedidos Completa -->
            <?php if (!empty($pedidos)): ?>
            <div style="margin-top: 2rem;">
                <h2>Historial Completo de Pedidos</h2>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Fecha</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo substr($pedido['id'], 0, 8); ?>...</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                            <td><?php echo $pedido['total_items']; ?> items</td>
                            <td>$<?php echo number_format($pedido['total_pedido'] ?? 0, 2); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $pedido['estado']; ?>">
                                    <?php echo ucfirst($pedido['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>

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

    <script src="script.js"></script>
</body>
</html>
