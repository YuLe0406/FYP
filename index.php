<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

    <?php include 'header.php'; ?> <!-- Include Header -->

    <!-- Discount Label -->
    <div class="discount-label">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>

    <!-- Hero Banner Section -->
    <div class="banner-container">
        <div class="banner">
            <a href="shop.php">
                <img src="images/homepage1.png" alt="Fashion 1">
            </a>
            <a href="shop.php">
                <img src="images/homepage2.png" alt="Fashion 2">
            </a>
            <a href="shop.php">
                <img src="images/homepage3.png" alt="Fashion 3">
            </a>
        </div>
    </div>

    <button class="banner-btn left-btn">&#10094;</button>
    <button class="banner-btn right-btn">&#10095;</button>

    <div class="dots-container"></div> <!-- Dots will be generated dynamically -->

    <section class="info-section">
        <div class="info-container">
            <div class="info-box">
                <h2>About CTRL+X – Fashion Redefined</h2>
                <p>CTRL+X is not just an online clothing store; it's a movement to break away from the ordinary and embrace the future of fashion. Our collection features cutting-edge styles, high-quality materials, and bold designs to help you express your individuality.</p>
            </div>
    
            <div class="info-box">
                <h2>Why Choose CTRL+X?</h2>
                <ul>
                    <li><strong>Trendy & Futuristic Styles:</strong> We bring you the latest trends with a modern edge.</li>
                    <li><strong>High-Quality Materials:</strong> Every piece is designed for comfort and durability.</li>
                    <li><strong>Affordable Pricing:</strong> Premium fashion at budget-friendly prices.</li>
                    <li><strong>Fast & Secure Shipping:</strong> Get your orders delivered quickly and securely.</li>
                    <li><strong>Exclusive Collections:</strong> Limited edition designs that make you stand out.</li>
                </ul>
            </div>
    
            <div class="info-box">
                <h2>Join the CTRL+X Movement</h2>
                <p>Be part of a community that values bold fashion and unique expression. Follow us on social media, subscribe to our newsletter, and stay updated on exclusive deals and new arrivals!</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?> <!-- Include Footer -->

</body>
</html>
