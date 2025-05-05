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

<!-- Product Listing Section -->
<main>
    <h1>Shop Our Collection</h1>
    <div class="filters">
        <select id="categoryFilter">
            <option value="all">All</option>
            <option value="oversized-t">Oversized T-Shirts</option>
            <option value="hoodie">Hoodies</option>
        </select>
        <select id="sortPrice">
            <option value="default">Sort By</option>
            <option value="low-to-high">Price: Low to High</option>
            <option value="high-to-low">Price: High to Low</option>
        </select>
    </div>
    <div id="product-list" class="product-grid">
        <!-- Products will be dynamically loaded here -->
    </div>
</main>

<script src="script.js"></script>

<?php include 'footer.php'; ?>

</body>
</html>

