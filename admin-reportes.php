<?php
session_start();
require_once 'config.php';

// Verificar que el usuario sea administrador
if (!estaLogueado() || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Función para verificar si una columna existe en una tabla
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $stmt->rowCount() > 0;
}

// Obtener datos para reportes
try {
    $pdo = conectarDB();

    // Filtro de período para pedidos
    $periodo = $_GET['periodo'] ?? 'month';
    $pedidos = [];
    $error = '';
    if (columnExists($pdo, 'pedidos', 'fecha') && columnExists($pdo, 'pedidos', 'estado')) {
        $sql = "SELECT DATE(p.fecha) as fecha, COUNT(*) as pedido_count
                FROM pedidos p
                WHERE p.estado = 'entregado'
                GROUP BY DATE(p.fecha)";
        if ($periodo === 'week') {
            $sql = "SELECT WEEK(p.fecha) as fecha, COUNT(*) as pedido_count
                    FROM pedidos p
                    WHERE p.estado = 'entregado'
                    GROUP BY WEEK(p.fecha)";
        } elseif ($periodo === 'month') {
            $sql = "SELECT DATE_FORMAT(p.fecha, '%Y-%m') as fecha, COUNT(*) as pedido_count
                    FROM pedidos p
                    WHERE p.estado = 'entregado'
                    GROUP BY DATE_FORMAT(p.fecha, '%Y-%m')";
        } elseif ($periodo === 'year') {
            $sql = "SELECT DATE_FORMAT(p.fecha, '%Y') as fecha, COUNT(*) as pedido_count
                    FROM pedidos p
                    WHERE p.estado = 'entregado'
                    GROUP BY DATE_FORMAT(p.fecha, '%Y')";
        }
        $stmt = $pdo->query($sql);
        $pedidos = $stmt->fetchAll();
        if (empty($pedidos)) {
            $error = 'No hay pedidos registrados para el período seleccionado o estado "entregado".';
        }
    } else {
        $error = 'Faltan columnas esenciales (fecha, estado) en la tabla pedidos.';
    }

    // Usuario más activo
    $usuario_mas_activo = null;
    if (columnExists($pdo, 'usuarios', 'nombre') && columnExists($pdo, 'pedidos', 'usuario_id')) {
        $stmt = $pdo->query("
            SELECT u.id, u.nombre, COUNT(p.usuario_id) as pedido_count
            FROM usuarios u
            LEFT JOIN pedidos p ON u.id = p.usuario_id
            WHERE p.estado = 'entregado'
            GROUP BY u.id, u.nombre
            ORDER BY pedido_count DESC
            LIMIT 1
        ");
        $usuario_mas_activo = $stmt->fetch();
    } else {
        $error .= ' Faltan columnas para usuario más activo (nombre, usuario_id).';
    }

    // Estadísticas generales
    $totalPedidos = 0;
    if (columnExists($pdo, 'pedidos', 'estado')) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
        $totalPedidos = $stmt->fetch()['total'] ?? 0;
    } else {
        $error .= ' Faltan columnas para total pedidos (estado).';
    }

} catch (Exception $e) {
    $error = "Error al obtener reportes: " . $e->getMessage();
    error_log("Reporte error: " . $e->getMessage());
    $pedidos = [];
    $usuario_mas_activo = null;
    $totalPedidos = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <link rel="icon" type="image/png" href="img/favicon.png">
    <style>
        .admin-sidebar {
            width: 250px;
            background-color: #6d5a42;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 130px;
            z-index: 900;
        }
        
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .admin-sidebar li {
            padding: 0;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .admin-sidebar i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 100px;
        }
        
        .admin-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .admin-card h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .admin-card h2 i {
            margin-right: 10px;
            color: #8b7355;
        }
        
        .chart-container {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .reporte-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .reporte-card {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .reporte-card h3 {
            margin: 0;
            color: #8b7355;
            font-size: 1.5rem;
        }
        
        .reporte-card p {
            margin: 5px 0 0 0;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height:70px;">
                    </a>
                </div>
                <div style="flex-grow: 1; text-align: center;">
                    <h2 style="margin: 0; color: #8b7355;">Panel de Administración</h2>
                </div>
                <div class="header-actions">
                    <a href="logout.php" class="login-btn">CERRAR SESIÓN</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin-productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="admin-categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
            <li><a href="admin-pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
            <li><a href="admin-usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="admin-inventario.php"><i class="fas fa-warehouse"></i> Inventario</a></li>
            <li><a href="admin-reportes.php" class="active"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="admin-configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="admin-index.php"><i class="fas fa-home"></i> Ver Tienda</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Reportes</h1>
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Filtros de Período -->
        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Filtrar Pedidos</h2>
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 20px;">
                <select name="periodo" onchange="this.form.submit()">
                    <option value="day" <?php echo $periodo === 'day' ? 'selected' : ''; ?>>Por Día</option>
                    <option value="week" <?php echo $periodo === 'week' ? 'selected' : ''; ?>>Por Semana</option>
                    <option value="month" <?php echo $periodo === 'month' ? 'selected' : ''; ?>>Por Mes</option>
                    <option value="year" <?php echo $periodo === 'year' ? 'selected' : ''; ?>>Por Año</option>
                </select>
            </form>
        </div>

        <!-- Estadísticas Generales -->
        <div class="admin-card">
            <h2><i class="fas fa-chart-pie"></i> Estadísticas Generales</h2>
            <div class="reporte-grid">
                <div class="reporte-card">
                    <h3><?php echo $totalPedidos; ?></h3>
                    <p>Total Pedidos</p>
                </div>
            </div>
        </div>

        <!-- Gráfica de Pedidos -->
        <div class="admin-card">
            <h2><i class="fas fa-chart-line"></i> Pedidos por Período</h2>
            <div class="chart-container">
                <canvas id="pedidosChart"></canvas>
            </div>
        </div>

        <!-- Usuario Más Activo -->
        <div class="admin-card">
            <h2><i class="fas fa-user"></i> Usuario Más Activo</h2>
            <?php if ($usuario_mas_activo): ?>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario_mas_activo['nombre']); ?></p>
                <p><strong>Pedidos:</strong> <?php echo $usuario_mas_activo['pedido_count']; ?></p>
            <?php else: ?>
                <p>No hay datos de usuarios activos o las columnas necesarias no existen.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Datos para la gráfica de pedidos
        const pedidosData = <?php echo json_encode(array_column($pedidos, 'pedido_count')); ?>;
        const fechas = <?php echo json_encode(array_column($pedidos, 'fecha')); ?>;

        // Configurar gráfica con Chart.js
        const ctx = document.getElementById('pedidosChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: fechas,
                datasets: [{
                    label: 'Número de Pedidos',
                    data: pedidosData,
                    borderColor: '#8b7355',
                    backgroundColor: 'rgba(139, 115, 85, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Pedidos'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Período'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>