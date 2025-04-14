<?php
// hash_admin_passwords.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php'; // Your DB connection

// Fetch all admin accounts
$sql = "SELECT A_ID, A_Password FROM ADMIN";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $updated = 0;

    while ($row = $result->fetch_assoc()) {
        $id = $row['A_ID'];
        $currentPassword = $row['A_Password'];

        // Skip if already hashed (starts with $2y$ and length ≥ 60)
        if (strlen($currentPassword) >= 60 && str_starts_with($currentPassword, '$2y$')) {
            continue;
        }

        // Hash the plain password
        $hashed = password_hash($currentPassword, PASSWORD_DEFAULT);

        // Update in DB
        $updateStmt = $conn->prepare("UPDATE ADMIN SET A_Password = ? WHERE A_ID = ?");
        $updateStmt->bind_param("si", $hashed, $id);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            $updated++;
        }
    }

    echo "✅ Password hashing complete. $updated account(s) updated.";
} else {
    echo "⚠️ No admin accounts found.";
}

$conn->close();
?>
