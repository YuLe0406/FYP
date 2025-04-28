<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
include 'header.php';
include 'db.php';
$user_id = $_SESSION['user_id'];

// 🔥 Fetch user info
$stmt = $conn->prepare("SELECT U_FName, U_LName, U_Email, U_PNumber, U_Address FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// Combine full name
$fullName = $user['U_FName'] . ' ' . $user['U_LName'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | CTRL+X</title>
    <link rel="stylesheet" href="checkout.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

<main>
    <h1>Checkout</h1>

    <section id="checkout-container">
        <div id="billing-details">
            <h2>Billing Information</h2>
            <form id="checkout-form">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly>

                <label for="email">Email Address</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" readonly>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" value="<?php echo htmlspecialchars($_SESSION['user_phone']); ?>" readonly>

                <label for="address1">Street Address</label>
                <input type="text" id="address1" value="<?php echo htmlspecialchars($_SESSION['user_address']); ?>" readonly>

                <label for="payment-method">Payment Method</label>
                <select id="payment-method" required>
                    <option value="credit_card" selected>Credit/Debit Card</option>
                </select>

                <!-- Card Details -->
                <div id="card-details">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>

                    <label for="card-name">Cardholder Name</label>
                    <input type="text" id="card-name" placeholder="Name on Card" required>

                    <label for="expiry-date">Expiry Date</label>
                    <input type="text" id="expiry-date" maxlength="5" placeholder="MM/YY" required>

                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" maxlength="3" placeholder="CVV" required>
                </div>

                <p style="font-size:12px; color:red;">* To update your address, please go to <a href="profile.php">Profile</a> page.</p>
                
            </form>
        </div>

        <!-- Order Summary -->
        <div id="order-summary">
            <h2>Order Summary</h2>
            <div id="cart-items"></div>
            <hr>
            <p>Total: <span id="cart-total">RM 0.00</span></p>
            <button id="place-order-btn">Place Order</button>
            <p id="loading-message" style="display:none;">Processing payment...</p>
            <p id="success-message" style="display:none; color:green;">Order Successful! 🎉</p>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="checkout.js"></script>
</body>
</html>
