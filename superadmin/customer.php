<?php
session_start();
include 'db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build base query
$query = "SELECT U_ID, U_FName, U_LName, U_Email, U_PNumber, U_Gender FROM USER";
$count_query = "SELECT COUNT(*) as total FROM USER";

// Add search filter if provided
if (!empty($search)) {
    $search_term = "%$search%";
    $query .= " WHERE U_FName LIKE ? OR U_LName LIKE ? OR U_Email LIKE ?";
    $count_query .= " WHERE U_FName LIKE ? OR U_LName LIKE ? OR U_Email LIKE ?";
}

// Add sorting (A-Z by last name)
$query .= " ORDER BY U_LName ASC LIMIT ? OFFSET ?";

// Get total count of customers
$stmt = $conn->prepare($count_query);
if (!empty($search)) {
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_customers = $total_row['total'];
$total_pages = ceil($total_customers / $per_page);
$stmt->close();

// Get paginated customers
$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $per_page, $offset);
} else {
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
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
        
       /* Search and Filter */
        .customer-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 400px; /* Changed from 250px to 400px */
            max-width: 600px; /* Added max-width */
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            font-size: 14px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%237f8c8d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>');
            background-repeat: no-repeat;
            background-position: 15px center;
        }
        
        /* Customer Table */
        .customer-header {
            display: grid;
            grid-template-columns: 1.5fr 1.5fr 1fr 1fr 1fr;
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
            grid-template-columns: 1.5fr 1.5fr 1fr 1fr 1fr;
            padding: 12px 15px;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            align-items: center;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .customer-item:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Order Details Section */
        .order-details {
            display: none;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #eee;
        }
        
        .order-details.active {
            display: block;
        }
        
        .order-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 80px 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .order-header {
            font-weight: bold;
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #2c3e50;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
        }
        
        .pagination .current {
            background: #3498db;
            color: white;
            border-color: #3498db;
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
            
            .order-item {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <section class="customer-view">
            <div class="customer-controls">
                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search customers..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
            </div>
            
            <div class="customer-header">
                <span>Name</span>
                <span>Email</span>
                <span>Phone</span>
                <span>Gender</span>
                <span>Details</span>
            </div>
            <ul class="customer-list">
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $customer): 
                        // Get customer's orders
                        $order_stmt = $conn->prepare("
                            SELECT O_ID, O_Date, O_TotalAmount 
                            FROM ORDERS 
                            WHERE U_ID = ? 
                            ORDER BY O_Date DESC
                        ");
                        $order_stmt->bind_param("i", $customer['U_ID']);
                        $order_stmt->execute();
                        $orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $order_stmt->close();
                    ?>
                        <li class="customer-item" onclick="toggleOrders(this)">
                            <span data-label="Name"><?= htmlspecialchars($customer['U_FName'] . ' ' . $customer['U_LName']) ?></span>
                            <span data-label="Email"><?= htmlspecialchars($customer['U_Email']) ?></span>
                            <span data-label="Phone"><?= htmlspecialchars($customer['U_PNumber']) ?></span>
                            <span data-label="Gender"><?= htmlspecialchars($customer['U_Gender']) ?></span>
                            <span data-label="Details"><?= count($orders) ?> order(s)</span>
                            
                            <div class="order-details">
                                <?php if (count($orders) > 0): ?>
                                    <div class="order-item order-header">
                                        <span>Order ID</span>
                                        <span>Date</span>
                                        <span>Amount</span>
                                        <span>Items</span>
                                    </div>
                                    <?php foreach ($orders as $order): 
                                        // Get order items
                                        $items_stmt = $conn->prepare("
                                            SELECT p.P_Name, oi.OI_Quantity, oi.OI_Price 
                                            FROM ORDER_ITEMS oi
                                            JOIN PRODUCT p ON oi.P_ID = p.P_ID
                                            WHERE oi.O_ID = ?
                                        ");
                                        $items_stmt->bind_param("i", $order['O_ID']);
                                        $items_stmt->execute();
                                        $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                        $items_stmt->close();
                                    ?>
                                        <div class="order-item">
                                            <span>#<?= $order['O_ID'] ?></span>
                                            <span><?= date('M d, Y', strtotime($order['O_Date'])) ?></span>
                                            <span>RM<?= number_format($order['O_TotalAmount'], 2) ?></span>
                                            <span>
                                                <?php foreach ($items as $item): ?>
                                                    <?= $item['OI_Quantity'] ?>x <?= htmlspecialchars($item['P_Name']) ?> (RM<?= number_format($item['OI_Price'], 2) ?>)<br>
                                                <?php endforeach; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">No orders found for this customer</div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="customer-item">
                        <span class="empty-state">No customers found<?= !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : '' ?></span>
                    </li>
                <?php endif; ?>
            </ul>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
function toggleOrders(element) {
    const details = element.querySelector('.order-details');
    details.classList.toggle('active');
}

// Auto-submit search form when typing stops
let searchTimer;
document.querySelector('.search-box input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>
</body>
</html>