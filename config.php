<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Cambia esto por tu usuario de MySQL
define('DB_PASS', 'root'); // Cambia esto por tu contraseña de MySQL
define('DB_NAME', 'graciashoes');

// Función para conectar a la base de datos
function conectarDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        return false;
    }
}

// Función para verificar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Función para requerir autenticación
function requerirAuth() {
    if (!estaLogueado()) {
        header('Location: index.php');
        exit();
    }
}

// Función para requerir rol de administrador
function requerirAdmin() {
    if (!estaLogueado() || $_SESSION['rol'] !== 'ADMIN') {
        header('Location: index.php');
        exit();
    }
}

// Función para requerir rol de cliente
function requerirCliente() {
    if (!estaLogueado() || $_SESSION['rol'] !== 'CLIENTE') {
        header('Location: index.php');
        exit();
    }
}

// Función para limpiar datos de entrada
function limpiarDatos($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>