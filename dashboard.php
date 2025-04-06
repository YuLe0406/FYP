<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Clothing Store</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">

        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1 class="section-title">Dashboard Overview</h1>
        
            <!-- Cards Section -->
            <div class="cards">
                <div class="card">
                    <h3>Total Sales</h3>
                    <p>$5,000</p>
                </div>
                <div class="card">
                    <h3>Orders</h3>
                    <p>150</p>
                </div>
                <div class="card">
                    <h3>Customers</h3>
                    <p>1,200</p>
                </div>
            </div>
        
            <h1 class="section-title">Weekly Overview</h1>
            <div class="weekly-overview">
                <p>Here's a summary of the past week's sales and customer activity.</p>
                <div class="stats">
                    <div class="stat">
                        <h4>Revenue</h4>
                        <p>$10,000</p>
                    </div>
                    <div class="stat">
                        <h4>Orders</h4>
                        <p>230</p>
                    </div>
                    <div class="stat">
                        <h4>New Customers</h4>
                        <p>500</p>
                    </div>
                </div>
            </div>
        
            <h1 class="section-title">Recent Orders</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1023</td>
                            <td>YuLe</td>
                            <td>RM250</td>
                            <td>Shipped</td>
                        </tr>
                        <tr>
                            <td>#1024</td>
                            <td>ShiHao</td>
                            <td>RM180</td>
                            <td>Processing</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>