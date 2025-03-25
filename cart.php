<?php
session_start();
include 'db.php'; // Connect to database

// Check if user is logged in (optional)
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Fetch cart items from session or database
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | CTRL+X</title>
    <link rel="stylesheet" href="cart.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <header>
        <div class="logo">
            <a href="index.php">CTRL+X</a>
        </div>        
        <div class="search-container">
            <input type="text" placeholder="Search for products, trends, and brands">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <div class="icons">
            <a href="login.php" class="icon"><i class="fas fa-user"></i> Login / Register</a>
            <a href="#" class="icon"><i class="fas fa-heart"></i></a>
            <a href="cart.php" class="icon"><i class="fas fa-shopping-cart"></i></a>
        </div>
    </header>

    <!-- Discount Label -->
    <div class="discount-label">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>

    <h1>Your Cart</h1>
    <a href="shop.php" class="back-to-shop">← Continue Shopping</a>

    <section id="cart-container">
        <div id="cart-items">
            <?php if (empty($cart_items)) : ?>
                <p>Your cart is empty.</p>
            <?php else : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $id => $item) : ?>
                            <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" class="quantity" data-id="<?php echo $id; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="15">
                                </td>
                                <td>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="remove-item" data-id="<?php echo $id; ?>">Remove</button>
                                </td>
                            </tr>
                            <?php $total_price += $item['price'] * $item['quantity']; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div id="cart-summary">
            <h2>Cart Summary</h2>
            <p>Total: <span id="cart-total">RM <?php echo number_format($total_price, 2); ?></span></p>
            <button id="checkout-btn">Proceed to Checkout</button>
        </div>
    </section>

    <script src="cart.js"></script>
</body>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3>Delivery</h3>
            <ul>
                <li><a href="#">Delivery & Shipping</a></li>
                <li><a href="#">Returns & Exchange</a></li>
                <li><a href="#">Track Your Order</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>About Us</h3>
            <ul>
                <li><a href="#">Our Story</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Help</h3>
            <ul>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Terms & Conditions</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Customer Service</h3>
            <p>📞 +6010 828 0026</p>
            <p>📧 support@ctrlx.com</p>
        </div>

        <div class="footer-column">
            <h3>Operation Time</h3>
            <p>Mon - Fri: 9:30am - 6:00pm</p>
        </div>

        <div class="footer-column newsletter">
            <h3>Sign up for our Newsletter</h3>
            <form>
                <input type="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2025 CTRL+X. All Rights Reserved.</p>
    </div>
</footer>
</html>
