<?php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Management</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .delivery-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        }

        .Preparing { background-color: #f0ad4e; color: white; }
        .Shipped { background-color: #17a2b8; color: white; }
        .Delivered { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <h1>Delivery Management</h1>

            <div class="delivery-table">
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
                        $sql = "SELECT D.D_ID, D.O_ID, U.U_FName, U.U_LName, D.D_Carrier, D.D_TrackingNumber, D.D_StartDate, D.D_EstimatedDelivery, D.D_ActualDelivery, D.D_Status
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
                                        <td>{$row['D_StartDate']}</td>
                                        <td>{$row['D_EstimatedDelivery']}</td>
                                        <td>" . ($row['D_ActualDelivery'] ?? '-') . "</td>
                                        <td><span class='status {$row['D_Status']}'>{$row['D_Status']}</span></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No deliveries found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
