<?php
// Configuración de la base de datos unificada
define('DB_HOST', 'localhost');
define('DB_NAME', 'graciashoess');
define('DB_USER', 'root'); // Cambia por tu usuario
define('DB_PASS', 'root'); // Contraseña de MySQL en Laragon (vacío '' o 'root')
define('DB_CHARSET', 'utf8mb4');

// Función para conectar a la base de datos
function conectarDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        // Log del error para debugging
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        
        // Mostrar mensaje de error más amigable
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            die("Error: La base de datos 'graciashoess' no existe. Por favor, créala primero.");
        } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
            die("Error: Credenciales de base de datos incorrectas. Verifica el usuario y contraseña en config.php");
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            die("Error: No se puede conectar al servidor MySQL. Asegúrate de que MySQL esté ejecutándose.");
        } else {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}

// Función para verificar y crear la base de datos si no existe
function verificarBaseDatos() {
    try {
        // Intentar conectar sin especificar la base de datos
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar si la base de datos existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        $database_exists = $stmt->rowCount() > 0;
        
        if (!$database_exists) {
            // Crear la base de datos
            $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return "Base de datos '" . DB_NAME . "' creada exitosamente.";
        }
        
        return "Base de datos verificada correctamente.";
    } catch (PDOException $e) {
        return "Error al verificar/crear la base de datos: " . $e->getMessage();
    }
}

// Función para generar UUID
function generarUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Función para verificar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Función para verificar si el usuario es admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Función para obtener información del usuario actual
function obtenerUsuarioActual() {
    if (!estaLogueado()) {
        return null;
    }
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        return null;
    }
}

// Función para obtener productos
function obtenerProductos($categoria_id = null, $limite = null) {
    try {
        $pdo = conectarDB();
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id";
        
        $params = [];
        
        if ($categoria_id) {
            $sql .= " WHERE p.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        $sql .= " ORDER BY p.nombre";
        
        if ($limite) {
            $sql .= " LIMIT ?";
            $params[] = $limite;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener productos: " . $e->getMessage());
        return [];
    }
}

// Función para obtener categorías
function obtenerCategorias() {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener categorías: " . $e->getMessage());
        return [];
    }
}

// Función para obtener producto por ID
function obtenerProductoPorId($id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre 
                              FROM productos p 
                              LEFT JOIN categorias c ON p.categoria_id = c.id 
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error al obtener producto: " . $e->getMessage());
        return null;
    }
}

// Función para crear pedido
function crearPedido($usuario_id, $items) {
    try {
        $pdo = conectarDB();
        $pdo->beginTransaction();
        
        // Crear pedido
        $pedido_id = generarUUID();
        $stmt = $pdo->prepare("INSERT INTO pedidos (id, usuario_id, fecha, estado) VALUES (?, ?, NOW(), 'pendiente')");
        $stmt->execute([$pedido_id, $usuario_id]);
        
        // Agregar items del pedido
        foreach ($items as $item) {
            $item_id = generarUUID();
            $stmt = $pdo->prepare("INSERT INTO pedido_items (id, pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$item_id, $pedido_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario']]);
            
            // Actualizar stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['cantidad'], $item['producto_id']]);
            
            // Registrar movimiento de inventario
            $movimiento_id = generarUUID();
            $stmt = $pdo->prepare("INSERT INTO movimientos_inventario (id, producto_id, tipo, cantidad, descripcion, realizado_por, fecha) VALUES (?, ?, 'salida', ?, ?, ?, NOW())");
            $stmt->execute([$movimiento_id, $item['producto_id'], $item['cantidad'], "Venta - Pedido: $pedido_id", $usuario_id]);
        }
        
        $pdo->commit();
        return $pedido_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error al crear pedido: " . $e->getMessage());
        return false;
    }
}

// Función para obtener pedidos
function obtenerPedidos($usuario_id = null, $limite = null) {
    try {
        $pdo = conectarDB();
        
        $sql = "SELECT p.*, u.nombre as usuario_nombre, u.correo as usuario_correo,
                       COUNT(pi.id) as total_items,
                       SUM(pi.cantidad * pi.precio_unitario) as total_pedido
                FROM pedidos p 
                JOIN usuarios u ON p.usuario_id = u.id 
                LEFT JOIN pedido_items pi ON p.id = pi.pedido_id";
        
        $params = [];
        
        if ($usuario_id) {
            $sql .= " WHERE p.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        $sql .= " GROUP BY p.id, u.id ORDER BY p.fecha DESC";
        
        if ($limite) {
            $sql .= " LIMIT ?";
            $params[] = $limite;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener pedidos: " . $e->getMessage());
        return [];
    }
}

// Función para obtener estadísticas del dashboard
function obtenerEstadisticasDashboard() {
    try {
        $pdo = conectarDB();
        
        $stats = [];
        
        // Total usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'");
        $stats['total_usuarios'] = $stmt->fetch()['total'];
        
        // Total productos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $stats['total_productos'] = $stmt->fetch()['total'];
        
        // Total pedidos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
        $stats['total_pedidos'] = $stmt->fetch()['total'];
        
        // Ventas del mes
        $stmt = $pdo->query("SELECT COALESCE(SUM(pi.cantidad * pi.precio_unitario), 0) as total 
                            FROM pedidos p 
                            JOIN pedido_items pi ON p.id = pi.pedido_id 
                            WHERE MONTH(p.fecha) = MONTH(CURRENT_DATE()) 
                            AND YEAR(p.fecha) = YEAR(CURRENT_DATE())");
        $stats['ventas_mes'] = $stmt->fetch()['total'];
        
        // Productos con stock bajo
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock <= 5");
        $stats['productos_stock_bajo'] = $stmt->fetch()['total'];
        
        // Pedidos pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
        $stats['pedidos_pendientes'] = $stmt->fetch()['total'];
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error al obtener estadísticas: " . $e->getMessage());
        return [];
    }
}

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración SMTP para PHPMailer
// Cambia estos valores por los de tu correo remitente
define('MAIL_HOST', 'smtp.gmail.com'); // Servidor SMTP principal (Gmail por defecto)
define('MAIL_USERNAME', 'catfished03@gmail.com'); // Tu correo remitente
define('MAIL_PASSWORD', 'ckfo cijs ebva dein'); // Tu contraseña o app password
define('MAIL_PORT', 587); // Puerto SMTP (587 para TLS, 465 para SSL)
define('MAIL_FROM', 'catfished03@gmail.com'); // El mismo que MAIL_USERNAME normalmente
define('MAIL_FROM_NAME', 'Gracia Shoes'); // Nombre que aparecerá como remitente
define('MAIL_SMTP_SECURE', 'tls'); // 'tls' o 'ssl' según el puerto
?>


