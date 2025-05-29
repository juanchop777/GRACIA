<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit();
}

$nombre = limpiarDatos($_POST['nombre']);
$correo = limpiarDatos($_POST['correo']);
$contrasena = $_POST['contrasena'];
$confirmar_contrasena = $_POST['confirmar_contrasena'];

// Validar datos
if (empty($nombre) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
    header('Location: registro.php?error=datos');
    exit();
}

if ($contrasena !== $confirmar_contrasena) {
    header('Location: registro.php?error=contrasenas');
    exit();
}

try {
    $pdo = conectarDB();
    if (!$pdo) {
        header('Location: registro.php?error=servidor');
        exit();
    }
    
    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    
    if ($stmt->fetch()) {
        header('Location: registro.php?error=correo_existe');
        exit();
    }
    
    // Generar ID único y hash de contraseña
    $id = uniqid('user_', true);
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (id, nombre, correo, contrasena, rol, creado_en) 
        VALUES (?, ?, ?, ?, 'CLIENT', NOW())
    ");
    
    $stmt->execute([$id, $nombre, $correo, $contrasena_hash]);
    
    // Redirigir al login con mensaje de éxito
    header('Location: index.php?registered=true');
    exit();
    
} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    header('Location: registro.php?error=servidor');
    exit();
}
?>