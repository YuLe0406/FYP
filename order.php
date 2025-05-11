<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
    <style>
        /* Main content padding to account for fixed header */
        main {
            padding-top: 70px;
            min-height: calc(100vh - 160px);
            background-color: #f5f5f5;
        }
        
        .order-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .order-card {
            border-radius: 12px;
            overflow: hidden;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        
        .order-header {
            background-color: #f9fafb;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .order-items {
            padding: 30px;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #f3f3f3;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .item-image {
            width: 140px;
            height: 140px;
            object-fit: contain;
            margin-right: 30px;
            border-radius: 8px;
            border: 1px solid #eee;
            background-color: #fafafa;
            padding: 8px;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-price {
            min-width: 150px;
            text-align: right;
        }
        
        .item-total {
            font-weight: bold;
            margin-top: 8px;
            font-size: 16px;
            color: #333;
        }
        
        .order-status {
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            text-transform: capitalize;
        }
        
        .status-processing {
            background-color: #e6f0ff;
            color: #2c68d6;
        }
        
        .status-shipped {
            background-color: #e0f7ed;
            color: #1a8c5e;
        }
        
        .status-delivered {
            background-color: #e6f7e6;
            color: #0d8a0d;
        }
        
        .order-total {
            text-align: right;
            padding: 20px 30px;
            background-color: #f9fafb;
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #e8e8e8;
            color: #333;
        }
        
        .no-orders {
            text-align: center;
            padding: 70px 20px;
            color: #6c757d;
            background-color: #f9f9f9;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .no-orders i {
            font-size: 50px;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .no-orders p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .item-attributes {
            color: #666;
            font-size: 15px;
            margin-bottom: 10px;
        }
        
        .item-quantity {
            color: #444;
            font-size: 15px;
        }
        
        .order-id {
            font-weight: bold;
            font-size: 20px;
            color: #333;
        }
        
        .order-date {
            color: #666;
            font-size: 15px;
            margin-top: 5px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
            font-weight: 600;
        }
        
        /* Tab styles */
        .order-tabs {
            display: flex;
            border-bottom: 1px solid #e8e8e8;
            margin-bottom: 40px;
            padding: 0 15px;
        }
        
        .order-tab {
            padding: 14px 30px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            position: relative;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .order-tab.active {
            color: #2c7be5;
            border-bottom: 3px solid #2c7be5;
        }
        
        .order-tab:hover:not(.active) {
            color: #444;
            border-bottom: 3px solid #ddd;
        }
        
        .tab-content {
            min-height: 200px;
        }
        
        .tab-count {
            background-color: #e0e0e0;
            color: #555;
            border-radius: 12px;
            padding: 4px 10px;
            font-size: 13px;
            margin-left: 8px;
            font-weight: normal;
        }
        
        .order-tab.active .tab-count {
            background-color: #2c7be5;
            color: white;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background-color: #2c7be5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: #1a68d1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
            color: #2c7be5;
        }
        
        .loading-spinner i {
            font-size: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <main>
        <div class="order-container">
            <h1><i class="fas fa-clipboard-list" style="margin-right: 12px;"></i>My Orders</h1>
            
            <div class="order-tabs">
                <div class="order-tab active" data-status="Processing" onclick="loadOrders('Processing')">
                    <i class="fas fa-spinner" style="margin-right: 10px;"></i>Processing
                    <span class="tab-count" id="count-processing">0</span>
                </div>
                <div class="order-tab" data-status="Shipped" onclick="loadOrders('Shipped')">
                    <i class="fas fa-truck" style="margin-right: 10px;"></i>Shipped
                    <span class="tab-count" id="count-shipped">0</span>
                </div>
                <div class="order-tab" data-status="Delivered" onclick="loadOrders('Delivered')">
                    <i class="fas fa-check-circle" style="margin-right: 10px;"></i>Delivered
                    <span class="tab-count" id="count-delivered">0</span>
                </div>
            </div>

            <div class="loading-spinner" id="loading-spinner">
                <i class="fas fa-spinner"></i>
            </div>
            
            <div class="tab-content" id="orders-content">
                <!-- Orders will be loaded here via AJAX -->
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial orders (Processing)
            loadOrders('Processing');
            
            // Load counts for all tabs
            loadOrderCounts();
        });
        
        function loadOrders(status) {
            const contentDiv = document.getElementById('orders-content');
            const spinner = document.getElementById('loading-spinner');
            const tabs = document.querySelectorAll('.order-tab');
            
            // Show loading spinner
            contentDiv.style.display = 'none';
            spinner.style.display = 'block';
            
            // Update active tab
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('data-status') === status) {
                    tab.classList.add('active');
                }
            });
            
            // Load orders via AJAX
            fetch(`orders_controller.php?status=${status}`)
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                    spinner.style.display = 'none';
                    contentDiv.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = '<div class="no-orders"><p>Error loading orders. Please try again.</p></div>';
                    spinner.style.display = 'none';
                    contentDiv.style.display = 'block';
                });
        }
        
        function loadOrderCounts() {
            const statuses = ['Processing', 'Shipped', 'Delivered'];
            
            statuses.forEach(status => {
                fetch(`orders_controller.php?status=${status}&count_only=true`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById(`count-${status.toLowerCase()}`).textContent = data.count;
                    })
                    .catch(error => {
                        console.error('Error loading count for', status, error);
                    });
            });
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>