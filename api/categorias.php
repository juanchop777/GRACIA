<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Método no permitido', 405);

try {
    $categorias = obtenerCategorias();
    $lista = array_map(function ($c) {
        return [
            'id' => $c['id'],
            'nombre' => $c['nombre'],
            'imagen' => $c['imagen'] ?? null,
        ];
    }, $categorias);
    jsonRespuesta(['ok' => true, 'categorias' => $lista]);
} catch (Exception $e) {
    jsonError('Error en el servidor', 500);
}
