<?php
include 'db.php';
include 'sidebar.php';

// Filter handling
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';
$deliveryStatus = $_GET['delivery_status'] ?? '';

$where = [];
if ($fromDate) {
    $where[] = "O.O_Date >= '$fromDate'";
}
if ($toDate) {
    $where[] = "O.O_Date <= '$toDate'";
}
if ($paymentStatus) {
    $where[] = "P.payment_status = '$paymentStatus'";
}
if ($deliveryStatus) {
    $where[] = "DS.D_Status = '$deliveryStatus'";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT O.O_ID, U.U_FName, U.U_LName, O.O_Date, O.O_TotalAmount,
           P.payment_status, DS.D_Status
    FROM ORDERS O
    JOIN USER U ON O.U_ID = U.U_ID
    LEFT JOIN PAYMENT P ON O.O_ID = P.O_ID
    LEFT JOIN DELIVERY D ON O.O_ID = D.O_ID
    LEFT JOIN DELIVERY_STATUS DS ON D.DS_ID = DS.DS_ID
    $whereClause
    ORDER BY O.O_Date DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Report</title>
    <style>
        /* Main Content Area - Adjusted for Sidebar */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            background-color: #f5f7fa;
            min-height: 100vh;
        }
        
        /* Report Header */
        .report-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        
        .report-header h2 {
            color: #2c3e50;
            font-weight: 600;
            display: inline-block;
            margin-right: 20px;
        }
        
        /* Filter Section */
        .filter-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            margin-bottom: 0;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #f8fafc;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            grid-column: 1 / -1;
        }
        
        .filter-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .filter-btn {
            background-color: #3498db;
            color: white;
        }
        
        .filter-btn:hover {
            background-color: #2980b9;
        }
        
        .export-btn {
            background-color: #2ecc71;
            color: white;
        }
        
        .export-btn:hover {
            background-color: #27ae60;
        }
        
        /* Report Table */
        .report-table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        #reportTable {
            width: 100%;
            border-collapse: collapse;
        }
        
        #reportTable th {
            background-color: #3498db;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        
        #reportTable td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        #reportTable tr:last-child td {
            border-bottom: none;
        }
        
        #reportTable tr:hover {
            background-color: #f8fafc;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
        }
        
        .status-completed {
            background-color: #2ecc71;
        }
        
        .status-pending {
            background-color: #f39c12;
        }
        
        .status-preparing {
            background-color: #3498db;
        }
        
        .status-in-transit {
            background-color: #9b59b6;
        }
        
        .status-delivered {
            background-color: #27ae60;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            #reportTable {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="report-header">
            <h2>Order Report</h2>
        </div>
        
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="from_date">From Date</label>
                    <input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($fromDate) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="to_date">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($toDate) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="payment_status">Payment Status</label>
                    <select name="payment_status" id="payment_status">
                        <option value="">All</option>
                        <option value="Completed" <?= $paymentStatus == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Pending" <?= $paymentStatus == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="delivery_status">Delivery Status</label>
                    <select name="delivery_status" id="delivery_status">
                        <option value="">All</option>
                        <option value="Preparing" <?= $deliveryStatus == 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                        <option value="In Transit" <?= $deliveryStatus == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="Delivered" <?= $deliveryStatus == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <button type="button" class="export-btn" onclick="exportTable('csv')">Export CSV</button>
                    <button type="button" class="export-btn" onclick="exportTable('pdf')">Export PDF</button>
                </div>
            </form>
        </div>
        
        <div class="report-table-container">
            <table id="reportTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['O_ID']) ?></td>
                                <td><?= htmlspecialchars($row['U_FName'] . ' ' . $row['U_LName']) ?></td>
                                <td><?= htmlspecialchars($row['O_Date']) ?></td>
                                <td>RM <?= number_format($row['O_TotalAmount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['payment_status'] ?? 'N/A')) ?>">
                                        <?= htmlspecialchars($row['payment_status'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['D_Status'] ?? 'N/A')) ?>">
                                        <?= htmlspecialchars($row['D_Status'] ?? 'N/A') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Export Logic -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
    function exportTable(type) {
        const table = document.getElementById('reportTable');
        if (type === 'csv') {
            let csv = '';
            for (let row of table.rows) {
                let rowData = [];
                for (let cell of row.cells) {
                    rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
                }
                csv += rowData.join(',') + "\n";
            }
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "order-report.csv";
            link.click();
        } else if (type === 'pdf') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Order Report", 14, 16);
            doc.autoTable({ html: '#reportTable', startY: 20 });
            doc.save("order-report.pdf");
        }
    }
    </script>
</body>
</html>