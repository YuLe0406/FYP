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
            Dashboard
        </a></li>
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

    <div class="sidebar-footer">
        <div class="sidebar-icon">
            <a href="admin_profile.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                </svg>
                <span>My Profile</span>
            </a>
        </div>
        <div class="sidebar-icon logout">
            <a href="admin_logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<style>
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

.sidebar-icon svg {
    margin-right: 10px;
}

.sidebar-icon span {
    font-size: 14px;
}

.sidebar-icon.logout a:hover {
    background-color: rgba(231, 76, 60, 0.2);
}

.sidebar-icon.logout svg {
    color: #e74c3c;
}
</style>