<?php
include 'db.php'; // Database connection
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order & Delivery Management</title>
    <style>
        /* Combined styles from both files */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .container {
            display: flex;
            flex-direction: column;
            margin-left: 250px;
        }

        .main-content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .order-list, .delivery-table {
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

        .Preparing { background-color: #f0ad4e; color: white; }
        .Shipped { background-color: #17a2b8; color: white; }
        .Delivered { background-color: #28a745; color: white; }
        .Processing { background-color: #6c757d; color: white; }

        /* Tab styling */
        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }

        .tab.active {
            background: #1abc9c;
            color: white;
            border-color: #1abc9c;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

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
            width: 400px;
            max-width: 90%;
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
    </style>
</head>
<body>
<div class="container">
    <main class="main-content">
        <h1>Order & Delivery Management</h1>

        <div class="tab-container">
            <div class="tab active" data-tab="orders">Orders</div>
            <div class="tab" data-tab="deliveries">Deliveries</div>
        </div>

        <!-- Orders Tab Content -->
        <div class="tab-content active" id="orders-tab">
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $orderQuery = "
                        SELECT O.O_ID, O.O_Date, O.O_TotalAmount, U.U_FName, U.U_LName, D.D_Status
                        FROM ORDERS O
                        JOIN USER U ON O.U_ID = U.U_ID
                        LEFT JOIN DELIVERY D ON O.O_ID = D.O_ID
                        ORDER BY O.O_Date DESC
                    ";
                    $orderResult = mysqli_query($conn, $orderQuery);

                    while ($order = mysqli_fetch_assoc($orderResult)) {
                        $orderId = $order['O_ID'];
                        echo "<tr>
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
                        while ($item = mysqli_fetch_assoc($itemsResult)) {
                            echo "<li><strong>Product:</strong> {$item['P_Name']}, <strong>Quantity:</strong> {$item['OI_Quantity']}, <strong>Price:</strong> RM" . number_format($item['OI_Price'], 2) . "</li>";
                        }

                        // Status badge
                        $status = strtolower($order['D_Status'] ?? 'Preparing');
                        $displayStatus = $order['D_Status'] ?? 'Preparing';
                        echo "</ul></td>
                            <td><span class='status {$status}'>{$displayStatus}</span></td>
                              <td>
                                  <button class='view-btn'>View</button>
                                  <button class='update-status-btn' data-order-id='{$orderId}'>Update Status</button>
                              </td>
                            </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Deliveries Tab Content -->
        <div class="tab-content" id="deliveries-tab">
            <section class="delivery-table">
                <h2>Delivery List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Delivery ID</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Carrier</th>
                            <th>Tracking No.</th>
                            <th>Start Date</th>
                            <th>Estimated Delivery</th>
                            <th>Actual Delivery</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT D.D_ID, D.O_ID, U.U_FName, U.U_LName, D.D_Carrier, D.D_TrackingNumber, 
                                       D.D_StartDate, D.D_EstimatedDelivery, D.D_ActualDelivery, D.D_Status
                                FROM DELIVERY D
                                JOIN ORDERS O ON D.O_ID = O.O_ID
                                JOIN USER U ON O.U_ID = U.U_ID
                                ORDER BY D.D_StartDate DESC";

                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['D_ID']}</td>
                                        <td>{$row['O_ID']}</td>
                                        <td>{$row['U_FName']} {$row['U_LName']}</td>
                                        <td>{$row['D_Carrier']}</td>
                                        <td>{$row['D_TrackingNumber']}</td>
                                        <td>" . date('M d, Y', strtotime($row['D_StartDate'])) . "</td>
                                        <td>" . date('M d, Y', strtotime($row['D_EstimatedDelivery'])) . "</td>
                                        <td>" . ($row['D_ActualDelivery'] ? date('M d, Y', strtotime($row['D_ActualDelivery'])) : '-') . "</td>
                                        <td><span class='status {$row['D_Status']}'>{$row['D_Status']}</span></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No deliveries found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Order Status Modal -->
        <div class="modal" id="statusModal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Update Order Status</h2>
                <form class="status-form" method="POST" action="update_order_status.php">
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <div class="form-group">
                        <label for="orderStatus">Select Status:</label>
                        <select id="orderStatus" name="orderStatus" required>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Update Status</button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab functionality
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Modal functionality
        const modal = document.getElementById('statusModal');
        const closeModal = document.querySelector('.close-modal');
        const modalOrderId = document.getElementById('modalOrderId');

        document.querySelectorAll('.update-status-btn').forEach(button => {
            button.addEventListener('click', function () {
                modal.style.display = 'flex';
                modalOrderId.value = this.dataset.orderId;
            });
        });

        closeModal.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>