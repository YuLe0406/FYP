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

// Block User
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'block') {
    $userId = (int)$_POST['userId'];
    $stmt = $conn->prepare("UPDATE USER SET U_Status = 1 WHERE U_ID = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $_SESSION['block_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error blocking user: " . $stmt->error;
    }
    $stmt->close();
}

// Unblock User
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'unblock') {
    $userId = (int)$_POST['userId'];
    $stmt = $conn->prepare("UPDATE USER SET U_Status = 0 WHERE U_ID = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $_SESSION['unblock_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error unblocking user: " . $stmt->error;
    }
    $stmt->close();
}

// Show success messages
if (isset($_SESSION['block_success'])) {
    $success = "User blocked successfully!";
    unset($_SESSION['block_success']);
}
if (isset($_SESSION['unblock_success'])) {
    $success = "User unblocked successfully!";
    unset($_SESSION['unblock_success']);
}

// Fetch all users
$users = [];
$query = "SELECT * FROM USER ORDER BY U_ID DESC";
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
                            <?php if ($user['U_Status'] == 1): ?>
                                <img src="https://img.icons8.com/ios-filled/24/cancel.png"/> Blocked
                            <?php else: ?>
                                <img src="https://img.icons8.com/ios-filled/24/checkmark.png"/> Active
                            <?php endif; ?>
                        </span>
                        <span>
                            <?php if ($user['U_Status'] == 0): ?>
                                <form method="POST" onsubmit="return confirmBlock(event, this);">
                                    <input type="hidden" name="action" value="block">
                                    <input type="hidden" name="userId" value="<?= $user['U_ID']; ?>">
                                    <button type="submit" class="action-button deactivate">Block</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" onsubmit="return confirmUnblock(event, this);">
                                    <input type="hidden" name="action" value="unblock">
                                    <input type="hidden" name="userId" value="<?= $user['U_ID']; ?>">
                                    <button type="submit" class="action-button activate">Unblock</button>
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
function confirmBlock(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Block this user?',
        text: "They won't be able to log in anymore!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Block'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function confirmUnblock(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Unblock this user?',
        text: "They will regain access to their account.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2ecc71',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Unblock'
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
