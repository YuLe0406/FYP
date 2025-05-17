<?php
session_start();
include 'db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Define status order for display
$statusOrder = ['Processing', 'Shipped', 'Delivered'];

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base query
$query = "
    SELECT O.O_ID, O.O_Date, O.O_TotalAmount, O.O_Status,
           U.U_FName, U.U_LName
    FROM ORDERS O
    JOIN USER U ON O.U_ID = U.U_ID
";

$count_query = "SELECT COUNT(*) as total FROM ORDERS O";

// Add filters
$where_clauses = [];
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_clauses[] = "O.O_Status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($start_date) && !empty($end_date)) {
    $where_clauses[] = "O.O_Date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
} elseif (!empty($start_date)) {
    $where_clauses[] = "O.O_Date >= ?";
    $params[] = $start_date;
    $types .= 's';
} elseif (!empty($end_date)) {
    $where_clauses[] = "O.O_Date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if (!empty($search_term)) {
    $where_clauses[] = "O.O_ID LIKE ?";
    $params[] = "%$search_term%";
    $types .= 's';
}

// Add WHERE clause if needed
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
    $count_query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add sorting and pagination
$query .= " ORDER BY FIELD(O.O_Status, 'Processing', 'Shipped', 'Delivered'), O.O_Date DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $per_page;
$params[] = $offset;

// Get total count
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    // Remove the pagination parameters for count query
    $count_params = array_slice($params, 0, count($params) - 2);
    $count_types = substr($types, 0, -2);
    if (!empty($count_types)) {
        $stmt->bind_param($count_types, ...$count_params);
    }
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $per_page);
$stmt->close();

