// Variables globales
let currentSlide = 0;
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Funciones del carrusel
function nextSlide() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    
    currentSlide = (currentSlide + 1) % slides.length;
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function prevSlide() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function currentSlideFunc(n) {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    
    currentSlide = n - 1;
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

// Auto-play del carrusel
setInterval(nextSlide, 5000);

// Funciones del modal de login
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Funciones del carrito de compras
function openCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.style.display = 'block';
        updateCartDisplay();
    }
}

function closeCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function addToCart(id, name, price) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    
    // Mostrar notificación
    showNotification('Producto añadido al carrito');
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
}

function updateQuantity(id, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(id);
        return;
    }
    
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity = newQuantity;
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        updateCartDisplay();
    }
}

function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = `(${totalItems})`;
    }
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    if (!cartItems || !cartTotal) return;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p>Tu carrito está vacío</p>';
        cartTotal.textContent = '0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>$${item.price.toFixed(2)} c/u</p>
                </div>
                <div class="cart-item-controls">
                    <button onclick="updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                    <button onclick="removeFromCart('${item.id}')" class="remove-btn">🗑️</button>
                </div>
                <div class="cart-item-total">$${itemTotal.toFixed(2)}</div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    cartTotal.textContent = total.toFixed(2);
}

function proceedToCheckout() {
    if (cart.length === 0) {
        alert('Tu carrito está vacío');
        return;
    }
    
    // Aquí puedes redirigir a la página de checkout
    window.location.href = 'checkout.php';
}

function showNotification(message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #8b7355;
        color: white;
        padding: 1rem 2rem;
        border-radius: 5px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Cerrar modales al hacer clic fuera de ellos
window.onclick = function(event) {
    const loginModal = document.getElementById('loginModal');
    const cartModal = document.getElementById('cartModal');
    
    if (event.target === loginModal) {
        closeLoginModal();
    }
    
    if (event.target === cartModal) {
        closeCartModal();
    }
}

// Inicializar carrito al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();

    // --- Recuperación de contraseña moderna ---
    window.openRecoverModal = function() {
        document.getElementById('recoverModal').style.display = 'block';
        document.getElementById('recover-step-correo').style.display = 'block';
        document.getElementById('recover-step-pin').style.display = 'none';
        document.getElementById('recover-step-nueva').style.display = 'none';
        document.getElementById('recover-step-final').style.display = 'none';
        document.getElementById('recover-error').style.display = 'none';
        document.getElementById('recover-success').style.display = 'none';
        document.getElementById('recoverCorreoForm').reset();
        document.getElementById('recoverPinForm').reset();
        document.getElementById('recoverNuevaForm').reset();
    }
    window.closeRecoverModal = function() {
        document.getElementById('recoverModal').style.display = 'none';
    }
    if (document.getElementById('openRecoverModal')) {
        document.getElementById('openRecoverModal').onclick = function(e) {
            e.preventDefault();
            openRecoverModal();
        }
    }
    if (document.getElementById('recoverCorreoForm')) {
        document.getElementById('recoverCorreoForm').onsubmit = function(e) {
            e.preventDefault();
            var correo = document.getElementById('recoverCorreo').value;
            fetch('ajax_recuperar_contrasena.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'step=correo&correo=' + encodeURIComponent(correo)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('recover-step-correo').style.display = 'none';
                    document.getElementById('recover-step-pin').style.display = 'block';
                    document.getElementById('recover-error').style.display = 'none';
                    document.getElementById('recover-success').style.display = 'block';
                    document.getElementById('recover-success').innerText = data.message;
                } else {
                    document.getElementById('recover-error').style.display = 'block';
                    document.getElementById('recover-error').innerText = data.message;
                }
            });
        }
    }
    if (document.getElementById('recoverPinForm')) {
        document.getElementById('recoverPinForm').onsubmit = function(e) {
            e.preventDefault();
            var pin = document.getElementById('recoverPin').value;
            fetch('ajax_recuperar_contrasena.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'step=pin&pin=' + encodeURIComponent(pin)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('recover-step-pin').style.display = 'none';
                    document.getElementById('recover-step-nueva').style.display = 'block';
                    document.getElementById('recover-error').style.display = 'none';
                    document.getElementById('recover-success').style.display = 'block';
                    document.getElementById('recover-success').innerText = data.message;
                } else {
                    document.getElementById('recover-error').style.display = 'block';
                    document.getElementById('recover-error').innerText = data.message;
                }
            });
        }
    }
    if (document.getElementById('recoverNuevaForm')) {
        document.getElementById('recoverNuevaForm').onsubmit = function(e) {
            e.preventDefault();
            var nueva = document.getElementById('recoverNueva').value;
            var confirmar = document.getElementById('recoverConfirmar').value;
            fetch('ajax_recuperar_contrasena.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'step=nueva&nueva_contrasena=' + encodeURIComponent(nueva) + '&confirmar_contrasena=' + encodeURIComponent(confirmar)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('recover-step-nueva').style.display = 'none';
                    document.getElementById('recover-step-final').style.display = 'block';
                    document.getElementById('recover-error').style.display = 'none';
                } else {
                    document.getElementById('recover-error').style.display = 'block';
                    document.getElementById('recover-error').innerText = data.message;
                }
            });
        }
    }
});

// Agregar estilos CSS para las notificaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .cart-item-info h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
    }
    
    .cart-item-info p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .cart-item-controls button {
        background-color: #f0f0f0;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 3px;
        cursor: pointer;
    }
    
    .cart-item-controls .remove-btn {
        background-color: #dc3545;
        color: white;
        margin-left: 0.5rem;
    }
    
    .cart-item-total {
        font-weight: bold;
        color: #8b7355;
    }
    
    .cart-container {
        padding: 2rem;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .cart-total {
        text-align: center;
        padding: 1rem;
        border-top: 2px solid #8b7355;
        margin: 1rem 0;
    }
    
    .cart-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
`;
document.head.appendChild(style);

