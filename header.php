<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION['user_id']);
$userFirstName = $loggedIn ? explode(' ', $_SESSION['user_name'])[0] : '';
?>

<header>
    <div class="logo">
        <a href="index.php">CTRL+X</a>
    </div>        
    <div class="search-container">
        <input type="text" placeholder="Search for products, trends, and brands">
        <button type="submit"><i class="fas fa-search"></i></button>
    </div>
    <div class="icons">
        <?php if ($loggedIn): ?>
            <div class="dropdown">
                <a href="#" class="icon dropdown-toggle">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($userFirstName); ?>
                </a>
                <div class="dropdown-menu">
                    <a href="account_settings.php">Account Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.html" class="icon"><i class="fas fa-user"></i> Login / Register</a>
        <?php endif; ?>
        <a href="wishlist.php" class="icon"><i class="fas fa-heart"></i></a>
        <a href="cart.php" class="icon"><i class="fas fa-shopping-cart"></i></a>
    </div>
</header>

<!-- Discount Label -->
<div class="discount-label">
    <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
</div>

<!-- Optional dropdown style -->
<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        color: black;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
        z-index: 999;
        border-radius: 4px;
    }
    .dropdown:hover .dropdown-menu {
        display: block;
    }
    .dropdown-menu a {
        padding: 10px;
        display: block;
        text-decoration: none;
        color: black;
    }
    .dropdown-menu a:hover {
        background-color: #eee;
    }
</style>
