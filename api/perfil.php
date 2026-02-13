<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Método no permitido', 405);

$usuario_id = requireAuth();

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT id, nombre, correo, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $u = $stmt->fetch();
    if (!$u) jsonError('Usuario no encontrado', 404);
    $rol = $u['rol'] === 'admin' ? 'admin' : 'cliente';
    jsonRespuesta([
        'ok' => true,
        'usuario' => [
            'id' => $u['id'],
            'nombre' => $u['nombre'],
            'email' => $u['correo'],
            'correo' => $u['correo'],
            'rol' => $rol,
        ],
    ]);
} catch (Exception $e) {
    jsonError('Error en el servidor', 500);
}
