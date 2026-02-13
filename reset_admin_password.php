<?php
/**
 * Script temporal para restablecer la contraseña de administrador.
 * USO: Abre en el navegador y sigue las instrucciones.
 * IMPORTANTE: Borra este archivo después de usarlo por seguridad.
 */

// Clave de seguridad: cámbiala y pásala en la URL como ?clave=TU_CLAVE
$CLAVE_RESET = 'gracia_reset_2025';

require_once 'config.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = trim($_POST['clave'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar = $_POST['confirmar_contrasena'] ?? '';

    if ($clave !== $CLAVE_RESET) {
        $error = 'Clave de seguridad incorrecta.';
    } elseif (empty($correo) || empty($nueva_contrasena)) {
        $error = 'Completa correo y nueva contraseña.';
    } elseif (strlen($nueva_contrasena) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva_contrasena !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("SELECT id, nombre, rol FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $error = 'No existe ningún usuario con ese correo.';
            } else {
                $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = ?, rol = 'admin' WHERE id = ?");
                $stmt->execute([$hash, $usuario['id']]);
                $mensaje = 'Contraseña actualizada correctamente. El usuario "' . htmlspecialchars($usuario['nombre']) . '" ahora puede entrar como admin con esa contraseña. <strong>Borra este archivo (reset_admin_password.php) por seguridad.</strong>';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Si no recuerdas el correo: listar admins (solo para ayudar)
$admins_correos = [];
try {
    $pdo = conectarDB();
    $stmt = $pdo->query("SELECT correo, nombre FROM usuarios WHERE rol = 'admin'");
    while ($row = $stmt->fetch()) {
        $admins_correos[] = $row;
    }
} catch (Exception $e) {
    // ignorar
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña admin</title>
    <style>
        body { font-family: sans-serif; max-width: 480px; margin: 2rem auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-weight: 600; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        button { background: #333; color: #fff; border: none; padding: 0.6rem 1.2rem; cursor: pointer; }
        .error { color: #c00; margin-bottom: 1rem; }
        .ok { color: #080; margin-bottom: 1rem; }
        .hint { font-size: 0.9rem; color: #666; margin-top: 0.25rem; }
        ul { margin: 0.5rem 0; padding-left: 1.2rem; }
    </style>
</head>
<body>
    <h1>Restablecer contraseña de administrador</h1>
    <p>Usa el <strong>correo</strong> del usuario al que quieres poner nueva contraseña. Si es un usuario normal, también se le asignará rol admin.</p>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($mensaje): ?>
        <p class="ok"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <?php if (count($admins_correos) > 0): ?>
        <p class="hint">Correos que actualmente son admin (por si no recuerdas):</p>
        <ul>
            <?php foreach ($admins_correos as $a): ?>
                <li><?php echo htmlspecialchars($a['correo']); ?> (<?php echo htmlspecialchars($a['nombre']); ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="clave">Clave de seguridad (la de este script)</label>
            <input type="text" id="clave" name="clave" value="<?php echo htmlspecialchars($CLAVE_RESET); ?>" required>
        </div>
        <div class="form-group">
            <label for="correo">Correo del usuario a restablecer</label>
            <input type="text" id="correo" name="correo" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="nueva_contrasena">Nueva contraseña (mín. 6 caracteres)</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required minlength="6">
        </div>
        <div class="form-group">
            <label for="confirmar_contrasena">Repetir contraseña</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="6">
        </div>
        <button type="submit">Restablecer contraseña</button>
    </form>

    <p class="hint" style="margin-top: 1.5rem;">Recuerda borrar <code>reset_admin_password.php</code> después de usarlo.</p>
</body>
</html>
