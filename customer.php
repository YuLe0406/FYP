<?php
include 'db.php'; // Include your database connection file

// Fetch all users from the USER table with their status from USER_STATUS
$sql = "
    SELECT U.U_ID, U.U_FName, U.U_LName, U.U_Email, U.U_PNumber, US.US_Blocked
    FROM USER U
    LEFT JOIN USER_STATUS US ON U.U_ID = US.U_ID
";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);  // Fetch all rows as associative array

// Handle block/unblock user
if (isset($_POST['toggle_block'])) {
    $userID = $_POST['user_id'];
    $currentStatus = $_POST['current_status'];
    
    // Toggle the status (if 1 -> set to 0, if 0 -> set to 1)
    $newStatus = $currentStatus == 1 ? 0 : 1;

    // Update the USER_STATUS table with the new block/unblock status
    $updateStmt = $conn->prepare("
        INSERT INTO USER_STATUS (U_ID, US_Blocked) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE US_Blocked = ?
    ");
    $updateStmt->execute([$userID, $newStatus, $newStatus]);

    // Redirect to refresh the page
    header('Location: customer.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Customer Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="customer.css">
</head>
<body>
    <div class="container">
        
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <h1>Customer Management</h1>

            <!-- Customer List Table -->
            <section class="customer-list">
                <h2>Customer List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through users and populate the table -->
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['U_ID'] ?></td>
                                <td><?= htmlspecialchars($user['U_FName']) ?></td>
                                <td><?= htmlspecialchars($user['U_LName']) ?></td>
                                <td><?= htmlspecialchars($user['U_Email']) ?></td>
                                <td><?= htmlspecialchars($user['U_PNumber']) ?></td>
                                <td>
                                    <?php if ($user['US_Blocked'] == 1): ?>
                                        <span style="color: red;">Blocked</span>
                                    <?php else: ?>
                                        <span style="color: green;">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="customer.php" method="post" style="display: inline-block;">
                                        <button class="view-btn" type="button">View</button>
                                    </form>
                                    <form action="customer.php" method="post" style="display: inline-block;">
                                        <button class="toggle-status-btn" type="submit" name="toggle_block" value="toggle_block">
                                            <?= $user['US_Blocked'] == 1 ? 'Unblock' : 'Block' ?>
                                        </button>
                                        <input type="hidden" name="user_id" value="<?= $user['U_ID'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $user['US_Blocked'] ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
