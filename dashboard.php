<?php
include 'db.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch monthly statistics
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM ORDERS) AS total_orders,
        (SELECT COUNT(*) FROM USER) AS total_customers,
        (SELECT SUM(O_TotalAmount) FROM ORDERS) AS total_sales,
        (SELECT COUNT(*) FROM ORDERS WHERE O_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AS monthly_orders,
        (SELECT COUNT(*) FROM USER WHERE U_ID IN (SELECT DISTINCT U_ID FROM ORDERS WHERE O_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) AS monthly_customers,
        (SELECT SUM(O_TotalAmount) FROM ORDERS WHERE O_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AS monthly_sales,
        (SELECT SUM(O_TotalAmount) FROM ORDERS WHERE DATE(O_Date) = CURDATE()) AS today_sales,
        (SELECT COUNT(*) FROM ORDERS WHERE DATE(O_Date) = CURDATE()) AS today_orders
")->fetch_assoc();

// Fetch today's orders with delivery status
$todays_orders = $conn->query("
    SELECT o.O_ID, CONCAT(u.U_FName, ' ', u.U_LName) AS customer_name, 
           o.O_TotalAmount, d.D_Status, o.O_Date
    FROM ORDERS o
    JOIN USER u ON o.U_ID = u.U_ID
    LEFT JOIN DELIVERY d ON o.O_ID = d.O_ID
    WHERE DATE(o.O_Date) = CURDATE()
    ORDER BY o.O_Date DESC
    LIMIT 10
");
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
            <h1 class="section-title">Monthly Overview</h1>
            
            <!-- Summary Cards -->
            <div class="cards">
                <div class="card sales">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Sales</h3>
                        <p>RM <?= number_format($stats['total_sales'] ?? 0, 2) ?></p>
                    </div>
                </div>
                
                <div class="card orders">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Orders</h3>
                        <p><?= $stats['total_orders'] ?? 0 ?></p>
                    </div>
                </div>
                
                <div class="card customers">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h3>Customers</h3>
                        <p><?= $stats['total_customers'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <!-- Monthly Performance -->
            <div class="performance-section">
                <h1 class="section-title">This Month's Performance</h1>
                <div class="performance-grid">
                    <div class="performance-card revenue">
                        <h4>Monthly Revenue</h4>
                        <p>RM <?= number_format($stats['monthly_sales'] ?? 0, 2) ?></p>
                        <div class="trend">
                            <?php 
                            $revenue_trend = ($stats['monthly_sales'] > 0) ? 'up' : 'down';
                            $revenue_percent = ($stats['monthly_sales'] > 0) ? round(($stats['monthly_sales'] / max(1, $stats['total_sales'] - $stats['monthly_sales'])) * 100) : 0;
                            ?>
                            <i class="fas fa-arrow-<?= $revenue_trend ?>"></i>
                            <span><?= $revenue_percent ?>% of total</span>
                        </div>
                    </div>
                    
                    <div class="performance-card orders">
                        <h4>Monthly Orders</h4>
                        <p><?= $stats['monthly_orders'] ?? 0 ?></p>
                        <div class="trend">
                            <?php 
                            $orders_trend = ($stats['monthly_orders'] > 0) ? 'up' : 'down';
                            $orders_percent = ($stats['monthly_orders'] > 0) ? round(($stats['monthly_orders'] / max(1, $stats['total_orders'] - $stats['monthly_orders'])) * 100) : 0;
                            ?>
                            <i class="fas fa-arrow-<?= $orders_trend ?>"></i>
                            <span><?= $orders_percent ?>% of total</span>
                        </div>
                    </div>
                    
                    <div class="performance-card new-customers">
                        <h4>New Customers</h4>
                        <p><?= $stats['monthly_customers'] ?? 0 ?></p>
                        <div class="trend">
                            <?php 
                            $customers_trend = ($stats['monthly_customers'] > 0) ? 'up' : 'down';
                            $customers_percent = ($stats['monthly_customers'] > 0) ? round(($stats['monthly_customers'] / max(1, $stats['total_customers'] - $stats['monthly_customers'])) * 100) : 0;
                            ?>
                            <i class="fas fa-arrow-<?= $customers_trend ?>"></i>
                            <span><?= $customers_percent ?>% of total</span>
                        </div>
                    </div>
                    
                    <div class="performance-card today">
                        <h4>Today's Summary</h4>
                        <div class="today-stats">
                            <div class="today-stat">
                                <span>Sales:</span>
                                <strong>RM <?= number_format($stats['today_sales'] ?? 0, 2) ?></strong>
                            </div>
                            <div class="today-stat">
                                <span>Orders:</span>
                                <strong><?= $stats['today_orders'] ?? 0 ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="table-section">
                <div class="section-header">
                    <h1 class="section-title">Today's Orders (<?= date('d M Y') ?>)</h1>
                    <a href="orderlist.php" class="view-all">View All Orders</a>
                </div>
                <div class="table-container">
                    <?php if ($todays_orders->num_rows > 0): ?>
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
                            <?php while ($order = $todays_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['O_ID'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= date('H:i', strtotime($order['O_Date'])) ?></td>
                                <td>RM <?= number_format($order['O_TotalAmount'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower(str_replace(' ', '-', $order['D_Status'] ?? 'preparing')) ?>">
                                        <?= $order['D_Status'] ?? 'Preparing' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?= $order['O_ID'] ?>" class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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