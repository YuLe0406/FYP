<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// Fetch additional product images
$sql_images = "SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE P_ID = ?";
$stmt = $conn->prepare($sql_images);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images_result = $stmt->get_result();

$additional_images = [];
while ($image = $images_result->fetch_assoc()) {
    $additional_images[] = $image['PRODUCT_IMAGE'];
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Product Details Page Styles */
        .product-details {
            display: flex;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .product-gallery {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            background: #f8f8f8;
            cursor: zoom-in;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid #ddd;
            transition: border-color 0.3s;
        }
        
        .thumbnail:hover, .thumbnail.active {
            border-color: #000;
        }
        
        .product-info {
            flex: 1;
            max-width: 500px;
        }
        
        .product-title {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .stock-status {
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .product-option {
            margin-bottom: 20px;
        }
        
        .product-option label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .size-guide-link {
            color: #666;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        
        /* Size Selection Grid */
        .size-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .size-option {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            cursor: pointer;
            position: relative;
            font-weight: 500;
        }
        
        .size-option.selected {
            border-color: #000;
            background-color: #f0f0f0;
        }
        
        .size-option.out-of-stock {
            color: #ccc;
            cursor: not-allowed;
        }
        
        .size-option.out-of-stock::after {
            content: "✕";
            position: absolute;
            font-size: 24px;
            color: #ff0000;
        }
        
        /* Quantity Input */
        #quantity {
            width: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            max-width: 80px;
        }
        
        /* Buttons */
        .add-to-cart {
            width: 100%;
            padding: 12px;
            background-color: #d32f2f; /* Red background */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 8px;
        }
        
        .add-to-cart:hover {
            background-color: #b71c1c; /* Darker red on hover */
        }
        
        .add-to-wishlist {
            width: 100%;
            padding: 12px;
            background-color: transparent;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .add-to-wishlist:hover {
            border-color: #000;
            background-color: #f8f8f8;
        }
        
        .add-to-wishlist i {
            color: #000;
            transition: color 0.3s;
        }
        
        .add-to-wishlist.active i {
            color: #d32f2f; /* Red heart when active */
        }
        
        .product-description {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            max-width: 600px;
            width: 90%;
        }
        
        .close-modal {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
        
        .size-guide-image {
            width: 100%;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .product-details {
                flex-direction: column;
                padding: 15px;
            }
            
            .main-image {
                height: 350px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <div class="breadcrumb">
        <a href="shop.php">← Continue Shopping</a>
    </div>
    
    <div class="product-details">
        <div class="product-gallery">
            <img id="mainImage" src="<?php echo 'http://localhost/FYP/' . $product['P_Picture']; ?>" alt="<?php echo $product['P_Name']; ?>" class="main-image">
            
            <?php if (!empty($additional_images)): ?>
            <div class="thumbnail-container">
                <img src="<?php echo 'http://localhost/FYP/' . $product['P_Picture']; ?>" alt="Main view" class="thumbnail active" onclick="changeImage(this, '<?php echo 'http://localhost/FYP/' . $product['P_Picture']; ?>')">
                <?php foreach (array_slice($additional_images, 0, 4) as $image): ?>
                    <img src="<?php echo 'http://localhost/FYP/' . $image; ?>" alt="Additional view" class="thumbnail" onclick="changeImage(this, '<?php echo 'http://localhost/FYP/' . $image; ?>')">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?php echo $product['P_Name']; ?></h1>
            <p class="product-price">RM <?php echo number_format($product['P_Price'], 2); ?></p>
            <p class="stock-status">✅ In Stock</p>
            
            <!-- Size Selection -->
            <div class="product-option">
                <label>Size <span class="size-guide-link" onclick="openSizeGuide()">Size Guide</span></label>
                <div class="size-grid" id="sizeGrid">
                    <?php foreach ($variants as $variant): ?>
                        <div class="size-option <?php echo $variant['P_Quantity'] <= 0 ? 'out-of-stock' : ''; ?>" 
                             data-size="<?php echo $variant['P_Size']; ?>"
                             data-stock="<?php echo $variant['P_Quantity']; ?>"
                             data-variant-id="<?php echo $variant['PV_ID']; ?>"
                             <?php echo $variant['P_Quantity'] <= 0 ? '' : 'onclick="selectSize(this)"'; ?>>
                            <?php echo $variant['P_Size']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected-size" name="size">
            </div>
            
            <!-- Quantity Selector -->
            <div class="product-option">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" value="1" min="1" max="10">
            </div>
            
            <!-- Add to Cart Button -->
            <button class="add-to-cart" onclick="addToCart(<?php echo $product['P_ID']; ?>)">Add to Cart</button>
            
            <!-- Wishlist Button -->
            <button class="add-to-wishlist" id="wishlistBtn" onclick="toggleWishlist(<?php echo $product['P_ID']; ?>, '<?php echo addslashes($product['P_Name']); ?>', '<?php echo addslashes($product['P_Picture']); ?>', <?php echo $product['P_Price']; ?>)">
                <i class="far fa-heart"></i> Add to Wishlist
            </button>
                    
            <!-- Product Description -->
            <div class="product-description">
                <h3>Product Details</h3>
                <p><strong>Material:</strong> 80% Polyamide, 20% Spandex</p>
                <p><strong>Care Instructions:</strong> Machine wash cold, do not bleach, non iron.</p>
                <p><strong>Product Code:</strong> <?php echo $product['P_ID']; ?></p>
            </div>
        </div>
    </div>
</main>

<!-- Size Guide Modal -->
<div id="sizeGuideModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeSizeGuide()">&times;</span>
        <h2>Size Guide</h2>
        <img src="images/sizechart.png" alt="Size Chart" class="size-guide-image">
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
// Product data for JavaScript functions
const product = {
    id: <?php echo $product['P_ID']; ?>,
    name: "<?php echo addslashes($product['P_Name']); ?>",
    price: <?php echo $product['P_Price']; ?>,
    image: "<?php echo 'http://localhost/FYP/' . $product['P_Picture']; ?>"
};

// Change main product image when thumbnail is clicked
function changeImage(thumbnail, newSrc) {
    document.getElementById('mainImage').src = newSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(img => {
        img.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Size Guide Modal functions
function openSizeGuide() {
    document.getElementById('sizeGuideModal').style.display = 'block';
}

function closeSizeGuide() {
    document.getElementById('sizeGuideModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == document.getElementById('sizeGuideModal')) {
        closeSizeGuide();
    }
}

// Size selection function
function selectSize(element) {
    // Remove selected class from all size options
    document.querySelectorAll('.size-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    element.classList.add('selected');
    
    // Update hidden input with selected size
    document.getElementById('selected-size').value = element.getAttribute('data-size');
    
    // Update quantity max based on stock
    const stock = parseInt(element.getAttribute('data-stock'));
    const quantityInput = document.getElementById('quantity');
    quantityInput.max = stock;
    quantityInput.value = 1;
    
    // Check wishlist status for this size
    const wishlistBtn = document.getElementById('wishlistBtn');
    if (wishlistBtn) {
        let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        const exists = wishlist.some(item => 
            item.id === product.id && item.size === element.getAttribute('data-size')
        );
        
        const heartIcon = wishlistBtn.querySelector('i');
        if (exists) {
            heartIcon.classList.remove('far', 'fa-heart');
            heartIcon.classList.add('fas', 'fa-heart');
            wishlistBtn.classList.add('active');
        } else {
            heartIcon.classList.remove('fas', 'fa-heart');
            heartIcon.classList.add('far', 'fa-heart');
            wishlistBtn.classList.remove('active');
        }
    }
}

// Wishlist toggle function
function toggleWishlist(id, name, image, price) {
    const selectedSize = document.getElementById('selected-size').value;
    
    if (!selectedSize) {
        showErrorAlert('Size Required', 'Please select a size first');
        return;
    }
    
    let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    const productIndex = wishlist.findIndex(item => item.id === id && item.size === selectedSize);
    
    const wishlistBtn = document.getElementById('wishlistBtn');
    const heartIcon = wishlistBtn.querySelector('i');
    
    if (productIndex >= 0) {
        // Remove from wishlist
        wishlist.splice(productIndex, 1);
        heartIcon.classList.remove('fas', 'fa-heart');
        heartIcon.classList.add('far', 'fa-heart');
        wishlistBtn.classList.remove('active');
    } else {
        // Add to wishlist
        const selectedOption = document.querySelector('.size-option.selected');
        const variantId = selectedOption ? selectedOption.getAttribute('data-variant-id') : '';
        
        wishlist.push({
            id,
            name,
            image,
            price,
            size: selectedSize,
            variantId
        });
        heartIcon.classList.remove('far', 'fa-heart');
        heartIcon.classList.add('fas', 'fa-heart');
        wishlistBtn.classList.add('active');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistCounter();
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Check if any size is selected by default
    const selectedSize = document.getElementById('selected-size').value;
    const wishlistBtn = document.getElementById('wishlistBtn');
    
    if (selectedSize && wishlistBtn) {
        let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        const exists = wishlist.some(item => 
            item.id === product.id && item.size === selectedSize
        );
        
        if (exists) {
            const heartIcon = wishlistBtn.querySelector('i');
            heartIcon.classList.remove('far', 'fa-heart');
            heartIcon.classList.add('fas', 'fa-heart');
            wishlistBtn.classList.add('active');
        }
    }
});
</script>

</body>
</html>