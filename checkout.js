document.addEventListener("DOMContentLoaded", function () {
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    let cartContainer = document.getElementById("cart-items");
    let totalElement = document.getElementById("cart-total");

    let totalPrice = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = "<p>Your cart is empty.</p>";
    } else {
        cartItems.forEach(item => {
            let itemElement = document.createElement("div");
            itemElement.classList.add("cart-item");
            itemElement.innerHTML = `
                <p><strong>${item.name}</strong> (${item.size}) x${item.quantity}</p>
                <p>RM ${item.price.toFixed(2)}</p>
            `;
            cartContainer.appendChild(itemElement);
            totalPrice += item.price * item.quantity;
        });

        totalElement.innerText = `RM ${totalPrice.toFixed(2)}`;
    }
});

// Placeholder for order submission
document.getElementById("place-order-btn").addEventListener("click", function () {
    alert("Order placed successfully!");
    localStorage.removeItem("cart");
    window.location.href = "order-confirmation.html"; // Redirect after placing order
});
