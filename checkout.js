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
        cardDetails.style.display = this.value === "Credit Card" ? "block" : "none";
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
        const paymentMethod = paymentMethodSelect?.value;

        const cardNumber = cardNumberInput?.value.replace(/\s/g, "") || '';
        const cardName = document.getElementById("card-name")?.value.trim() || '';
        const expiryDate = expiryDateInput?.value.trim() || '';
        const cvv = cvvInput?.value.trim() || '';

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        // Stock validation before submission
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
                        await Swal.fire({
                            icon: 'error',
                            title: 'Variant Not Found',
                            text: `Variant not found for item: ${item.name}`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            // Get latest stock
            const stockResponse = await fetch(`get_stock.php?pv_id=${cart[i].variantId}`);
            const stockResult = await stockResponse.json();
            const maxStock = stockResult.stock || 1;

            if (cart[i].quantity > maxStock) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit',
                    html: `Only <b>${maxStock}</b> units available for ${item.name} (Size: ${item.size}).<br>Please update your cart.`,
                    confirmButtonText: 'OK'
                });
                loadCartItems();
                return;
            }
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        const orderData = {
            fullname: fullName,
            email: email,
            phone: phone,
            address_id: document.getElementById("address").value,
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
                await Swal.fire({
                    icon: 'success',
                    title: 'Thank you for your order!',
                    html: `Your Order ID is: <b>#${result.order_id}</b><br>We will process your order very soon!`,
                    confirmButtonText: 'OK'
                });
                window.location.href = "index.php";
            } else {
                throw new Error(result.message || "Order failed. Please try again.");
            }
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Order Error',
                text: error.message,
                confirmButtonText: 'OK'
            });
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerText = "Place Order";
            loadingMessage.style.display = "none";
        }
    });

    async function loadCartItems() {
        const container = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        let total = 0;
    
        container.innerHTML = cart.length === 0 ? "<p>Your cart is empty.</p>" : "";
    
        const stockPromises = cart.map(async (item, index) => {
            if (!item.variantId) {
                try {
                    const response = await fetch('find_variant_id.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ P_ID: item.id, P_Size: item.size })
                    });
                    const result = await response.json();
                    if (result.PV_ID) {
                        cart[index].variantId = result.PV_ID;
                        localStorage.setItem("cart", JSON.stringify(cart));
                    }
                } catch (error) {
                    console.error("Error finding variant:", error);
                    return { index, stock: 0, error: true };
                }
            }
    
            try {
                const stockRes = await fetch(`get_stock.php?pv_id=${item.variantId || 0}`);
                const stockData = await stockRes.json();
                
                if (!stockData.success) {
                    console.error("Stock check failed:", stockData.error);
                    return { index, stock: 0, error: true };
                }
                
                return { 
                    index, 
                    stock: stockData.stock || 0,
                    variantId: item.variantId 
                };
            } catch (error) {
                console.error("Error fetching stock:", error);
                return { index, stock: 0, error: true };
            }
        });
    
        const stockResults = await Promise.all(stockPromises);
    
        cart.forEach((item, index) => {
            const stockInfo = stockResults.find(r => r.index === index);
            const maxStock = stockInfo?.stock || 0;
            const currentQty = Math.min(item.quantity, maxStock);
    
            if (item.quantity > maxStock) {
                item.quantity = currentQty;
            }
    
            const div = document.createElement("div");
            div.classList.add("cart-item");
            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="checkout-item-info">
                    <p><strong>${item.name}</strong></p>
                    <p>Size: ${item.size}</p>
                    <p>Price: RM ${item.price.toFixed(2)}</p>
                    <input type="number" min="1" max="${maxStock}" 
                           value="${currentQty}" 
                           data-index="${index}" 
                           class="checkout-qty">
                    <button onclick="removeFromCheckout(${index})">Remove</button>
                    ${item.quantity > maxStock ? 
                        `<p class="stock-warning">Only ${maxStock} available!</p>` : ''}
                    ${stockInfo?.error ? 
                        `<p class="stock-error">⚠️ Failed to check stock</p>` : ''}
                </div>
            `;
            container.appendChild(div);
            total += item.price * currentQty;
        });
    
        localStorage.setItem("cart", JSON.stringify(cart));
        document.getElementById("cart-total").innerText = `RM ${total.toFixed(2)}`;
        attachQtyEvents();
    }

    function attachQtyEvents() {
        document.querySelectorAll(".checkout-qty").forEach(input => {
            input.addEventListener("change", async function () {
                const index = parseInt(this.getAttribute("data-index"));
                let cart = JSON.parse(localStorage.getItem("cart")) || [];
                const item = cart[index];

                let maxStock = 20;
                if (item.variantId) {
                    const stockRes = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                    const stockData = await stockRes.json();
                    maxStock = stockData.stock || 1;
                }

                let newQty = parseInt(this.value) || 1;
                newQty = Math.max(1, Math.min(newQty, maxStock));

                if (newQty !== parseInt(this.value)) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit',
                        html: `Only <b>${maxStock}</b> units available for ${item.name} (Size: ${item.size}).`,
                        confirmButtonText: 'OK'
                    });
                    this.value = newQty;
                }

                cart[index].quantity = newQty;
                localStorage.setItem("cart", JSON.stringify(cart));
                loadCartItems();
            });
        });
    }

    window.removeFromCheckout = function (index) {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const item = cart[index];
        
        Swal.fire({
            title: 'Remove Item',
            html: `Are you sure you want to remove <b>${item.name}</b> from your cart?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                cart.splice(index, 1);
                localStorage.setItem("cart", JSON.stringify(cart));
                loadCartItems();
                Swal.fire(
                    'Removed!',
                    'The item has been removed from your cart.',
                    'success'
                );
            }
        });
    };

    loadCartItems();

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

    expiryDateInput.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, "");
        if (value.length >= 2) {
            value = value.substring(0, 2) + "/" + value.substring(2, 4);
        }
        this.value = value.substring(0, 5);
    });
});