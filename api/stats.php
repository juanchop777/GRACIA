<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Método no permitido', 405);

requireAuth();

try {
    $pdo = conectarDB();

    $ingresos = 0.0;
    $stmt = $pdo->query("SELECT COALESCE(SUM(pi.cantidad * pi.precio_unitario), 0) as total 
                         FROM pedidos p 
                         JOIN pedido_items pi ON p.id = pi.pedido_id 
                         WHERE p.estado = 'entregado'");
    if ($row = $stmt->fetch()) $ingresos = (float) $row['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
    $total_pedidos = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
    $pedidos_pendientes = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'entregado'");
    $pedidos_entregados = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'cancelado'");
    $pedidos_cancelados = (int) $stmt->fetch()['total'];

    jsonRespuesta([
        'ok' => true,
        'ingresos' => $ingresos,
        'gastos' => 0,
        'balance' => $ingresos,
        'totalProductos' => $total_productos,
        'totalPedidos' => $total_pedidos,
        'pedidosPendientes' => $pedidos_pendientes,
        'pedidosEntregados' => $pedidos_entregados,
        'pedidosCancelados' => $pedidos_cancelados,
    ]);
} catch (Exception $e) {
    error_log('API stats: ' . $e->getMessage());
    jsonError('Error en el servidor', 500);
}
