<?php
session_start();
include 'db.php';

$error = '';
$success = '';

// Handle Add Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['adminName'];
    $email = $_POST['adminEmail'];
    $contact = $_POST['adminContact'];
    $role = $_POST['role'];
    $password = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $roleValue = ($role === 'superadmin') ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO ADMIN (A_Name, A_Password, A_Email, A_CN, A_Level) VALUES (?, ?, ?, ?, ?)");
        // REMOVED PASSWORD HASHING - storing plain text password
        if ($stmt) {
            $stmt->bind_param("ssssi", $name, $password, $email, $contact, $roleValue);
            if ($stmt->execute()) {
                $adminId = $stmt->insert_id;
                $statusStmt = $conn->prepare("INSERT INTO ADMIN_STATUS (A_ID, A_Status) VALUES (?, 0)");
                if ($statusStmt) {
                    $statusStmt->bind_param("i", $adminId);
                    if ($statusStmt->execute()) {
                        $success = "Admin added successfully!";
                    } else {
                        $error = "Admin added, but status insert failed: " . $statusStmt->error;
                    }
                    $statusStmt->close();
                }
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Deactivate
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'deactivate') {
    $adminId = $_POST['adminId'];
    $stmt = $conn->prepare("UPDATE ADMIN_STATUS SET A_Status = 1 WHERE A_ID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $adminId);
        if ($stmt->execute()) {
            $_SESSION['deactivate_success'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Activate
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'activate') {
    $adminId = $_POST['adminId'];
    $stmt = $conn->prepare("UPDATE ADMIN_STATUS SET A_Status = 0 WHERE A_ID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $adminId);
        if ($stmt->execute()) {
            $_SESSION['activate_success'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Set success messages
if (isset($_SESSION['deactivate_success'])) {
    $success = "Admin deactivated successfully!";
    unset($_SESSION['deactivate_success']);
}
if (isset($_SESSION['activate_success'])) {
    $success = "Admin activated successfully!";
    unset($_SESSION['activate_success']);
}

// Get recent admins
$admins = [];
$query = "
    SELECT a.*, s.A_Status 
    FROM ADMIN a 
    LEFT JOIN ADMIN_STATUS s ON a.A_ID = s.A_ID 
    ORDER BY a.A_ID DESC LIMIT 5";
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

    <script>
        function confirmDeactivate(form) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, deactivate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        function confirmActivate(form) {
            Swal.fire({
                title: 'Reactivate Admin?',
                text: "This will enable the admin account again.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, activate it!'
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
                text: '<?php echo $success; ?>',
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
                text: '<?php echo $error; ?>'
            });
        });
        <?php endif; ?>
    </script>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <section class="add-admin">
            <h2>Add Admin</h2>
            <form class="admin-form" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="adminName">Admin Name:</label>
                    <input type="text" id="adminName" name="adminName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Admin Email:</label>
                    <input type="email" id="adminEmail" name="adminEmail" required>
                </div>
                <div class="form-group">
                    <label for="adminContact">Contact Number:</label>
                    <input type="tel" id="adminContact" name="adminContact" pattern="[0-9]{10,12}" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminPassword">Password:</label>
                    <input type="password" id="adminPassword" name="adminPassword" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="submit-btn">Add Admin</button>
            </form>
        </section>

        <section class="recent-admins">
        <h2>Recent Admins</h2>
        <div class="admin-header">
            <span class="admin-col-name">Name</span>
            <span class="admin-col-email">Email</span>
            <span class="admin-col-contact">Contact</span>
            <span class="admin-col-role">Role</span>
            <span class="admin-col-action">Action</span>
        </div>
        <ul class="admin-list">
            <?php foreach ($admins as $admin): ?>
                <li class="admin-item">
                    <span class="admin-col-name"><?php echo htmlspecialchars($admin['A_Name']); ?></span>
                    <span class="admin-col-email"><?php echo htmlspecialchars($admin['A_Email']); ?></span>
                    <span class="admin-col-contact"><?php echo htmlspecialchars($admin['A_CN']); ?></span>
                    <span class="admin-col-role"><?php echo $admin['A_Level'] == 1 ? 'Superadmin' : 'Admin'; ?></span>
                    <span class="admin-col-action">
                        <?php if ($admin['A_Status'] == 0): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDeactivate(this);">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="adminId" value="<?php echo $admin['A_ID']; ?>">
                                <button type="submit" class="deactivate-btn">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirmActivate(this);">
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="adminId" value="<?php echo $admin['A_ID']; ?>">
                                <button type="submit" class="activate-btn">Activate</button>
                            </form>
                        <?php endif; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        </section>
    </main>
</div>
</body>
</html>
