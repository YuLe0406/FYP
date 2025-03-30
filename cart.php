<?php
session_start();
include 'db.php';

// Debug: Log received POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['product_id'], $_POST['pv_id'], $_POST['quantity'])) {
        echo "Missing required fields!";
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $pv_id = intval($_POST['pv_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL; // Assuming user_id exists

    // Debug: Check values before inserting
    echo "Received Data: Product ID = $product_id, PV_ID = $pv_id, Quantity = $quantity<br>";

    // Prepare SQL statement
    $insert_sql = "INSERT INTO cart (user_id, product_id, pv_id, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiii", $user_id, $product_id, $pv_id, $quantity);
    if ($stmt->execute()) {
        echo "Product successfully added to cart!";
    } else {
        echo "Insert Error: " . $stmt->error; // Debug: Print MySQL error
    }
    exit;
}

// Fetch cart items
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
