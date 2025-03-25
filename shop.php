<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>

    <?php include 'header.php'; ?> <!-- Include Header -->

    <!-- Discount Label -->
    <div class="discount-label">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>

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
    
    <div class="product-list">
        <?php
        $products = [
            ["id" => 1, "name" => "Oversized T-Shirt", "price" => 50, "image" => "images/tshirt1.png"],
            ["id" => 2, "name" => "Hoodie", "price" => 80, "image" => "images/hoodie1.png"],
            ["id" => 3, "name" => "Oversized T-Shirt", "price" => 55, "image" => "images/tshirt2.png"]
        ];

        foreach ($products as $product) {
            echo "<div class='product'>";
            echo "<a href='product-details.php?id=" . $product['id'] . "'>";
            echo "<img src='" . $product['image'] . "' alt='" . $product['name'] . "'>";
            echo "<h3>" . $product['name'] . "</h3>";
            echo "<p>RM " . $product['price'] . "</p>";
            echo "</a>";
            echo "</div>";
        }
        ?>
    </div>

    <?php include 'footer.php'; ?> <!-- Include Footer -->

</body>
</html>
