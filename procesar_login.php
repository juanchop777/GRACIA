<?php
session_start();
require_once 'config.php';

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$correo = isset($_POST['correo']) ? limpiarDatos($_POST['correo']) : '';
$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

// Verificar que los campos no estén vacíos
if (empty($correo) || empty($contrasena)) {
    header('Location: index.php?error=credenciales');
    exit();
}

try {
    $pdo = conectarDB();
    if (!$pdo) {
        // Error de conexión a la base de datos
        header('Location: index.php?error=servidor');
        exit();
    }
    
    // Primero verificamos si es un administrador
    // Nota: Ajustamos el nombre de la columna de contraseña según tu base de datos
    $stmt = $pdo->prepare("SELECT * FROM administrador WHERE email = ?");
    $stmt->execute([$correo]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Para depuración: verificar si encontramos un administrador
        error_log("Administrador encontrado: " . $admin['nombre']);
        
        // Verificar contraseña del administrador
        // Nota: Verificamos si la contraseña está almacenada como texto plano o con hash
        if ($contrasena === $admin['contraseña'] || (function_exists('password_verify') && password_verify($contrasena, $admin['contraseña']))) {
            // Crear sesión de administrador
            $_SESSION['usuario_id'] = $admin['id'];
            $_SESSION['nombre'] = $admin['nombre'];
            $_SESSION['correo'] = $admin['email'];
            $_SESSION['rol'] = 'ADMIN';
            
            // Para depuración
            error_log("Sesión de administrador creada. Redirigiendo a admin-dashboard.php");
            
            // Redirigir al panel de administrador
            header('Location: admin-dashboard.php');
            exit();
        } else {
            // Para depuración
            error_log("Contraseña incorrecta para administrador");
            header('Location: index.php?error=credenciales');
            exit();
        }
    } else {
        // Para depuración
        error_log("No es administrador, verificando si es usuario normal");
    }
    
    // Si no es administrador, verificamos si es un usuario normal
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        // Para depuración
        error_log("Usuario no encontrado");
        header('Location: index.php?error=credenciales');
        exit();
    }
    
    // Para depuración
    error_log("Usuario encontrado: " . $usuario['nombre']);
    
    // Verificar contraseña del usuario
    // Nota: Verificamos si la contraseña está almacenada como texto plano o con hash
    if ($contrasena === $usuario['contrasena'] || (function_exists('password_verify') && password_verify($contrasena, $usuario['contrasena']))) {
        // Crear sesión de usuario normal
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = 'CLIENTE';
        
        // Para depuración
        error_log("Sesión de cliente creada. Redirigiendo a dashboard.php");
        
        // Redirigir al dashboard de usuario
        header('Location: dashboard.php');
        exit();
    } else {
        // Para depuración
        error_log("Contraseña incorrecta para usuario");
        header('Location: index.php?error=credenciales');
        exit();
    }
    
} catch (Exception $e) {
    // Para depuración
    error_log("Error en login: " . $e->getMessage());
    header('Location: index.php?error=servidor&mensaje=' . urlencode($e->getMessage()));
    exit();
}
?>