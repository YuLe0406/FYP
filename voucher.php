<?php
include 'db.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_voucher'])) {
        // Add new voucher
        $code = $_POST['code'];
        $discount = $_POST['discount'];
        $expiry = $_POST['expiry'];
        $limit = $_POST['usage_limit'];
        
        $stmt = $conn->prepare("INSERT INTO VOUCHER (V_Code, V_Discount, V_ExpiryDate, V_UsageLimit) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $code, $discount, $expiry, $limit);
        $stmt->execute();
    } 
    elseif (isset($_POST['update_status'])) {
        // Update voucher status
        $voucher_id = $_POST['voucher_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE VOUCHER SET V_Status = ? WHERE V_ID = ?");
        $stmt->bind_param("ii", $status, $voucher_id);
        $stmt->execute();
    }
}

// Fetch all vouchers
$vouchers = $conn->query("SELECT * FROM VOUCHER ORDER BY V_ExpiryDate DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Management - CTRL-X Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .container {
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
            margin-left: 250px;
        }

        .main-content {
            width: 100%;
            max-width: 1500px;
            padding: 30px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .voucher-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .voucher-form, .voucher-list {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .active {
            color: green;
        }
        .inactive {
            color: red;
        }
        .expired {
            color: orange;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1 class="section-title">Voucher Management</h1>
            
            <div class="voucher-container">
                <!-- Add Voucher Form -->
                <div class="voucher-form">
                    <h2>Create New Voucher</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="code">Voucher Code:</label>
                            <input type="text" id="code" name="code" required>
                        </div>
                        <div class="form-group">
                            <label for="discount">Discount (%):</label>
                            <input type="number" id="discount" name="discount" min="1" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="expiry">Expiry Date:</label>
                            <input type="date" id="expiry" name="expiry" required>
                        </div>
                        <div class="form-group">
                            <label for="usage_limit">Usage Limit:</label>
                            <input type="number" id="usage_limit" name="usage_limit" min="1" value="1" required>
                        </div>
                        <button type="submit" name="add_voucher">Create Voucher</button>
                    </form>
                </div>
                
                <!-- Voucher List -->
                <div class="voucher-list">
                    <h2>Existing Vouchers</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Expiry</th>
                                <th>Used</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($voucher = $vouchers->fetch_assoc()): 
                                $status_class = '';
                                if ($voucher['V_Status'] == 0) {
                                    $status_class = 'inactive';
                                } elseif (strtotime($voucher['V_ExpiryDate']) < time()) {
                                    $status_class = 'expired';
                                } else {
                                    $status_class = 'active';
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($voucher['V_Code']) ?></td>
                                <td><?= $voucher['V_Discount'] ?>%</td>
                                <td><?= date('d M Y', strtotime($voucher['V_ExpiryDate'])) ?></td>
                                <td><?= $voucher['V_UsedCount'] ?>/<?= $voucher['V_UsageLimit'] ?></td>
                                <td class="<?= $status_class ?>">
                                    <?php 
                                    if ($voucher['V_Status'] == 0) {
                                        echo 'Inactive';
                                    } elseif (strtotime($voucher['V_ExpiryDate']) < time()) {
                                        echo 'Expired';
                                    } else {
                                        echo 'Active';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="voucher_id" value="<?= $voucher['V_ID'] ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="1" <?= $voucher['V_Status'] == 1 ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= $voucher['V_Status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>