<?php
session_start();
include 'db.php'; // Database connection
include 'header.php'; // Include header

$user_id = 1; // Replace with session user ID when login system is ready

// Fetch cart items from database
$sql = "SELECT cart.cart_id, cart.product_id, cart.size, cart.quantity, 
        PRODUCT.P_Name, PRODUCT.P_Price, PRODUCT.P_Picture 
        FROM cart 
        INNER JOIN PRODUCT ON cart.product_id = PRODUCT.P_ID 
        WHERE cart.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

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
                            $item_price = $item['P_Price'] * $item['quantity'];
                            $total_price += $item_price;
                        ?>
                            <tr>
                                <td>
                                    <img src="images/<?php echo $item['P_Picture']; ?>" alt="<?php echo $item['P_Name']; ?>" width="50">
                                    <?php echo $item['P_Name']; ?>
                                </td>
                                <td><?php echo $item['size']; ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>RM <?php echo number_format($item['P_Price'], 2); ?></td>
                                <td>RM <?php echo number_format($item_price, 2); ?></td>
                                <td><a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="remove-btn">❌</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>

        <div id="cart-summary">
            <h2>Cart Summary</h2>
            <p>Total: <span id="cart-total">RM <?php echo number_format($total_price, 2); ?></span></p>
            <a href="checkout.php"><button id="checkout-btn">Proceed to Checkout</button></a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>  <!-- Include footer -->

</body>
</html>
