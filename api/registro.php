<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Método no permitido', 405);

$input = obtenerInputJson();
$nombre = trim($input['nombre'] ?? '');
$correo = trim($input['correo'] ?? $input['email'] ?? '');
$contrasena = $input['contrasena'] ?? $input['password'] ?? '';

if (empty($nombre) || empty($correo) || empty($contrasena)) jsonError('Nombre, correo y contraseña requeridos', 400);
if (strlen($contrasena) < 6) jsonError('La contraseña debe tener al menos 6 caracteres', 400);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) jsonError('Correo no válido', 400);

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) jsonError('Este correo ya está registrado', 409);

    $id = generarUUID();
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (id, nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?, 'usuario')");
    $stmt->execute([$id, $nombre, $correo, $hash]);

    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $pdo->prepare("INSERT INTO api_tokens (token, usuario_id, expira) VALUES (?, ?, ?)");
    $stmt->execute([$token, $id, $expira]);

    jsonRespuesta([
        'ok' => true,
        'token' => $token,
        'usuario' => [
            'id' => $id,
            'nombre' => $nombre,
            'email' => $correo,
            'correo' => $correo,
            'rol' => 'cliente',
        ],
    ]);
} catch (Exception $e) {
    error_log('API registro: ' . $e->getMessage());
    jsonError('Error en el servidor', 500);
}
