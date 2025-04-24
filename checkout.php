<?php
include 'header.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
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


        <!-- Billing -->
        <div id="billing-details">
            <h2>Billing Information</h2>
            <form id="checkout-form">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="012-345 6789" required>

                <label for="existing-address">Choose Saved Address</label>
                    <select id="existing-address" name="existing-address">
                        <option value="">-- Select an Address --</option>
                        <?php
                        include 'db.php';
                        $user_id = $_SESSION['U_ID'];
                        $sql = "SELECT * FROM ADDRESS WHERE U_ID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['AD_ID']}' data-details='" . htmlspecialchars($row['AD_Details']) . "'>{$row['AD_Details']}</option>";
                        }
                        ?>
                    </select>

                <label for="address1">Address Line 1</label>
                <input type="text" id="address1" name="address1" required>

                <div class="checkbox-wrapper-4">
                    <input class="inp-cbx" id="morning" type="checkbox"/>
                    <label class="cbx" for="morning"><span>
                    <svg width="12px" height="10px">
                        <use xlink:href="#check-4"></use>
                    </svg></span><span>Save Address for Future Use</span></label>
                    <svg class="inline-svg">
                        <symbol id="check-4" viewbox="0 0 12 10">
                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </symbol>
                    </svg>
                </div>

                <label for="payment-method">Payment Method</label>
                <select id="payment-method" name="payment-method" required>
                    <option value="cod">Cash on Delivery</option>
                    <option value="credit_card">Credit/Debit Card</option>
                    <option value="paypal">PayPal</option>
                </select>

                <!-- Card Details -->
                <div id="card-details">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX">

                    <label for="card-name">Cardholder Name</label>
                    <input type="text" id="card-name">

                    <label for="expiry-date">Expiry Date</label>
                    <input type="text" id="expiry-date" placeholder="MM/YY">

                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" placeholder="XXX">
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div id="order-summary">
            <h2>Order Summary</h2>
            <div id="cart-items"></div>
            <hr>
            <p>Total: <span id="cart-total">RM 0.00</span></p>
            <button type="button" id="place-order-btn">Place Order</button>

            <p id="loading-message" style="display:none;">Processing payment...</p>
            <p id="success-message" style="display:none; color:green;">Order Successful! 🎉</p>
        </div>

</main>

<?php include 'footer.php'; ?>
<script src="checkout.js"></script>

</body>
</html>
