<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado y sea administrador
if (!estaLogueado() || !esAdmin()) {
    header('Location: index.php');
    exit;
}

// Inicializar variables
$error = '';
$success = '';

// Manejar mensajes de la sesión
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Manejar acciones
$action = $_GET['action'] ?? 'list';

// Cambiar estado de pedido
if ($action === 'change_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $pedido_id = $_GET['id']; // Accept as string initially
    $nuevo_estado = $_GET['status'];
    
    $estados_validos = ['pendiente', 'procesado', 'enviado', 'entregado', 'cancelado'];
    
    if (in_array($nuevo_estado, $estados_validos)) {
        try {
            $pdo = conectarDB();
            
            // Verificar y crear columna 'estado' si no existe
            $stmt = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'estado'");
            $has_estado_column = $stmt->rowCount() > 0;
            if (!$has_estado_column) {
                $pdo->exec("ALTER TABLE pedidos ADD COLUMN estado VARCHAR(20) DEFAULT 'pendiente'");
                $has_estado_column = true;
            }
            
            // Verificar y crear columna 'fecha_actualizacion' si no existe
            $stmt = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'fecha_actualizacion'");
            $has_fecha_actualizacion_column = $stmt->rowCount() > 0;
            if (!$has_fecha_actualizacion_column) {
                $pdo->exec("ALTER TABLE pedidos ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                $has_fecha_actualizacion_column = true;
            }
            
            if ($has_estado_column && $has_fecha_actualizacion_column) {
                $stmt = $pdo->prepare("UPDATE pedidos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
                $stmt->execute([$nuevo_estado, $pedido_id]);
                
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                    $success = "Estado del pedido cambiado a: $nuevo_estado";
                    $_SESSION['success'] = $success;
                    header('Location: admin-pedidos.php');
                    exit;
                } else {
                    // Check if the pedido exists
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id = ?");
                    $checkStmt->execute([$pedido_id]);
                    $exists = $checkStmt->fetchColumn();
                    if ($exists > 0) {
                        $error = "No se pudo actualizar el estado a '$nuevo_estado'. Verifique las reglas de transición o permisos.";
                    } else {
                        $error = 'No se encontró el pedido con el ID: ' . htmlspecialchars($pedido_id);
                    }
                }
            } else {
                $error = 'Faltan columnas esenciales en la tabla pedidos';
            }
        } catch (PDOException $e) {
            error_log("Error al cambiar estado (ID: $pedido_id, Estado: $nuevo_estado): " . $e->getMessage());
            $error = 'Error al actualizar el estado: ' . $e->getMessage();
        } catch (Exception $e) {
            error_log("Error general al cambiar estado (ID: $pedido_id, Estado: $nuevo_estado): " . $e->getMessage());
            $error = 'Error inesperado al cambiar el estado: ' . $e->getMessage();
        }
    } else {
        $error = 'Estado no válido';
    }
}

// Ver detalles del pedido
$pedido_detalle = null;
if ($action === 'view' && isset($_GET['id'])) {
    $pedido_id = $_GET['id']; // Accept as string initially
    
    try {
        $pdo = conectarDB();
        
        // Obtener información del pedido
        $stmt = $pdo->prepare("SELECT p.*, u.nombre as usuario_nombre, u.correo as usuario_correo 
                              FROM pedidos p 
                              JOIN usuarios u ON p.usuario_id = u.id 
                              WHERE p.id = ?");
        $stmt->execute([$pedido_id]);
        $pedido_detalle = $stmt->fetch();
        
        if ($pedido_detalle) {
            // Obtener items del pedido
            $stmt = $pdo->prepare("SELECT pi.*, pr.nombre as producto_nombre 
                                  FROM pedido_items pi 
                                  JOIN productos pr ON pi.producto_id = pr.id 
                                  WHERE pi.pedido_id = ?");
            $stmt->execute([$pedido_id]);
            $pedido_detalle['items'] = $stmt->fetchAll();
            
            // Calcular total
            $total = 0;
            foreach ($pedido_detalle['items'] as $item) {
                $total += $item['cantidad'] * $item['precio_unitario'];
            }
            $pedido_detalle['total'] = $total;
        } else {
            $error = 'No se encontró el pedido con el ID proporcionado';
        }
    } catch (Exception $e) {
        error_log("Error al obtener detalles del pedido: " . $e->getMessage());
        $error = 'Error al cargar los detalles del pedido';
        $action = 'list';
    }
}

// Obtener lista de pedidos
try {
    $pdo = conectarDB();
    $stmt = $pdo->query("SELECT p.*, u.nombre as usuario_nombre, u.correo as usuario_correo,
                               COUNT(pi.id) as total_items,
                               SUM(pi.cantidad * pi.precio_unitario) as total_pedido
                        FROM pedidos p 
                        JOIN usuarios u ON p.usuario_id = u.id 
                        LEFT JOIN pedido_items pi ON p.id = pi.pedido_id 
                        GROUP BY p.id 
                        ORDER BY p.fecha DESC");
    $pedidos = $stmt->fetchAll();
} catch (Exception $e) {
    $pedidos = [];
    $error = 'Error al cargar pedidos';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - GraciaShoes</title>
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
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-enviado {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .status-entregado {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelado {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .order-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .order-detail {
                grid-template-columns: 1fr;
            }
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
            <li><a href="admin-pedidos.php" class="active"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
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
            <h1><?php echo $action === 'view' ? 'Detalles del Pedido' : 'Gestión de Pedidos'; ?></h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($action === 'view' && $pedido_detalle): ?>
            <!-- Detalles del Pedido -->
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-receipt"></i> Pedido #<?php echo substr($pedido_detalle['id'], 0, 8); ?></h2>
                    <a href="admin-pedidos.php" class="btn btn-secondary">Volver a la Lista</a>
                </div>
                
                <div class="order-detail">
                    <div>
                        <h3>Información del Cliente</h3>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido_detalle['usuario_nombre']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($pedido_detalle['usuario_correo']); ?></p>
                        
                        <h3>Información del Pedido</h3>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_detalle['fecha'])); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="status-badge status-<?php echo $pedido_detalle['estado']; ?>">
                                <?php echo ucfirst($pedido_detalle['estado']); ?>
                            </span>
                        </p>
                        <p><strong>Total:</strong> $<?php echo number_format($pedido_detalle['total'], 2); ?></p>
                        
                        <h3>Cambiar Estado</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                            <a href="admin-pedidos.php?action=change_status&id=<?php echo $pedido_detalle['id']; ?>&status=pendiente" 
                               class="btn btn-secondary btn-sm">Pendiente</a>
                            <a href="admin-pedidos.php?action=change_status&id=<?php echo $pedido_detalle['id']; ?>&status=procesado" 
                               class="btn btn-secondary btn-sm">Procesado</a>
                            <a href="admin-pedidos.php?action=change_status&id=<?php echo $pedido_detalle['id']; ?>&status=enviado" 
                               class="btn btn-secondary btn-sm">Enviado</a>
                            <a href="admin-pedidos.php?action=change_status&id=<?php echo $pedido_detalle['id']; ?>&status=entregado" 
                               class="btn btn-secondary btn-sm">Entregado</a>
                            <a href="admin-pedidos.php?action=change_status&id=<?php echo $pedido_detalle['id']; ?>&status=cancelado" 
                               class="btn btn-secondary btn-sm">Cancelado</a>
                        </div>
                    </div>
                    
                    <div>
                        <h3>Productos del Pedido</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido_detalle['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                                        <td><?php echo $item['cantidad']; ?></td>
                                        <td>$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                                        <td>$<?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f9f9f9;">
                                    <td colspan="3">Total</td>
                                    <td>$<?php echo number_format($pedido_detalle['total'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Lista de Pedidos -->
            <div class="admin-card">
                <h2><i class="fas fa-shopping-cart"></i> Todos los Pedidos</h2>
                
                <?php if (empty($pedidos)): ?>
                    <p>No hay pedidos registrados.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo substr($pedido['id'], 0, 8); ?>...</td>
                                    <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                                    <td><?php echo $pedido['total_items']; ?> items</td>
                                    <td>$<?php echo number_format($pedido['total_pedido'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                            <?php echo ucfirst($pedido['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin-pedidos.php?action=view&id=<?php echo $pedido['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>