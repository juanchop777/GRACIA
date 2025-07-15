<?php
session_start();
require_once 'config.php';

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header('Location: index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header('Location: admin-productos.php');
    exit();
}

// Obtener producto
try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        header('Location: admin-productos.php?error=Producto no encontrado');
        exit();
    }
} catch (Exception $e) {
    header('Location: admin-productos.php?error=' . urlencode($e->getMessage()));
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = limpiarDatos($_POST['nombre']);
        $descripcion = limpiarDatos($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $categoria = limpiarDatos($_POST['categoria']);
        $stock = intval($_POST['stock']);
        $imagen = limpiarDatos($_POST['imagen']);
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        // Validaciones
        if (empty($nombre) || $precio <= 0) {
            throw new Exception("Nombre y precio son obligatorios");
        }
        
        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, stock = ?, imagen = ?, activo = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria, $stock, $imagen, $activo, $id]);
        
        header('Location: admin-productos.php?mensaje=Producto actualizado exitosamente');
        exit();
        
    } catch (Exception $e) {
        $error = "Error al actualizar producto: " . $e->getMessage();
    }
}

// Obtener categorías
try {
    $stmt = $pdo->query("SELECT * FROM categorias WHERE activa = 1");
    $categorias = $stmt->fetchAll();
} catch (Exception $e) {
    $categorias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - GraciaShoes</title>
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
        
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 600px;
            margin: 2rem auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b7355;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #8b7355 0%, #6d5a42 100%);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
                    <i class="fas fa-edit"></i> Editar Producto
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

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo $producto['precio']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo htmlspecialchars($categoria['nombre']); ?>" 
                                    <?php echo $categoria['nombre'] === $producto['categoria'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="zapatos" <?php echo $producto['categoria'] === 'zapatos' ? 'selected' : ''; ?>>Zapatos</option>
                        <option value="bolsos" <?php echo $producto['categoria'] === 'bolsos' ? 'selected' : ''; ?>>Bolsos</option>
                        <option value="accesorios" <?php echo $producto['categoria'] === 'accesorios' ? 'selected' : ''; ?>>Accesorios</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo $producto['stock']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="imagen">URL de la Imagen</label>
                    <input type="url" id="imagen" name="imagen" value="<?php echo htmlspecialchars($producto['imagen']); ?>">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="activo" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                        <label for="activo">Producto activo</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="admin-productos.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Actualizar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
