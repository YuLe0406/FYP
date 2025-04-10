<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            <img src="https://img.icons8.com/ios-filled/20/ffffff/dashboard.png" alt="Dashboard" class="sidebar-icon-img"/>
            Dashboard
        </a></li>
        <li><a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/admin-settings-male.png" alt="Admin" class="sidebar-icon-img"/>
            Admin
        </a></li>
        <li><a href="category.php" class="<?= basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/category.png" alt="Category" class="sidebar-icon-img"/>
            Category
        </a></li>
        <li><a href="product.php" class="<?= basename($_SERVER['PHP_SELF']) == 'product.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/product.png" alt="Product" class="sidebar-icon-img"/>
            Product
        </a></li>
        <li><a href="customer.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customer.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/conference.png" alt="Customers" class="sidebar-icon-img"/>
            Customer List
        </a></li>
        <li><a href="orderlist.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orderlist.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/order-delivered.png" alt="Orders" class="sidebar-icon-img"/>
            Order List
        </a></li>
        <li><a href="report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/report-file.png" alt="Reports" class="sidebar-icon-img"/>
            Generate Report
        </a></li>
        <li><a href="banner.php" class="<?= basename($_SERVER['PHP_SELF']) == 'banner.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/image.png" alt="Banners" class="sidebar-icon-img"/>
            Banner
        </a></li>
        <li><a href="voucher.php" class="<?= basename($_SERVER['PHP_SELF']) == 'voucher.php' ? 'active' : '' ?>">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/discount.png" alt="Vouchers" class="sidebar-icon-img"/>
            Voucher
        </a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-icon">
            <a href="admin_profile.php">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/user-male-circle.png" alt="Profile" class="sidebar-icon-img"/>
                <span>My Profile</span>
            </a>
        </div>
        <div class="sidebar-icon logout">
            <a href="admin_logout.php">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/exit.png" alt="Logout" class="sidebar-icon-img"/>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
}

.container {
    display: flex;
    flex-direction: column;
    background-color: #f5f7fa;
    margin-left: 250px; /* Adjusted for sidebar */
}

.main-content {
    width: 100%;
    max-width: 1000px;
    padding: 30px;
    margin: 0 auto;
    box-sizing: border-box;
}
.sidebar {
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    background-color: #2c3e50;
    padding: 20px;
    color: white;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
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
    flex-grow: 1;
}

.sidebar li a {
    display: flex;
    align-items: center;
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

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-icon {
    margin-bottom: 10px;
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

.sidebar-icon-img {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    filter: brightness(0) invert(1);
}

.sidebar-icon span {
    font-size: 14px;
}

.sidebar-icon.logout a:hover {
    background-color: rgba(231, 76, 60, 0.2);
}

.sidebar-icon.logout .sidebar-icon-img {
    filter: invert(48%) sepia(79%) saturate(2476%) hue-rotate(346deg) brightness(118%) contrast(119%);
}
</style>