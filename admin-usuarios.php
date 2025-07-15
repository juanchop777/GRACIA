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

// Cambiar rol de usuario
if ($action === 'toggle_role' && isset($_GET['id'])) {
    $usuario_id = $_GET['id'];
    
    try {
        $pdo = conectarDB();
        
        // Obtener usuario actual
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // No permitir cambiar el rol del usuario actual
            if ($usuario_id === $_SESSION['usuario_id']) {
                $error = 'No puedes cambiar tu propio rol';
            } else {
                // Cambiar rol
                $nuevo_rol = $usuario['rol'] === 'admin' ? 'usuario' : 'admin';
                $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                $stmt->execute([$nuevo_rol, $usuario_id]);
                
                $success = "Rol de usuario cambiado a: $nuevo_rol";
            }
        } else {
            $error = 'Usuario no encontrado';
        }
        
        header('Location: admin-usuarios.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        error_log("Error al cambiar rol: " . $e->getMessage());
        $error = 'Error al cambiar el rol del usuario: ' . $e->getMessage();
    }
}

// Eliminar usuario
if ($action === 'delete' && isset($_GET['id'])) {
    $usuario_id = $_GET['id'];
    
    try {
        $pdo = conectarDB();
        
        // No permitir eliminar el usuario actual
        if ($usuario_id === $_SESSION['usuario_id']) {
            $error = 'No puedes eliminar tu propia cuenta';
        } else {
            // Verificar si el usuario tiene pedidos
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $result = $stmt->fetch();
            
            if ($result['total'] > 0) {
                $error = 'No se puede eliminar el usuario porque tiene pedidos asociados';
            } else {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $success = 'Usuario eliminado exitosamente';
            }
        }
        
        header('Location: admin-usuarios.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        $error = 'Error al eliminar el usuario: ' . $e->getMessage();
    }
}

// Obtener lista de usuarios
$usuarios = [];
try {
    $pdo = conectarDB();
    
    // Consulta simple para obtener usuarios con información de pedidos
    $sql = "SELECT u.*, 
                   COALESCE(pedidos_info.total_pedidos, 0) as total_pedidos,
                   COALESCE(pedidos_info.total_gastado, 0) as total_gastado
            FROM usuarios u 
            LEFT JOIN (
                SELECT p.usuario_id,
                       COUNT(p.id) as total_pedidos,
                       COALESCE(SUM(pi.cantidad * pi.precio_unitario), 0) as total_gastado
                FROM pedidos p 
                LEFT JOIN pedido_items pi ON p.id = pi.pedido_id 
                GROUP BY p.usuario_id
            ) as pedidos_info ON u.id = pedidos_info.usuario_id
            ORDER BY u.nombre";
    
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Si falla la consulta compleja, usar una simple
    try {
        $sql = "SELECT *, 0 as total_pedidos, 0 as total_gastado FROM usuarios ORDER BY nombre";
        $stmt = $pdo->query($sql);
        $usuarios = $stmt->fetchAll();
    } catch (Exception $e2) {
        $error = 'Error al cargar usuarios: ' . $e2->getMessage();
        error_log("Error en admin-usuarios.php: " . $e2->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - GraciaShoes</title>
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
        
        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-admin {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .role-usuario {
            background-color: #e8f5e9;
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
            <li><a href="admin-usuarios.php" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="admin-inventario.php"><i class="fas fa-warehouse"></i> Inventario</a></li>
            <li><a href="admin-reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="admin-configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="admin-index.php"><i class="fas fa-home"></i> Ver Tienda</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Usuarios</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Lista de Usuarios -->
        <div class="admin-card">
            <h2><i class="fas fa-users"></i> Usuarios Registrados</h2>
            
            <?php if (empty($usuarios)): ?>
                <p>No hay usuarios registrados en la base de datos.</p>
                <div style="margin-top: 20px;">
                    <a href="crear_admin.php" class="btn btn-primary">Crear Usuario Administrador</a>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo substr($usuario['id'], 0, 8); ?>...</td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $usuario['rol']; ?>">
                                        <?php echo ucfirst($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo $usuario['total_pedidos']; ?></td>
                                <td>$<?php echo number_format($usuario['total_gastado'], 2); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($usuario['id'] !== $_SESSION['usuario_id']): ?>
                                            <a href="admin-usuarios.php?action=toggle_role&id=<?php echo $usuario['id']; ?>" 
                                               class="btn btn-secondary btn-sm"
                                               onclick="return confirm('¿Cambiar el rol de este usuario?');">
                                                <i class="fas fa-user-cog"></i> 
                                                <?php echo $usuario['rol'] === 'admin' ? 'Hacer Usuario' : 'Hacer Admin'; ?>
                                            </a>
                                            <?php if ($usuario['total_pedidos'] == 0): ?>
                                                <a href="admin-usuarios.php?action=delete&id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-secondary btn-sm" 
                                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #666; font-style: italic;">Tu cuenta</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Estadísticas de Usuarios -->
        <div class="admin-card">
            <h2><i class="fas fa-chart-pie"></i> Estadísticas de Usuarios</h2>
            
            <?php
            $total_usuarios = count($usuarios);
            $total_admins = count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; }));
            $total_usuarios_normales = $total_usuarios - $total_admins;
            $usuarios_con_pedidos = count(array_filter($usuarios, function($u) { return $u['total_pedidos'] > 0; }));
            ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #8b7355; font-size: 2rem;"><?php echo $total_usuarios; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Total Usuarios</p>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #c62828; font-size: 2rem;"><?php echo $total_admins; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Administradores</p>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #2e7d32; font-size: 2rem;"><?php echo $total_usuarios_normales; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Usuarios Normales</p>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin: 0; color: #f57c00; font-size: 2rem;"><?php echo $usuarios_con_pedidos; ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Con Pedidos</p>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="admin-card">
            <h2><i class="fas fa-tools"></i> Acciones Rápidas</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="crear_admin.php" class="btn btn-primary">Crear Nuevo Admin</a>
                <a href="registro.php" class="btn btn-secondary">Registrar Usuario</a>
                <a href="index.php" class="btn btn-secondary">Ver Tienda</a>
            </div>
        </div>

        <!-- Información del Usuario Actual -->
        <div class="admin-card">
            <h2><i class="fas fa-user"></i> Tu Información</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'No definido'); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['correo'] ?? 'No definido'); ?></p>
            <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['rol'] ?? 'No definido'); ?></p>
            <p><strong>ID de sesión:</strong> <?php echo htmlspecialchars($_SESSION['usuario_id'] ?? 'No definido'); ?></p>
        </div>
    </main>
</body>
</html>






