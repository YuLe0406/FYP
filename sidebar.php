<?php
// sidebar.php
session_start();
// Check if user is admin
$is_admin = isset($_SESSION['admin_id']); 
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<nav class="sidebar">
    <h2>CTRL-X Admin</h2>
    <div class="welcome-message">
        <p>Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
    </div>
    <ul>
        <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <b>Dashboard</b>
        </a></li>
        <!-- Other menu items remain the same -->
        <li><a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">
            Admin
        </a></li>
        <li><a href="category.php" class="<?= basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : '' ?>">
            Category
        </a></li>
        <li><a href="product.php" class="<?= basename($_SERVER['PHP_SELF']) == 'product.php' ? 'active' : '' ?>">
            Product
        </a></li>
        <li><a href="customer.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customer.php' ? 'active' : '' ?>">
            Customer List
        </a></li>
        <li><a href="orderlist.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orderlist.php' ? 'active' : '' ?>">
            Order List
        </a></li>
        <li><a href="report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : '' ?>">
            Generate Report
        </a></li>
        <li><a href="banner.php" class="<?= basename($_SERVER['PHP_SELF']) == 'banner.php' ? 'active' : '' ?>">
            Banner
        </a></li>
        <li><a href="voucher.php" class="<?= basename($_SERVER['PHP_SELF']) == 'voucher.php' ? 'active' : '' ?>">
            Voucher
        </a></li>
    </ul>

    <div class="sidebar-icon">
        <a href="admin_profile.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
            </svg>
            <span>My Profile</span>
        </a>
    </div>
</nav>

<style>
.sidebar {
    background-color: #2c3e50;
    color: white;
    width: 250px;
    height: 100vh;
    position: fixed;
    padding: 20px;
    box-sizing: border-box;
}

.sidebar h2 {
    color: white;
    text-align: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.welcome-message {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    background-color: rgba(255,255,255,0.1);
    border-radius: 4px;
    font-size: 14px;
}

.welcome-message p {
    margin: 0;
    color: #ecf0f1;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li a {
    display: block;
    color: #ecf0f1;
    padding: 12px 15px;
    margin-bottom: 5px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s;
}

.sidebar li a:hover {
    background-color: #34495e;
    color: white;
}

.sidebar li a.active {
    background-color: #3498db;
    color: white;
    font-weight: bold;
}

.sidebar-icon {
    position: absolute;
    bottom: 20px;
    left: 20px;
    width: calc(100% - 40px);
}

.sidebar-icon a {
    display: flex;
    align-items: center;
    color: #ecf0f1;
    text-decoration: none;
    padding: 10px;
    border-radius: 4px;
    transition: all 0.3s;
}

.sidebar-icon a:hover {
    background-color: #34495e;
}

.sidebar-icon svg {
    margin-right: 10px;
}

.sidebar-icon span {
    font-size: 14px;
}
</style>