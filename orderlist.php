<?php
include 'db.php'; // Database connection
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="orderlist.css">
</head>
<body>
<div class="container">
    <main class="main-content">
        <h1>Order Management</h1>

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
                    echo "</ul></td>
                          <td><span class='status {$status}'>{$order['D_Status']}</span></td>
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
    });
</script>
</body>
</html>
