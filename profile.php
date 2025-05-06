<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT U_FName, U_LName, U_Email, U_PNumber, U_Gender FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = strtolower($_POST['email'] ?? '');
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    // Basic validation
    $errors = [];
    if (empty(trim($firstName))) $errors[] = "First name is required";
    if (empty(trim($lastName))) $errors[] = "Last name is required";
    if (empty(trim($email))) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty(trim($phone))) $errors[] = "Phone number is required";
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = "Phone number must be 10 or 11 digits";
    if (empty(trim($gender))) $errors[] = "Gender is required";
    
    if (empty($errors)) {
        // Update user data
        $update_stmt = $conn->prepare("UPDATE USER SET U_FName = ?, U_LName = ?, U_Email = ?, U_PNumber = ?, U_Gender = ? WHERE U_ID = ?");
        $update_stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $gender, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            if ($conn->errno == 1062) {
                $error = "This email is already registered to another account";
            } else {
                $error = "Failed to update profile: " . $conn->error;
            }
        }
    } else {
        $error = implode("<br>", $errors);
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
        .profile-form {
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input, 
        .form-group select {
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
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        small {
            color: #7f8c8d;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
            <a href="addresses.php"><i class="fas fa-map-marker-alt"></i> My Addresses</a>
            <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
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
                    <p><?= htmlspecialchars($user['U_Email']) ?></p>
                </div>
            </div>

            <!-- Profile Information Form -->
            <h2 class="section-title">PERSONAL INFORMATION</h2>
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['U_FName']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['U_LName']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['U_Email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['U_PNumber']) ?>" pattern="[0-9]{10,11}" required>
                    <small>Format: 10 or 11 digits (e.g. 0123456789)</small>
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="male" <?= $user['U_Gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $user['U_Gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
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
</body>
</html>