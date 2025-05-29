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
// Encapsular todo en una función autoejecutable para evitar conflictos globales
;(() => {
  // Variables globales para el carrito
  let cart = JSON.parse(localStorage.getItem("cart")) || []
  let currentSlide = 0

  // Funciones del carrito
  window.addToCart = (id, name, price) => {
    const existingItem = cart.find((item) => item.id === id)

    if (existingItem) {
      existingItem.quantity += 1
    } else {
      cart.push({
        id: id,
        name: name,
        price: price,
        quantity: 1,
      })
    }

    localStorage.setItem("cart", JSON.stringify(cart))
    updateCartCount()
    showNotification("Producto añadido al carrito")
  }

  window.removeFromCart = (id) => {
    cart = cart.filter((item) => item.id !== id)
    localStorage.setItem("cart", JSON.stringify(cart))
    updateCartCount()
    updateCartDisplay()
  }

  window.updateQuantity = (id, newQuantity) => {
    if (newQuantity <= 0) {
      window.removeFromCart(id)
      return
    }

    const item = cart.find((item) => item.id === id)
    if (item) {
      item.quantity = newQuantity
      localStorage.setItem("cart", JSON.stringify(cart))
      updateCartCount()
      updateCartDisplay()
    }
  }

  function updateCartDisplay() {
    const cartItems = document.getElementById("cart-items")
    const cartTotal = document.getElementById("cart-total")

    if (!cartItems || !cartTotal) return

    if (cart.length === 0) {
      cartItems.innerHTML = "<p>Tu carrito está vacío</p>"
      cartTotal.textContent = "0.00"
      return
    }

    let html = ""
    let total = 0

    cart.forEach((item) => {
      const itemTotal = item.price * item.quantity
      total += itemTotal

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
            `
    })

    cartItems.innerHTML = html
    cartTotal.textContent = total.toFixed(2)
  }

  function updateCartCount() {
    const cartCount = document.getElementById("cart-count")
    if (cartCount) {
      const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0)
      cartCount.textContent = `(${totalItems})`
    }
  }

  function showNotification(message) {
    const notification = document.createElement("div")
    notification.className = "notification"
    notification.textContent = message
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
        `

    document.body.appendChild(notification)

    setTimeout(() => {
      notification.remove()
    }, 3000)
  }

  // Funciones del modal del carrito
  window.openCartModal = () => {
    const modal = document.getElementById("cartModal")
    if (modal) {
      modal.style.display = "block"
      updateCartDisplay()
    }
  }

  window.closeCartModal = () => {
    const modal = document.getElementById("cartModal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  // Función para proceder al checkout
  window.proceedToCheckout = () => {
    // Verificar si hay productos en el carrito
    if (cart.length === 0) {
      alert("Tu carrito está vacío")
      return
    }

    // Verificar si el usuario está autenticado
    const loginButton = document.querySelector('.login-btn[onclick*="openLoginModal"]')

    if (loginButton) {
      // Usuario no autenticado - mostrar modal de login con mensaje específico
      window.closeCartModal()
      showLoginForCheckout()
      return
    }

    // Usuario autenticado - proceder al checkout
    window.location.href = "checkout.php"
  }

  // Nueva función para mostrar login específicamente para checkout
  function showLoginForCheckout() {
    const loginModal = document.getElementById("loginModal")
    if (!loginModal) return

    const loginContainer = loginModal.querySelector(".login-form-container")

    // Añadir mensaje específico para checkout
    const existingMessage = loginContainer.querySelector(".checkout-message")
    if (!existingMessage) {
      const checkoutMessage = document.createElement("div")
      checkoutMessage.className = "alert alert-info checkout-message"
      checkoutMessage.innerHTML = "🛒 Para completar tu compra necesitas iniciar sesión o crear una cuenta"

      const title = loginContainer.querySelector("h2")
      title.insertAdjacentElement("afterend", checkoutMessage)
    }

    window.openLoginModal()
  }

  // Funciones del modal de login
  window.openLoginModal = () => {
    const modal = document.getElementById("loginModal")
    if (modal) {
      modal.style.display = "block"
    }
  }

  window.closeLoginModal = () => {
    const modal = document.getElementById("loginModal")
    if (modal) {
      modal.style.display = "none"

      // Eliminar mensaje de checkout si existe
      const checkoutMessage = document.querySelector(".checkout-message")
      if (checkoutMessage) {
        checkoutMessage.remove()
      }
    }
  }

  // Funciones del carrusel
  function showSlide(n) {
    const slides = document.querySelectorAll(".carousel-slide")
    const dots = document.querySelectorAll(".dot")

    if (!slides.length) return

    slides[currentSlide].classList.remove("active")
    dots[currentSlide].classList.remove("active")

    currentSlide = (n + slides.length) % slides.length

    slides[currentSlide].classList.add("active")
    dots[currentSlide].classList.add("active")
  }

  window.nextSlide = () => {
    showSlide(currentSlide + 1)
  }

  window.prevSlide = () => {
    showSlide(currentSlide - 1)
  }

  window.currentSlide = (n) => {
    showSlide(n - 1)
  }

  // Cerrar modales al hacer clic fuera
  window.onclick = (event) => {
    const loginModal = document.getElementById("loginModal")
    const cartModal = document.getElementById("cartModal")

    if (event.target === loginModal) {
      window.closeLoginModal()
    }
    if (event.target === cartModal) {
      window.closeCartModal()
    }
  }

  // Inicializar cuando el DOM esté listo
  document.addEventListener("DOMContentLoaded", () => {
    updateCartCount()

    // Auto-play del carrusel
    setInterval(window.nextSlide, 5000)

    // Añadir estilos CSS
    const style = document.createElement("style")
    style.textContent = `
            
        `
    document.head.appendChild(style)
  })
})()

