<?php
session_start();
include 'db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = '';

// Deactivate User
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'Deactivate') {
    $userId = (int)$_POST['userId'];
    $stmt = $conn->prepare("UPDATE USER SET U_Status = 1 WHERE U_ID = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $_SESSION['Deactivate_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error Deactivating user: " . $stmt->error;
    }
    $stmt->close();
}

// Activate User
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'Activate') {
    $userId = (int)$_POST['userId'];
    $stmt = $conn->prepare("UPDATE USER SET U_Status = 0 WHERE U_ID = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $_SESSION['Activate_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error Activating user: " . $stmt->error;
    }
    $stmt->close();
}

// Show success messages
if (isset($_SESSION['Deactivate_success'])) {
    $success = "User Deactivated successfully!";
    unset($_SESSION['Deactivate_success']);
}
if (isset($_SESSION['Activate_success'])) {
    $success = "User Activated successfully!";
    unset($_SESSION['Activate_success']);
}

// Fetch all users with specific columns including U_Status
$users = [];
$query = "SELECT U_ID, U_FName, U_LName, U_Email, U_PNumber, U_Gender, U_Status FROM USER ORDER BY U_ID DESC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management</title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.all.min.js"></script>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <section class="customer-table">
            <h2><img src="https://img.icons8.com/ios-filled/24/user-group-man-man.png"/> CUSTOMER MANAGEMENT</h2>
            <div class="customer-header">
                <span>Name</span>
                <span>Email</span>
                <span>Phone</span>
                <span>Gender</span>
                <span>Status</span>
                <span>Action</span>
            </div>
            <ul class="customer-list">
                <?php foreach ($users as $user): ?>
                    <li class="customer-item">
                        <span><?= htmlspecialchars($user['U_FName'] . ' ' . $user['U_LName']); ?></span>
                        <span><?= htmlspecialchars($user['U_Email']); ?></span>
                        <span><?= htmlspecialchars($user['U_PNumber']); ?></span>
                        <span><?= htmlspecialchars($user['U_Gender']); ?></span>
                        <span>
                            <?php if ((int)$user['U_Status'] === 1): ?>
                                <img src="https://img.icons8.com/ios-filled/24/cancel.png"/> Deactivated
                            <?php else: ?>
                                <img src="https://img.icons8.com/ios-filled/24/checkmark.png"/> Active
                            <?php endif; ?>
                        </span>
                        <span>
                            <?php if ((int)$user['U_Status'] === 0): ?>
                                <form method="POST" onsubmit="return confirmDeactivate(event, this);">
                                    <input type="hidden" name="action" value="Deactivate">
                                    <input type="hidden" name="userId" value="<?= $user['U_ID']; ?>">
                                    <button type="submit" class="action-button Deactivate">Deactivate</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" onsubmit="return confirmActivate(event, this);">
                                    <input type="hidden" name="action" value="Activate">
                                    <input type="hidden" name="userId" value="<?= $user['U_ID']; ?>">
                                    <button type="submit" class="action-button Activate">Activate</button>
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
        title: 'Deactivate this user?',
        text: "They won't be able to log in anymore!",
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
        title: 'Activate this user?',
        text: "They will regain access to their account.",
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