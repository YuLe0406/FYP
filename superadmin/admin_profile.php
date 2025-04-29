<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$error_msg = '';
$success_msg = '';

// Get admin details
$admin_details = [];
$sql = "SELECT A_Name, A_Email, A_CN, A_Picture FROM ADMIN WHERE A_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $admin_details = $result->fetch_assoc();
}
$stmt->close();

// Handle password change
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "All password fields are required!";
    } elseif (strlen($new_password) < 8) {
        $error_msg = "Password must be at least 8 characters!";
    } elseif (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
        $error_msg = "Password must contain at least one uppercase letter and one number!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords don't match!";
    } else {
        $stmt = $conn->prepare("SELECT A_Password FROM ADMIN WHERE A_ID = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $db_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE ADMIN SET A_Password = ? WHERE A_ID = ?");
            $update_stmt->bind_param("si", $hashed_password, $admin_id);
            if ($update_stmt->execute()) {
                $success_msg = "Password updated successfully!";
                $_POST = [];
            } else {
                $error_msg = "Error updating password.";
            }
            $update_stmt->close();
        } else {
            $error_msg = "Current password is incorrect!";
        }
    }
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/admin_pictures/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_name = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (in_array($imageFileType, ["jpg", "jpeg", "png"])) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE ADMIN SET A_Picture = ? WHERE A_ID = ?");
            $stmt->bind_param("si", $target_file, $admin_id);
            if ($stmt->execute()) {
                $admin_details['A_Picture'] = $target_file;
                $success_msg = "Profile picture updated!";
            } else {
                $error_msg = "Failed to save picture path to database.";
            }
            $stmt->close();
        } else {
            $error_msg = "Failed to upload image.";
        }
    } else {
        $error_msg = "Only JPG, JPEG, and PNG files are allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile | CTRL-X</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: Arial, sans-serif;
        }
        .main-content {
            margin: 40px auto;
            max-width: 900px;
        }
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile-header h2 {
            margin: 0 0 20px;
        }
        .profile-section {
            margin-bottom: 30px;
        }
        .profile-picture img {
            width: 150px;
            border-radius: 10px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-user-cog me-2"></i>Admin Profile</h2>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php elseif ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <div class="profile-section">
            <h4>Profile Information</h4>
            <div class="row">
                <div class="col-md-4"><strong>Name:</strong><br><?php echo htmlspecialchars($admin_details['A_Name']); ?></div>
                <div class="col-md-4"><strong>Email:</strong><br><?php echo htmlspecialchars($admin_details['A_Email']); ?></div>
                <div class="col-md-4"><strong>Contact:</strong><br><?php echo htmlspecialchars($admin_details['A_CN']); ?></div>
            </div>
        </div>

        <div class="profile-section">
            <h4>Profile Picture</h4>
            <div class="profile-picture mb-3">
                <?php if (!empty($admin_details['A_Picture'])): ?>
                    <img src="<?php echo htmlspecialchars($admin_details['A_Picture']); ?>" alt="Admin Picture">
                <?php else: ?>
                    <p>No picture uploaded.</p>
                <?php endif; ?>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" class="form-control mb-2" required accept=".jpg,.jpeg,.png">
                <button type="submit" name="upload_picture" class="btn btn-primary">Upload New Picture</button>
            </form>
        </div>

        <div class="profile-section">
            <h4>Change Password</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                    <small class="text-muted">Min 8 characters, include uppercase & number</small>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-success">Update Password</button>
            </form>
        </div>
    </div>
</div>

<!-- FontAwesome + Bootstrap JS -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
