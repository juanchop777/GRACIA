<?php
session_start();

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Información de la sesión actual
echo "<h1>Información de Sesión</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar conexión a la base de datos
require_once 'config.php';
echo "<h1>Prueba de Conexión a la Base de Datos</h1>";
try {
    $pdo = conectarDB();
    if ($pdo) {
        echo "<p style='color:green'>Conexión exitosa a la base de datos</p>";
        
        // Verificar tabla administrador
        $stmt = $pdo->query("SHOW TABLES LIKE 'administrador'");
        if ($stmt->rowCount() > 0) {
            echo "<p>Tabla 'administrador' encontrada</p>";
            
            // Mostrar estructura de la tabla
            $stmt = $pdo->query("DESCRIBE administrador");
            echo "<h2>Estructura de la tabla 'administrador':</h2>";
            echo "<pre>";
            print_r($stmt->fetchAll());
            echo "</pre>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM administrador");
            $total = $stmt->fetch()['total'];
            echo "<p>Total de administradores: $total</p>";
        } else {
            echo "<p style='color:red'>Tabla 'administrador' no encontrada</p>";
        }
        
        // Verificar tabla usuarios
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->rowCount() > 0) {
            echo "<p>Tabla 'usuarios' encontrada</p>";
            
            // Mostrar estructura de la tabla
            $stmt = $pdo->query("DESCRIBE usuarios");
            echo "<h2>Estructura de la tabla 'usuarios':</h2>";
            echo "<pre>";
            print_r($stmt->fetchAll());
            echo "</pre>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
            $total = $stmt->fetch()['total'];
            echo "<p>Total de usuarios: $total</p>";
        } else {
            echo "<p style='color:red'>Tabla 'usuarios' no encontrada</p>";
        }
    } else {
        echo "<p style='color:red'>Error al conectar a la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Probar redirecciones
echo "<h1>Prueba de Redirecciones</h1>";
echo "<p>Haz clic en los siguientes enlaces para probar las redirecciones:</p>";
echo "<ul>";
echo "<li><a href='dashboard.php'>Dashboard de Usuario</a></li>";
echo "<li><a href='admin-dashboard.php'>Dashboard de Administrador</a></li>";
echo "</ul>";

// Formulario de prueba
echo "<h1>Formulario de Prueba</h1>";
echo "<form action='procesar_login.php' method='POST'>";
echo "<div>";
echo "<label for='correo'>Correo:</label>";
echo "<input type='email' id='correo' name='correo' value='admin@example.com'>";
echo "</div><br>";
echo "<div>";
echo "<label for='contrasena'>Contraseña:</label>";
echo "<input type='password' id='contrasena' name='contrasena' value='admin123'>";
echo "</div><br>";
echo "<button type='submit'>Iniciar Sesión</button>";
echo "</form>";
?>