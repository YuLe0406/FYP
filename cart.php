<?php
session_start();
include 'db.php'; // Database connection
include 'header.php'; // Header

// Retrieve cart items from session
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
    <script defer src="cart.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

    <div class="discount-label">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>

<main>
    <h1>Your Cart</h1>
    <a href="shop.php" class="back-to-shop">← Continue Shopping</a>

    <section id="cart-container">
        <div id="cart-items">
            <?php if (empty($cart_items)) { ?>
                <p>Your cart is empty.</p>
            <?php } else { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item) { 
                            $product_id = $item['id'];
                            $size = $item['size'];
                            $quantity = $item['quantity'];

                            // Fetch product details
                            $sql = "SELECT P_Name, P_Price, P_Picture FROM PRODUCT WHERE P_ID = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product = $result->fetch_assoc();

                            if ($product) {
                                $item_price = $product['P_Price'] * $quantity;
                                $total_price += $item_price;
                            ?>
                                <tr data-id="<?php echo $product_id; ?>" data-size="<?php echo $size; ?>">
                                    <td>
                                        <img src="images/<?php echo $product['P_Picture']; ?>" alt="<?php echo $product['P_Name']; ?>" width="50">
                                        <?php echo $product['P_Name']; ?>
                                    </td>
                                    <td><?php echo $size; ?></td>
                                    <td>
                                        <button class="quantity-btn minus">-</button>
                                        <input type="text" class="quantity-input" value="<?php echo $quantity; ?>" readonly>
                                        <button class="quantity-btn plus">+</button>
                                    </td>
                                    <td>RM <?php echo number_format($product['P_Price'], 2); ?></td>
                                    <td class="item-total">RM <?php echo number_format($item_price, 2); ?></td>
                                    <td><button class="remove-btn">❌</button></td>
                                </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>

        <div id="cart-summary">
            <h2>Cart Summary</h2>
            <p>Total: <span id="cart-total">RM <?php echo number_format($total_price, 2); ?></span></p>
            <a href="checkout.html"><button id="checkout-btn">Proceed to Checkout</button></a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?> <!-- Footer -->

</body>
</html>
