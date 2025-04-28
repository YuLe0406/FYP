<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch user addresses
$address_stmt = $conn->prepare("SELECT * FROM USER_ADDRESS WHERE U_ID = ? ORDER BY UA_IsDefault DESC, UA_Type");
$address_stmt->bind_param("i", $_SESSION['user_id']);
$address_stmt->execute();
$addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $address = $_POST['address'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['dob'] ?? '';
        
        // Update user data
        $update_stmt = $conn->prepare("UPDATE USER SET U_Address = ?, U_Gender = ?, U_DOB = ? WHERE U_ID = ?");
        $update_stmt->bind_param("sssi", $address, $gender, $dob, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Failed to update profile: " . $conn->error;
        }
    }
    
    // Handle address operations
    if (isset($_POST['add_address'])) {
        $address1 = $_POST['address1'] ?? '';
        $address2 = $_POST['address2'] ?? '';
        $postcode = $_POST['postcode'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $type = $_POST['type'] ?? 'home';
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, first unset any existing default
        if ($is_default) {
            $conn->query("UPDATE USER_ADDRESS SET UA_IsDefault = 0 WHERE U_ID = {$_SESSION['user_id']}");
        }
        
        $add_stmt = $conn->prepare("INSERT INTO USER_ADDRESS (U_ID, UA_Type, UA_Address1, UA_Address2, UA_Postcode, UA_City, UA_State, UA_IsDefault) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $add_stmt->bind_param("issssssi", $_SESSION['user_id'], $type, $address1, $address2, $postcode, $city, $state, $is_default);
        
        if ($add_stmt->execute()) {
            $success = "Address added successfully!";
            // Refresh addresses
            $address_stmt->execute();
            $addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Failed to add address: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete_address'])) {
        $ua_id = $_POST['ua_id'] ?? 0;
        
        $del_stmt = $conn->prepare("DELETE FROM USER_ADDRESS WHERE UA_ID = ? AND U_ID = ?");
        $del_stmt->bind_param("ii", $ua_id, $_SESSION['user_id']);
        
        if ($del_stmt->execute()) {
            $success = "Address deleted successfully!";
            // Refresh addresses
            $address_stmt->execute();
            $addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Failed to delete address: " . $conn->error;
        }
    }
    
    if (isset($_POST['set_default_address'])) {
        $ua_id = $_POST['ua_id'] ?? 0;
        
        // First unset any existing default
        $conn->query("UPDATE USER_ADDRESS SET UA_IsDefault = 0 WHERE U_ID = {$_SESSION['user_id']}");
        
        // Set the new default
        $default_stmt = $conn->prepare("UPDATE USER_ADDRESS SET UA_IsDefault = 1 WHERE UA_ID = ? AND U_ID = ?");
        $default_stmt->bind_param("ii", $ua_id, $_SESSION['user_id']);
        
        if ($default_stmt->execute()) {
            $success = "Default address updated successfully!";
            // Refresh addresses
            $address_stmt->execute();
            $addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Failed to set default address: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | CTRL+X</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .sidebar a.active {
            background: #3498db;
            border-left: 4px solid #2980b9;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #3498db;
        }
        .profile-info h2 {
            margin: 0;
            color: #2c3e50;
        }
        .profile-info p {
            margin: 5px 0 0;
            color: #7f8c8d;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .address-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .address-card.default {
            border-color: #3498db;
            background-color: rgba(52, 152, 219, 0.05);
        }
        .address-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .address-name {
            font-weight: bold;
            font-size: 18px;
        }
        .address-default {
            background: #3498db;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        .address-contact {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .address-details {
            line-height: 1.6;
        }
        .address-actions {
            margin-top: 15px;
        }
        .address-actions a {
            color: #3498db;
            margin-right: 15px;
            text-decoration: none;
        }
        .address-actions a:hover {
            text-decoration: underline;
        }
        .add-address-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-address-btn i {
            margin-right: 8px;
        }
        .address-form {
            display: none;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .address-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-actions {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            margin-right: 10px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
            <a href="addresses.php"><i class="fas fa-map-marker-alt"></i> My Addresses</a>
            <a href="login.html"><i class="fas fa-key"></i> Change Password</a>
            <a href="cart.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
            <a href="#"><i class="fas fa-tag"></i> My Coupons</a>
            <a href="wishlist.php"><i class="fas fa-heart"></i> My Wishlist</a>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['U_FName'].'+'.$user['U_LName']) ?>&background=3498db&color=fff" 
                     alt="Profile" class="profile-avatar">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['U_FName'] . ' ' . $user['U_LName']) ?></h2>
                    <p>Member since <?= date('F Y', strtotime($user['U_AccountCreated'])) ?></p>
                </div>
            </div>

            <h2 class="section-title">ADDRESSES</h2>

            <button id="toggleAddressForm" class="add-address-btn">
                <i class="fas fa-plus"></i> Add New Address
            </button>

            <!-- New Address Form -->
            <form method="POST" id="newAddressForm" class="address-form">
                <input type="hidden" name="add_address" value="1">
                <div class="form-group">
                    <label for="address1">Address Line 1</label>
                    <input type="text" id="address1" name="address1" required placeholder="Street address">
                </div>
                <div class="form-group">
                    <label for="address2">Address Line 2 (Optional)</label>
                    <input type="text" id="address2" name="address2" placeholder="Apartment, suite, unit">
                </div>
                <div class="form-group">
                    <label for="postcode">Postcode</label>
                    <input type="text" id="postcode" name="postcode" required placeholder="Postal code">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required placeholder="City">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <select name="state" id="state" required>
                        <option value="">Select State</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Penang">Penang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_default" id="is_default">
                        Set as default shipping address
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Address</button>
                    <button type="button" id="cancelAddressForm" class="btn btn-secondary">Cancel</button>
                </div>
            </form>

            <!-- Address List -->
            <?php if (empty($addresses)): ?>
                <p>No addresses saved yet. Add your first address above.</p>
            <?php else: ?>
                <?php foreach ($addresses as $address): ?>
                    <div class="address-card <?= $address['UA_IsDefault'] ? 'default' : '' ?>">
                        <div class="address-header">
                            <div class="address-name"><?= htmlspecialchars($user['U_FName'] . ' ' . $user['U_LName']) ?></div>
                            <?php if ($address['UA_IsDefault']): ?>
                                <div class="address-default">Default shipping</div>
                            <?php endif; ?>
                        </div>
                        <div class="address-contact"><?= htmlspecialchars($user['U_PNumber']) ?></div>
                        <div class="address-details">
                            <p><?= htmlspecialchars($address['UA_Address1']) ?></p>
                            <?php if (!empty($address['UA_Address2'])): ?>
                                <p><?= htmlspecialchars($address['UA_Address2']) ?></p>
                            <?php endif; ?>
                            <p><?= htmlspecialchars($address['UA_Postcode']) ?> <?= htmlspecialchars($address['UA_City']) ?></p>
                            <p><?= htmlspecialchars($address['UA_State']) ?></p>
                            <p>Malaysia</p>
                        </div>
                        <div class="address-actions">
                            <?php if (!$address['UA_IsDefault']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="set_default_address" value="1">
                                    <input type="hidden" name="ua_id" value="<?= $address['UA_ID'] ?>">
                                    <a href="#" onclick="this.closest('form').submit(); return false;">Set as default billing</a>
                                </form>
                            <?php endif; ?>
                            <a href="#"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="delete_address" value="1">
                                <input type="hidden" name="ua_id" value="<?= $address['UA_ID'] ?>">
                                <a href="#" onclick="if(confirm('Are you sure you want to delete this address?')) { this.closest('form').submit(); } return false;">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <script>
        Swal.fire({
            title: 'Success!',
            text: '<?= $success ?>',
            icon: 'success',
            confirmButtonColor: '#3498db'
        });
        </script>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <script>
        Swal.fire({
            title: 'Error!',
            text: '<?= $error ?>',
            icon: 'error',
            confirmButtonColor: '#3498db'
        });
        </script>
    <?php endif; ?>
    
    <script>
        // Toggle new address form visibility
        document.getElementById('toggleAddressForm').addEventListener('click', function() {
            document.getElementById('newAddressForm').classList.add('active');
            this.style.display = 'none';
        });

        document.getElementById('cancelAddressForm').addEventListener('click', function() {
            document.getElementById('newAddressForm').classList.remove('active');
            document.getElementById('toggleAddressForm').style.display = 'flex';
        });
    </script>
</body>
</html>