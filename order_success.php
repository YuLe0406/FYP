<?php
session_start();

// Check if user logged in (optional)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get order_id from URL
if (!isset($_GET['order_id'])) {
    header("Location: index.php"); // Go back home if no order id
    exit();
}

$orderId = intval($_GET['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Successful | CTRL+X</title>
    <link rel="stylesheet" href="checkout.css"> <!-- You can reuse your checkout CSS -->
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <style>
        .success-container {
            padding: 50px;
            text-align: center;
        }
        .success-container h1 {
            color: green;
            font-size: 40px;
        }
        .success-container p {
            font-size: 20px;
            margin: 20px 0;
        }
        .success-container a {
            background-color: #1db954;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .success-container a:hover {
            background-color: #00ff88;
            color: black;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="success-container">
    <h1>🎉 Thank you for your order!</h1>
    <p>Your Order ID is: <strong>#<?php echo htmlspecialchars($orderId); ?></strong></p>
    <p>We will process your order very soon!</p>
    <a href="shop.php">Continue Shopping</a>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
        