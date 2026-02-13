<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Método no permitido', 405);

$input = obtenerInputJson();
$correo = trim($input['correo'] ?? $input['email'] ?? '');
$contrasena = $input['contrasena'] ?? $input['password'] ?? '';

if (empty($correo) || empty($contrasena)) jsonError('Correo y contraseña requeridos', 400);

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT id, nombre, correo, contrasena, rol FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $u = $stmt->fetch();
    if (!$u || !password_verify($contrasena, $u['contrasena'])) jsonError('Credenciales incorrectas', 401);

    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+30 days'));
    $pdo->prepare("DELETE FROM api_tokens WHERE usuario_id = ?")->execute([$u['id']]);
    $stmt = $pdo->prepare("INSERT INTO api_tokens (token, usuario_id, expira) VALUES (?, ?, ?)");
    $stmt->execute([$token, $u['id'], $expira]);

    $rol = $u['rol'] === 'admin' ? 'admin' : 'cliente';
    jsonRespuesta([
        'ok' => true,
        'token' => $token,
        'usuario' => [
            'id' => $u['id'],
            'nombre' => $u['nombre'],
            'email' => $u['correo'],
            'correo' => $u['correo'],
            'rol' => $rol,
        ],
    ]);
} catch (Exception $e) {
    error_log('API login: ' . $e->getMessage());
    jsonError('Error en el servidor', 500);
}
