<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!estaLogueado()) {
    $_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
$usuario = obtenerUsuarioActual();
if (!$usuario) {
    header('Location: logout.php');
    exit;
}

// Inicializar variables
$error = '';
$success = '';

// Manejar mensajes de la sesión
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Procesar el pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    // Obtener datos del carrito desde el formulario
    $items_json = $_POST['cart_items'];
    $items = json_decode($items_json, true);
    
    if (empty($items)) {
        $error = 'Tu carrito está vacío';
    } else {
        try {
            $pdo = conectarDB();
            $pdo->beginTransaction();
            
            // Verificar stock disponible
            $stock_insuficiente = false;
            $productos_sin_stock = [];
            
            foreach ($items as &$item) {
                $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $producto = $stmt->fetch();
                
                if (!$producto || $producto['stock'] < $item['quantity']) {
                    $stock_insuficiente = true;
                    $productos_sin_stock[] = $item['name'];
                }
            }
            
            if ($stock_insuficiente) {
                $error = 'Stock insuficiente para: ' . implode(', ', $productos_sin_stock);
                $pdo->rollBack();
            } else {
                // Crear pedido
                $pedido_id = generarUUID();
                $stmt = $pdo->prepare("INSERT INTO pedidos (id, usuario_id, fecha, estado) VALUES (?, ?, NOW(), 'pendiente')");
                $stmt->execute([$pedido_id, $_SESSION['usuario_id']]);
                
                $total_pedido = 0;
                
                // Agregar items del pedido
                foreach ($items as $item) {
                    $item_id = generarUUID();
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_pedido += $subtotal;
                    
                    $stmt = $pdo->prepare("INSERT INTO pedido_items (id, pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$item_id, $pedido_id, $item['id'], $item['quantity'], $item['price']]);
                    
                    // Actualizar stock
                    $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);
                    
                    // Registrar movimiento de inventario
                    $movimiento_id = generarUUID();
                    $stmt = $pdo->prepare("INSERT INTO movimientos_inventario (id, producto_id, tipo, cantidad, descripcion, realizado_por, fecha) VALUES (?, ?, 'salida', ?, ?, ?, NOW())");
                    $stmt->execute([$movimiento_id, $item['id'], $item['quantity'], "Venta - Pedido: $pedido_id", $_SESSION['usuario_id']]);
                }
                
                // Crear factura
                $factura_id = generarUUID();
                $stmt = $pdo->prepare("INSERT INTO facturas (id, pedido_id, total, fecha) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$factura_id, $pedido_id, $total_pedido]);
                
                // Registrar transacción contable
                $transaccion_id = generarUUID();
                $stmt = $pdo->prepare("INSERT INTO transacciones_contables (id, tipo, descripcion, monto, relacionado_con, referencia_id, fecha, realizado_por) VALUES (?, 'ingreso', ?, ?, 'pedido', ?, NOW(), ?)");
                $stmt->execute([$transaccion_id, "Venta - Pedido: $pedido_id", $total_pedido, $pedido_id, $_SESSION['usuario_id']]);
                
                $pdo->commit();
                
                // Preparar mensaje de éxito para JavaScript
                $success = 'Pago realizado con éxito. Tu número de pedido es: ' . substr($pedido_id, 0, 8);
                $_SESSION['success'] = $success;
                
                // Limpiar carrito
                echo "<script>localStorage.removeItem('cart');</script>";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al procesar pedido: " . $e->getMessage());
            $error = 'Error al procesar el pedido: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
        
        .checkout-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .checkout-section h2 {
            margin-top: 0;
            color: #8b7355;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: normal;
        }
        
        .checkout-summary {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .checkout-summary h2 {
            margin-top: 0;
            color: #8b7355;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: normal;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #8b7355;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .checkout-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 576px) {
            .checkout-form {
                grid-template-columns: 1fr;
            }
        }
        
        /* Enhanced Payment Method Styles */
        .payment-methods {
            margin-top: 1rem;
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border: 2px solid #ddd;
            border-radius: 10px;
            background: #fff;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            font-size: 1.1rem;
            color: #333;
        }
        
        .payment-method:hover {
            border-color: #8b7355;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 1.5rem;
            transform: scale(1.5);
            cursor: pointer;
            position: absolute;
            left: 1rem;
            opacity: 0;
        }
        
        .payment-method .method-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #8b7355;
        }
        
        .payment-method.selected {
            border-color: #8b7355;
            background: #f9fafb;
        }
        
        .payment-method.selected .method-icon::after {
            content: '\f00c'; /* Checkmark icon */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 1.5rem;
            color: #8b7355;
        }
        
        .payment-details {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fafafa;
            animation: fadeIn 0.3s ease;
        }
        
        .payment-details.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .payment-details .form-group {
            margin-bottom: 1.5rem;
        }
        
        .payment-details label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #444;
        }
        
        .payment-details input,
        .payment-details textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .payment-details input:focus,
        .payment-details textarea:focus {
            border-color: #8b7355;
            outline: none;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #8b7355;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #6d5a42;
        }
        
        .btn-secondary {
            background: #ddd;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #ccc;
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
                <nav class="nav">
                    <a href="index.php">INICIO</a>
                    <a href="tienda.php">TIENDA</a>
                    <a href="nosotros.php">NOSOTROS</a>
                    <a href="colecciones.php">COLECCIONES</a>
                    <a href="contacto.php">CONTACTO</a>
                </nav>
                <div class="header-actions">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="admin-dashboard.php" class="login-btn">PANEL ADMIN</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="login-btn">MI CUENTA</a>
                        <?php endif; ?>
                        <a href="logout.php" class="login-btn">CERRAR SESIÓN</a>
                    <?php else: ?>
                        <button class="login-btn" onclick="openLoginModal()">INICIAR SESIÓN</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Finalizar Compra</h1>
                <p>Completa tu información para procesar tu pedido</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="checkout-container">
                <!-- Información de Envío y Pago -->
                <div class="checkout-section">
                    <h2>Información de Envío</h2>
                    
                    <form id="checkout-form" method="POST" action="checkout.php">
                        <input type="hidden" name="cart_items" id="cart-items-input" value="">
                        
                        <div class="checkout-form">
                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="correo">Correo electrónico</label>
                                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" placeholder="Tu número de teléfono" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" placeholder="Tu dirección de envío" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" id="ciudad" name="ciudad" placeholder="Tu ciudad" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="codigo_postal">Código postal</label>
                                <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Tu código postal" required>
                            </div>
                        </div>
                        
                        <h2 style="margin-top: 2rem;">Método de Pago</h2>
                        
                        <ul class="payment-methods">
                            <li class="payment-method" data-method="tarjeta">
                                <input type="radio" name="metodo_pago" value="tarjeta" checked>
                                <i class="method-icon fas fa-credit-card"></i>
                                <span>Tarjeta de Crédito/Débito</span>
                            </li>
                            <li class="payment-method" data-method="transferencia">
                                <input type="radio" name="metodo_pago" value="transferencia">
                                <i class="method-icon fas fa-university"></i>
                                <span>Transferencia Bancaria</span>
                            </li>
                            <li class="payment-method" data-method="efectivo">
                                <input type="radio" name="metodo_pago" value="efectivo">
                                <i class="method-icon fas fa-money-bill-wave"></i>
                                <span>Pago en Efectivo (Contraentrega)</span>
                            </li>
                        </ul>

                        <!-- Payment Details -->
                        <div id="tarjeta-details" class="payment-details active">
                            <div class="checkout-form">
                                <div class="form-group">
                                    <label for="numero_tarjeta">Número de tarjeta</label>
                                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="XXXX XXXX XXXX XXXX" required>
                                </div>
                                <div class="form-group">
                                    <label for="nombre_tarjeta">Nombre en la tarjeta</label>
                                    <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" placeholder="Nombre como aparece en la tarjeta" required>
                                </div>
                                <div class="form-group">
                                    <label for="fecha_expiracion">Fecha de expiración</label>
                                    <input type="text" id="fecha_expiracion" name="fecha_expiracion" placeholder="MM/AA" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                        </div>
                        
                        <div id="transferencia-details" class="payment-details">
                            <div class="checkout-form">
                                <div class="form-group">
                                    <label for="banco_transferencia">Banco</label>
                                    <input type="text" id="banco_transferencia" name="banco_transferencia" placeholder="Nombre del banco" required>
                                </div>
                                <div class="form-group">
                                    <label for="numero_cuenta">Número de cuenta</label>
                                    <input type="text" id="numero_cuenta" name="numero_cuenta" placeholder="Número de cuenta" required>
                                </div>
                                <div class="form-group">
                                    <label for="titular_cuenta">Titular de la cuenta</label>
                                    <input type="text" id="titular_cuenta" name="titular_cuenta" placeholder="Nombre del titular" required>
                                </div>
                                <div class="form-group">
                                    <label for="fecha_transferencia">Fecha de transferencia</label>
                                    <input type="date" id="fecha_transferencia" name="fecha_transferencia" required>
                                </div>
                                <div class="form-group">
                                    <label for="referencia_transferencia">Referencia o comprobante</label>
                                    <input type="text" id="referencia_transferencia" name="referencia_transferencia" placeholder="Código de referencia o número de transacción" required>
                                </div>
                            </div>
                        </div>
                        
                        <div id="efectivo-details" class="payment-details">
                            <div class="checkout-form">
                                <div class="form-group">
                                    <label>Monto a pagar</label>
                                    <input type="text" id="monto_efectivo" name="monto_efectivo" placeholder="Ingresa el monto exacto a pagar" required>
                                </div>
                                <div class="form-group">
                                    <label>Dirección exacta de entrega</label>
                                    <input type="text" id="direccion_entrega_efectivo" name="direccion_entrega_efectivo" placeholder="Dirección precisa para la entrega" required>
                                </div>
                                <div class="form-group">
                                    <label>Horario preferido</label>
                                    <input type="text" id="horario_entrega" name="horario_entrega" placeholder="Ejemplo: 9:00 AM - 12:00 PM" required>
                                </div>
                                <div class="form-group">
                                    <label>Instrucciones</label>
                                    <p>Pago en efectivo se realizará al momento de la entrega. Por favor, ten el monto exacto listo.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 2rem;">
                            <label for="notas">Notas adicionales (opcional)</label>
                            <textarea id="notas" name="notas" rows="3" placeholder="Instrucciones especiales para la entrega"></textarea>
                        </div>
                        
                        <button type="submit" name="finalizar_pedido" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%;">Finalizar Pedido</button>
                    </form>
                </div>
                
                <!-- Resumen del Pedido -->
                <div class="checkout-summary">
                    <h2>Resumen del Pedido</h2>
                    
                    <div id="checkout-items">
                        <p>Cargando productos...</p>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="checkout-total">$0.00</span>
                    </div>
                    
                    <button class="btn btn-secondary" style="margin-top: 1.5rem; width: 100%;" onclick="window.location.href='tienda.php'">
                        Seguir Comprando
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GraciaShoes</h3>
                    <p>Elegancia y comodidad en cada paso.</p>
                </div>
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="tienda.php">Tienda</a></li>
                        <li><a href="nosotros.php">Nosotros</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> info@graciashoes.com</p>
                    <p><i class="fas fa-phone"></i> +57 3116448364</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar carrito
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Actualizar resumen del pedido
            updateCheckoutSummary(cart);
            
            // Actualizar campo oculto con items del carrito
            document.getElementById('cart-items-input').value = JSON.stringify(cart);
            
            // Manejar selección de método de pago
            const paymentMethods = document.querySelectorAll('.payment-method');
            const paymentDetails = document.querySelectorAll('.payment-details');
            
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    const methodValue = this.dataset.method;
                    paymentDetails.forEach(detail => {
                        detail.classList.remove('active');
                        if (detail.id === `${methodValue}-details`) {
                            detail.classList.add('active');
                        }
                    });
                    
                    // Actualizar required attributes based on selected method
                    paymentDetails.forEach(detail => {
                        const inputs = detail.querySelectorAll('input, textarea');
                        inputs.forEach(input => {
                            if (detail.classList.contains('active')) {
                                input.required = true;
                            } else {
                                input.required = false;
                            }
                        });
                    });
                });
            });
            
            // Validar formulario antes de enviar
            document.getElementById('checkout-form').addEventListener('submit', function(e) {
                if (cart.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Carrito vacío',
                        text: 'Tu carrito está vacío. Agrega productos antes de finalizar la compra.',
                    });
                    return;
                }
                
                const selectedMethod = document.querySelector('input[name="metodo_pago"]:checked').value;
                const activeDetails = document.querySelector('.payment-details.active');
                const requiredInputs = activeDetails.querySelectorAll('input[required], textarea[required]');
                
                let isValid = true;
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor, completa todos los campos requeridos para el método de pago seleccionado.',
                    });
                }
            });
            
            // Mostrar alerta de éxito si el pedido fue procesado
            <?php if ($success): ?>
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '<?php echo addslashes($success); ?>',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        localStorage.removeItem('cart');
                        window.location.href = 'tienda.php';
                    }
                });
            <?php endif; ?>
        });
        
        function updateCheckoutSummary(cart) {
            const checkoutItems = document.getElementById('checkout-items');
            const checkoutTotal = document.getElementById('checkout-total');
            
            if (cart.length === 0) {
                checkoutItems.innerHTML = '<p>Tu carrito está vacío</p>';
                checkoutTotal.textContent = '$0.00';
                return;
            }
            
            let html = '';
            let total = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                html += `
                    <div class="summary-item">
                        <div>
                            <strong>${item.name}</strong> x ${item.quantity}
                        </div>
                        <div>$${itemTotal.toFixed(2)}</div>
                    </div>
                `;
            });
            
            checkoutItems.innerHTML = html;
            checkoutTotal.textContent = '$' + total.toFixed(2);
        }
    </script>
</body>
</html>