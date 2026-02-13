<?php
/**
 * Bootstrap para la API REST - Gracia
 * CORS, cabeceras JSON y helpers.
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Token');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';

// Crear tabla de tokens si no existe
try {
    $pdo = conectarDB();
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_tokens (
        token VARCHAR(64) PRIMARY KEY,
        usuario_id CHAR(36) NOT NULL,
        expira DATETIME NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {
    // ignorar si ya existe
}

function jsonRespuesta($data, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError($mensaje, $codigo = 400) {
    jsonRespuesta(['ok' => false, 'error' => $mensaje], $codigo);
}

function obtenerInputJson() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function obtenerToken() {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) return trim($m[1]);
    return $_SERVER['HTTP_X_API_TOKEN'] ?? '';
}

function requireAuth() {
    $token = obtenerToken();
    if (empty($token)) jsonError('Token requerido', 401);
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT usuario_id, expira FROM api_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if (!$row || (strtotime($row['expira']) < time())) jsonError('Token inválido o expirado', 401);
        return $row['usuario_id'];
    } catch (Exception $e) {
        jsonError('Error de autenticación', 500);
    }
}

function requireAdmin() {
    $usuario_id = requireAuth();
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $u = $stmt->fetch();
    $rol = isset($u['rol']) ? strtolower(trim((string) $u['rol'])) : '';
    if (!$u || $rol !== 'admin') jsonError('Solo administradores', 403);
    return $usuario_id;
}

/** Resuelve nombre de categoría a categoria_id (UUID). Acepta calzado/zapatos, accesorios, bolsos. */
function resolverCategoriaId($pdo, $nombre) {
    if (empty($nombre)) return null;
    $nombre = strtolower(trim($nombre));
    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE LOWER(TRIM(nombre)) = ? LIMIT 1");
    $stmt->execute([$nombre]);
    $row = $stmt->fetch();
    if ($row) return $row['id'];
    $sinonimos = [
        'calzado' => ['zapatos', 'calzado', 'zapato', 'calzados'],
        'accesorios' => ['accesorios', 'accesorio'],
        'bolsos' => ['bolsos', 'bolso'],
    ];
    $buscar = $sinonimos[$nombre] ?? [$nombre];
    foreach ($buscar as $n) {
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE LOWER(TRIM(nombre)) = ? LIMIT 1");
        $stmt->execute([$n]);
        $row = $stmt->fetch();
        if ($row) return $row['id'];
    }
    return null;
}
