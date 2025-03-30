<?php
session_start();
include 'db.php';

$user_id = 1; // Change this to actual logged-in user ID

$sql = "SELECT CART.cart_id, PRODUCT.P_Name, PRODUCT.P_Picture, PRODUCT.P_Price, CART.size, CART.quantity 
        FROM CART 
        INNER JOIN PRODUCT ON CART.product_id = PRODUCT.P_ID 
        WHERE CART.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | CTRL+X</title>
    <link rel="stylesheet" href="cart.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <h1>Your Cart</h1>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_price = 0;
            while ($row = $result->fetch_assoc()) {
                $subtotal = $row['P_Price'] * $row['quantity'];
                $total_price += $subtotal;
                echo "<tr>
                    <td><img src='{$row['P_Picture']}' width='50'> {$row['P_Name']}</td>
                    <td>{$row['size']}</td>
                    <td>{$row['quantity']}</td>
                    <td>RM " . number_format($row['P_Price'], 2) . "</td>
                    <td>RM " . number_format($subtotal, 2) . "</td>
                    <td><button onclick='removeFromCart({$row['cart_id']})'>❌</button></td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <h2>Total: RM <?php echo number_format($total_price, 2); ?></h2>
    <a href="checkout.html">Proceed to Checkout</a>
</main>

<?php include 'footer.php'; ?>
</body>
</html>



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

                            // Fetch product details from database
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
                                <tr>
                                    <td>
                                        <img src="images/<?php echo $product['P_Picture']; ?>" alt="<?php echo $product['P_Name']; ?>" width="50">
                                        <?php echo $product['P_Name']; ?>
                                    </td>
                                    <td><?php echo $size; ?></td>
                                    <td><?php echo $quantity; ?></td>
                                    <td>RM <?php echo number_format($product['P_Price'], 2); ?></td>
                                    <td>RM <?php echo number_format($item_price, 2); ?></td>
                                    <td><a href="remove_from_cart.php?id=<?php echo $product_id; ?>&size=<?php echo $size; ?>" class="remove-btn">❌</a></td>
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