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

// Handle profile update (name and contact only)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);

    if (empty($name)) {
        $error_msg = "Name is required!";
    } elseif (empty($contact)) {
        $error_msg = "Contact number is required!";
    } else {
        $update_stmt = $conn->prepare("UPDATE ADMIN SET A_Name = ?, A_CN = ? WHERE A_ID = ?");
        $update_stmt->bind_param("ssi", $name, $contact, $admin_id);
        if ($update_stmt->execute()) {
            $admin_details['A_Name'] = $name;
            $admin_details['A_CN'] = $contact;
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Error updating profile.";
        }
        $update_stmt->close();
    }
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            margin: 40px auto;
            max-width: 900px;
        }
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .profile-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .profile-picture {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            cursor: pointer;
        }
        .profile-picture img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .profile-picture:hover img {
            opacity: 0.7;
        }
        .profile-picture .edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }
        .profile-picture:hover .edit-overlay {
            opacity: 1;
        }
        .profile-section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .profile-section h4 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-label {
            font-weight: 500;
            color: #34495e;
        }
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
        }
        .no-image {
            display: inline-block;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #ecf0f1;
            text-align: center;
            line-height: 150px;
            color: #7f8c8d;
        }
        .readonly-email {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header">
            <!-- Profile Picture with Edit Overlay -->
            <div class="profile-picture" data-bs-toggle="modal" data-bs-target="#pictureModal">
                <?php if (!empty($admin_details['A_Picture'])): ?>
                    <img src="<?php echo htmlspecialchars($admin_details['A_Picture']); ?>" alt="Admin Picture">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                <?php endif; ?>
                <div class="edit-overlay">
                    <i class="fas fa-camera me-2"></i> Edit
                </div>
            </div>
            
            <h2 class="mb-1"><?php echo htmlspecialchars($admin_details['A_Name']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($admin_details['A_Email']); ?></p>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h4><i class="fas fa-user-edit me-2"></i>Profile Information</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($admin_details['A_Name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control readonly-email" id="email" 
                           value="<?php echo htmlspecialchars($admin_details['A_Email']); ?>" readonly>
                    <small class="text-muted">Email address cannot be changed</small>
                </div>
                <div class="mb-3">
                    <label for="contact" class="form-label">Contact Number</label>
                    <input type="text" class="form-control" id="contact" name="contact" 
                           value="<?php echo htmlspecialchars($admin_details['A_CN']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>
        </div>

        <div class="profile-section">
            <h4><i class="fas fa-key me-2"></i>Change Password</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small class="text-muted">Minimum 8 characters with at least one uppercase letter and one number</small>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-success">
                    <i class="fas fa-lock me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Picture Upload Modal -->
<div class="modal fade" id="pictureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="pictureForm">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Select Image</label>
                        <input class="form-control" type="file" id="profile_picture" name="profile_picture" 
                               accept=".jpg,.jpeg,.png" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="pictureForm" name="upload_picture" class="btn btn-primary">
                    Upload Picture
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show success/error messages with animation
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('show');
            }, 100);
        });
    });
</script>
</body>
</html>