<?php
// Archivo para configurar la base de datos automáticamente
require_once 'config.php';

echo "<h1>Configuración de GraciaShoes</h1>";

// Verificar conexión al servidor MySQL
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Conexión al servidor MySQL exitosa</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error de conexión al servidor MySQL: " . $e->getMessage() . "</p>";
    echo "<h3>Soluciones posibles:</h3>";
    echo "<ul>";
    echo "<li>Asegúrate de que XAMPP/WAMP/MAMP esté ejecutándose</li>";
    echo "<li>Verifica que el servicio MySQL esté activo</li>";
    echo "<li>Revisa las credenciales en config.php</li>";
    echo "</ul>";
    exit;
}

// Verificar/crear base de datos
echo "<h2>Verificando base de datos...</h2>";
$resultado = verificarBaseDatos();
echo "<p style='color: blue;'>$resultado</p>";

// Crear tablas
echo "<h2>Creando tablas...</h2>";
try {
    $pdo = conectarDB();
    
    // Leer y ejecutar el archivo SQL
    $sql_file = 'scripts/graciashoess.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Tablas creadas exitosamente</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Archivo SQL no encontrado. Creando tablas manualmente...</p>";
        
        // Crear tablas manualmente
        $tablas = [
            "CREATE TABLE IF NOT EXISTS categorias (
                id VARCHAR(36) PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL UNIQUE,
                descripcion TEXT,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS usuarios (
                id VARCHAR(36) PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                correo VARCHAR(100) NOT NULL UNIQUE,
                contrasena VARCHAR(255) NOT NULL,
                rol ENUM('admin', 'usuario') DEFAULT 'usuario',
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS productos (
                id VARCHAR(36) PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                precio DECIMAL(10,2) NOT NULL,
                stock INT DEFAULT 0,
                categoria_id VARCHAR(36),
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS pedidos (
                id VARCHAR(36) PRIMARY KEY,
                usuario_id VARCHAR(36) NOT NULL,
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                estado ENUM('pendiente', 'procesado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS pedido_items (
                id VARCHAR(36) PRIMARY KEY,
                pedido_id VARCHAR(36) NOT NULL,
                producto_id VARCHAR(36) NOT NULL,
                cantidad INT NOT NULL,
                precio_unitario DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
                FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS movimientos_inventario (
                id VARCHAR(36) PRIMARY KEY,
                producto_id VARCHAR(36) NOT NULL,
                tipo ENUM('entrada', 'salida') NOT NULL,
                cantidad INT NOT NULL,
                descripcion TEXT,
                realizado_por VARCHAR(36),
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
                FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS facturas (
                id VARCHAR(36) PRIMARY KEY,
                pedido_id VARCHAR(36) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS tipos_gasto (
                id VARCHAR(36) PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS transacciones_contables (
                id VARCHAR(36) PRIMARY KEY,
                tipo ENUM('ingreso', 'gasto') NOT NULL,
                descripcion TEXT NOT NULL,
                monto DECIMAL(10,2) NOT NULL,
                relacionado_con ENUM('pedido', 'gasto', 'otro') DEFAULT 'otro',
                referencia_id VARCHAR(36),
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                realizado_por VARCHAR(36),
                FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
            )"
        ];
        
        foreach ($tablas as $tabla_sql) {
            $pdo->exec($tabla_sql);
        }
        echo "<p style='color: green;'>✓ Tablas creadas manualmente</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al crear tablas: " . $e->getMessage() . "</p>";
}

// Insertar datos iniciales
echo "<h2>Insertando datos iniciales...</h2>";
try {
    // Verificar si ya hay datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $usuarios_existentes = $stmt->fetch()['total'];
    
    if ($usuarios_existentes == 0) {
        // Insertar categorías
        $categorias = [
            ['cat-zapatos-001', 'Zapatos'],
            ['cat-bolsos-002', 'Bolsos'],
            ['cat-accesorios-003', 'Accesorios']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categorias (id, nombre) VALUES (?, ?)");
        foreach ($categorias as $categoria) {
            $stmt->execute($categoria);
        }
        
        // Insertar usuario administrador
        $admin_id = 'admin-001';
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (id, nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'Administrador', 'admin@graciashoes.com', $admin_password, 'admin']);
        
        // Insertar productos de ejemplo
        $productos = [
            ['prod-zapatos-001', 'Zapatos Elegantes', 'Zapatos elegantes para ocasiones especiales', 89.99, 15, 'cat-zapatos-001'],
            ['prod-bolsos-001', 'Bolso Tejido', 'Bolso tejido artesanal', 159.99, 8, 'cat-bolsos-002'],
            ['prod-acc-001', 'Set de Perlas', 'Conjunto de collar y aretes de perlas', 49.99, 25, 'cat-accesorios-003']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO productos (id, nombre, descripcion, precio, stock, categoria_id) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($productos as $producto) {
            $stmt->execute($producto);
        }
        
        echo "<p style='color: green;'>✓ Datos iniciales insertados</p>";
        echo "<p><strong>Usuario administrador creado:</strong></p>";
        echo "<ul>";
        echo "<li>Email: admin@graciashoes.com</li>";
        echo "<li>Contraseña: admin123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ Ya existen datos en la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al insertar datos: " . $e->getMessage() . "</p>";
}

echo "<h2>Configuración completada</h2>";
echo "<p><a href='index.php' style='background: #8b7355; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la tienda</a></p>";
?>
