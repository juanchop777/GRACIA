<?php
// Archivo para verificar el estado de la base de datos
require_once 'config.php';

echo "<h1>Verificación de Base de Datos</h1>";

try {
    $pdo = conectarDB();
    echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";
    
    // Verificar tablas existentes
    echo "<h2>Tablas en la base de datos:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        echo "<p style='color: red;'>✗ No hay tablas en la base de datos</p>";
        echo "<p><a href='setup.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ejecutar Setup</a></p>";
    } else {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>$tabla</li>";
        }
        echo "</ul>";
        
        // Verificar datos en tabla usuarios
        if (in_array('usuarios', $tablas)) {
            echo "<h2>Usuarios en la base de datos:</h2>";
            $stmt = $pdo->query("SELECT id, nombre, correo, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
            $usuarios = $stmt->fetchAll();
            
            if (empty($usuarios)) {
                echo "<p style='color: orange;'>⚠ La tabla usuarios existe pero está vacía</p>";
                echo "<p><a href='crear_admin.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Crear Usuario Admin</a></p>";
            } else {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f5f5f5;'>";
                echo "<th style='padding: 10px;'>ID</th>";
                echo "<th style='padding: 10px;'>Nombre</th>";
                echo "<th style='padding: 10px;'>Correo</th>";
                echo "<th style='padding: 10px;'>Rol</th>";
                echo "<th style='padding: 10px;'>Fecha</th>";
                echo "</tr>";
                
                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td style='padding: 10px;'>" . substr($usuario['id'], 0, 8) . "...</td>";
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($usuario['nombre']) . "</td>";
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($usuario['correo']) . "</td>";
                    echo "<td style='padding: 10px;'>";
                    if ($usuario['rol'] === 'admin') {
                        echo "<span style='color: red; font-weight: bold;'>ADMIN</span>";
                    } else {
                        echo "Usuario";
                    }
                    echo "</td>";
                    echo "<td style='padding: 10px;'>" . $usuario['fecha_registro'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<h3>Posibles soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que MySQL esté ejecutándose</li>";
    echo "<li>Verifica las credenciales en config.php</li>";
    echo "<li>Crea la base de datos 'graciashoess' manualmente</li>";
    echo "</ul>";
}

echo "<br><p><a href='admin-usuarios.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver a Usuarios</a></p>";
?>
