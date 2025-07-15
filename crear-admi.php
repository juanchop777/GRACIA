<?php
// Archivo para crear o verificar el usuario administrador
require_once 'config.php';

echo "<h1>Gestión de Usuario Administrador</h1>";

try {
    $pdo = conectarDB();
    
    // Verificar si existe el usuario admin
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? OR rol = 'admin'");
    $stmt->execute(['catfished03@gmail.com']);
    $admin_existente = $stmt->fetch();
    
    if ($admin_existente) {
        echo "<h2>Usuario Administrador Existente:</h2>";
        echo "<p><strong>ID:</strong> " . $admin_existente['id'] . "</p>";
        echo "<p><strong>Nombre:</strong> " . $admin_existente['nombre'] . "</p>";
        echo "<p><strong>Correo:</strong> " . $admin_existente['correo'] . "</p>";
        echo "<p><strong>Rol:</strong> " . $admin_existente['rol'] . "</p>";
        echo "<p><strong>Fecha registro:</strong> " . $admin_existente['fecha_registro'] . "</p>";
        
        // Opción para actualizar contraseña
        if (isset($_POST['actualizar_password'])) {
            $nueva_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            $stmt->execute([$nueva_password, $admin_existente['id']]);
            echo "<p style='color: green;'>✓ Contraseña actualizada a 'admin123'</p>";
        }
        
        echo "<form method='POST'>";
        echo "<button type='submit' name='actualizar_password' style='background: #8b7355; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Resetear Contraseña a 'admin123'</button>";
        echo "</form>";
        
    } else {
        echo "<h2>No se encontró usuario administrador. Creando uno nuevo...</h2>";
        
        // Crear nuevo usuario administrador
        $admin_id = generarUUID();
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (id, nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'Administrador GraciaShoes', 'admin@graciashoes.com', $admin_password, 'admin']);
        
        echo "<p style='color: green;'>✓ Usuario administrador creado exitosamente</p>";
        echo "<p><strong>Credenciales:</strong></p>";
        echo "<ul>";
        echo "<li>Email: admin@graciashoes.com</li>";
        echo "<li>Contraseña: admin123</li>";
        echo "</ul>";
    }
    
    // Opción para convertir usuario actual en admin
    if (isset($_POST['hacer_admin']) && isset($_POST['correo_usuario'])) {
        $correo_usuario = $_POST['correo_usuario'];
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = 'admin' WHERE correo = ?");
        $result = $stmt->execute([$correo_usuario]);
        
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Usuario $correo_usuario convertido a administrador</p>";
        } else {
            echo "<p style='color: red;'>✗ No se encontró usuario con ese correo</p>";
        }
    }
    
    echo "<h2>Convertir Usuario Existente a Administrador</h2>";
    echo "<form method='POST'>";
    echo "<p>Correo del usuario: <input type='email' name='correo_usuario' placeholder='usuario@ejemplo.com' required></p>";
    echo "<button type='submit' name='hacer_admin' style='background: #8b7355; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Hacer Administrador</button>";
    echo "</form>";
    
    // Mostrar todos los usuarios
    echo "<h2>Todos los Usuarios:</h2>";
    $stmt = $pdo->query("SELECT id, nombre, correo, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll();
    
    if (!empty($usuarios)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 10px;'>Nombre</th>";
        echo "<th style='padding: 10px;'>Correo</th>";
        echo "<th style='padding: 10px;'>Rol</th>";
        echo "<th style='padding: 10px;'>Fecha Registro</th>";
        echo "<th style='padding: 10px;'>Acciones</th>";
        echo "</tr>";
        
        foreach ($usuarios as $usuario) {
            echo "<tr>";
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
            echo "<td style='padding: 10px;'>";
            if ($usuario['rol'] !== 'admin') {
                echo "<form method='POST' style='display: inline;'>";
                echo "<input type='hidden' name='correo_usuario' value='" . $usuario['correo'] . "'>";
                echo "<button type='submit' name='hacer_admin' style='background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;'>Hacer Admin</button>";
                echo "</form>";
            } else {
                echo "<span style='color: green;'>✓ Admin</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<br><p><a href='index.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver a la tienda</a></p>";
?>
