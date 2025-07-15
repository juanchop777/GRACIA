<?php
session_start();
require_once 'config.php';

// Verificar que el usuario sea administrador
if (!estaLogueado() || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Procesar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conectarDB();
        
        // Crear tabla de configuración si no existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                clave VARCHAR(100) UNIQUE NOT NULL,
                valor TEXT,
                descripcion TEXT,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        $configuraciones = [
            'nombre_tienda' => $_POST['nombre_tienda'],
            'email_contacto' => $_POST['email_contacto'],
            'telefono_contacto' => $_POST['telefono_contacto'],
            'direccion_tienda' => $_POST['direccion_tienda'],
            'moneda' => $_POST['moneda'],
            'costo_envio' => $_POST['costo_envio']
        ];
        
        foreach ($configuraciones as $clave => $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO configuracion (clave, valor) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ");
            $stmt->execute([$clave, $valor]);
        }
        
        $mensaje = "Configuración actualizada exitosamente";
        
    } catch (Exception $e) {
        $error = "Error al actualizar configuración: " . $e->getMessage();
    }
}

// Obtener configuración actual
try {
    $pdo = conectarDB();
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    $config = [];
    while ($row = $stmt->fetch()) {
        $config[$row['clave']] = $row['valor'];
    }
} catch (Exception $e) {
    $config = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 10px; /* Reduced padding to bring content up */
            padding-top: 60px; /* Reduced padding-top to move title up */
        }
        
        .admin-header {
            background: linear-gradient(135deg, #8b7355 0%, #6d5a42 100%);
            color: white;
            padding: 1rem 0; /* Reduced padding to move title up */
            margin-top: 60px; /* Adjusted margin to align with new padding */
        }
        
        .config-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 800px;
            margin: 1rem auto; /* Reduced margin to bring container up */
        }
        
        .config-section {
            margin-bottom: 3rem;
        }
        
        .config-section h3 {
            color: #8b7355;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #8b7355;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
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
            <li><a href="admin-inventario.php"><i class="fas fa-warehouse"></i> Inventario</a></li>
            <li><a href="admin-reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="admin-configuracion.php" class="active"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="admin-index.php"><i class="fas fa-home"></i> Ver Tienda</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Configuración del Sistema</h1>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="config-container">
            <form method="POST">
                <div class="config-section">
                    <h3><i class="fas fa-store"></i> Información de la Tienda</h3>
                    
                    <div class="form-group">
                        <label for="nombre_tienda">Nombre de la Tienda</label>
                        <input type="text" id="nombre_tienda" name="nombre_tienda" 
                               value="<?php echo htmlspecialchars($config['nombre_tienda'] ?? 'GraciaShoes'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email_contacto">Email de Contacto</label>
                            <input type="email" id="email_contacto" name="email_contacto" 
                                   value="<?php echo htmlspecialchars($config['email_contacto'] ?? 'info@graciashoes.com'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono_contacto">Teléfono de Contacto</label>
                            <input type="tel" id="telefono_contacto" name="telefono_contacto" 
                                   value="<?php echo htmlspecialchars($config['telefono_contacto'] ?? '+57 3116448364'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion_tienda">Dirección de la Tienda</label>
                        <textarea id="direccion_tienda" name="direccion_tienda" rows="3"><?php echo htmlspecialchars($config['direccion_tienda'] ?? 'Rivera Huila, Centro Abajo'); ?></textarea>
                    </div>
                </div>

                <div class="config-section">
                    <h3><i class="fas fa-shopping-cart"></i> Configuración de Ventas</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="moneda">Moneda</label>
                            <select id="moneda" name="moneda">
                                <option value="COP" <?php echo ($config['moneda'] ?? 'COP') === 'COP' ? 'selected' : ''; ?>>Peso Colombiano (COP)</option>
                                <option value="USD" <?php echo ($config['moneda'] ?? '') === 'USD' ? 'selected' : ''; ?>>Dólar Americano (USD)</option>
                                <option value="EUR" <?php echo ($config['moneda'] ?? '') === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="costo_envio">Costo de Envío</label>
                            <input type="number" id="costo_envio" name="costo_envio" step="0.01" min="0" 
                                   value="<?php echo $config['costo_envio'] ?? '10.00'; ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </form>
        </div>
    </main>
</body>
</html>