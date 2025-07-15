<?php
// Archivo para debuggear el proceso de login
session_start();
require_once 'config.php';

echo "<h1>Debug del Sistema de Login</h1>";

// Mostrar información de la sesión actual
echo "<h2>Información de Sesión Actual:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p><strong>Usuario ID:</strong> " . $_SESSION['usuario_id'] . "</p>";
    echo "<p><strong>Nombre:</strong> " . ($_SESSION['nombre'] ?? 'No definido') . "</p>";
    echo "<p><strong>Correo:</strong> " . ($_SESSION['correo'] ?? 'No definido') . "</p>";
    echo "<p><strong>Rol:</strong> " . ($_SESSION['rol'] ?? 'No definido') . "</p>";
    
    // Verificar información en la base de datos
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario_db = $stmt->fetch();
        
        if ($usuario_db) {
            echo "<h3>Información en Base de Datos:</h3>";
            echo "<p><strong>Nombre:</strong> " . $usuario_db['nombre'] . "</p>";
            echo "<p><strong>Correo:</strong> " . $usuario_db['correo'] . "</p>";
            echo "<p><strong>Rol:</strong> " . $usuario_db['rol'] . "</p>";
            
            if ($usuario_db['rol'] !== $_SESSION['rol']) {
                echo "<p style='color: red;'>⚠ INCONSISTENCIA: El rol en sesión no coincide con la base de datos</p>";
                echo "<p>Actualizando sesión...</p>";
                $_SESSION['rol'] = $usuario_db['rol'];
                $_SESSION['nombre'] = $usuario_db['nombre'];
                $_SESSION['correo'] = $usuario_db['correo'];
                echo "<p style='color: green;'>✓ Sesión actualizada</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Usuario no encontrado en la base de datos</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al consultar base de datos: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No hay sesión activa</p>";
}

// Formulario de login de prueba
echo "<h2>Probar Login:</h2>";
if (isset($_POST['test_login'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "<h3>Usuario encontrado:</h3>";
            echo "<p><strong>Nombre:</strong> " . $usuario['nombre'] . "</p>";
            echo "<p><strong>Correo:</strong> " . $usuario['correo'] . "</p>";
            echo "<p><strong>Rol:</strong> " . $usuario['rol'] . "</p>";
            
            if (password_verify($contrasena, $usuario['contrasena'])) {
                echo "<p style='color: green;'>✓ Contraseña correcta</p>";
                
                // Simular login
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['correo'] = $usuario['correo'];
                $_SESSION['rol'] = $usuario['rol'];
                
                echo "<p style='color: green;'>✓ Sesión iniciada</p>";
                
                if ($usuario['rol'] === 'admin') {
                    echo "<p style='color: blue;'>→ Debería redirigir a admin-dashboard.php</p>";
                    echo "<p><a href='admin-dashboard.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Panel Admin</a></p>";
                } else {
                    echo "<p style='color: blue;'>→ Debería redirigir a dashboard.php</p>";
                    echo "<p><a href='dashboard.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Dashboard</a></p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Contraseña incorrecta</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Usuario no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='POST'>";
echo "<p>Correo: <input type='email' name='correo' value='admin@graciashoes.com' required></p>";
echo "<p>Contraseña: <input type='password' name='contrasena' value='admin123' required></p>";
echo "<button type='submit' name='test_login' style='background: #8b7355; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Probar Login</button>";
echo "</form>";

echo "<br><p><a href='logout.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cerrar Sesión</a></p>";
echo "<p><a href='index.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver a la tienda</a></p>";
?>
