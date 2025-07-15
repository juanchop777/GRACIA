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

// Verificar si existe la columna 'imagen' en la tabla productos
function verificarColumnaImagen($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        $pdo = conectarDB();
        $tieneColumnaImagen = verificarColumnaImagen($pdo);
        
        switch ($accion) {
            case 'agregar':
                $id = generarUUID();
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $precio = floatval($_POST['precio']);
                $stock = intval($_POST['stock']);
                $categoria_id = $_POST['categoria_id'] ?: null;
                
                if (empty($nombre) || $precio <= 0) {
                    throw new Exception('Nombre y precio son obligatorios');
                }
                
                if ($tieneColumnaImagen && isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $imagen = $_FILES['imagen']['name'];
                    $temp_name = $_FILES['imagen']['tmp_name'];
                    $target_dir = "img/";
                    $target_file = $target_dir . basename($imagen);
                    move_uploaded_file($temp_name, $target_file);
                    $stmt = $pdo->prepare("INSERT INTO productos (id, nombre, descripcion, precio, stock, categoria_id, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $nombre, $descripcion, $precio, $stock, $categoria_id, $imagen]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO productos (id, nombre, descripcion, precio, stock, categoria_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $nombre, $descripcion, $precio, $stock, $categoria_id]);
                }
                
                $mensaje = 'Producto agregado exitosamente';
                $tipo_mensaje = 'success';
                break;
                
            case 'editar':
                $id = $_POST['id'];
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $precio = floatval($_POST['precio']);
                $stock = intval($_POST['stock']);
                $categoria_id = $_POST['categoria_id'] ?: null;
                
                if (empty($nombre) || $precio <= 0) {
                    throw new Exception('Nombre y precio son obligatorios');
                }
                
                if ($tieneColumnaImagen && isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $imagen = $_FILES['imagen']['name'];
                    $temp_name = $_FILES['imagen']['tmp_name'];
                    $target_dir = "img/";
                    $target_file = $target_dir . basename($imagen);
                    move_uploaded_file($temp_name, $target_file);
                    $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?, imagen = ? WHERE id = ?");
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id, $imagen, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ? WHERE id = ?");
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id, $id]);
                }
                
                $mensaje = 'Producto actualizado exitosamente';
                $tipo_mensaje = 'success';
                break;
                
            case 'eliminar':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                
                $mensaje = 'Producto eliminado exitosamente';
                $tipo_mensaje = 'success';
                break;
                
            case 'agregar_columna_imagen':
                $stmt = $pdo->exec("ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) DEFAULT NULL");
                $mensaje = 'Columna "imagen" agregada exitosamente. Ahora puedes asignar imágenes a los productos.';
                $tipo_mensaje = 'success';
                break;
                
            case 'migrar_productos':
                // Migrar productos hardcodeados a la base de datos
                $productos_hardcodeados = [
                    ['id' => 'zapatos-elegantes', 'nombre' => 'Zapatos Elegantes', 'descripcion' => 'Zapatos elegantes para ocasiones especiales', 'precio' => 89.99, 'stock' => 15, 'categoria_id' => 'cat-zapatos-001', 'imagen' => 'elegantes.jpg'],
                    ['id' => 'tacones-altos', 'nombre' => 'Tacones Altos', 'descripcion' => 'Tacones altos de diseño exclusivo', 'precio' => 129.99, 'stock' => 10, 'categoria_id' => 'cat-zapatos-001', 'imagen' => 'zapatos-altos.jpg'],
                    ['id' => 'flats-comodos', 'nombre' => 'Flats Cómodos', 'descripcion' => 'Zapatos planos cómodos para uso diario', 'precio' => 69.99, 'stock' => 20, 'categoria_id' => 'cat-zapatos-001', 'imagen' => 'floats.jpg'],
                    ['id' => 'bolso-tejido', 'nombre' => 'Bolso Tejido', 'descripcion' => 'Bolso tejido artesanal de alta calidad', 'precio' => 159.99, 'stock' => 8, 'categoria_id' => 'cat-bolsos-002', 'imagen' => 'bolsotejido.jpg'],
                    ['id' => 'bolso-marmol', 'nombre' => 'Bolso Mármol Elegante', 'descripcion' => 'Bolso con diseño de mármol elegante', 'precio' => 99.99, 'stock' => 12, 'categoria_id' => 'cat-bolsos-002', 'imagen' => 'marmol.jpg'],
                    ['id' => 'bolso-palma', 'nombre' => 'Bolso Palma de Iraca', 'descripcion' => 'Bolso artesanal de palma de iraca', 'precio' => 189.99, 'stock' => 6, 'categoria_id' => 'cat-bolsos-002', 'imagen' => 'palma.jpg'],
                    ['id' => 'set-perlas', 'nombre' => 'Set de Perlas', 'descripcion' => 'Conjunto elegante de collar y aretes de perlas', 'precio' => 49.99, 'stock' => 25, 'categoria_id' => 'cat-accesorios-003', 'imagen' => 'accesorios.jpg'],
                    ['id' => 'set-mar-salado', 'nombre' => 'Set Mar Salado', 'descripcion' => 'Accesorios inspirados en el mar', 'precio' => 79.99, 'stock' => 18, 'categoria_id' => 'cat-accesorios-003', 'imagen' => 'acc-pica.png'],
                    ['id' => 'set-floral', 'nombre' => 'Set Floral', 'descripcion' => 'Conjunto de accesorios con diseño floral', 'precio' => 39.99, 'stock' => 30, 'categoria_id' => 'cat-accesorios-003', 'imagen' => 'flores-removebg-preview-pica.png']
                ];
                
                // Primero crear las categorías si no existen
                $stmt = $pdo->prepare("INSERT IGNORE INTO categorias (id, nombre) VALUES (?, ?)");
                $stmt->execute(['cat-zapatos-001', 'Zapatos']);
                $stmt->execute(['cat-bolsos-002', 'Bolsos']);
                $stmt->execute(['cat-accesorios-003', 'Accesorios']);
                
                // Insertar productos
                $productos_insertados = 0;
                foreach ($productos_hardcodeados as $producto) {
                    if ($tieneColumnaImagen) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO productos (id, nombre, descripcion, precio, stock, categoria_id, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $result = $stmt->execute([$producto['id'], $producto['nombre'], $producto['descripcion'], $producto['precio'], $producto['stock'], $producto['categoria_id'], $producto['imagen']]);
                    } else {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO productos (id, nombre, descripcion, precio, stock, categoria_id) VALUES (?, ?, ?, ?, ?, ?)");
                        $result = $stmt->execute([$producto['id'], $producto['nombre'], $producto['descripcion'], $producto['precio'], $producto['stock'], $producto['categoria_id']]);
                    }
                    if ($stmt->rowCount() > 0) {
                        $productos_insertados++;
                    }
                }
                
                $mensaje = "Se migraron {$productos_insertados} productos a la base de datos. Ahora todos tus productos están centralizados.";
                $tipo_mensaje = 'success';
                break;
        }
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener productos y categorías
try {
    $pdo = conectarDB();
    $tieneColumnaImagen = verificarColumnaImagen($pdo);
    
    // Verificar si hay productos en la base de datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $total_productos_db = $stmt->fetch()['total'];
    
    // Obtener productos con información de categoría
    if ($tieneColumnaImagen) {
        $stmt = $pdo->query("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.nombre
        ");
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.nombre as categoria_nombre, NULL as imagen
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.nombre
        ");
    }
    $productos = $stmt->fetchAll();
    
    // Obtener categorías para el formulario
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
} catch (Exception $e) {
    $mensaje = 'Error al cargar datos: ' . $e->getMessage();
    $tipo_mensaje = 'error';
    $productos = [];
    $categorias = [];
    $tieneColumnaImagen = false;
    $total_productos_db = 0;
}

// Obtener producto para editar si se especifica
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    foreach ($productos as $producto) {
        if ($producto['id'] === $id_editar) {
            $producto_editar = $producto;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - GraciaShoes</title>
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
        
        .product-image-preview {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        
        .stock-low {
            color: #c62828;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #2e7d32;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-migrate {
            background-color: #10b981;
            color: white;
        }
        
        .btn-migrate:hover {
            background-color: #059669;
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
            <li><a href="admin-productos.php" class="active"><i class="fas fa-box"></i> Productos</a></li>
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
            <h1>Gestión de Productos</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- Alertas de configuración -->
        <?php if ($total_productos_db == 0): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>¡Configura tu catálogo!</strong> 
                Actualmente tu tienda muestra productos hardcodeados. Para centralizar todo en la base de datos:
                <form method="POST" style="display: inline; margin-left: 10px;">
                    <input type="hidden" name="accion" value="migrar_productos">
                    <button type="submit" class="btn btn-sm btn-migrate" 
                            onclick="return confirm('¿Quieres migrar todos los productos de la tienda a la base de datos? Esto te permitirá gestionarlos desde el admin.')">
                        <i class="fas fa-database"></i> Migrar productos a la base de datos
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!$tieneColumnaImagen): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>Funcionalidad adicional disponible:</strong> 
                Puedes agregar la capacidad de asignar imágenes a tus productos.
                <form method="POST" style="display: inline; margin-left: 10px;">
                    <input type="hidden" name="accion" value="agregar_columna_imagen">
                    <button type="submit" class="btn btn-sm btn-secondary" 
                            onclick="return confirm('¿Quieres agregar la funcionalidad de imágenes para productos? Esto NO afectará a tus productos existentes.')">
                        <i class="fas fa-plus"></i> Habilitar imágenes para productos
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="form-grid">
            <!-- Formulario de Producto -->
            <div class="admin-card">
                <h2>
                    <i class="fas fa-plus"></i>
                    <?php echo $producto_editar ? 'Editar Producto' : 'Agregar Nuevo Producto'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'editar' : 'agregar'; ?>">
                    <?php if ($producto_editar): ?>
                        <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['nombre']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"><?php echo $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio">Precio *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" 
                               value="<?php echo $producto_editar ? $producto_editar['precio'] : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" min="0" 
                               value="<?php echo $producto_editar ? $producto_editar['stock'] : '0'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" 
                                        <?php echo ($producto_editar && $producto_editar['categoria_id'] === $categoria['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($tieneColumnaImagen): ?>
                    <div class="form-group">
                        <label for="imagen">Imagen</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $producto_editar ? 'Actualizar' : 'Agregar'; ?> Producto
                        </button>
                        
                        <?php if ($producto_editar): ?>
                            <a href="admin-productos.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Estadísticas de Productos -->
            <div class="admin-card">
                <h2><i class="fas fa-chart-pie"></i> Estadísticas de Productos</h2>
                
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
        </div>

        <!-- Lista de Productos -->
        <div class="admin-card">
            <h2><i class="fas fa-box"></i> Productos en Base de Datos (<?php echo count($productos); ?>)</h2>
            
            <?php if (empty($productos)): ?>
                <p>No hay productos en la base de datos.</p>
                <?php if ($total_productos_db == 0): ?>
                    <p style="color: #666; font-size: 0.9rem;">
                        Usa el botón "Migrar productos" arriba para transferir los productos de la tienda a la base de datos, o agrega productos manualmente.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <?php if ($tieneColumnaImagen): ?>
                            <th>Imagen</th>
                            <?php endif; ?>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <?php if ($tieneColumnaImagen): ?>
                                <td>
                                    <?php if ($producto['imagen']): ?>
                                    <img src="img/<?php echo $producto['imagen']; ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         class="product-image-preview"
                                         onerror="this.src='img/placeholder.jpg'">
                                    <?php else: ?>
                                    <div class="product-image-preview" style="display: flex; align-items: center; justify-content: center; background-color: #f3f4f6;">
                                        <i class="fas fa-box" style="color: #8b7355;"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    <?php if ($producto['descripcion']): ?>
                                    <br><small style="color: #666;"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . (strlen($producto['descripcion']) > 50 ? '...' : ''); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <span class="<?php echo $producto['stock'] <= 5 ? 'stock-low' : 'stock-ok'; ?>">
                                        <?php echo $producto['stock']; ?> unidades
                                    </span>
                                </td>
                                <td><?php echo $producto['categoria_nombre'] ?: 'Sin categoría'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin-productos.php?editar=<?php echo $producto['id']; ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Acciones Rápidas -->
        <div class="admin-card">
            <h2><i class="fas fa-tools"></i> Acciones Rápidas</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="admin-categorias.php" class="btn btn-primary">Gestionar Categorías</a>
                <a href="admin-inventario.php" class="btn btn-secondary">Ver Inventario</a>
                <a href="tienda.php" class="btn btn-secondary" target="_blank">Ver Tienda</a>
            </div>
        </div>
    </main>
</body>
</html>