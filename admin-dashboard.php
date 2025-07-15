<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado y sea administrador
if (!estaLogueado() || !esAdmin()) {
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
$usuario = obtenerUsuarioActual();
if (!$usuario) {
    header('Location: logout.php');
    exit;
}

// Obtener estadísticas para el dashboard
$stats = obtenerEstadisticasDashboard();

// Obtener pedidos recientes
$pedidos_recientes = obtenerPedidos(null, 5);

// Obtener productos con stock bajo
$pdo = conectarDB();
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.stock <= 5 
                     ORDER BY p.stock ASC 
                     LIMIT 5");
$productos_stock_bajo = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
     <link rel="icon" type="image/png" href="img/favicon.png">
    <style>
        .admin-sidebar {
             width: 250px;
            background-color: #6d5a42;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 130px;
            z-index: 900;
        }
        
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .admin-sidebar li {
            padding: 0;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .admin-sidebar i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 100px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 1rem;
            color: #666;
            font-weight: normal;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #8b7355;
            margin: 10px 0;
        }
        
        .stat-card .stat-icon {
            font-size: 1.5rem;
            color: #8b7355;
            margin-bottom: 10px;
        }
        
        .admin-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .admin-card h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .admin-card h2 i {
            margin-right: 10px;
            color: #8b7355;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #333;
        }
        
        .admin-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pendiente {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .status-procesado {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelado {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .stock-warning {
            color: #f57c00;
            font-weight: 600;
        }
        
        .stock-danger {
            color: #c62828;
            font-weight: 600;
        }
    </style>
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
                <div style="flex-grow: 1; text-align: center;">
                    <h2 style="margin: 0; color: #8b7355;">Panel de Administración</h2>
                </div>
                <div class="header-actions">
                    <a href="logout.php" class="login-btn">CERRAR SESIÓN</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <ul>
            <li><a href="admin-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin-productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="admin-categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
            <li><a href="admin-pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
            <li><a href="admin-usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="admin-inventario.php"><i class="fas fa-warehouse"></i> Inventario</a></li>
            <li><a href="admin-reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="admin-configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="admin-index.php"><i class="fas fa-home"></i> Ver Tienda</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Bienvenido/a, <?php echo htmlspecialchars($usuario['nombre']); ?></p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <h3>Usuarios</h3>
                <div class="stat-value"><?php echo number_format($stats['total_usuarios'] ?? 0); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <h3>Productos</h3>
                <div class="stat-value"><?php echo number_format($stats['total_productos'] ?? 0); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <h3>Pedidos</h3>
                <div class="stat-value"><?php echo number_format($stats['total_pedidos'] ?? 0); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <h3>Ventas del Mes</h3>
                <div class="stat-value">$<?php echo number_format($stats['ventas_mes'] ?? 0, 2); ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Pedidos Recientes -->
            <div class="admin-card">
                <h2><i class="fas fa-shopping-cart"></i> Pedidos Recientes</h2>
                <?php if (!empty($pedidos_recientes)): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos_recientes as $pedido): ?>
                                <tr>
                                    <td><?php echo substr($pedido['id'], 0, 8); ?>...</td>
                                    <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total_pedido'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                            <?php echo ucfirst($pedido['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="admin-pedidos.php" class="btn btn-secondary">Ver Todos</a>
                    </div>
                <?php else: ?>
                    <p>No hay pedidos recientes.</p>
                <?php endif; ?>
            </div>

            <!-- Productos con Stock Bajo -->
            <div class="admin-card">
                <h2><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h2>
                <?php if (!empty($productos_stock_bajo)): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_stock_bajo as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                    <td>
                                        <?php if ($producto['stock'] <= 0): ?>
                                            <span class="stock-danger"><?php echo $producto['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="stock-warning"><?php echo $producto['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin-inventario.php?action=add&id=<?php echo $producto['id']; ?>" class="btn btn-secondary btn-sm">Añadir Stock</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="admin-inventario.php" class="btn btn-secondary">Gestionar Inventario</a>
                    </div>
                <?php else: ?>
                    <p>No hay productos con stock bajo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="admin-card">
            <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="admin-productos.php?action=new" class="btn btn-primary">Añadir Producto</a>
                <a href="admin-categorias.php?action=new" class="btn btn-primary">Añadir Categoría</a>
                <a href="admin-inventario.php?action=movement" class="btn btn-primary">Registrar Movimiento</a>
                <a href="admin-reportes.php?report=ventas" class="btn btn-secondary">Reporte de Ventas</a>
                <a href="admin-reportes.php?report=inventario" class="btn btn-secondary">Reporte de Inventario</a>
            </div>
        </div>
    </main>
</body>
</html>
