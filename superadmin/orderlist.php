<?php
include 'db.php';
include 'sidebar.php';

// Define status order for display
$statusOrder = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
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
        .Cancelled { background-color: #dc3545; color: white; }
        .Preparing { background-color: #f0ad4e; color: white; }
        .In-Transit { background-color: #6c757d; color: white; }
        .Out-for-Delivery { background-color: #17a2b8; color: white; }
        .Failed-Delivery { background-color: #dc3545; color: white; }

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
        .status-filter.Cancelled { background-color: #dc3545; color: white; }

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
    </style>
</head>
<body>
<div class="container">
    <main class="main-content">
        <h1>Order Management</h1>

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
                    SELECT O.O_ID, O.O_Date, O.O_TotalAmount, OS.O_Status, 
                           U.U_FName, U.U_LName
                    FROM ORDERS O
                    JOIN ORDER_STATUS OS ON O.OS_ID = OS.OS_ID
                    JOIN USER U ON O.U_ID = U.U_ID
                    WHERE OS.O_Status != 'Pending'
                    ORDER BY FIELD(OS.O_Status, 'Processing', 'Shipped', 'Delivered', 'Cancelled'), 
                             O.O_Date DESC
                ";
                $orderResult = mysqli_query($conn, $orderQuery);

                if ($orderResult) {
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
                              </td>
                            </tr>";
                    }
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
                
                <!-- Order Status Update Section -->
                <div class="order-status-form">
                    <h3>Update Order Status</h3>
                    <form id="orderStatusForm">
                        <input type="hidden" name="order_id" id="orderStatusOrderId">
                        <div class="form-group">
                            <label for="orderStatus">Status:</label>
                            <select id="orderStatus" name="status" class="form-control" required>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Update Order Status</button>
                    </form>
                </div>
                
                <!-- Delivery Information Section -->
                <div class="delivery-info" id="deliveryInfoSection">
                    <h3>Delivery Information</h3>
                    <div id="deliveryInfoContent"></div>
                    <button id="editDeliveryBtn" class="update-status-btn">Edit Delivery</button>
                </div>
            </div>
        </div>

        <!-- Delivery Edit Modal -->
        <div class="modal" id="deliveryEditModal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Edit Delivery Information</h2>
                <form id="deliveryEditForm">
                    <input type="hidden" name="order_id" id="editOrderId">
                    <input type="hidden" name="delivery_id" id="editDeliveryId">
                    
                    <div class="form-group">
                        <label for="editCarrier">Carrier:</label>
                        <input type="text" id="editCarrier" name="carrier" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editTrackingNumber">Tracking Number:</label>
                        <input type="text" id="editTrackingNumber" name="tracking_number">
                    </div>
                    
                    <div class="form-group">
                        <label for="editStatus">Delivery Status:</label>
                        <select id="editStatus" name="status" required>
                            <option value="Preparing">Preparing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Out for Delivery">Out for Delivery</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Failed Delivery">Failed Delivery</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEstimatedDelivery">Estimated Delivery:</label>
                        <input type="date" id="editEstimatedDelivery" name="estimated_delivery" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editActualDelivery">Actual Delivery (if completed):</label>
                        <input type="datetime-local" id="editActualDelivery" name="actual_delivery">
                    </div>
                    
                    <button type="submit" class="submit-btn">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Order details modal
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            // Fetch order details via AJAX
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderDetailsContent').innerHTML = data;
                    document.getElementById('orderStatusOrderId').value = orderId;
                    
                    // Set current order status in the select
                    const statusMatch = data.match(/<span class='status ([^']+)'>([^<]+)<\/span>/);
                    if (statusMatch && statusMatch[2]) {
                        document.getElementById('orderStatus').value = statusMatch[2];
                    }
                    
                    // Fetch delivery info for this order
                    fetch('get_delivery_info.php?order_id=' + orderId)
                        .then(response => response.text())
                        .then(deliveryData => {
                            document.getElementById('deliveryInfoContent').innerHTML = deliveryData;
                            document.getElementById('editDeliveryBtn').dataset.orderId = orderId;
                            
                            // Check if delivery exists to show/hide edit button
                            const deliveryExists = deliveryData.includes('Delivery ID');
                            document.getElementById('editDeliveryBtn').style.display = deliveryExists ? 'inline-block' : 'none';
                        });
                    
                    orderDetailsModal.style.display = 'flex';
                });
        });
    });

    // Delivery edit button click
    document.getElementById('editDeliveryBtn').addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        const deliveryModal = document.getElementById('deliveryEditModal');
        
        // Fetch delivery info to populate form
        fetch('get_delivery_info_json.php?order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('editOrderId').value = orderId;
                    document.getElementById('editDeliveryId').value = data.D_ID || '';
                    document.getElementById('editCarrier').value = data.D_Carrier || '';
                    document.getElementById('editTrackingNumber').value = data.D_TrackingNumber || '';
                    document.getElementById('editStatus').value = data.D_Status.replace(' ', '-') || 'Preparing';
                    document.getElementById('editEstimatedDelivery').value = data.D_EstimatedDelivery || '';
                    
                    if (data.D_ActualDelivery) {
                        const actualDelivery = new Date(data.D_ActualDelivery);
                        const formattedDate = actualDelivery.toISOString().slice(0, 16);
                        document.getElementById('editActualDelivery').value = formattedDate;
                    } else {
                        document.getElementById('editActualDelivery').value = '';
                    }
                    
                    deliveryModal.style.display = 'flex';
                    orderDetailsModal.style.display = 'none';
                }
            });
    });

    // Order status form submission
    document.getElementById('orderStatusForm').addEventListener('submit', function(e) {
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
                    // Update the status in the table
                    const orderId = formData.get('order_id');
                    const newStatus = formData.get('status');
                    
                    // Find the row and update the status
                    const rows = document.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        if (row.querySelector('td:first-child').textContent === orderId) {
                            row.setAttribute('data-status', newStatus);
                            const statusCell = row.querySelector('td:nth-child(6) span');
                            statusCell.textContent = newStatus;
                            statusCell.className = 'status ' + newStatus;
                        }
                    });
                    
                    // Close the modal
                    document.getElementById('orderDetailsModal').style.display = 'none';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'Failed to update order status'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating order status'
            });
        });
    });

    // Delivery edit form submission
    document.getElementById('deliveryEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update_delivery.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Delivery information updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Close the modal and refresh the delivery info
                    document.getElementById('deliveryEditModal').style.display = 'none';
                    document.getElementById('orderDetailsModal').style.display = 'flex';
                    
                    // Refresh delivery info
                    const orderId = formData.get('order_id');
                    fetch('get_delivery_info.php?order_id=' + orderId)
                        .then(response => response.text())
                        .then(deliveryData => {
                            document.getElementById('deliveryInfoContent').innerHTML = deliveryData;
                        });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'Failed to update delivery information'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating delivery information'
            });
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