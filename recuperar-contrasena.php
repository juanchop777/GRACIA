<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generarPin() {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

$step = isset($_POST['step']) ? $_POST['step'] : 'correo';
$error = '';
$success = '';

if ($step === 'correo' && isset($_POST['correo'])) {
    $correo = trim($_POST['correo']);
    if (empty($correo)) {
        $error = 'Por favor, ingresa tu correo.';
    } else {
        $pdo = conectarDB();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            $error = 'El correo no existe.';
        } else {
            $pin = generarPin();
            $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            // Guardar PIN y expiración en la sesión (puedes usar una tabla en BD si prefieres)
            $_SESSION['recuperar_correo'] = $correo;
            $_SESSION['recuperar_pin'] = $pin;
            $_SESSION['recuperar_expira'] = $expira;
            // Enviar correo
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_SMTP_SECURE;
                $mail->Port = MAIL_PORT;
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña - Gracia Shoes';
                $mail->Body = 'Tu código de recuperación es: <b>' . $pin . '</b><br>Este código es válido por 10 minutos.';
                $mail->send();
                $success = 'Se ha enviado un PIN a tu correo. Ingresa el código para continuar.';
                $step = 'pin';
            } catch (Exception $e) {
                $error = 'No se pudo enviar el correo. Error: ' . $mail->ErrorInfo . ' | ' . $e->getMessage();
            }
        }
    }
}

if ($step === 'pin' && isset($_POST['pin'])) {
    $pin = trim($_POST['pin']);
    if (empty($pin)) {
        $error = 'Por favor, ingresa el PIN.';
    } elseif (!isset($_SESSION['recuperar_pin']) || !isset($_SESSION['recuperar_expira']) || !isset($_SESSION['recuperar_correo'])) {
        $error = 'Sesión expirada. Intenta de nuevo.';
        $step = 'correo';
    } elseif ($pin !== $_SESSION['recuperar_pin']) {
        $error = 'PIN incorrecto.';
        $step = 'pin';
    } elseif (strtotime($_SESSION['recuperar_expira']) < time()) {
        $error = 'El PIN ha expirado. Solicita uno nuevo.';
        unset($_SESSION['recuperar_pin'], $_SESSION['recuperar_expira']);
        $step = 'correo';
    } else {
        $success = 'PIN verificado. Ingresa tu nueva contraseña.';
        $step = 'nueva';
    }
}

if ($step === 'nueva' && isset($_POST['nueva_contrasena'])) {
    $nueva = $_POST['nueva_contrasena'];
    $confirmar = $_POST['confirmar_contrasena'];
    if (empty($nueva) || empty($confirmar)) {
        $error = 'Completa ambos campos.';
        $step = 'nueva';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
        $step = 'nueva';
    } elseif (!isset($_SESSION['recuperar_correo'])) {
        $error = 'Sesión expirada. Intenta de nuevo.';
        $step = 'correo';
    } else {
        $pdo = conectarDB();
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = ? WHERE correo = ?');
        $stmt->execute([$hash, $_SESSION['recuperar_correo']]);
        unset($_SESSION['recuperar_correo'], $_SESSION['recuperar_pin'], $_SESSION['recuperar_expira']);
        $success = 'Contraseña actualizada. Ya puedes iniciar sesión.';
        $step = 'final';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <!-- CSS específico para recuperación -->
    <link rel="stylesheet" href="recuperar-style.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="recovery-page">
    <div class="background-overlay"></div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="logo">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1>Recuperar Contraseña</h1>
                <p class="subtitle">
                    <?php if ($step === 'correo'): ?>
                        Ingresa tu correo electrónico para recibir un código de verificación
                    <?php elseif ($step === 'pin'): ?>
                        Ingresa el código de 6 dígitos enviado a tu correo
                    <?php elseif ($step === 'nueva'): ?>
                        Crea tu nueva contraseña segura
                    <?php elseif ($step === 'final'): ?>
                        ¡Contraseña actualizada exitosamente!
                    <?php endif; ?>
                </p>
            </div>

            <div class="card-body">
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-error">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success) && $success): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 11.08V12A10 10 0 1 1 5.93 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!isset($step) || $step === 'correo'): ?>
                    <form method="POST" class="form">
                        <input type="hidden" name="step" value="correo">
                        <div class="form-group">
                            <label for="correo">Correo electrónico</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <input type="email" name="correo" id="correo" placeholder="tu@email.com" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Enviar código PIN
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="12,5 19,12 12,19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>

                <?php elseif ($step === 'pin'): ?>
                    <form method="POST" class="form">
                        <input type="hidden" name="step" value="pin">
                        <div class="form-group">
                            <label for="pin">Código PIN</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                    <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <input type="text" name="pin" id="pin" maxlength="6" placeholder="000000" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Verificar código
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="12,5 19,12 12,19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>

                <?php elseif ($step === 'nueva'): ?>
                    <form method="POST" class="form">
                        <input type="hidden" name="step" value="nueva">
                        <div class="form-group">
                            <label for="nueva_contrasena">Nueva contraseña</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                    <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_contrasena">Confirmar contraseña</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                    <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="••••••••" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Cambiar contraseña
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 11.08V12A10 10 0 1 1 5.93 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>

                <?php elseif ($step === 'final'): ?>
                    <div class="success-state">
                        <div class="success-icon">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 11.08V12A10 10 0 1 1 5.93 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>¡Contraseña actualizada!</h3>
                        <p>Tu contraseña ha sido cambiada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.</p>
                        <a href="index.php" class="btn btn-success">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 3H19A2 2 0 0 1 21 5V19A2 2 0 0 1 19 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="10,17 15,12 10,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Iniciar sesión
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
