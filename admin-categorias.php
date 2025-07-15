<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado y sea administrador
if (!estaLogueado() || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Inicializar variables
$error = '';
$success = '';
$categoria = null;

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

// Procesar formulario de creación/edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_categoria']) || isset($_POST['actualizar_categoria'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $imagen = $_FILES['imagen'] ?? null;

        if (empty($nombre)) {
            $error = 'El nombre de la categoría es obligatorio';
        } else {
            try {
                $pdo = conectarDB();
                
                if (isset($_POST['crear_categoria'])) {
                    // Verificar que no exista ya
                    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
                    $stmt->execute([$nombre]);
                    if ($stmt->fetch()) {
                        $error = 'Ya existe una categoría con ese nombre';
                    } else {
                        // Crear nueva categoría usando la función de config.php
                        $id = generarUUID();
                        
                        // Verificar si la tabla tiene columna descripcion
                        $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'descripcion'");
                        $tieneDescripcion = $stmt->rowCount() > 0;

                        // Manejar la subida de imagen
                        $imagen_nombre = null;
                        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
                            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                            $imagen_nombre = 'cat_' . $id . '.' . $extension;
                            $destino = 'img/' . $imagen_nombre;
                            if (!move_uploaded_file($imagen['tmp_name'], $destino)) {
                                $error = 'Error al subir la imagen';
                            }
                        }

                        if (!$error) {
                            if ($tieneDescripcion && $imagen_nombre) {
                                $stmt = $pdo->prepare("INSERT INTO categorias (id, nombre, descripcion, imagen) VALUES (?, ?, ?, ?)");
                                $stmt->execute([$id, $nombre, $descripcion, $imagen_nombre]);
                            } elseif ($tieneDescripcion) {
                                $stmt = $pdo->prepare("INSERT INTO categorias (id, nombre, descripcion) VALUES (?, ?, ?)");
                                $stmt->execute([$id, $nombre, $descripcion]);
                            } elseif ($imagen_nombre) {
                                $stmt = $pdo->prepare("INSERT INTO categorias (id, nombre, imagen) VALUES (?, ?, ?)");
                                $stmt->execute([$id, $nombre, $imagen_nombre]);
                            } else {
                                $stmt = $pdo->prepare("INSERT INTO categorias (id, nombre) VALUES (?, ?)");
                                $stmt->execute([$id, $nombre]);
                            }
                            
                            $success = 'Categoría creada exitosamente';
                        }
                    }
                } else {
                    // Actualizar categoría existente
                    $id = $_POST['id'];
                    
                    // Verificar si la tabla tiene columna descripcion e imagen
                    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'descripcion'");
                    $tieneDescripcion = $stmt->rowCount() > 0;
                    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'imagen'");
                    $tieneImagen = $stmt->rowCount() > 0;

                    $imagen_nombre = null;
                    if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
                        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                        $imagen_nombre = 'cat_' . $id . '.' . $extension;
                        $destino = 'img/' . $imagen_nombre;
                        if (!move_uploaded_file($imagen['tmp_name'], $destino)) {
                            $error = 'Error al subir la imagen';
                        }
                    }

                    if (!$error) {
                        if ($tieneDescripcion && $tieneImagen && $imagen_nombre) {
                            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?");
                            $stmt->execute([$nombre, $descripcion, $imagen_nombre, $id]);
                        } elseif ($tieneDescripcion) {
                            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
                            $stmt->execute([$nombre, $descripcion, $id]);
                        } elseif ($tieneImagen && $imagen_nombre) {
                            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, imagen = ? WHERE id = ?");
                            $stmt->execute([$nombre, $imagen_nombre, $id]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
                            $stmt->execute([$nombre, $id]);
                        }
                        
                        $success = 'Categoría actualizada exitosamente';
                    }
                }
                
                if ($success) {
                    header('Location: admin-categorias.php?success=' . urlencode($success));
                    exit;
                }
            } catch (Exception $e) {
                error_log("Error al guardar categoría: " . $e->getMessage());
                $error = 'Error al guardar la categoría: ' . $e->getMessage();
            }
        }
    }
}

// Cargar categoría para edición
if ($action === 'edit' && isset($_GET['id'])) {
    $categoria_id = $_GET['id'];
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        $categoria = $stmt->fetch();
        
        if (!$categoria) {
            $error = 'Categoría no encontrada';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error al cargar categoría';
        $action = 'list';
    }
}

