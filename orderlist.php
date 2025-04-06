<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="orderlist.css">
</head>
<body>
    <div class="container">
        
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <h1>Order Management</h1>

            <!-- Order List Section -->
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
                        <!-- Example Row (Replace with dynamic data from your database) -->
                        <tr>
                            <td>768648</td>
                            <td>YuLe</td>
                            <td>Oct 21, 2023</td>
                            <td>RM122.00</td>
                            <td>
                                <ul>
                                    <li><strong>Product:</strong> Men's Casual Shirt, <strong>Quantity:</strong> 2, <strong>Price:</strong> RM50.00</li>
                                    <li><strong>Product:</strong> Women's Summer Dress, <strong>Quantity:</strong> 1, <strong>Price:</strong> RM22.00</li>
                                </ul>
                            </td>
                            <td><span class="status processing">Processing</span></td>
                            <td>
                                <button class="view-btn">View</button>
                                <button class="update-status-btn">Update Status</button>
                            </td>
                        </tr>
                        <tr>
                            <td>768649</td>
                            <td>ShiHao</td>
                            <td>Oct 21, 2023</td>
                            <td>RM79.00</td>
                            <td>
                                <ul>
                                    <li><strong>Product:</strong> Unisex Hoodie, <strong>Quantity:</strong> 1, <strong>Price:</strong> RM79.00</li>
                                </ul>
                            </td>
                            <td><span class="status shipped">Shipped</span></td>
                            <td>
                                <button class="view-btn">View</button>
                                <button class="update-status-btn">Update Status</button>
                            </td>
                        </tr>
                        <tr>
                            <td>768650</td>
                            <td>WeiFu</td>
                            <td>Oct 18, 2023</td>
                            <td>RM79.00</td>
                            <td>
                                <ul>
                                    <li><strong>Product:</strong> Men's Casual Shirt, <strong>Quantity:</strong> 1, <strong>Price:</strong> RM79.00</li>
                                </ul>
                            </td>
                            <td><span class="status delivered">Delivered</span></td>
                            <td>
                                <button class="view-btn">View</button>
                                <button class="update-status-btn">Update Status</button>
                            </td>
                        </tr>
                        <!-- Add more rows dynamically -->
                    </tbody>
                </table>
            </section>

            <!-- Order Status Modal -->
            <div class="modal" id="statusModal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Update Order Status</h2>
                    <form class="status-form">
                        <div class="form-group">
                            <label for="orderStatus">Select Status:</label>
                            <select id="orderStatus" name="orderStatus" required>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Update Status</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript for Order Status Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('statusModal');
            const updateStatusButtons = document.querySelectorAll('.update-status-btn');
            const closeModal = document.querySelector('.close-modal');

            // Open Modal
            updateStatusButtons.forEach(button => {
                button.addEventListener('click', function () {
                    modal.style.display = 'block';
                });
            });

            // Close Modal
            closeModal.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            // Submit Status Form
            document.querySelector('.status-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const selectedStatus = document.getElementById('orderStatus').value;
                alert(`Order status updated to: ${selectedStatus}`);
                modal.style.display = 'none';
            });
        });
    </script>
</body>
</html>