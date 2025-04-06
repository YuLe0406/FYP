<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="customer.css">
</head>
<body>
    <div class="container">
        
        <?php include 'sidebar.php'; ?>

         <!-- Main Content -->
         <main class="main-content">
            <h1>Customer Management</h1>

            <!-- Customer List Table -->
            <section class="customer-list">
                <h2>Customer List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Row (Replace with dynamic data from your database) -->
                        <tr>
                            <td>1</td>
                            <td>Yule</td>
                            <td>Tan</td>
                            <td>yuletan@yahoo.com</td>
                            <td>1234567890</td>
                            <td>
                                <strong>Address:</strong> 123,Jalan Merdeka,Taman Merdeka<br>
                                <strong>City:</strong> Ayer Keroh<br>
                                <strong>State:</strong> Melaka<br>
                                <strong>Zip Code:</strong> 85450
                            </td>
                            <td>
                                <button class="view-btn">View</button>
                                <button class="delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>ShiHao</td>
                            <td>Yaw</td>
                            <td>ysh@gmail.com</td>
                            <td>0987654321</td>
                            <td>
                                <strong>Address:</strong> 456,Jalan Fu,Taman Fu<br>
                                <strong>City:</strong> Kluang<br>
                                <strong>State:</strong> Johor<br>
                                <strong>Zip Code:</strong> 80000
                            </td>
                            <td>
                                <button class="view-btn">View</button>
                                <button class="delete-btn">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>