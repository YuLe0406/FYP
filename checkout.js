document.addEventListener("DOMContentLoaded", function () {
    const paymentMethodSelect = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const loadingMessage = document.getElementById("loading-message");
    const successMessage = document.getElementById("success-message");

    // 🟡 Toggle card details
    paymentMethodSelect.addEventListener("change", function () {
        cardDetails.style.display = this.value === "credit_card" ? "block" : "none";
    });

    // 🇲🇾 Malaysia phone number formatting
    phoneInput.addEventListener("input", function () {
        let val = this.value.replace(/\D/g, "");
        if (val.length >= 3) val = val.substring(0, 3) + "-" + val.substring(3);
        if (val.length >= 7) val = val.substring(0, 7) + " " + val.substring(7);
        this.value = val;
    });

    // ✅ Place order
    placeOrderBtn.addEventListener("click", async function (event) {
        event.preventDefault();

        const fullName = document.getElementById("fullname")?.value.trim();
        const email = document.getElementById("email")?.value.trim();
        const phone = document.getElementById("phone")?.value.trim();
        const address1 = document.getElementById("address1")?.value.trim();
        const paymentMethod = document.getElementById("payment-method")?.value;

        if (!fullName || !email || !phone || !address1 || !paymentMethod) {
            alert("Please fill in all required fields.");
            return;
        }

        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        if (cart.length === 0) {
            alert("Your cart is empty.");
            return;
        }

        const orderData = {
            fullname: fullName,
            email: email,
            phone: phone,
            address1: address1,
            payment_method: paymentMethod,
            cart: cart,
            discount: 0, 
            total: cart.reduce((sum, item) => sum + item.price * item.quantity, 0)
        };

        try {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerText = "Placing Order...";
            loadingMessage.style.display = "block";

            const response = await fetch("save_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (result.success) {
                localStorage.removeItem("cart");
                loadingMessage.style.display = "none";
                successMessage.style.display = "block";
                setTimeout(() => {
                    window.location.href = `order_success.php?order_id=${result.order_id}`;
                }, 3000);
            } else {
                throw new Error(result.message || "Order failed. Please try again.");
            }
        } catch (error) {
            alert("Error: " + error.message);
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerText = "Place Order";
            loadingMessage.style.display = "none";
        }
    });

    // 🛒 Load cart into checkout page
    function loadCartItems() {
        const container = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;

        container.innerHTML = "";

        cart.forEach((item, index) => {
            const div = document.createElement("div");
            div.classList.add("cart-item");

            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="checkout-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                    <p>Price: RM ${item.price.toFixed(2)}</p>
                    <input type="number" min="1" value="${item.quantity}" data-index="${index}" class="checkout-qty">
                    <button onclick="removeFromCheckout(${index})">Remove</button>
                </div>
            `;

            container.appendChild(div);
            total += item.price * item.quantity;
        });

        document.getElementById("cart-total").innerText = `RM ${total.toFixed(2)}`;
        attachQtyEvents();
    }

    function attachQtyEvents() {
        document.querySelectorAll(".checkout-qty").forEach(input => {
            input.addEventListener("change", function () {
                let cart = JSON.parse(localStorage.getItem("cart")) || [];
                const index = this.getAttribute("data-index");
                cart[index].quantity = parseInt(this.value) || 1;
                localStorage.setItem("cart", JSON.stringify(cart));
                loadCartItems();
            });
        });
    }

    window.removeFromCheckout = function (index) {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        loadCartItems();
    };

    loadCartItems();
});
