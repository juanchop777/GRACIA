<?php
session_start();
require_once 'config.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header('Location: index.php');
    exit();
}

// Obtener estadísticas reales de la base de datos
try {
    $pdo = conectarDB();
    
    // Contar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'CLIENTE'");
    $totalUsuarios = $stmt->fetch()['total'];
    
    // Contar administradores
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'ADMIN'");
    $totalAdmins = $stmt->fetch()['total'];
    
    // Contar productos (si existe la tabla)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $totalProductos = $stmt->fetch()['total'];
    } catch (Exception $e) {
        $totalProductos = 0;
    }
    
    // Contar pedidos (si existe la tabla)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
        $totalPedidos = $stmt->fetch()['total'];
    } catch (Exception $e) {
        $totalPedidos = 0;
    }
    
    // Calcular ventas totales (si existe la tabla)
    try {
        $stmt = $pdo->query("SELECT SUM(total) as ventas FROM pedidos WHERE estado = 'completado'");
        $result = $stmt->fetch();
        $ventasTotales = $result['ventas'] ?? 0;
    } catch (Exception $e) {
        $ventasTotales = 0;
    }
    
} catch (Exception $e) {
    error_log("Error en admin dashboard: " . $e->getMessage());
    $totalUsuarios = 0;
    $totalAdmins = 0;
    $totalProductos = 0;
    $totalPedidos = 0;
    $ventasTotales = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="icon" type="image/png" href="img/favicon.png">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #8b7355 0%, #6d5a42 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 80px;
        }
        
        .admin-nav {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .admin-nav li a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .admin-nav li a:hover,
        .admin-nav li a.active {
            background-color: #8b7355;
            color: white;
            transform: translateY(-2px);
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease;
            border-left: 4px solid #8b7355;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #8b7355;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #8b7355;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .admin-actions {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .admin-actions h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #8b7355 0%, #6d5a42 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: center;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 115, 85, 0.3);
        }
        
        .action-btn i {
            font-size: 1.2rem;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="nav-wrapper">
                <h1 style="margin: 0; font-size: 2rem;">
                    <i class="fas fa-tachometer-alt"></i> Panel de Administración
                </h1>
                <div class="header-actions">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="admin-dashboard.php" class="active">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a></li>
                <li><a href="admin-productos.php">
                    <i class="fas fa-box"></i> Productos
                </a></li>
                <li><a href="admin-usuarios.php">
                    <i class="fas fa-users"></i> Usuarios
                </a></li>
                <li><a href="admin-pedidos.php">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a></li>
                <li><a href="admin-categorias.php">
                    <i class="fas fa-folder"></i> Categorías
                </a></li>
                <li><a href="index.php" target="_blank">
                    <i class="fas fa-globe"></i> Ver Tienda
                </a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $totalUsuarios; ?></div>
                <div class="stat-label">Usuarios Registrados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-number"><?php echo $totalAdmins; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?php echo $totalProductos; ?></div>
                <div class="stat-label">Productos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo $totalPedidos; ?></div>
                <div class="stat-label">Pedidos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number">$<?php echo number_format($ventasTotales, 0); ?></div>
                <div class="stat-label">Ventas Totales</div>
            </div>
        </div>
        
        <div class="admin-actions">
            <h2>
                <i class="fas fa-bolt"></i> Acciones Rápidas
            </h2>
            <div class="action-grid">
                <a href="admin-producto-nuevo.php" class="action-btn">
                    <i class="fas fa-plus"></i> Añadir Producto
                </a>
                <a href="admin-categoria-nueva.php" class="action-btn">
                    <i class="fas fa-folder-plus"></i> Nueva Categoría
                </a>
                <a href="admin-usuarios.php" class="action-btn">
                    <i class="fas fa-users-cog"></i> Gestionar Usuarios
                </a>
                <a href="admin-reportes.php" class="action-btn">
                    <i class="fas fa-chart-line"></i> Ver Reportes
                </a>
                <a href="admin-configuracion.php" class="action-btn">
                    <i class="fas fa-cog"></i> Configuración
                </a>
                <a href="admin-backup.php" class="action-btn">
                    <i class="fas fa-database"></i> Backup
                </a>
            </div>
        </div>
    </div>
</body>
</html>
