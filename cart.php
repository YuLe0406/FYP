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
    <title>Cart | CTRL+X</title>
    <link rel="stylesheet" href="cart.css">
    <script defer src="cart.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<main>
    <h1>Your Cart</h1>
    <a href="shop.php" class="back-to-shop">← Continue Shopping</a>

    <section id="cart-container">
        <div id="cart-items">
            <!-- Cart items will be dynamically loaded here -->
        </div>

        <div id="cart-summary">
            <h2>Cart Summary</h2>
            <p>Total: <span id="cart-total">RM 0.00</span></p>
            <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="checkout.php"><button id="checkout-btn">Proceed to Checkout</button></a>
        <?php } else { ?>
            <button id="checkout-btn" onclick="alert('Please log in before proceeding to checkout.'); window.location.href='login.html';">Proceed to Checkout</button>
        <?php } ?>

        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
