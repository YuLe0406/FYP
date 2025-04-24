<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
include 'header.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
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
                <input type="text" id="fullname" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" required>

                <label for="existing-address">Choose Saved Address</label>
                <select id="existing-address">
                    <option value="">-- Select Saved Address --</option>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM USER_ADDRESS WHERE U_ID = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($row = $res->fetch_assoc()) {
                        $fullAddress = $row['UA_Address1'] . ", " . $row['UA_Postcode'] . " " . $row['UA_City'] . ", " . $row['UA_State'];
                        echo "<option value='{$row['UA_ID']}' data-address1='{$row['UA_Address1']}' data-city='{$row['UA_City']}' data-state='{$row['UA_State']}' data-postcode='{$row['UA_Postcode']}'>$fullAddress</option>";
                    }
                    ?>
                </select>

                <label for="address1">Street Address</label>
                <input type="text" id="address1" required>

                <label for="city">City</label>
                <input type="text" id="city" required>

                <label for="state">State</label>
                <select id="state" required>
                    <option value="">-- Select State --</option>
                    <?php
                    $states = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu'];
                    foreach ($states as $state) {
                        echo "<option value='$state'>$state</option>";
                    }
                    ?>
                </select>

                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" required>

                <label><input type="checkbox" id="save-address"> Save Address for Future Use</label>

                <label for="payment-method">Payment Method</label>
                <select id="payment-method" required>
                    <option value="cod">Cash on Delivery</option>
                    <option value="credit_card">Credit/Debit Card</option>
                    <option value="paypal">PayPal</option>
                </select>

                <div id="card-details" style="display: none;">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number">

                    <label for="card-name">Cardholder Name</label>
                    <input type="text" id="card-name">

                    <label for="expiry-date">Expiry Date</label>
                    <input type="text" id="expiry-date">

                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv">
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
