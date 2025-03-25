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

    <?php include 'footer.php'; ?> <!-- Include Footer -->

</body>
</html>
