<?php
session_start();
include 'db.php';

// Redirect if not superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = '';

// Add Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'add') {
    $name = trim($_POST['adminName']);
    $email = trim($_POST['adminEmail']);
    $contact = trim($_POST['adminContact']);
    $role = $_POST['role'];
    $password = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $roleValue = ($role === 'superadmin') ? 1 : 0;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkStmt = $conn->prepare("SELECT A_ID FROM ADMIN WHERE A_Email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO ADMIN (A_Name, A_Password, A_Email, A_CN, A_Level, A_Status) VALUES (?, ?, ?, ?, ?, 0)");
            if ($stmt) {
                $stmt->bind_param("ssssi", $name, $hashedPassword, $email, $contact, $roleValue);
                if ($stmt->execute()) {
                    $success = "Admin added successfully!";
                } else {
                    $error = "Error adding admin: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $checkStmt->close();
    }
}

// Deactivate Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'deactivate') {
    $adminId = (int)$_POST['adminId'];
    if ($adminId === $_SESSION['admin_id']) {
        $error = "You cannot deactivate your own account!";
    } else {
        $stmt = $conn->prepare("UPDATE ADMIN SET A_Status = 1 WHERE A_ID = ?");
        $stmt->bind_param("i", $adminId);
        if ($stmt->execute()) {
            $_SESSION['deactivate_success'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error deactivating admin: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Activate Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'activate') {
    $adminId = (int)$_POST['adminId'];
    $stmt = $conn->prepare("UPDATE ADMIN SET A_Status = 0 WHERE A_ID = ?");
    $stmt->bind_param("i", $adminId);
    if ($stmt->execute()) {
        $_SESSION['activate_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error activating admin: " . $stmt->error;
    }
    $stmt->close();
}

// Show success messages
if (isset($_SESSION['deactivate_success'])) {
    $success = "Admin deactivated successfully!";
    unset($_SESSION['deactivate_success']);
}
if (isset($_SESSION['activate_success'])) {
    $success = "Admin activated successfully!";
    unset($_SESSION['activate_success']);
}

// Get latest 5 admins
$admins = [];
$query = "SELECT * FROM ADMIN ORDER BY A_ID DESC LIMIT 5";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management</title>
    <link rel="stylesheet" href="addadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.all.min.js"></script>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <section class="add-admin">
            <h2><img src="https://img.icons8.com/ios-filled/24/admin-settings-male.png" alt="Add Admin Icon"/> ADMIN MANAGEMENT</h2>
            <form class="admin-form" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="adminName"><img src="https://img.icons8.com/ios-filled/24/name.png" alt="Name Icon"/> Admin Name:</label>
                    <input type="text" id="adminName" name="adminName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail"><img src="https://img.icons8.com/ios-filled/24/email.png" alt="Email Icon"/> Admin Email:</label>
                    <input type="email" id="adminEmail" name="adminEmail" required>
                </div>
                <div class="form-group">
                    <label for="adminContact"><img src="https://img.icons8.com/ios-filled/24/phone.png" alt="Phone Icon"/> Contact Number:</label>
                    <input type="tel" id="adminContact" name="adminContact" pattern="[0-9]{10,12}" required>
                </div>
                <div class="form-group">
                    <label for="role"><img src="https://img.icons8.com/ios-filled/24/user-shield.png" alt="Role Icon"/> Role:</label>
                    <select id="role" name="role">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminPassword"><img src="https://img.icons8.com/ios-filled/24/lock-2.png" alt="Password Icon"/> Password:</label>
                    <input type="password" id="adminPassword" name="adminPassword" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword"><img src="https://img.icons8.com/ios-filled/24/password.png" alt="Confirm Password Icon"/> Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" minlength="8" required>
                </div>
                <button type="submit" class="submit-btn">Add Admin</button>
            </form>
        </section>

        <section class="recent-admins">
            <h2><img src="https://img.icons8.com/ios-filled/24/conference-call.png" alt="Recent Admins Icon"/> Recent Admins</h2>
            <div class="admin-header">
                <span>Name</span>
                <span>Email</span>
                <span>Contact</span>
                <span>Role</span>
                <span>Status</span>
                <span>Action</span>
            </div>
            <ul class="admin-list">
                <?php foreach ($admins as $admin): ?>
                    <li class="admin-item">
                        <span><?= htmlspecialchars($admin['A_Name']); ?></span>
                        <span><?= htmlspecialchars($admin['A_Email']); ?></span>
                        <span><?= htmlspecialchars($admin['A_CN']); ?></span>
                        <span><?= $admin['A_Level'] == 1 ? 'Superadmin' : 'Admin'; ?></span>
                        <span>
                            <?php if ($admin['A_Status'] == 1): ?>
                                <img src="https://img.icons8.com/ios-filled/24/cancel.png" alt="Deactivated Icon"/> Deactivated
                            <?php else: ?>
                                <img src="https://img.icons8.com/ios-filled/24/checkmark.png" alt="Active Icon"/> Active
                            <?php endif; ?>
                        </span>
                        <span>
                            <?php if ($admin['A_Status'] == 0): ?>
                                <?php if ($admin['A_ID'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" onsubmit="return confirmDeactivate(event, this);">
                                        <input type="hidden" name="action" value="deactivate">
                                        <input type="hidden" name="adminId" value="<?= $admin['A_ID']; ?>">
                                        <button type="submit" class="action-button deactivate">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <span class="current-user">Current User</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <form method="POST" onsubmit="return confirmActivate(event, this);">
                                    <input type="hidden" name="action" value="activate">
                                    <input type="hidden" name="adminId" value="<?= $admin['A_ID']; ?>">
                                    <button type="submit" class="action-button activate">Activate</button>
                                </form>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</div>

<script>
function confirmDeactivate(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "This admin will no longer be able to login!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Deactivate'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function confirmActivate(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Reactivate Admin?',
        text: "This admin will regain access to the system.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2ecc71',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Activate'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

<?php if ($success): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?= $success; ?>',
        timer: 3000,
        showConfirmButton: false
    });
});
<?php endif; ?>

<?php if ($error): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= $error; ?>'
    });
});
<?php endif; ?>

</script>
</body>
</html>
