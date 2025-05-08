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
            max-width: 1000px;
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
        }
        
        .search-box input {
            width: 100%;
            max-width: 400px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <h1>Order Management</h1>

        <!-- Search Box -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search orders...">
        </div>

        <!-- Status Filters -->
        <div class="status-filters">
            <?php foreach ($statusOrder as $status): ?>
                <div class="status-filter <?= $status ?>" data-status="<?= $status ?>">
                    <?= $status ?>
                </div>
            <?php endforeach; ?>
            <div class="status-filter active" data-status="all">All</div>
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
                $orderQuery = "
                    SELECT O.O_ID, O.O_Date, O.O_TotalAmount, O.O_Status,
                           U.U_FName, U.U_LName
                    FROM ORDERS O
                    JOIN USER U ON O.U_ID = U.U_ID
                    ORDER BY FIELD(O.O_Status, 'Processing', 'Shipped', 'Delivered'), 
                             O.O_Date DESC
                ";
                $orderResult = mysqli_query($conn, $orderQuery);

                if ($orderResult && mysqli_num_rows($orderResult) > 0) {
                    while ($order = mysqli_fetch_assoc($orderResult)) {
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
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status filter functionality
    const statusFilters = document.querySelectorAll('.status-filter');
    statusFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const status = this.dataset.status;
            
            // Update active filter
            statusFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter orders
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

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