<?php
session_start();
require_once 'config.php';

// Verificar que sea administrador
requerirAdmin();

// Obtener lista de usuarios
try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY creado_en DESC");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .users-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .role-admin {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .role-client {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Administrar Usuarios</h1>
                    <p>Gestiona los usuarios del sistema</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary">Cerrar sesión</a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h2>Lista de Usuarios</h2>
                <p>Total de usuarios registrados: <?php echo count($usuarios); ?></p>
                
                <?php if (empty($usuarios)): ?>
                    <p>No hay usuarios registrados.</p>
                <?php else: ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo $usuario['rol'] === 'ADMIN' ? 'role-admin' : 'role-client'; ?>">
                                            <?php echo htmlspecialchars($usuario['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['creado_en'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>