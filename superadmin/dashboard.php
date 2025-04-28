<?php
include 'db.php';
session_start();

// Set timezone to Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get current date in Malaysia time
$currentDate = date('d M Y');
$currentMonth = date('F Y'); // e.g. "April 2025"

// Initialize variables with default values
$monthly_stats = [
    'monthly_orders' => 0,
    'monthly_customers' => 0,
    'monthly_sales' => 0,
    'today_sales' => 0,
    'today_orders' => 0
];

$todays_orders = [];
$num_orders = 0;
$monthly_history = [];

// Fetch monthly statistics with error handling
try {
    $stats_query = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM ORDERS WHERE DATE_FORMAT(O_Date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS monthly_orders,
            (SELECT COUNT(*) FROM USER WHERE U_ID IN (SELECT DISTINCT U_ID FROM ORDERS WHERE DATE_FORMAT(O_Date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m'))) AS monthly_customers,
            (SELECT SUM(O_TotalAmount) FROM ORDERS WHERE DATE_FORMAT(O_Date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS monthly_sales,
            (SELECT SUM(O_TotalAmount) FROM ORDERS WHERE DATE(O_Date) = CURDATE()) AS today_sales,
            (SELECT COUNT(*) FROM ORDERS WHERE DATE(O_Date) = CURDATE()) AS today_orders
    ");

    if ($stats_query) {
        $monthly_stats = $stats_query->fetch_assoc();
        // Ensure all keys exist
        $monthly_stats = array_merge([
            'monthly_orders' => 0,
            'monthly_customers' => 0,
            'monthly_sales' => 0,
            'today_sales' => 0,
            'today_orders' => 0
        ], $monthly_stats);
    }
} catch (Exception $e) {
    error_log("Error fetching monthly stats: " . $e->getMessage());
}

// Check if DELIVERY table exists
$delivery_table_exists = false;
try {
    $check_delivery = $conn->query("SELECT 1 FROM DELIVERY LIMIT 1");
    $delivery_table_exists = ($check_delivery !== false);
} catch (Exception $e) {
    $delivery_table_exists = false;
}

// Fetch today's orders with delivery status (if available)
try {
    if ($delivery_table_exists) {
        $todays_orders_result = $conn->query("
            SELECT o.O_ID, CONCAT(u.U_FName, ' ', u.U_LName) AS customer_name, 
                   o.O_TotalAmount, IFNULL(d.D_Status, 'Preparing') AS D_Status, o.O_Date
            FROM ORDERS o
            JOIN USER u ON o.U_ID = u.U_ID
            LEFT JOIN DELIVERY d ON o.O_ID = d.O_ID
            WHERE DATE(o.O_Date) = CURDATE()
            ORDER BY o.O_Date DESC
            LIMIT 10
        ");
    } else {
        $todays_orders_result = $conn->query("
            SELECT o.O_ID, CONCAT(u.U_FName, ' ', u.U_LName) AS customer_name, 
                   o.O_TotalAmount, 'Preparing' AS D_Status, o.O_Date
            FROM ORDERS o
            JOIN USER u ON o.U_ID = u.U_ID
            WHERE DATE(o.O_Date) = CURDATE()
            ORDER BY o.O_Date DESC
            LIMIT 10
        ");
    }

    if ($todays_orders_result !== false) {
        $todays_orders = $todays_orders_result->fetch_all(MYSQLI_ASSOC);
        $num_orders = count($todays_orders);
    }
} catch (Exception $e) {
    error_log("Error fetching today's orders: " . $e->getMessage());
}

// Fetch monthly history
try {
    $history_result = $conn->query("
        SELECT 
            DATE_FORMAT(O_Date, '%M %Y') AS month_year,
            COUNT(*) AS order_count,
            SUM(O_TotalAmount) AS monthly_sales
        FROM ORDERS
        GROUP BY month_year
        ORDER BY O_Date DESC
        LIMIT 12
    ");

    if ($history_result !== false) {
        $monthly_history = $history_result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error fetching monthly history: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CTRL-X Clothing</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1 class="section-title">Dashboard Overview</h1>
            
            <!-- Summary Cards -->
            <div class="cards">
                <div class="card sales">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Sales</h3>
                        <p>RM <?= number_format($monthly_stats['monthly_sales'], 2) ?></p>
                    </div>
                </div>
                
                <div class="card orders">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-content">
                        <h3>Monthly Orders</h3>
                        <p><?= $monthly_stats['monthly_orders'] ?></p>
                    </div>
                </div>
                
                <div class="card customers">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h3>New Customers</h3>
                        <p><?= $monthly_stats['monthly_customers'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Monthly Performance -->
            <div class="performance-section">
                <h1 class="section-title">Monthly Performance (<?= $currentMonth ?>)</h1>
                <div class="performance-grid">
                    <div class="performance-card revenue">
                        <h4>Monthly Revenue</h4>
                        <p>RM <?= number_format($monthly_stats['monthly_sales'], 2) ?></p>
                        <div class="trend">
                            <?php 
                            $revenue_trend = ($monthly_stats['monthly_sales'] > 0) ? 'up' : 'down';
                            ?>
                            <i class="fas fa-arrow-<?= $revenue_trend ?>"></i>
                            <span>Current month</span>
                        </div>
                    </div>
                    
                    <div class="performance-card orders">
                        <h4>Monthly Orders</h4>
                        <p><?= $monthly_stats['monthly_orders'] ?></p>
                        <div class="trend">
                            <?php 
                            $orders_trend = ($monthly_stats['monthly_orders'] > 0) ? 'up' : 'down';
                            ?>
                            <i class="fas fa-arrow-<?= $orders_trend ?>"></i>
                            <span>Current month</span>
                        </div>
                    </div>
                    
                    <div class="performance-card new-customers">
                        <h4>New Customers</h4>
                        <p><?= $monthly_stats['monthly_customers'] ?></p>
                        <div class="trend">
                            <?php 
                            $customers_trend = ($monthly_stats['monthly_customers'] > 0) ? 'up' : 'down';
                            ?>
                            <i class="fas fa-arrow-<?= $customers_trend ?>"></i>
                            <span>Current month</span>
                        </div>
                    </div>
                    
                    <div class="performance-card today">
                        <h4>Today's Summary (<?= $currentDate ?>)</h4>
                        <div class="today-stats">
                            <div class="today-stat">
                                <span>Sales:</span>
                                <strong>RM <?= number_format($monthly_stats['today_sales'], 2) ?></strong>
                            </div>
                            <div class="today-stat">
                                <span>Orders:</span>
                                <strong><?= $monthly_stats['today_orders'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly History -->
            <div class="table-section">
                <div class="section-header">
                    <h1 class="section-title">Monthly History</h1>
                </div>
                <div class="table-container">
                    <?php if (count($monthly_history) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Total Sales</th>
                                <th>Average Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_history as $month): ?>
                            <tr>
                                <td><?= htmlspecialchars($month['month_year']) ?></td>
                                <td><?= htmlspecialchars($month['order_count']) ?></td>
                                <td>RM <?= number_format($month['monthly_sales'], 2) ?></td>
                                <td>RM <?= number_format($month['monthly_sales'] / max(1, $month['order_count']), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No order history available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="table-section">
                <div class="section-header">
                    <h1 class="section-title">Today's Orders (<?= $currentDate ?>)</h1>
                    <a href="orderlist.php" class="view-all">View All Orders</a>
                </div>
                <div class="table-container">
                    <?php if ($num_orders > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Time</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todays_orders as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['O_ID']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= date('H:i', strtotime($order['O_Date'])) ?></td>
                                <td>RM <?= number_format($order['O_TotalAmount'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower(str_replace(' ', '-', $order['D_Status'])) ?>">
                                        <?= htmlspecialchars($order['D_Status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?= $order['O_ID'] ?>" class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No orders placed today</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>