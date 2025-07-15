<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    
    // Log para debugging
    error_log("Intento de login para: " . $correo);
    
    if (empty($correo) || empty($contrasena)) {
        $_SESSION['error'] = 'Por favor, completa todos los campos';
        header('Location: index.php');
        exit;
    }
    
    try {
        $pdo = conectarDB();
        
        // Buscar usuario por correo
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        
        error_log("Usuario encontrado: " . ($usuario ? 'Sí' : 'No'));
        if ($usuario) {
            error_log("Rol del usuario: " . $usuario['rol']);
        }
        
        if (!$usuario) {
            $_SESSION['error'] = 'El correo no existe';
            header('Location: index.php');
            exit;
        }
        
        if (!password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['error'] = 'Correo o contraseña incorrectos';
            header('Location: index.php');
            exit;
        }
        
        // Login exitoso
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];
        
        error_log("Login exitoso para: " . $correo . " con rol: " . $usuario['rol']);
        
        // Redirigir según el rol
        if ($usuario['rol'] === 'admin') {
            error_log("Redirigiendo a admin-dashboard.php");
            header('Location: admin-dashboard.php');
        } else {
            error_log("Redirigiendo a dashboard.php");
            header('Location: dashboard.php');
        }
        exit;
    } catch (Exception $e) {
        error_log("Error en login: " . $e->getMessage());
        $_SESSION['error'] = 'Error interno del servidor';
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