// Eliminar categoría
if ($action === 'delete' && isset($_GET['id'])) {
    $categoria_id = $_GET['id'];
    
    try {
        $pdo = conectarDB();
        
        // Verificar si hay productos en esta categoría
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
        $stmt->execute([$categoria_id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            $error = 'No se puede eliminar la categoría porque tiene ' . $result['total'] . ' productos asociados';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$categoria_id]);
            $success = 'Categoría eliminada exitosamente';
        }
        
        header('Location: admin-categorias.php?success=' . urlencode($success) . '&error=' . urlencode($error));
        exit;
    } catch (Exception $e) {
        error_log("Error al eliminar categoría: " . $e->getMessage());
        $error = 'Error al eliminar la categoría';
    }
}

// Obtener lista de categorías usando la función de config.php
$categorias = obtenerCategorias();

// Verificar estructura de la tabla
$tieneDescripcion = false;
$tieneFechaCreacion = false;
$tieneImagen = false;
try {
    $pdo = conectarDB();
    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'descripcion'");
    $tieneDescripcion = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'fecha_creacion'");
    $tieneFechaCreacion = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'imagen'");
    $tieneImagen = $stmt->rowCount() > 0;
} catch (Exception $e) {
    // Ignorar errores de verificación
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - GraciaShoes</title>
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
            min-height: calc(100vh - 100px);
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
        
        .form-group input[type="file"] {
            padding: 5px 0;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        
        .debug-info {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
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
            <li><a href="admin-categorias.php" class="active"><i class="fas fa-tags"></i> Categorías</a></li>
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
            <h1><?php echo $action === 'new' ? 'Crear Categoría' : ($action === 'edit' ? 'Editar Categoría' : 'Gestión de Categorías'); ?></h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Alerta de mejora de estructura -->
        <?php if (!$tieneDescripcion || !$tieneFechaCreacion || !$tieneImagen): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>Mejora disponible:</strong> 
                Puedes agregar campos adicionales a la tabla de categorías para más funcionalidad.
                <form method="POST" action="admin-categorias.php" style="display: inline; margin-left: 10px;">
                    <input type="hidden" name="accion" value="mejorar_estructura">
                    <button type="submit" class="btn btn-sm btn-secondary" 
                            onclick="return confirm('¿Quieres agregar campos de descripción, fecha y imagen a las categorías? Esto NO afectará las categorías existentes.')">
                        <i class="fas fa-plus"></i> Mejorar estructura de categorías
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($action === 'new' || $action === 'edit'): ?>
            <!-- Formulario de Categoría -->
            <div class="admin-card">
                <h2><i class="fas fa-<?php echo $action === 'new' ? 'plus' : 'edit'; ?>"></i> <?php echo $action === 'new' ? 'Nueva Categoría' : 'Editar Categoría'; ?></h2>
                
                <form method="POST" action="admin-categorias.php" enctype="multipart/form-data">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($categoria['id']); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre de la Categoría *</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($categoria['nombre'] ?? ''); ?>" required>
                    </div>
                    
                    <?php if ($tieneDescripcion): ?>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($categoria['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <?php endif; ?>

                    <?php if ($tieneImagen): ?>
                    <div class="form-group">
                        <label for="imagen">Imagen de la Categoría</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(event)">
                        <?php if ($action === 'edit' && $categoria['imagen']): ?>
                            <img src="img/<?php echo htmlspecialchars($categoria['imagen']); ?>" alt="Imagen actual" class="preview-image" id="current-image">
                        <?php endif; ?>
                        <img src="" alt="Vista previa" class="preview-image" id="preview-image">
                    </div>
                    <script>
                        function previewImage(event) {
                            const file = event.target.files[0];
                            const preview = document.getElementById('preview-image');
                            const currentImage = document.getElementById('current-image');
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.src = e.target.result;
                                    preview.style.display = 'block';
                                    if (currentImage) currentImage.style.display = 'none';
                                }
                                reader.readAsDataURL(file);
                            }
                        }
                    </script>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="submit" name="<?php echo $action === 'new' ? 'crear_categoria' : 'actualizar_categoria'; ?>" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $action === 'new' ? 'Crear Categoría' : 'Actualizar Categoría'; ?>
                        </button>
                        <a href="admin-categorias.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Lista de Categorías -->
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-tags"></i> Categorías Registradas</h2>
                    <a href="admin-categorias.php?action=new" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </a>
                </div>
                
                <?php if (empty($categorias)): ?>
                    <p>No hay categorías registradas en la base de datos.</p>
                    <div style="margin-top: 20px;">
                        <a href="admin-categorias.php?action=new" class="btn btn-primary">Crear Primera Categoría</a>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <?php if ($tieneDescripcion): ?>
                                <th>Descripción</th>
                                <?php endif; ?>
                                <?php if ($tieneImagen): ?>
                                <th>Imagen</th>
                                <?php endif; ?>
                                <th>Productos</th>
                                <?php if ($tieneFechaCreacion): ?>
                                <th>Fecha Creación</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                                <?php
                                // Contar productos en esta categoría
                                try {
                                    $pdo = conectarDB();
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
                                    $stmt->execute([$cat['id']]);
                                    $productos_count = $stmt->fetch()['total'];
                                } catch (Exception $e) {
                                    $productos_count = 0;
                                }
                                ?>
                                <tr>
                                    <td><?php echo substr($cat['id'], 0, 12); ?>...</td>
                                    <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                                    <?php if ($tieneDescripcion): ?>
                                    <td><?php echo htmlspecialchars($cat['descripcion'] ?? 'Sin descripción'); ?></td>
                                    <?php endif; ?>
                                    <?php if ($tieneImagen && $cat['imagen']): ?>
                                    <td><img src="img/<?php echo htmlspecialchars($cat['imagen']); ?>" alt="Imagen" style="max-width: 50px; max-height: 50px;"></td>
                                    <?php elseif ($tieneImagen): ?>
                                    <td>Sin imagen</td>
                                    <?php endif; ?>
                                    <td>
                                        <span style="color: <?php echo $productos_count > 0 ? '#2e7d32' : '#666'; ?>;">
                                            <?php echo $productos_count; ?> productos
                                        </span>
                                    </td>
                                    <?php if ($tieneFechaCreacion): ?>
                                    <td>
                                        <?php 
                                        if (isset($cat['fecha_creacion']) && $cat['fecha_creacion']) {
                                            echo date('d/m/Y', strtotime($cat['fecha_creacion'])); 
                                        } else {
                                            echo 'No disponible';
                                        }
                                        ?>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin-categorias.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <?php if ($productos_count == 0): ?>
                                                <a href="admin-categorias.php?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-secondary btn-sm" 
                                                   onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Estadísticas de Categorías -->
            <div class="admin-card">
                <h2><i class="fas fa-chart-pie"></i> Estadísticas de Categorías</h2>
                
                <?php
                $total_categorias = count($categorias);
                $categorias_con_productos = 0;
                $total_productos_categorizados = 0;
                
                foreach ($categorias as $cat) {
                    try {
                        $pdo = conectarDB();
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
                        $stmt->execute([$cat['id']]);
                        $count = $stmt->fetch()['total'];
                        if ($count > 0) {
                            $categorias_con_productos++;
                            $total_productos_categorizados += $count;
                        }
                    } catch (Exception $e) {
                        // Ignorar errores
                    }
                }
                ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 style="margin: 0; color: #8b7355; font-size: 2rem;"><?php echo $total_categorias; ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666;">Total Categorías</p>
                    </div>
                    
                    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 style="margin: 0; color: #2e7d32; font-size: 2rem;"><?php echo $categorias_con_productos; ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666;">Con Productos</p>
                    </div>
                    
                    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h2 style="margin: 0; color: #f57c00; font-size: 2rem;"><?php echo $total_productos_categorizados; ?></h2>
                        <p style="margin: 5px 0 0 0; color: #666;">Productos Categorizados</p>
                    </div>
                    
                    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h3 style="margin: 0; color: #c62828; font-size: 2rem;"><?php echo $total_categorias - $categorias_con_productos; ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666;">Vacías</p>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="admin-card">
                <h2><i class="fas fa-tools"></i> Acciones Rápidas</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <a href="admin-categorias.php?action=new" class="btn btn-primary">Nueva Categoría</a>
                    <a href="admin-productos.php" class="btn btn-secondary">Gestionar Productos</a>
                    <a href="tienda.php" class="btn btn-secondary" target="_blank">Ver Tienda</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>