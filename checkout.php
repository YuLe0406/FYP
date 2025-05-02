<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
include 'header.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user addresses
$stmt = $conn->prepare("SELECT * FROM USER_ADDRESS WHERE U_ID = ? ORDER BY UA_IsDefault DESC, UA_Type ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$fullName = $user['U_FName'] . ' ' . $user['U_LName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | CTRL+X</title>
    <link rel="stylesheet" href="checkout.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                <label for="address">Shipping Address</label>
                <select id="address" name="address" required>
                    <option value="">-- Select Address --</option>
                    <?php foreach ($addresses as $address): ?>
                        <option value="<?php echo $address['UA_ID']; ?>" 
                                <?php echo $address['UA_IsDefault'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(
                                $address['UA_Type'] . ': ' . 
                                $address['UA_Address1'] . ', ' . 
                                ($address['UA_Address2'] ? $address['UA_Address2'] . ', ' : '') . 
                                $address['UA_Postcode'] . ' ' . 
                                $address['UA_City'] . ', ' . 
                                $address['UA_State']
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="address-details">
                    <?php if (!empty($addresses)): ?>
                        <?php $defaultAddress = array_filter($addresses, function($a) { return $a['UA_IsDefault']; }); ?>
                        <?php $displayAddress = !empty($defaultAddress) ? reset($defaultAddress) : $addresses[0]; ?>
                        <p>
                            <?php echo htmlspecialchars($displayAddress['UA_Address1']); ?><br>
                            <?php if ($displayAddress['UA_Address2']): ?>
                                <?php echo htmlspecialchars($displayAddress['UA_Address2']); ?><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars(
                                $displayAddress['UA_Postcode'] . ' ' . 
                                $displayAddress['UA_City'] . ', ' . 
                                $displayAddress['UA_State']
                            ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <label for="payment-method">Payment Method</label>
                <select id="payment-method" name="payment-method" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="Credit Card">Credit/Debit Card</option>
                    <option value="PayPal">PayPal</option>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addressSelect = document.getElementById("address");
    const addressDetails = document.getElementById("address-details");
    
    addressSelect.addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];
        addressDetails.innerHTML = `<p>${selectedOption.text}</p>`;
    });

    // Check if there are no addresses and show alert
    <?php if (empty($addresses)): ?>
        Swal.fire({
            icon: 'warning',
            title: 'No Shipping Address',
            html: 'You need to add a shipping address before you can checkout.<br><br>You will be redirected to your profile to add one.',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'profile.php';
            }
        });
        
        // Disable the place order button
        document.getElementById('place-order-btn').disabled = true;
    <?php endif; ?>
});
</script>

<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        color: black;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
        z-index: 999;
        border-radius: 4px;
    }
    .dropdown:hover .dropdown-menu {
        display: block;
    }
    .dropdown-menu a {
        padding: 10px;
        display: block;
        text-decoration: none;
        color: black;
    }
    .dropdown-menu a:hover {
        background-color: #eee;
    }
</style>

</body>
</html>