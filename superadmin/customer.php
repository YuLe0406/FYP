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
        header("Location: " . $_SERVER['PHP_SELF'] . "?view=inactive");
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
        header("Location: " . $_SERVER['PHP_SELF'] . "?view=active");
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

// Determine which users to show
$view = isset($_GET['view']) ? $_GET['view'] : 'active';
$status = ($view === 'inactive') ? 1 : 0;

// Fetch users based on view
$users = [];
$query = "SELECT U_ID, U_FName, U_LName, U_Email, U_PNumber, U_Gender, U_Status FROM USER WHERE U_Status = ? ORDER BY U_ID DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management</title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.all.min.js"></script>
    <style>
        /* Main Container */
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        /* Customer View Section */
        .customer-view {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        /* View Selector */
        .view-selector {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .view-selector h3 {
            font-size: 22px;
            margin: 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-dropdown {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dcdde1;
            background: #f9f9f9;
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Customer Table */
        .customer-header {
            display: grid;
            grid-template-columns: 1.5fr 1.5fr 1fr 1fr 1fr 100px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-weight: bold;
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .customer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .customer-item {
            display: grid;
            grid-template-columns: 1.5fr 1.5fr 1fr 1fr 1fr 100px;
            padding: 12px 15px;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            align-items: center;
            background: #ffffff;
        }
        
        .customer-item:hover {
            background: #f8f9fa;
        }
        
        /* Status Styling */
        .status-active {
            color: #2ecc71;
            font-weight: 500;
        }
        
        .status-inactive {
            color: #e74c3c;
            font-weight: 500;
        }
        
        /* Action Buttons */
        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .Deactivate {
            background-color: #e74c3c;
            color: white;
        }
        
        .Deactivate:hover {
            background-color: #c0392b;
        }
        
        .Activate {
            background-color: #2ecc71;
            color: white;
        }
        
        .Activate:hover {
            background-color: #27ae60;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
            grid-column: 1 / -1;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .customer-header {
                display: none;
            }
            
            .customer-item {
                grid-template-columns: 1fr;
                gap: 8px;
                padding: 15px;
            }
            
            .customer-item > span {
                display: flex;
                justify-content: space-between;
            }
            
            .customer-item > span::before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 10px;
                color: #2c3e50;
            }
            
            .action-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <section class="customer-view">
            <div class="view-selector">
                <h3>
                    <img src="<?= $view === 'active' ? 'https://img.icons8.com/ios-filled/24/checkmark.png' : 'https://img.icons8.com/ios-filled/24/cancel.png' ?>" alt="Status Icon"/>
                    <?= $view === 'active' ? 'Active Customers' : 'Inactive Customers' ?>
                </h3>
                <select class="view-dropdown" onchange="window.location.href='?view='+this.value">
                    <option value="active" <?= $view === 'active' ? 'selected' : '' ?>>Active Customers</option>
                    <option value="inactive" <?= $view === 'inactive' ? 'selected' : '' ?>>Inactive Customers</option>
                </select>
            </div>
            
            <div class="customer-header">
                <span>Name</span>
                <span>Email</span>
                <span>Phone</span>
                <span>Gender</span>
                <span>Status</span>
                <span>Action</span>
            </div>
            <ul class="customer-list">
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <li class="customer-item">
                            <span data-label="Name"><?= htmlspecialchars($user['U_FName'] . ' ' . $user['U_LName']); ?></span>
                            <span data-label="Email"><?= htmlspecialchars($user['U_Email']); ?></span>
                            <span data-label="Phone"><?= htmlspecialchars($user['U_PNumber']); ?></span>
                            <span data-label="Gender"><?= htmlspecialchars($user['U_Gender']); ?></span>
                            <span data-label="Status" class="<?= (int)$user['U_Status'] === 0 ? 'status-active' : 'status-inactive' ?>">
                                <?= (int)$user['U_Status'] === 0 ? 'Active' : 'Inactive' ?>
                            </span>
                            <span data-label="Action">
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
                <?php else: ?>
                    <li class="customer-item">
                        <span class="empty-state">No <?= $view === 'active' ? 'active' : 'inactive' ?> customers found</span>
                    </li>
                <?php endif; ?>
            </ul>
        </section>
    </main>
</div>

<script>
function confirmDeactivate(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Deactivate this customer?',
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
        title: 'Activate this customer?',
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