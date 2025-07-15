<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    
    // Validaciones
    if (empty($nombre) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
        $_SESSION['error'] = 'Por favor, completa todos los campos';
        header('Location: registro.php');
        exit;
    }
    
    if ($contrasena !== $confirmar_contrasena) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        header('Location: registro.php');
        exit;
    }
    
    if (strlen($contrasena) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: registro.php');
        exit;
    }
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El correo electrónico no es válido';
        header('Location: registro.php');
        exit;
    }
    
    try {
        $pdo = conectarDB();
        
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Este correo electrónico ya está registrado';
            header('Location: registro.php');
            exit;
        }
        
        // Crear nuevo usuario
        $usuario_id = generarUUID();
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (id, nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?, 'usuario')");
        $stmt->execute([$usuario_id, $nombre, $correo, $contrasena_hash]);
        
        $_SESSION['success'] = 'Registro exitoso. Ya puedes iniciar sesión';
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        error_log("Error en registro: " . $e->getMessage());
        $_SESSION['error'] = 'Error interno del servidor';
        header('Location: registro.php');
        exit;
    }
} else {
    header('Location: registro.php');
    exit;
}
?>
