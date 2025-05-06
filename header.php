<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION['user_id']);
$userFirstName = $loggedIn ? explode(' ', $_SESSION['user_name'])[0] : '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<header>
    <div class="logo">
        <a href="index.php">CTRL+X</a>
    </div>
    
    <nav class="tabs">
        <a href="index.php" class="tab">Home</a>
        <a href="shop.php" class="tab">Shop</a>
        <a href="aboutus.php" class="tab">About Us</a>
    </nav>
    
    <div class="icons">
        <?php if ($loggedIn): ?>
            <div class="dropdown">
                <a href="#" class="icon dropdown-toggle">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($userFirstName); ?>
                </a>
                <div class="dropdown-menu">
                    <a href="profile.php">Account Settings</a>
                    <a href="order.php">My Orders</a>
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
    <div class="discount-content">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>
</div>

<style>
    /* Base Styles */
    :root {
    --primary: #1a1a1a;
    --secondary: #333;
    --accent: #d4af37;
    --light: #f5f5f5;
    --white: #ffffff;
    --gray: #e0e0e0;
    --error: #e74c3c;
    }

    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    }

    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Header Styles */
    header {
    background-color: var(--white);
    color: var(--white);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;

    }
    
    .logo a {
        color: black;
        font-weight: bold;
        font-size: 1.8rem;
        text-decoration: none;
        letter-spacing: 1px;
    }
    
    .tabs {
        display: flex;
        gap: 30px;
    }
    
    .tab {
        padding: 10px 0;
        text-decoration: none;
        color: black;
        font-weight: 500;
        font-size: 16px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .tab:hover {
        color: black;
    }
    
    .tab:hover::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: black;
        animation: underline 0.3s ease forwards;
    }
    
    @keyframes underline {
        from {
            transform: scaleX(0);
        }
        to {
            transform: scaleX(1);
        }
    }
    
    .icons {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    
    .icon {
        color: black;
        text-decoration: none;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .discount-label {
        background-color:#ff4d4d;
        color: white;
        padding: 20px 0;
        overflow: hidden;
        font-weight: bold;
        white-space: nowrap;
    }
    
    .discount-content {
        display: inline-block;
        padding-left: 100%;
        animation: scroll 15s linear infinite;
    }
    
    @keyframes scroll {
        from {
            transform: translateX(0);
        }
        to {
            transform: translateX(-100%);
        }
    }
    
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
        background-color: #f5f5f5;
    }
</style>