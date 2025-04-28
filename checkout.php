<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
include 'header.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

// 🛠️ Fetch user information from USER table
$stmt = $conn->prepare("SELECT * FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Prepare full name
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
                <input type="text" id="fullname" value="<?php echo htmlspecialchars($fullName); ?>" readonly>

                <label for="email">Email Address</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['U_Email']); ?>" readonly>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" value="<?php echo htmlspecialchars($user['U_PNumber']); ?>" readonly>

                <label for="address1">Saved Address</label>
                <textarea id="address1" rows="3" readonly><?php echo htmlspecialchars($user['U_Address']); ?></textarea>

                <label for="payment-method">Payment Method</label>
                <select id="payment-method" name="payment-method" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="credit_card">Credit/Debit Card</option> <!-- Only credit card allowed -->
                </select>

                <div id="card-details" style="display: none;">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">

                    <label for="card-name">Cardholder Name</label>
                    <input type="text" id="card-name" placeholder="Name on Card">

                    <label for="expiry-date">Expiry Date</label>
                    <input type="text" id="expiry-date" placeholder="MM/YY" maxlength="5">

                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" placeholder="XXX" maxlength="3">
                </div>
            </form>
        </div>

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
