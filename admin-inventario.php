<?php
session_start();
require_once 'config.php';

// Verificar que el usuario sea administrador
if (!estaLogueado() || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Obtener productos y categorías
try {
    $pdo = conectarDB();
    $stmt = $pdo->query("
        SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.nombre
    ");
    $productos = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
} catch (Exception $e) {
    $mensaje = 'Error al cargar datos: ' . $e->getMessage();
    $tipo_mensaje = 'error';
    $productos = [];
    $categorias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - GraciaShoes</title>
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
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .stock-low {
            color: #c62828;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #2e7d32;
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
            <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin-productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="admin-categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
            <li><a href="admin-pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
            <li><a href="admin-usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="admin-inventario.php" class="active"><i class="fas fa-warehouse"></i> Inventario</a></li>
            <li><a href="admin-reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="admin-configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="admin-index.php"><i class="fas fa-home"></i> Ver Tienda</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Inventario</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h2><i class="fas fa-warehouse"></i> Inventario Actual</h2>
            
            <?php if (empty($productos)): ?>
                <p>No hay productos en el inventario.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                                <td>
                                    <span class="<?php echo $producto['stock'] <= 5 ? 'stock-low' : 'stock-ok'; ?>">
                                        <?php echo $producto['stock']; ?> unidades
                                    </span>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo $producto['categoria_nombre'] ?: 'Sin categoría'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Estadísticas de Inventario -->
        <div class="admin-card">
            <h2><i class="fas fa-chart-pie"></i> Estadísticas de Inventario</h2>
            
            <?php
            $total_productos = count($productos);
            $total_stock = array_sum(array_column($productos, 'stock'));
            $productos_sin_stock = count(array_filter($productos, function($p) { return $p['stock'] == 0; }));
            $valor_inventario = array_sum(array_map(function($p) { return $p['precio'] * $p['stock']; }, $productos));
            ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #8b7355; font-size: 1.5rem;"><?php echo $total_productos; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Total Productos</p>
                </div>
                
                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #2e7d32; font-size: 1.5rem;"><?php echo $total_stock; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Unidades en Stock</p>
                </div>
                
                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #c62828; font-size: 1.5rem;"><?php echo $productos_sin_stock; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Sin Stock</p>
                </div>
                
                <div style="text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #f57c00; font-size: 1.5rem;">$<?php echo number_format($valor_inventario, 0); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Valor Inventario</p>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="admin-card">
            <h2><i class="fas fa-tools"></i> Acciones Rápidas</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="admin-productos.php" class="btn btn-primary">Gestionar Productos</a>
                <a href="admin-categorias.php" class="btn btn-secondary">Gestionar Categorías</a>
                <a href="tienda.php" class="btn btn-secondary" target="_blank">Ver Tienda</a>
            </div>
        </div>
    </main>
</body>
</html>