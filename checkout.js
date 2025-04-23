document.addEventListener("DOMContentLoaded", function () {
    const paymentMethod = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const checkoutForm = document.getElementById("checkout-form");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const loadingMessage = document.getElementById("loading-message");
    const successMessage = document.getElementById("success-message");
    const addressDropdown = document.getElementById("existing-address");
    const address1Input = document.getElementById("address1");

    addressDropdown.addEventListener("change", function () {
        const selectedOption = addressDropdown.options[addressDropdown.selectedIndex];
        const addressDetails = selectedOption.getAttribute("data-details");
        if (addressDetails) {
            address1Input.value = addressDetails;
        } else {
            address1Input.value = "";
        }
    });

    // Show/Hide Card Details Based on Payment Method
    paymentMethod.addEventListener("change", function () {
        cardDetails.style.display = this.value === "credit_card" ? "block" : "none";
    });

    // Malaysia Phone Number Validation (Format: 012-345 6789)
    phoneInput.addEventListener("input", function () {
        let formattedPhone = this.value.replace(/\D/g, ""); // Remove non-numeric characters
        if (formattedPhone.length >= 3) {
            formattedPhone = formattedPhone.substring(0, 3) + "-" + formattedPhone.substring(3);
        }
        if (formattedPhone.length >= 7) {
            formattedPhone = formattedPhone.substring(0, 7) + " " + formattedPhone.substring(7);
        }
        this.value = formattedPhone;
    });

    // Form Validation & Payment Processing Simulation
    placeOrderBtn.addEventListener("click", async function (event) {
        event.preventDefault();
    
        // Validate fields
        const fullName = document.getElementById("fullname").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const address1 = document.getElementById("address1").value.trim();
        const paymentMethod = document.getElementById("payment-method").value;
    
        if (!fullName || !email || !phone || !address1) {
            alert("Please fill in all required billing details.");
            return;
        }
    
        const cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    
        if (cartItems.length === 0) {
            alert("Your cart is empty.");
            return;
        }
    
        // Build order data
        const orderData = {
            fullname: fullName,
            email: email,
            phone: phone,
            address: address1,
            payment_method: paymentMethod,
            cart: cartItems
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
                // Clear localStorage
                localStorage.removeItem("cart");
    
                loadingMessage.style.display = "none";
                successMessage.style.display = "block";

                setTimeout(() => {
                    window.location.href = "order_success.php"; // You can change this
                }, 3000);
            } else {
                throw new Error(result.message || "Failed to save order.");
            }
        } catch (error) {
            alert("Error: " + error.message);
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerText = "Place Order";
            loadingMessage.style.display = "none";
        }
    });

    // Load Cart Items into Order Summary
    function loadCartItems() {
        const cartItemsContainer = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;
    
        cartItemsContainer.innerHTML = "";
        cart.forEach((item, index) => {
            const itemElement = document.createElement("div");
            itemElement.classList.add("cart-item");
    
            itemElement.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="checkout-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                    <p>Price: RM ${parseFloat(item.price).toFixed(2)}</p>
                    <input type="number" min="1" value="${item.quantity}" data-index="${index}" class="checkout-qty">
                    <button onclick="removeFromCheckout(${index})">Remove</button>
                </div>
            `;
    
            cartItemsContainer.appendChild(itemElement);
            total += item.price * item.quantity;
        });
    
        document.getElementById("cart-total").innerText = `RM ${total.toFixed(2)}`;
        attachCheckoutQtyEvents();
    }

    function attachCheckoutQtyEvents() {
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
    
    function removeFromCheckout(index) {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        loadCartItems();
    }
    

    loadCartItems();



    fetch("save_order.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            fullname,
            email,
            phone,
            address1,
            payment,
            items: cart
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem("cart");
            window.location.href = `success.php?order_id=${data.order_id}`;
        } else {
            alert("Something went wrong. Please try again.");
        }
    });
    

    
});
