<?php
include 'db.php';

// Handle AJAX request for customer search
if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $stmt = $conn->prepare("SELECT customer_username FROM Customer WHERE customer_username LIKE ?");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    echo json_encode($customers);
    exit(); // Stop further output
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="report.css">
</head>
<body>
    <div class="container">
        
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <h1>Generate Report</h1>

            <!-- Report Filters -->
            <section class="report-filters">
                <h2>Filters</h2>
                <form class="filter-form">
                    <div class="form-group">
                        <label for="reportType">Report Type:</label>
                        <select id="reportType" name="reportType" required>
                            <option value="sales">Sales Report</option>
                            <option value="orders">Orders Report</option>
                            <option value="products">Products Report</option>
                            <option value="customer">Customer Report</option>
                        </select>
                    </div>

                    <!-- Customer Search Field (Hidden by Default) -->
                    <div class="form-group" id="customerSelectGroup" style="display: none;">
                        <label for="customerSearch">Search Customer:</label>
                        <input type="text" id="customerSearch" name="customerSearch" placeholder="Type to search...">
                        <div id="customerResults"></div>
                    </div>

                    <div class="form-group">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="1">Men's Clothing</option>
                            <option value="2">Women's Clothing</option>
                            <option value="3">Unisex</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Generate Report</button>
                </form>
            </section>

            <!-- Report Results -->
            <section class="report-results">
                <h2>Report Results</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Products</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>768648</td>
                            <td>YuLe</td>
                            <td>Oct 21, 2023</td>
                            <td>RM122.00</td>
                            <td>
                                <ul>
                                    <li><strong>Product:</strong> Men's Casual Shirt, <strong>Quantity:</strong> 2, <strong>Price:</strong> RM122.00</li>
                                </ul>
                            </td>
                            <td><span class="status processing">Processing</span></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const reportType = document.getElementById("reportType");
        const customerSelectGroup = document.getElementById("customerSelectGroup");
        const customerSearch = document.getElementById("customerSearch");
        const customerResults = document.getElementById("customerResults");

        // Show/Hide Customer Search Field Based on Selection
        reportType.addEventListener("change", function () {
            if (this.value === "customer") {
                customerSelectGroup.style.display = "block";
            } else {
                customerSelectGroup.style.display = "none";
            }
        });

        // AJAX Search for Customers
        customerSearch.addEventListener("input", function () {
            let query = this.value;
            if (query.length > 1) { // Search when 2+ characters are typed
                fetch("report.php?q=" + query)
                    .then(response => response.json())
                    .then(data => {
                        customerResults.innerHTML = ""; // Clear previous results
                        data.forEach(customer => {
                            let div = document.createElement("div");
                            div.textContent = customer.customer_username;
                            div.classList.add("customer-item");
                            div.addEventListener("click", function () {
                                customerSearch.value = customer.customer_username;
                                customerResults.innerHTML = "";
                            });
                            customerResults.appendChild(div);
                        });
                    });
            } else {
                customerResults.innerHTML = "";
            }
        });
    });
    </script>

</body>
</html>
