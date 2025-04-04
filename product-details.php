<?php
include 'db.php'; // Include database connection

// Get product ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<h2>Product Not Found</h2>";
    exit;
}

$product_id = intval($_GET['id']); // Convert to integer to prevent SQL injection

// Fetch product details from the database
$sql = "SELECT * FROM PRODUCT WHERE P_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h2>Product Not Found</h2>";
    exit;
}

$product = $result->fetch_assoc();

// Fetch product variants (color, size, quantity)
$sql_variants = "SELECT * FROM PRODUCT_VARIANTS WHERE P_ID = ?";
$stmt = $conn->prepare($sql_variants);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$variants_result = $stmt->get_result();

$variants = [];
while ($variant = $variants_result->fetch_assoc()) {
    $variants[] = $variant;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['P_Name']; ?> | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'header.php'; ?> <!-- Include header -->

<main>
    <div class="product-details">
        <div class="product-image">
        <img id="productImage" src="<?php echo 'http://localhost/FYP/' . $product['P_Picture']; ?>" alt="<?php echo $product['P_Name']; ?>">         
        </div>
        <div class="product-info">
            <h1><?php echo $product['P_Name']; ?></h1>
            <p>RM <?php echo number_format($product['P_Price'], 2); ?></p>
            <p class="stock-status">✅ In Stock</p>

             <!-- Size Selection -->
             <label for="size">
            <p>Size:</p>
            <img src="images/sizechart.png" alt="Size Chart"></label>
            <select id="size-select">
                <option value="">Select Size</option>
                <?php foreach ($variants as $variant) { ?>
                    <option value="<?php echo $variant['P_Size']; ?>"><?php echo $variant['P_Size']; ?></option>
                <?php } ?>
            </select>

            <!-- Quantity Selector -->
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" value="1" min="1" max="15">

            <!-- Buttons -->
            <button onclick="addToCart(<?php echo $product['P_ID']; ?>)">Add to Cart</button>
            <button onclick="addToWishlist(<?php echo $product['P_ID']; ?>, '<?php echo addslashes($product['P_Name']); ?>', '<?php echo addslashes($product['P_Picture']); ?>', <?php echo $product['P_Price']; ?>)">❤️ Add to Wishlist</button>

            <details>
                <summary>Product Info</summary>
                <p><strong>Material:</strong> 80% Polyamide, 20% Spandex</p>
                <p><strong>Care Instructions:</strong> Machine wash cold, do not bleach, cool iron.</p>
            </details>

            <p>Product Code: <?php echo $product['P_ID']; ?></p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?> <!-- Include footer -->

</body>
</html>
