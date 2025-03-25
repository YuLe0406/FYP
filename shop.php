<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

    <?php include 'header.php'; ?> <!-- Include Header -->

    <h1>Shop</h1>
    <div class="product-list">
        <?php
        $products = [
            1 => ["name" => "White Oversized T", "price" => 69.90, "image" => "images/1front.png", "desc" => "Comfortable and stylish oversized T-shirt."],
            2 => ["name" => "Black Oversized T", "price" => 89.90, "image" => "images/2front.png", "desc" => "Classic black oversized T-shirt."],
            3 => ["name" => "Red Oversized T", "price" => 79.90, "image" => "images/3front.png", "desc" => "Bold and trendy red oversized T-shirt."],
            4 => ["name" => "Clay Oversized T", "price" => 79.90, "image" => "images/4front.png", "desc" => "Earthy toned oversized T-shirt."],
            5 => ["name" => "Butter Oversized T", "price" => 79.90, "image" => "images/5front.png", "desc" => "Soft butter-colored oversized T-shirt."],
            6 => ["name" => "Grey Oversized T", "price" => 69.90, "image" => "images/6front.png", "desc" => "Minimalist grey oversized T-shirt."],
            7 => ["name" => "Orchid Oversized T", "price" => 79.90, "image" => "images/7front.png", "desc" => "Unique orchid-colored oversized T-shirt."],
            8 => ["name" => "White Hoodie", "price" => 169.90, "image" => "images/1Front.jpeg", "desc" => "Warm and stylish white hoodie."],
            9 => ["name" => "Grey Hoodie", "price" => 169.90, "image" => "images/2Front.jpeg", "desc" => "Versatile grey hoodie."],
            10 => ["name" => "Charcoal Hoodie", "price" => 169.90, "image" => "images/3Front.jpeg", "desc" => "Dark charcoal hoodie with a sleek look."],
            11 => ["name" => "Black Hoodie", "price" => 169.90, "image" => "images/4Front.jpeg", "desc" => "Classic black hoodie."],
            12 => ["name" => "Red Hoodie", "price" => 169.90, "image" => "images/5Front.jpeg", "desc" => "Vibrant red hoodie for a bold statement."],
            13 => ["name" => "Green Hoodie", "price" => 169.90, "image" => "images/6Front.jpeg", "desc" => "Stylish green hoodie for casual wear."],
            14 => ["name" => "Navy Hoodie", "price" => 169.90, "image" => "images/7Front.jpeg", "desc" => "Deep navy hoodie for a classic look."]
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
