<?php
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? trim($_GET['id']) : null;

try {
    $pdo = conectarDB();

    // --- GET: listar o obtener uno ---
    if ($method === 'GET') {
        $categoria_id = isset($_GET['categoria_id']) ? trim($_GET['categoria_id']) : null;
        $limite = isset($_GET['limite']) ? (int) $_GET['limite'] : null;

        if ($id !== null && $id !== '') {
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$p) jsonError('Producto no encontrado', 404);
            $p['precio'] = (float) $p['precio'];
            $p['stock'] = (int) $p['stock'];
            $p['categoria'] = $p['categoria_nombre'] ?? '';
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . preg_replace('#/api/.*$#', '/', $_SERVER['REQUEST_URI'] ?? '/') . 'img/';
            $p['imagenUrl'] = !empty($p['imagen']) ? (strpos($p['imagen'], 'http') === 0 ? $p['imagen'] : $baseUrl . $p['imagen']) : null;
            $p['activo'] = true;
            $p['fechaCreacion'] = isset($p['fecha_creacion']) ? $p['fecha_creacion'] : null;
            jsonRespuesta($p);
        }

        $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
        $params = [];
        if ($categoria_id) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoria_id;
        }
        $sql .= " ORDER BY p.nombre";
        if ($limite) $sql .= " LIMIT " . (int) $limite;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . preg_replace('#/api/.*$#', '/', $_SERVER['REQUEST_URI'] ?? '/') . 'img/';
        foreach ($lista as &$p) {
            $p['precio'] = (float) $p['precio'];
            $p['stock'] = (int) $p['stock'];
            $p['categoria'] = $p['categoria_nombre'] ?? '';
            $p['imagenUrl'] = !empty($p['imagen']) ? (strpos($p['imagen'], 'http') === 0 ? $p['imagen'] : $baseUrl . $p['imagen']) : null;
            $p['activo'] = true;
            $p['fechaCreacion'] = $p['fecha_creacion'] ?? null;
        }
        jsonRespuesta(['ok' => true, 'productos' => $lista]);
    }

    // --- POST: crear producto (usuario autenticado) ---
    if ($method === 'POST') {
        requireAuth();
        $input = obtenerInputJson();
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $precio = isset($input['precio']) ? (float) $input['precio'] : 0;
        $stock = isset($input['stock']) ? (int) $input['stock'] : 0;
        $categoria_id = $input['categoria_id'] ?? null;
        $categoria_nombre = $input['categoria'] ?? null;
        $imagen = trim($input['imagen'] ?? $input['imagenUrl'] ?? '');

        if (empty($nombre)) jsonError('Nombre requerido', 400);
        if ($precio <= 0) jsonError('Precio debe ser mayor a 0', 400);

        if (empty($categoria_id) && !empty($categoria_nombre)) {
            $categoria_id = resolverCategoriaId($pdo, $categoria_nombre);
        }
        $producto_id = generarUUID();
        $stmt = $pdo->prepare("INSERT INTO productos (id, nombre, descripcion, precio, stock, categoria_id, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$producto_id, $nombre, $descripcion ?: null, $precio, $stock, $categoria_id ?: null, $imagen ?: null]);
        jsonRespuesta(['ok' => true, 'id' => $producto_id], 201);
    }

    // --- PUT: actualizar producto (usuario autenticado) ---
    if ($method === 'PUT') {
        requireAuth();
        $input = obtenerInputJson();
        $id_put = $id ?? trim($input['id'] ?? '');
        if (empty($id_put)) jsonError('ID de producto requerido', 400);

        $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->execute([$id_put]);
        if (!$stmt->fetch()) jsonError('Producto no encontrado', 404);

        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $precio = isset($input['precio']) ? (float) $input['precio'] : null;
        $stock = isset($input['stock']) ? (int) $input['stock'] : null;
        $categoria_id = $input['categoria_id'] ?? null;
        $categoria_nombre = $input['categoria'] ?? null;
        $imagen = null;
        if (array_key_exists('imagenUrl', $input)) $imagen = trim((string) $input['imagenUrl']);
        if (($imagen === null || $imagen === '') && array_key_exists('imagen', $input)) $imagen = trim((string) $input['imagen']);
        if ($imagen !== null && $imagen !== '' && strpos($imagen, '/') !== false) {
            $imagen = basename(parse_url($imagen, PHP_URL_PATH) ?: $imagen);
        }

        $updates = [];
        $params = [];
        if ($nombre !== '') { $updates[] = 'nombre = ?'; $params[] = $nombre; }
        if ($descripcion !== null) { $updates[] = 'descripcion = ?'; $params[] = $descripcion; }
        if ($precio !== null) { $updates[] = 'precio = ?'; $params[] = $precio; }
        if ($stock !== null) { $updates[] = 'stock = ?'; $params[] = $stock; }
        if ($categoria_id !== null || $categoria_nombre !== null) {
            $cid = $categoria_id ?: resolverCategoriaId($pdo, $categoria_nombre);
            $updates[] = 'categoria_id = ?';
            $params[] = $cid;
        }
        if ($imagen !== null) { $updates[] = 'imagen = ?'; $params[] = $imagen === '' ? null : $imagen; }

        if (empty($updates)) jsonError('Nada que actualizar', 400);
        $params[] = $id_put;
        $sql = "UPDATE productos SET " . implode(', ', $updates) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        jsonRespuesta(['ok' => true]);
    }

    // --- DELETE: eliminar producto (usuario autenticado) ---
    if ($method === 'DELETE') {
        requireAuth();
        $id_del = $id ?? trim(obtenerInputJson()['id'] ?? '');
        if (empty($id_del)) jsonError('ID de producto requerido', 400);

        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id_del]);
        if ($stmt->rowCount() === 0) jsonError('Producto no encontrado', 404);
        jsonRespuesta(['ok' => true]);
    }

} catch (Exception $e) {
    error_log('API productos: ' . $e->getMessage());
    jsonError('Error en el servidor', 500);
}

jsonError('Método no permitido', 405);
