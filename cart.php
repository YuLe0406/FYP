<?php
include 'db.php'; // Database connection
include 'header.php'; // Header

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | CTRL+X</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4a6baf;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b6b;
            --text-color: #333;
            --light-text: #6c757d;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: white;
            color: var(--text-color);
        }

        .cart-main {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .cart-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cart-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .back-to-shop {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .back-to-shop:hover {
            background-color: var(--primary-color);
            color: white;
        }

        #cart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .cart-items-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 0.5fr;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            font-weight: 500;
            color: var(--light-text);
        }

        #cart-items {
            margin-bottom: 1.5rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 0.5fr;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .cart-item-name {
            font-weight: 500;
            font-size: 1.1rem;
        }

        .cart-item-size {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .cart-qty {
            width: 70px;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: center;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--accent-color);
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .remove-btn:hover {
            color: #e05555;
        }

        .cart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cart-total {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .cart-actions {
            display: flex;
            gap: 1rem;
        }

        .secondary-btn {
            background-color: var(--secondary-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .secondary-btn:hover {
            background-color: #e9ecef;
        }

        .checkout-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background-color: #3a5a9a;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 0;
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .stock-warning {
            color: #e67e22;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .stock-error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .cart-items-header {
                display: none;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1.5rem 0;
            }

            .cart-item-info {
                grid-column: 1;
            }

            .cart-qty {
                grid-column: 1;
                justify-self: start;
            }

            .remove-btn {
                grid-column: 1;
                justify-self: start;
            }

            .cart-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .cart-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<main class="cart-main">
    <div class="cart-header">
        <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
        <a href="shop.php" class="back-to-shop">
            <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
    </div>

    <div id="cart-container">
        <div class="cart-items-header">
            <span>Product</span>
            <span>Price</span>
            <span>Quantity</span>
            <span>Action</span>
        </div>
        
        <div id="cart-items">
            <?php if (empty($cart_items)): ?>
                <p class="empty-cart">Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <div class="cart-total">
                Total: <span id="cart-total">RM 0.00</span>
            </div>
            
            <div class="cart-actions">
                <button id="clear-cart-btn" class="secondary-btn">
                    <i class="fas fa-trash-alt"></i> Clear Cart
                </button>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="checkout-btn" id="checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                <?php else: ?>
                    <button id="checkout-btn" class="checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        loadCart();
        setupClearCartButton();
        setupCheckoutButton();
    });

    async function loadCart() {
        let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
        let cartContainer = document.getElementById("cart-items");
        let cartTotal = document.getElementById("cart-total");
        cartContainer.innerHTML = "";

        let total = 0;

        if (cartItems.length === 0) {
            cartContainer.innerHTML = "<p class='empty-cart'>Your cart is empty.</p>";
            document.getElementById("clear-cart-btn").style.display = "none";
        } else {
            document.getElementById("clear-cart-btn").style.display = "block";
            
            const stockPromises = cartItems.map(async (item, index) => {
                if (!item.variantId) {
                    try {
                        const response = await fetch('find_variant_id.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ P_ID: item.id, P_Size: item.size })
                        });
                        const result = await response.json();
                        if (result.PV_ID) {
                            cartItems[index].variantId = result.PV_ID;
                            localStorage.setItem("cart", JSON.stringify(cartItems));
                        }
                    } catch (error) {
                        console.error("Error finding variant:", error);
                        return { index, stock: 1, error: true };
                    }
                }
                
                try {
                    const response = await fetch(`get_stock.php?pv_id=${item.variantId || 0}`);
                    const data = await response.json();
                    return { 
                        index, 
                        stock: data.stock || 0,
                        variantId: item.variantId,
                        success: data.success
                    };
                } catch (error) {
                    console.error("Error fetching stock:", error);
                    return { index, stock: 0, error: true };
                }
            });

            const stockResults = await Promise.all(stockPromises);

            cartItems.forEach((item, index) => {
                const stockInfo = stockResults.find(r => r.index === index);
                const maxStock = stockInfo?.stock || 0;
                const currentQty = Math.min(item.quantity, maxStock);
                const itemTotal = item.price * currentQty;

                if (item.quantity > maxStock) {
                    item.quantity = currentQty;
                    localStorage.setItem("cart", JSON.stringify(cartItems));
                }

                let cartItemDiv = document.createElement("div");
                cartItemDiv.classList.add("cart-item");
                cartItemDiv.innerHTML = `
                    <div class="cart-item-info">
                        <img src="${item.image}" alt="${item.name}">
                        <div>
                            <p class="cart-item-name">${item.name}</p>
                            <p class="cart-item-size">Size: ${item.size}</p>
                            ${item.quantity > maxStock ? 
                                `<p class="stock-warning">Only ${maxStock} available!</p>` : ''}
                            ${stockInfo?.error ? 
                                `<p class="stock-error">⚠️ Failed to check stock</p>` : ''}
                        </div>
                    </div>
                    <p>RM ${item.price.toFixed(2)}</p>
                    <input type="number" min="1" max="${maxStock}" 
                           value="${currentQty}" 
                           data-index="${index}" 
                           class="cart-qty">
                    <button class="remove-btn" onclick="removeFromCart(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                cartContainer.appendChild(cartItemDiv);
                total += itemTotal;
            });
        }

        cartTotal.innerText = `RM ${total.toFixed(2)}`;
        attachQuantityChangeEvents();
    }

    function attachQuantityChangeEvents() {
        document.querySelectorAll(".cart-qty").forEach(input => {
            input.addEventListener("change", async function() {
                const index = parseInt(this.getAttribute("data-index"));
                let newQty = parseInt(this.value) || 1;
                let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
                const item = cartItems[index];

                let maxStock = 1;
                if (item.variantId) {
                    try {
                        const response = await fetch(`get_stock.php?pv_id=${item.variantId}`);
                        const data = await response.json();
                        maxStock = data.stock || 1;
                    } catch (error) {
                        console.error("Error fetching stock:", error);
                    }
                }

                newQty = Math.max(1, Math.min(newQty, maxStock));

                if (newQty !== parseInt(this.value)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock Limit',
                        html: `Only <b>${maxStock}</b> available for ${item.name} (${item.size})`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        this.value = newQty;
                        cartItems[index].quantity = newQty;
                        localStorage.setItem("cart", JSON.stringify(cartItems));
                        loadCart();
                    });
                    return;
                }

                cartItems[index].quantity = newQty;
                localStorage.setItem("cart", JSON.stringify(cartItems));
                loadCart();
            });
        });
    }

    function removeFromCart(index) {
        Swal.fire({
            title: 'Remove Item',
            text: 'Are you sure you want to remove this item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let cartItems = JSON.parse(localStorage.getItem("cart")) || [];
                cartItems.splice(index, 1);
                localStorage.setItem("cart", JSON.stringify(cartItems));
                loadCart();
                
                Swal.fire(
                    'Removed!',
                    'Item has been removed from your cart.',
                    'success'
                );
            }
        });
    }

    function setupClearCartButton() {
        document.getElementById("clear-cart-btn").addEventListener("click", function() {
            Swal.fire({
                title: 'Clear Cart',
                text: 'Are you sure you want to remove all items from your cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, clear it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    localStorage.removeItem("cart");
                    loadCart();
                    
                    Swal.fire(
                        'Cleared!',
                        'Your cart is now empty.',
                        'success'
                    );
                }
            });
        });
    }

    function setupCheckoutButton() {
        document.getElementById("checkout-btn").addEventListener("click", function(e) {
            // Prevent default action if it's a link
            if (e.preventDefault) e.preventDefault();
            
            const cartItems = JSON.parse(localStorage.getItem("cart")) || [];
            
            if (cartItems.length === 0) {
                Swal.fire({
                    title: 'Empty Cart',
                    text: 'Your cart is empty. Please add items before checkout.',
                    icon: 'warning',
                    confirmButtonColor: '#4a6baf',
                    confirmButtonText: 'Continue Shopping'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'shop.php';
                    }
                });
                return false;
            }

            <?php if (!isset($_SESSION['user_id'])): ?>
                Swal.fire({
                    title: 'Login Required',
                    text: 'Please log in to proceed to checkout',
                    icon: 'info',
                    confirmButtonText: 'Go to Login',
                    confirmButtonColor: '#4a6baf'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.html';
                    }
                });
            <?php else: ?>
                window.location.href = 'checkout.php';
            <?php endif; ?>
        });
    }

</script>
</body>
</html>