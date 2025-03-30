<?php
session_start();
include 'db.php';

// Get cart items from the database
$sql = "SELECT 
            cart.cart_id, 
            cart.quantity, 
            product.P_Name, 
            product.P_Price, 
            product.P_Picture, 
            product_variants.P_Size
        FROM cart
        INNER JOIN product ON cart.product_id = product.P_ID
        INNER JOIN product_variants ON cart.pv_id = product_variants.PV_ID";

$result = $conn->query($sql);
$total_price = 0;

// Handle adding to cart
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $pv_id = $_POST['pv_id'];
    $quantity = $_POST['quantity'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL";

    $insert_sql = "INSERT INTO cart (user_id, product_id, pv_id, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiii", $user_id, $product_id, $pv_id, $quantity);

    if ($stmt->execute()) {
        echo "Product added to cart!";
    } else {
        echo "Failed to add product.";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | CTRL+X</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>

<main>
    <h1>Your Cart</h1>
    <a href="shop.php" class="back-to-shop">← Continue Shopping</a>

    <section id="cart-container">
        <div id="cart-items">
            <?php if ($result->num_rows == 0) { ?>
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
                        <?php while ($item = $result->fetch_assoc()) { 
                            $item_price = $item['P_Price'] * $item['quantity'];
                            $total_price += $item_price;
                        ?>
                            <tr>
                                <td><img src="images/<?php echo $item['P_Picture']; ?>" width="50"><?php echo $item['P_Name']; ?></td>
                                <td><?php echo $item['P_Size']; ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>RM <?php echo number_format($item['P_Price'], 2); ?></td>
                                <td>RM <?php echo number_format($item_price, 2); ?></td>
                                <td><a href="remove_from_cart.php?id=<?php echo $item['cart_id']; ?>">❌</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
</main>

</body>
</html>
