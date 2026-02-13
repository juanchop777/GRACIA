<?php
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuario_id = requireAuth();
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $u = $stmt->fetch();
    $rol = isset($u['rol']) ? strtolower(trim((string) $u['rol'])) : '';
    $es_admin = ($rol === 'admin');
    try {
        $pedidos = obtenerPedidos($es_admin ? null : $usuario_id);
        $pdo = conectarDB();
        $lista = [];
        foreach ($pedidos as $p) {
            $st = $pdo->prepare("SELECT pi.*, pr.nombre as producto_nombre FROM pedido_items pi LEFT JOIN productos pr ON pi.producto_id = pr.id WHERE pi.pedido_id = ?");
            $st->execute([$p['id']]);
            $items = $st->fetchAll(PDO::FETCH_ASSOC);
            $lista[] = [
                'id' => $p['id'],
                'usuario_id' => $p['usuario_id'],
                'estado' => $p['estado'],
                'total' => (float) ($p['total_pedido'] ?? 0),
                'fechaCreacion' => $p['fecha'],
                'fechaActualizacion' => $p['fecha_actualizacion'] ?? null,
                'items' => array_map(function ($i) {
                    return [
                        'producto_id' => $i['producto_id'],
                        'producto_nombre' => $i['producto_nombre'] ?? '',
                        'cantidad' => (int) $i['cantidad'],
                        'precio_unitario' => (float) $i['precio_unitario'],
                        'subtotal' => (float) $i['cantidad'] * (float) $i['precio_unitario'],
                    ];
                }, $items),
            ];
        }
        jsonRespuesta(['ok' => true, 'pedidos' => $lista]);
    } catch (Exception $e) {
        error_log('API pedidos GET: ' . $e->getMessage());
        jsonError('Error en el servidor', 500);
    }
}

if ($method === 'POST') {
    $usuario_id = requireAuth();
    $input = obtenerInputJson();
    $items = $input['items'] ?? $input['detalles'] ?? [];
    if (empty($items) || !is_array($items)) jsonError('Se requiere un array "items" con producto_id, cantidad, precio_unitario', 400);

    $validos = [];
    foreach ($items as $it) {
        $pid = $it['producto_id'] ?? $it['productoId'] ?? null;
        $cant = (int) ($it['cantidad'] ?? 0);
        $precio = (float) ($it['precio_unitario'] ?? $it['precioUnitario'] ?? 0);
        if ($pid && $cant > 0 && $precio >= 0) $validos[] = ['producto_id' => $pid, 'cantidad' => $cant, 'precio_unitario' => $precio];
    }
    if (empty($validos)) jsonError('Items no válidos', 400);

    $pedido_id = crearPedido($usuario_id, $validos);
    if (!$pedido_id) jsonError('No se pudo crear el pedido', 500);
    jsonRespuesta(['ok' => true, 'pedido_id' => $pedido_id], 201);
}

if ($method === 'PUT') {
    requireAdmin();
    $input = obtenerInputJson();
    $id = trim($input['id'] ?? $input['pedido_id'] ?? '');
    $estado = trim($input['estado'] ?? '');
    $estados_validos = ['pendiente', 'procesado', 'enviado', 'entregado', 'cancelado', 'en_proceso'];
    if (empty($id)) jsonError('ID de pedido requerido', 400);
    if (!in_array($estado, $estados_validos)) jsonError('Estado no válido', 400);
    if ($estado === 'en_proceso') $estado = 'procesado';
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        if ($stmt->rowCount() === 0) jsonError('Pedido no encontrado', 404);
        jsonRespuesta(['ok' => true]);
    } catch (Exception $e) {
        error_log('API pedidos PUT: ' . $e->getMessage());
        jsonError('Error en el servidor', 500);
    }
}

jsonError('Método no permitido', 405);
