<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
include 'header.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user addresses
$stmt = $conn->prepare("SELECT * FROM USER_ADDRESS WHERE U_ID = ? ORDER BY UA_IsDefault DESC, UA_Type ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$fullName = $user['U_FName'] . ' ' . $user['U_LName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | CTRL+X</title>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4a6bff;
            --primary-dark: #3a56d4;
            --secondary: #f8f9fa;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f5f7fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }

        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        h1, h2, h3 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        h1 {
            font-size: 2rem;
            text-align: center;
        }

        h2 {
            font-size: 1.5rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1.5fr 1fr;
            }
        }

        .checkout-section {
            background: var(--white);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .order-summary {
            position: sticky;
            top: 1rem;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        input, select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.15);
        }

        input[readonly] {
            background-color: var(--secondary);
        }

        .card-details {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--secondary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .cart-items-container {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .cart-item-meta {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cart-item-qty {
            width: 60px;
            padding: 0.4rem;
            text-align: center;
        }

        .remove-item-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.9rem;
        }

        .stock-warning {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .price-summary {
            background: var(--secondary);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .price-row.total {
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .free-shipping {
            color: var(--success);
            font-weight: 500;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .checkout-btn:hover {
            background: var(--primary-dark);
        }

        .checkout-btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }

        .status-message {
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            text-align: center;
            font-weight: 500;
        }

        .loading {
            background: rgba(255, 193, 7, 0.1);
            color: var(--dark);
        }

        .success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .address-details {
            background: var(--secondary);
            padding: 1rem;
            border-radius: 6px;
            margin-top: 0.5rem;
            line-height: 1.5;
        }

        @media (max-width: 576px) {
            main {
                padding: 0 1rem;
            }
            
            .checkout-section, .order-summary {
                padding: 1.5rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>

<main>
    <h1>Checkout</h1>

    <div class="checkout-grid">
        <section class="checkout-section">
            <h2>Billing Information</h2>
            <form id="checkout-form">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" value="<?php echo htmlspecialchars($fullName); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['U_Email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" value="<?php echo htmlspecialchars($user['U_PNumber']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="address">Shipping Address</label>
                    <select id="address" name="address" required>
                        <option value="">-- Select Address --</option>
                        <?php foreach ($addresses as $address): ?>
                            <option value="<?php echo $address['UA_ID']; ?>" 
                                    <?php echo $address['UA_IsDefault'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(
                                    $address['UA_Type'] . ': ' . 
                                    $address['UA_Address1'] . ', ' . 
                                    ($address['UA_Address2'] ? $address['UA_Address2'] . ', ' : '') . 
                                    $address['UA_Postcode'] . ' ' . 
                                    $address['UA_City'] . ', ' . 
                                    $address['UA_State']
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="address-details" id="address-details">
                        <?php if (!empty($addresses)): ?>
                            <?php $defaultAddress = array_filter($addresses, function($a) { return $a['UA_IsDefault']; }); ?>
                            <?php $displayAddress = !empty($defaultAddress) ? reset($defaultAddress) : $addresses[0]; ?>
                            <p>
                                <?php echo htmlspecialchars($displayAddress['UA_Address1']); ?><br>
                                <?php if ($displayAddress['UA_Address2']): ?>
                                    <?php echo htmlspecialchars($displayAddress['UA_Address2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars(
                                    $displayAddress['UA_Postcode'] . ' ' . 
                                    $displayAddress['UA_City'] . ', ' . 
                                    $displayAddress['UA_State']
                                ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="payment-method">Payment Method</label>
                    <select id="payment-method" name="payment-method" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="Credit Card">Credit/Debit Card</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                </div>

                <div class="card-details" id="card-details" style="display: none;">
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>

                    <div class="form-group">
                        <label for="card-name">Cardholder Name</label>
                        <input type="text" id="card-name" placeholder="John Doe">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry-date">Expiry Date</label>
                            <input type="text" id="expiry-date" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <aside class="order-summary">
            <h2>Order Summary</h2>
            <div class="cart-items-container" id="cart-items"></div>
            
            <div class="price-summary">
                <div class="price-row">
                    <span>Subtotal</span>
                    <span id="subtotal">RM 0.00</span>
                </div>
                <div class="price-row">
                    <span>Shipping</span>
                    <span id="shipping-fee">RM 0.00</span>
                </div>
                <div id="free-shipping-message" class="free-shipping" style="display: none;">
                    <i class="fas fa-check-circle"></i> Free shipping on orders over RM250
                </div>
                <div class="price-row total">
                    <span>Total</span>
                    <span id="cart-total">RM 0.00</span>
                </div>
            </div>
            
            <button id="place-order-btn" class="checkout-btn">Place Order</button>
            <div id="loading-message" class="status-message loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Processing your order...
            </div>
            <div id="success-message" class="status-message success" style="display: none;">
                <i class="fas fa-check-circle"></i> Order placed successfully!
            </div>
        </aside>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const paymentMethodSelect = document.getElementById("payment-method");
    const cardDetails = document.getElementById("card-details");
    const phoneInput = document.getElementById("phone");
    const placeOrderBtn = document.getElementById("place-order-btn");
    const loadingMessage = document.getElementById("loading-message");
    const successMessage = document.getElementById("success-message");
    const expiryDateInput = document.getElementById("expiry-date");
    const cvvInput = document.getElementById("cvv");
    const cardNumberInput = document.getElementById("card-number");
    const addressSelect = document.getElementById("address");
    const addressDetails = document.getElementById("address-details");
    
    // Payment method toggle
    paymentMethodSelect.addEventListener("change", function() {
        cardDetails.style.display = this.value === "Credit Card" ? "block" : "none";
    });
    
    // Phone number formatting
    phoneInput.addEventListener("input", function() {
        let val = this.value.replace(/\D/g, "");
        if (val.length >= 3) val = val.substring(0, 3) + "-" + val.substring(3);
        if (val.length >= 7) val = val.substring(0, 7) + " " + val.substring(7);
        this.value = val;
    });
    
    // Address selection
    addressSelect.addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];
        addressDetails.innerHTML = `<p>${selectedOption.text}</p>`;
    });
    
    // Card number formatting
    cardNumberInput.addEventListener("input", function() {
        let value = this.value.replace(/\D/g, "");
        value = value.substring(0, 16);
        let formatted = "";
        for (let i = 0; i < value.length; i += 4) {
            if (i > 0) formatted += " ";
            formatted += value.substring(i, i + 4);
        }
        this.value = formatted;
    });
    
    // Expiry date formatting
    expiryDateInput.addEventListener("input", function() {
        let value = this.value.replace(/\D/g, "");
        if (value.length >= 2) {
            value = value.substring(0, 2) + "/" + value.substring(2, 4);
        }
        this.value = value.substring(0, 5);
    });
    
    // Place order button handler
    placeOrderBtn.addEventListener("click", async function(event) {
        event.preventDefault();
        
        const fullName = document.getElementById("fullname")?.value.trim();
        const email = document.getElementById("email")?.value.trim();
        const phone = document.getElementById("phone")?.value.trim();
        const paymentMethod = paymentMethodSelect?.value;
        const addressId = document.getElementById("address")?.value;
        
        const cardNumber = cardNumberInput?.value.replace(/\s/g, "") || '';
        const cardName = document.getElementById("card-name")?.value.trim() || '';
        const expiryDate = expiryDateInput?.value.trim() || '';
        const cvv = cvvInput?.value.trim() || '';
        
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        
        // Validate required fields
        if (!addressId) {
            await Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please select a shipping address',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        if (!paymentMethod) {
            await Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please select a payment method',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        if (paymentMethod === "Credit Card" && (!cardNumber || !cardName || !expiryDate || !cvv)) {
            await Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill in all card details',
                confirmButtonText: 'OK'
            });
            return;
        }
        
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
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const shippingFee = subtotal >= 250 ? 0 : 15;
        const total = subtotal + shippingFee;
        
        const orderData = {
            fullname: fullName,
            email: email,
            phone: phone,
            address_id: addressId,
            payment_method: paymentMethod,
            cart: cart,
            cardNumber: cardNumber,
            expiryDate: expiryDate,
            cvv: cvv,
            discount: 0,
            subtotal: subtotal,
            shipping_fee: shippingFee,
            total: total
        };
        
        try {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerText = "Processing...";
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
    
    // Load cart items function
    async function loadCartItems() {
        const container = document.getElementById("cart-items");
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        const SHIPPING_THRESHOLD = 250;
        const SHIPPING_FEE = 15;
        let subtotal = 0;
        
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
            
            const itemTotal = item.price * currentQty;
            subtotal += itemTotal;
            
            const div = document.createElement("div");
            div.className = "cart-item";
            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-details">
                    <h3 class="cart-item-title">${item.name}</h3>
                    <div class="cart-item-meta">
                        <p>Size: ${item.size}</p>
                        <p>RM ${item.price.toFixed(2)} × ${currentQty} = RM ${itemTotal.toFixed(2)}</p>
                    </div>
                    <div class="cart-item-actions">
                        <input type="number" min="1" max="${maxStock}" 
                               value="${currentQty}" 
                               data-index="${index}" 
                               class="cart-item-qty">
                        <button class="remove-item-btn" onclick="removeFromCheckout(${index})">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    </div>
                    ${item.quantity > maxStock ? 
                        `<p class="stock-warning"><i class="fas fa-exclamation-circle"></i> Only ${maxStock} available!</p>` : ''}
                    ${stockInfo?.error ? 
                        `<p class="stock-warning"><i class="fas fa-exclamation-triangle"></i> Failed to check stock</p>` : ''}
                </div>
            `;
            container.appendChild(div);
        });
        
        localStorage.setItem("cart", JSON.stringify(cart));
        
        // Calculate shipping and totals
        const shippingFee = subtotal >= SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
        const total = subtotal + shippingFee;
        
        // Update UI
        document.getElementById("subtotal").textContent = `RM ${subtotal.toFixed(2)}`;
        document.getElementById("shipping-fee").textContent = `RM ${shippingFee.toFixed(2)}`;
        document.getElementById("cart-total").textContent = `RM ${total.toFixed(2)}`;
        
        const freeShippingMsg = document.getElementById("free-shipping-message");
        if (subtotal >= SHIPPING_THRESHOLD) {
            freeShippingMsg.style.display = "flex";
            document.getElementById("shipping-fee").textContent = "RM 0.00";
        } else {
            freeShippingMsg.style.display = "none";
        }
        
        attachQtyEvents();
    }
    
    function attachQtyEvents() {
        document.querySelectorAll(".cart-item-qty").forEach(input => {
            input.addEventListener("change", async function() {
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
    
    window.removeFromCheckout = function(index) {
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
    
    // Check if there are no addresses and show alert
    <?php if (empty($addresses)): ?>
        Swal.fire({
            icon: 'warning',
            title: 'No Shipping Address',
            html: 'You need to add a shipping address before you can checkout.<br><br>You will be redirected to your profile to add one.',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'profile.php';
            }
        });
        
        // Disable the place order button
        document.getElementById('place-order-btn').disabled = true;
    <?php endif; ?>
    
    // Initial load
    loadCartItems();
});
</script>
</body>
</html>