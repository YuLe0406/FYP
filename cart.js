document.addEventListener("DOMContentLoaded", loadCart);

function loadCart() {
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    let cartContainer = document.getElementById("cart-items");
    let cartTotal = document.getElementById("cart-total");
    cartContainer.innerHTML = "";

    let total = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = "<p>Your cart is empty.</p>";
    } else {
        cartItems.forEach((item, index) => {
            let cartItemDiv = document.createElement("div");
            cartItemDiv.classList.add("cart-item");
            cartItemDiv.innerHTML = `
                <img src="images/${item.image}" alt="${item.name}">
                <div class="cart-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                </div>
                <p>RM ${item.price.toFixed(2)}</p>
                <input type="number" min="1" value="${item.quantity}" data-index="${index}" class="cart-qty">
                <button onclick="removeFromCart(${index})">Remove</button>
            `;
            cartContainer.appendChild(cartItemDiv);
            total += item.price * item.quantity;
        });
    }

    cartTotal.innerText = `RM ${total.toFixed(2)}`;
    attachQuantityChangeEvents();
}

function attachQuantityChangeEvents() {
    document.querySelectorAll(".cart-qty").forEach(input => {
        input.addEventListener("change", function () {
            let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
            let index = this.getAttribute("data-index");
            cartItems[index].quantity = parseInt(this.value);
            localStorage.setItem("cart", JSON.stringify(cartItems));
            loadCart();
        });
    });
}

function removeFromCart(index) {
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    cartItems.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cartItems));
    loadCart();
}
