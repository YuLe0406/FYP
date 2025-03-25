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

<?php include 'header.php'; ?>

<!-- Discount Label -->
<div class="discount-label">
    <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
</div>

<!-- Product Details Section -->
<main>
    <div class="product-details">
        <div class="product-image">
            <img id="productImage" src="" alt="Product Image">
        </div>
        <div class="product-info">
            <h1 id="productName"></h1>
            <p id="productPrice"></p>
            <p class="stock-status">✅ In Stock</p>

            <!-- Size Selection -->
            <label for="size">
            <p>Size:</p>
            <img src="images/sizechart.png" alt="Size Chart"></label>
            <label for="size-select">Size:</label>
            <select id="size-select">
                <option value="">Select Size</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
            </select>

            <!-- Model Information -->
            <p>👕 Model Height: 186 cm</p>
            <p>👕 Model Wearing: M</p>

            <!-- Quantity Selector -->
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" value="1" min="1" max="15">

            <!-- Buttons -->
            <button onclick="addToCart()">Add to Cart</button>
            <div class="wishlist-container">
                <i class="far fa-heart"></i>
                <a href="#" onclick="addToWishlist()">Add to Wishlist</a>
            </div>

            <!-- Expandable Product Info -->
            <details>
                <summary>Product Info</summary>
                <p><strong>Care Label</strong></p>
                <ul>
                    <li>Do Not Tumble Dry</li>
                    <li>Do Not Bleach</li>
                    <li>Do Not Soak</li>
                    <li>Wash Separately</li>
                    <li>Cool Iron</li>
                    <li>Machine Wash, Cold</li>
                </ul>
                <p><strong>Material</strong></p>
                <ul>
                    <li>80% Polyamide</li>
                    <li>20% Spandex</li>
                </ul>
            </details>

            <!-- Product Code -->
            <p>Product Code: P20466080</p>

            <!-- Social Share -->
            <div class="social-share">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
