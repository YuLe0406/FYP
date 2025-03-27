<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT A_Name, A_Email, A_CN FROM ADMIN WHERE A_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql = "SELECT A_Password FROM ADMIN WHERE A_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $db_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE ADMIN SET A_Password = ? WHERE A_ID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $admin_id);
            $update_stmt->execute();
            $update_stmt->close();
            $success_msg = "Password updated successfully!";
        } else {
            $error_msg = "New passwords do not match!";
        }
    } else {
        $error_msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="admin_profile.css">
</head>
<body>
    <div class="container">
        <h2>Admin Profile</h2>
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact); ?></p>
        </div>

        <h3>Change Password</h3>
        <form method="POST">
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="update_password">Update Password</button>
        </form>

        <?php if (isset($error_msg)) { echo "<p class='error'>$error_msg</p>"; } ?>
        <?php if (isset($success_msg)) { echo "<p class='success'>$success_msg</p>"; } ?>
    </div>
</body>
</html>
