<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generarPin() {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

header('Content-Type: application/json');
$step = isset($_POST['step']) ? $_POST['step'] : '';
$response = ['success' => false, 'message' => 'Acción no válida'];

if ($step === 'correo' && isset($_POST['correo'])) {
    $correo = trim($_POST['correo']);
    if (empty($correo)) {
        $response = ['success' => false, 'message' => 'Por favor, ingresa tu correo.'];
    } else {
        $pdo = conectarDB();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            $response = ['success' => false, 'message' => 'El correo no existe.'];
        } else {
            $pin = generarPin();
            $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $_SESSION['recuperar_correo'] = $correo;
            $_SESSION['recuperar_pin'] = $pin;
            $_SESSION['recuperar_expira'] = $expira;
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
                $response = ['success' => true, 'message' => 'Se ha enviado un PIN a tu correo. Ingresa el código para continuar.'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'No se pudo enviar el correo. Intenta más tarde.'];
            }
        }
    }
} elseif ($step === 'pin' && isset($_POST['pin'])) {
    $pin = trim($_POST['pin']);
    if (empty($pin)) {
        $response = ['success' => false, 'message' => 'Por favor, ingresa el PIN.'];
    } elseif (!isset($_SESSION['recuperar_pin']) || !isset($_SESSION['recuperar_expira']) || !isset($_SESSION['recuperar_correo'])) {
        $response = ['success' => false, 'message' => 'Sesión expirada. Intenta de nuevo.'];
    } elseif ($pin !== $_SESSION['recuperar_pin']) {
        $response = ['success' => false, 'message' => 'PIN incorrecto.'];
    } elseif (strtotime($_SESSION['recuperar_expira']) < time()) {
        unset($_SESSION['recuperar_pin'], $_SESSION['recuperar_expira']);
        $response = ['success' => false, 'message' => 'El PIN ha expirado. Solicita uno nuevo.'];
    } else {
        $response = ['success' => true, 'message' => 'PIN verificado. Ingresa tu nueva contraseña.'];
    }
} elseif ($step === 'nueva' && isset($_POST['nueva_contrasena']) && isset($_POST['confirmar_contrasena'])) {
    $nueva = $_POST['nueva_contrasena'];
    $confirmar = $_POST['confirmar_contrasena'];
    if (empty($nueva) || empty($confirmar)) {
        $response = ['success' => false, 'message' => 'Completa ambos campos.'];
    } elseif ($nueva !== $confirmar) {
        $response = ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
    } elseif (!isset($_SESSION['recuperar_correo'])) {
        $response = ['success' => false, 'message' => 'Sesión expirada. Intenta de nuevo.'];
    } else {
        $pdo = conectarDB();
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = ? WHERE correo = ?');
        $stmt->execute([$hash, $_SESSION['recuperar_correo']]);
        unset($_SESSION['recuperar_correo'], $_SESSION['recuperar_pin'], $_SESSION['recuperar_expira']);
        $response = ['success' => true, 'message' => 'Contraseña actualizada. Ya puedes iniciar sesión.'];
    }
}
echo json_encode($response); 