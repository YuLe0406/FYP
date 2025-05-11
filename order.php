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

        }
        
        .order-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .order-card {
            background-color: white;
            margin-bottom: 30px;
        }
        
        .order-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .order-id {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .order-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
            margin-top: 8px;
        }
        
        .order-items {
            padding: 15px 20px;
        }
        
        .order-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 15px;
            float: left;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .item-attributes {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
            clear: both;
        }
        
        .item-price {
            color: #333;
            font-weight: bold;
            float: right;
            text-align: right;
        }
        
        .item-total {
            display: block;
            margin-top: 5px;
        }
        
        .order-total {
            padding: 15px 20px;
            text-align: right;
            font-weight: bold;
            border-top: 1px solid #f0f0f0;
            clear: both;
        }
        
        /* Status colors */
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
        
        /* Tab styles */
        .order-tabs {
            display: flex;
            border-bottom: 1px solid #e8e8e8;
            margin-bottom: 30px;
        }
        
        .order-tab {
            padding: 12px 25px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            position: relative;
            margin-right: 5px;
            font-size: 15px;
        }
        
        .order-tab.active {
            color: #2c7be5;
            border-bottom: 3px solid #2c7be5;
        }
        
        .tab-count {
            background-color: #e0e0e0;
            color: #555;
            border-radius: 12px;
            padding: 3px 9px;
            font-size: 12px;
            margin-left: 6px;
            font-weight: normal;
        }
        
        .order-tab.active .tab-count {
            background-color: #2c7be5;
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-orders i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
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
            <h1><i class="fas fa-clipboard-list" style="margin-right: 10px;"></i>My Orders</h1>
            
            <div class="order-tabs">
                <div class="order-tab active" data-status="Processing" onclick="loadOrders('Processing')">
                    <i class="fas fa-spinner" style="margin-right: 8px;"></i>Processing
                    <span class="tab-count" id="count-processing">0</span>
                </div>
                <div class="order-tab" data-status="Shipped" onclick="loadOrders('Shipped')">
                    <i class="fas fa-truck" style="margin-right: 8px;"></i>Shipped
                    <span class="tab-count" id="count-shipped">0</span>
                </div>
                <div class="order-tab" data-status="Delivered" onclick="loadOrders('Delivered')">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>Delivered
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