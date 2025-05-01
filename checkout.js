document.addEventListener("DOMContentLoaded", function () {
    const paymentMethodSelect = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const loadingMessage = document.getElementById("loading-message");
    const successMessage = document.getElementById("success-message");
    const expiryDateInput = document.getElementById("expiry-date");
    const cvvInput = document.getElementById("cvv");
    const cardNumberInput = document.getElementById("card-number");

    paymentMethodSelect.addEventListener("change", function () {
        cardDetails.style.display = this.value === "credit_card" ? "block" : "none";
    });

    phoneInput.addEventListener("input", function () {
        let val = this.value.replace(/\D/g, "");
        if (val.length >= 3) val = val.substring(0, 3) + "-" + val.substring(3);
        if (val.length >= 7) val = val.substring(0, 7) + " " + val.substring(7);
        this.value = val;
    });

    placeOrderBtn.addEventListener("click", async function (event) {
        event.preventDefault();

        const fullName = document.getElementById("fullname")?.value.trim();
        const email = document.getElementById("email")?.value.trim();
        const phone = document.getElementById("phone")?.value.trim();
        const address1 = document.getElementById("address1")?.value.trim();
        const paymentMethod = paymentMethodSelect?.value;

        const cardNumber = cardNumberInput?.value.replace(/\s/g, "") || '';
        const cardName = document.getElementById("card-name")?.value.trim() || '';
        const expiryDate = expiryDateInput?.value.trim() || '';
        const cvv = cvvInput?.value.trim() || '';

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        if (!fullName || !email || !phone || !address1 || !paymentMethod) {
            alert("Please fill in all required fields.");
            return;
        }

        if (paymentMethod === 'credit_card') {
            if (!cardNumber || cardNumber.length !== 16 || !cardName || !expiryDate || !cvv) {
                alert("Please enter a valid 16-digit card number and complete card details.");
                return;
            }
        }

        if (cart.length === 0) {
            alert("Your cart is empty.");
            return;
        }

        for (let i = 0; i < cart.length; i++) {
            const item = cart[i];
            if (!item.variantId) {
                try {
                    const response = await fetch('find_variant_id.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ P_ID: item.id, P_Size: item.size })
                    });

                    const result = await response.json();
                    if (result.PV_ID) {
                        cart[i].variantId = result.PV_ID;
                    } else {
                        throw new Error('Variant not found for item: ' + item.name);
                    }
                } catch (error) {
                    alert(error.message);
                    return;
                }
            }

            // Get max stock
            const stockResponse = await fetch(`get_stock.php?pv_id=${cart[i].variantId}`);
            const stockResult = await stockResponse.json();
            const maxStock = stockResult.stock || 1;

            if (cart[i].quantity > maxStock) {
                alert(`Only ${maxStock} units available for size ${item.size}.`);
                return;
            }
        }

        localStorage.setItem('cart', JSON.stringify(cart));

        const orderData = {
            fullname: fullName,
            email: email,
            phone: phone,
            address1: address1,
            payment_method: paymentMethod,
            cart: cart,
            cardNumber: cardNumber,
            expiryDate: expiryDate,
            cvv: cvv,
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

                Swal.fire({
                    icon: 'success',
                    title: 'Thank you for your order!',
                    html: `Your Order ID is: <b>#${result.order_id}</b><br>We will process your order very soon!`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = "index.php";
                });
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

    function loadCartItems() {
        const container = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;

        container.innerHTML = "";

        cart.forEach(async (item, index) => {
            let maxStock = 10;
            if (item.variantId) {
                const stockRes = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                const stockData = await stockRes.json();
                maxStock = stockData.stock || 1;
            }

            const div = document.createElement("div");
            div.classList.add("cart-item");

            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="checkout-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                    <p>Price: RM ${item.price.toFixed(2)}</p>
                    <input type="number" min="1" max="${maxStock}" value="${item.quantity}" data-index="${index}" class="checkout-qty">
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
            input.addEventListener("input", function () {
                const index = parseInt(this.getAttribute("data-index"));
                const max = parseInt(this.max) || 1;
                const newQty = Math.min(parseInt(this.value) || 1, max);
                let cart = JSON.parse(localStorage.getItem("cart")) || [];
                cart[index].quantity = newQty;
                this.value = newQty;
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

    // 💳 Format Card Number (XXXX XXXX XXXX XXXX)
    cardNumberInput.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, "");
        value = value.substring(0, 16);
        let formatted = "";
        for (let i = 0; i < value.length; i += 4) {
            if (i > 0) formatted += " ";
            formatted += value.substring(i, i + 4);
        }
        this.value = formatted;
    });

    // 🗓 Format Expiry Date (MM/YY)
    expiryDateInput.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, "");
        if (value.length >= 2) {
            value = value.substring(0, 2) + "/" + value.substring(2, 4);
        }
        this.value = value.substring(0, 5);
    });
});
