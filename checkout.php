<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GraciaShoes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/gracie.png" alt="GraciaShoes Logo" style="height: 70px;">
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
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN'): ?>
                            <a href="admin-dashboard.php" class="login-btn">PANEL ADMIN</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="login-btn">MI CUENTA</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="login-btn" onclick="openLoginModal()">INICIAR SESIÓN</button>
                    <?php endif; ?>
                    <div class="cart" onclick="openCartModal()">
                        <span class="cart-icon">🛍️</span>
                        <span class="cart-count" id="cart-count">(0)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Checkout Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Finalizar Compra</h1>
                <p>Completa tu pedido de forma segura</p>
            </div>

            <div class="checkout-container">
                <div class="checkout-content">
                    <!-- Información de envío -->
                    <div class="checkout-section">
                        <h2>Información de Envío</h2>
                        <form id="checkout-form" class="checkout-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre completo</label>
                                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="direccion">Dirección completa</label>
                                <textarea id="direccion" name="direccion" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad</label>
                                    <input type="text" id="ciudad" name="ciudad" required>
                                </div>
                                <div class="form-group">
                                    <label for="departamento">Departamento</label>
                                    <input type="text" id="departamento" name="departamento" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notas">Notas adicionales (opcional)</label>
                                <textarea id="notas" name="notas" rows="2"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Método de pago -->
                    <div class="checkout-section">
                        <h2>Método de Pago</h2>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment" value="transferencia" checked>
                                <span class="payment-label">
                                    <strong>Transferencia Bancaria</strong>
                                    <small>Recibirás los datos bancarios por WhatsApp</small>
                                </span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment" value="contraentrega">
                                <span class="payment-label">
                                    <strong>Pago Contra Entrega</strong>
                                    <small>Paga cuando recibas tu pedido</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="order-summary">
                    <h2>Resumen del Pedido</h2>
                    <div id="order-items">
                        <!-- Los productos se cargarán aquí con JavaScript -->
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="total-line">
                            <span>Envío:</span>
                            <span id="shipping">$10.00</span>
                        </div>
                        <div class="total-line total-final">
                            <span>Total:</span>
                            <span id="final-total">$10.00</span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-large" onclick="submitOrder()">
                        Confirmar Pedido
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
                    <p>📧 info@graciashoes.com</p>
                    <p>📱 +57 3116448364</p>
                    <p>📍 Rivera Huila (centro arriba)</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 GraciaShoes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Cargar productos del carrito desde localStorage o variable global
        function loadOrderSummary() {
            const orderItems = document.getElementById('order-items');
            const subtotalEl = document.getElementById('subtotal');
            const finalTotalEl = document.getElementById('final-total');
            
            if (typeof cart !== 'undefined' && cart.length > 0) {
                let subtotal = 0;
                
                orderItems.innerHTML = cart.map(item => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;
                    
                    return `
                        <div class="order-item">
                            <div class="item-info">
                                <h4>${item.name}</h4>
                                <span class="item-quantity">Cantidad: ${item.quantity}</span>
                            </div>
                            <span class="item-price">$${itemTotal.toFixed(2)}</span>
                        </div>
                    `;
                }).join('');
                
                subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
                finalTotalEl.textContent = `$${(subtotal + 10).toFixed(2)}`;
            } else {
                orderItems.innerHTML = '<p>No hay productos en el carrito</p>';
            }
        }
        
        function submitOrder() {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            
            // Validar formulario
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Obtener método de pago
            const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
            
            // Preparar datos del pedido
            const orderData = {
                customer: Object.fromEntries(formData),
                items: cart,
                payment_method: paymentMethod,
                total: document.getElementById('final-total').textContent
            };
            
            // Enviar pedido (aquí puedes implementar el envío a tu backend)
            console.log('Pedido:', orderData);
            
            // Mostrar confirmación
            alert('¡Pedido confirmado! Te contactaremos pronto por WhatsApp.');
            
            // Limpiar carrito
            cart = [];
            updateCartDisplay();
            
            // Redirigir
            window.location.href = 'dashboard.php?order=success';
        }
        
        // Cargar resumen al cargar la página
        document.addEventListener('DOMContentLoaded', loadOrderSummary);
    </script>
    
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin: 2rem 0;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
        
        .checkout-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .checkout-section h2 {
            color: #8b7355;
            margin-bottom: 1.5rem;
            font-weight: normal;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .payment-option {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: #8b7355;
        }
        
        .payment-option input[type="radio"]:checked + .payment-label {
            color: #8b7355;
        }
        
        .payment-label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .payment-label small {
            color: #666;
        }
        
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 120px;
        }
        
        .order-summary h2 {
            color: #8b7355;
            margin-bottom: 1.5rem;
            font-weight: normal;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }
        
        .item-quantity {
            font-size: 0.875rem;
            color: #666;
        }
        
        .item-price {
            font-weight: bold;
            color: #8b7355;
        }
        
        .order-totals {
            margin: 1.5rem 0;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 1.2rem;
            color: #8b7355;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
        }
        
        .btn-large {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }
    </style>
</body>
</html>

