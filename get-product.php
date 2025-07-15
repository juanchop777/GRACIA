<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    http_response_code(400);
    error_log('ID de producto no proporcionado o vacío');
    echo json_encode(['error' => 'ID de producto requerido']);
    exit;
}

$id = trim($_GET['id']);
error_log('Solicitud recibida para ID: ' . $id); // Depuración

// Validar que el ID sea un UUID válido (formato básico)
if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $id)) {
    http_response_code(400);
    error_log('ID inválido recibido: ' . $id);
    echo json_encode(['error' => 'ID inválido, debe ser un UUID']);
    exit;
}

// Verificar si existe la columna 'imagen' en la tabla productos
function verificarColumnaImagen($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'imagen'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log('Error al verificar columna imagen: ' . $e->getMessage());
        return false;
    }
}

try {
    $pdo = conectarDB();
    if (!$pdo) {
        error_log('Fallo al conectar a la base de datos');
        http_response_code(500);
        echo json_encode(['error' => 'Fallo al conectar a la base de datos']);
        exit;
    }
    error_log('Conexión a la base de datos exitosa');

    $tieneColumnaImagen = verificarColumnaImagen($pdo);
    
    if ($tieneColumnaImagen) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = ?
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre, NULL as imagen
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = ?
        ");
    }
    
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        error_log('Producto no encontrado con ID: ' . $id);
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado con ID: ' . $id]);
        exit;
    }
    
    // Convertir tipos de datos para consistencia
    $producto['precio'] = floatval($producto['precio']);
    $producto['stock'] = intval($producto['stock']);
    
    // Asegurar que la descripción esté presente
    if (!isset($producto['descripcion']) || empty($producto['descripcion'])) {
        $producto['descripcion'] = 'Sin descripción disponible.';
    }
    
    error_log('Producto encontrado: ' . json_encode($producto)); // Depuración
    echo json_encode($producto);
    
} catch (Exception $e) {
    error_log('Error en get_product.php para ID ' . $id . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}
?>