// Get paginated orders
$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orderResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            margin-left: 250px; /* Adjusted for sidebar */
        }
        
        .main-content {
            width: 100%;
            max-width: 1200px;
            padding: 30px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        .order-list {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #1abc9c;
            color: white;
        }

        tr:hover {
            background: #f4f7f6;
        }

        .status {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .Processing { background-color: #6c757d; color: white; }
        .Shipped { background-color: #17a2b8; color: white; }
        .Delivered { background-color: #28a745; color: white; }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close-modal {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .submit-btn {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #16a085;
        }

        .view-btn, .update-status-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .view-btn {
            background: #3498db;
            color: white;
        }

        .update-status-btn {
            background: #2ecc71;
            color: white;
        }

        .status-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .status-filter {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            background: #f1f1f1;
            border: 1px solid #ddd;
        }
        
        .status-filter.active {
            background: #1abc9c;
            color: white;
            border-color: #1abc9c;
        }
        
        .status-filter.Processing { background-color: #6c757d; color: white; }
        .status-filter.Shipped { background-color: #17a2b8; color: white; }
        .status-filter.Delivered { background-color: #28a745; color: white; }

        .delivery-info {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .delivery-info h3 {
            margin-top: 0;
            color: #1abc9c;
        }

        .order-status-form {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        /* Search box styles */
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .search-box input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-input {
            flex: 1;
            min-width: 300px;
        }
        
        .date-filters {
            display: flex;
            gap: 10px;
        }
        
        .date-filter {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .date-filter label {
            white-space: nowrap;
        }
        
        /* Pagination styles */
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
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <h1>Order Management</h1>

        <!-- Search and Filter Box -->
        <form method="GET" action="" class="search-box">
            <input type="text" name="search" placeholder="Search by Order ID..." value="<?= htmlspecialchars($search_term) ?>" class="search-input">
            
            <div class="date-filters">
                <div class="date-filter">
                    <label for="start_date">From:</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="date-filter">
                    <label for="end_date">To:</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <button type="submit" class="submit-btn">Apply Filters</button>
                <?php if (!empty($search_term) || !empty($start_date) || !empty($end_date) || $status_filter !== 'all'): ?>
                    <a href="?" class="submit-btn" style="background-color: #dc3545;">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Status Filters -->
        <div class="status-filters">
            <?php foreach ($statusOrder as $status): ?>
                <div class="status-filter <?= $status ?> <?= $status_filter === $status ? 'active' : '' ?>" 
                     onclick="filterByStatus('<?= $status ?>')">
                    <?= $status ?>
                </div>
            <?php endforeach; ?>
            <div class="status-filter <?= $status_filter === 'all' ? 'active' : '' ?>" 
                 onclick="filterByStatus('all')">All</div>
        </div>

        <section class="order-list">
            <h2>Order List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Products</th>
                        <th>Order Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($orderResult && $orderResult->num_rows > 0) {
                    while ($order = $orderResult->fetch_assoc()) {
                        $orderId = $order['O_ID'];
                        echo "<tr data-status='{$order['O_Status']}'>
                                <td>{$orderId}</td>
                                <td>{$order['U_FName']} {$order['U_LName']}</td>
                                <td>" . date('M d, Y', strtotime($order['O_Date'])) . "</td>
                                <td>RM" . number_format($order['O_TotalAmount'], 2) . "</td>
                                <td><ul>";

                        // Get products for each order
                        $itemsQuery = "
                            SELECT P.P_Name, OI.OI_Quantity, OI.OI_Price
                            FROM ORDER_ITEMS OI
                            JOIN PRODUCT P ON OI.P_ID = P.P_ID
                            WHERE OI.O_ID = $orderId
                        ";
                        $itemsResult = mysqli_query($conn, $itemsQuery);
                        if ($itemsResult) {
                            while ($item = mysqli_fetch_assoc($itemsResult)) {
                                echo "<li>{$item['P_Name']} (Qty: {$item['OI_Quantity']}, RM" . number_format($item['OI_Price'], 2) . ")</li>";
                            }
                        }

                        // Order status badge
                        $orderStatus = $order['O_Status'];
                        echo "</ul></td>
                            <td><span class='status {$orderStatus}'>{$orderStatus}</span></td>
                            <td>
                                <button class='view-btn' data-order-id='{$orderId}'>Details</button>
                                <button class='update-status-btn' data-order-id='{$orderId}'>Update</button>
                            </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>No orders found</td></tr>";
                }
                ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Order Details Modal -->
        <div class="modal" id="orderDetailsModal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Order Details</h2>
                <div id="orderDetailsContent"></div>
                
                <!-- Delivery Information Section -->
                <div class="delivery-info">
                    <h3>Delivery Information</h3>
                    <div id="deliveryInfoContent"></div>
                </div>
            </div>
        </div>

        <!-- Order Status Modal -->
        <div class="modal" id="statusModal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Update Order Status</h2>
                <form id="statusForm">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    
                    <div class="form-group">
                        <label for="orderStatus">Status:</label>
                        <select id="orderStatus" name="status" required>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="deliveryFields">
                        <label for="carrier">Carrier:</label>
                        <select id="carrier" name="carrier">
                            <?php
                            $carriers = mysqli_query($conn, "SELECT * FROM DELIVERY_CARRIER");
                            while ($carrier = mysqli_fetch_assoc($carriers)) {
                                echo "<option value='{$carrier['DC_ID']}'>{$carrier['DC_Name']}</option>";
                            }
                            ?>
                        </select>
                        
                        <label for="trackingNumber">Tracking Number:</label>
                        <input type="text" id="trackingNumber" name="tracking_number">
                        
                        <label for="estimatedDelivery">Estimated Delivery:</label>
                        <input type="date" id="estimatedDelivery" name="estimated_delivery">
                    </div>
                    
                    <button type="submit" class="submit-btn">Update Status</button>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Filter by status function
function filterByStatus(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status', status);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // View order details
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            // Fetch order details
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderDetailsContent').innerHTML = data;
                    
                    // Fetch delivery info
                    fetch('get_delivery_info.php?order_id=' + orderId)
                        .then(response => response.text())
                        .then(deliveryData => {
                            document.getElementById('deliveryInfoContent').innerHTML = deliveryData;
                        });
                    
                    document.getElementById('orderDetailsModal').style.display = 'flex';
                });
        });
    });

    // Update status button
    const updateButtons = document.querySelectorAll('.update-status-btn');
    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            document.getElementById('statusOrderId').value = orderId;
            
            // Fetch current status
            fetch('get_order_status.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('orderStatus').value = data.order_status;
                    
                    // Set delivery fields if they exist
                    if (data.carrier_id) {
                        document.getElementById('carrier').value = data.carrier_id;
                        document.getElementById('trackingNumber').value = data.tracking_number || '';
                        if (data.estimated_delivery) {
                            document.getElementById('estimatedDelivery').value = data.estimated_delivery.split(' ')[0];
                        }
                    }
                    
                    // Show/hide delivery fields based on order status
                    toggleDeliveryFields(data.order_status);
                    
                    document.getElementById('statusModal').style.display = 'flex';
                });
        });
    });

    // Toggle delivery fields based on order status
    function toggleDeliveryFields(orderStatus) {
        const deliveryFields = document.getElementById('deliveryFields');
        if (orderStatus === 'Shipped') {
            deliveryFields.style.display = 'block';
        } else {
            deliveryFields.style.display = 'none';
        }
    }

    // Order status form submission
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('update_order_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Order status updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'Failed to update order status'
                });
            }
        });
    });

    // Close modals
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
});
</script>
</body>
</html>