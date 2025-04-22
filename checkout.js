document.addEventListener("DOMContentLoaded", function () {
    const paymentMethod = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const checkoutForm = document.getElementById("checkout-form");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const loadingMessage = document.getElementById("loading-message");
    const successMessage = document.getElementById("success-message");

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
    placeOrderBtn.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default form submission

        if (paymentMethod.value === "credit_card") {
            const cardNumber = document.getElementById("card-number").value.trim();
            const cardName = document.getElementById("card-name").value.trim();
            const expiryDate = document.getElementById("expiry-date").value.trim();
            const cvv = document.getElementById("cvv").value.trim();

            if (cardNumber === "" || cardName === "" || expiryDate === "" || cvv === "") {
                alert("Please enter all details.");
                return;
            }
        }

        // Validate Address Fields
        const address1 = document.getElementById("address1").value.trim();
        if (address1 === "") {
            alert("Please enter Address Line 1.");
            return;
        }

        // Disable button to prevent multiple clicks
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerText = "Processing...";

        // Show "Processing Payment..." message
        loadingMessage.style.display = "block";

        // Simulate a payment processing delay (3 seconds)
        setTimeout(() => {
            loadingMessage.style.display = "none"; // Hide "Processing..." message
            successMessage.style.display = "block"; // Show "Order Successful!"
            placeOrderBtn.after(successMessage); // Ensure it appears below the button


            // Clear the cart since order is placed
            localStorage.removeItem("cart");

            // Redirect to homepage or order summary page after 3 seconds
            setTimeout(() => {
                window.location.href = "http://localhost/FYP/index.php"; // Change this if you have an order summary page
            }, 3000);
        }, 3000);
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
