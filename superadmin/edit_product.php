<?php
include 'db.php';

if (!isset($_GET['productId'])) {
    die("Product ID not specified.");
}

$productId = intval($_GET['productId']);
$editVariantId = isset($_GET['editVariantId']) ? intval($_GET['editVariantId']) : null;

$stmt = $conn->prepare("SELECT * FROM PRODUCT WHERE P_ID = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all categories
$category_result = mysqli_query($conn, "SELECT * FROM CATEGORIES");

// Fetch variants
$variant_result = mysqli_query($conn, "
    SELECT PV_ID, P_Size, P_Quantity
    FROM PRODUCT_VARIANTS
    WHERE P_ID = $productId
");

// Fetch product images
$img_stmt = $conn->prepare("SELECT * FROM PRODUCT_IMAGES WHERE P_ID = ?");
$img_stmt->bind_param("i", $productId);
$img_stmt->execute();
$images_result = $img_stmt->get_result();
$img_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .btn-back {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        form {
            margin-bottom: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        button {
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        
        .btn-primary {
            background: #2ecc71;
        }
        
        .btn-primary:hover {
            background: #27ae60;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-warning {
            background: #f39c12;
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        ul {
            list-style: none;
            padding: 0;
        }
        
        li {
            padding: 10px;
            margin-bottom: 10px;
            background: #f0f0f0;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .variant-actions {
            display: flex;
            gap: 5px;
        }
        
        .variant-editing {
            background: #e3f2fd;
        }
        
        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }
        
        .section-title {
            margin: 20px 0 15px;
            color: #2c3e50;
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 5px;
        }
        
        .image-management {
            margin-bottom: 30px;
        }
        
        .image-section {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .image-section-title {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin: 15px 0;
        }
        
        .image-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .image-preview {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background: #f5f5f5;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .image-upload-form {
            margin-top: 15px;
        }
        
        .image-upload-form input[type="file"] {
            margin-bottom: 10px;
        }
        
        .no-image {
            color: #777;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Edit Product</h1>
            <a href="product.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Product List
            </a>
        </div>

        <!-- Product Update Form -->
        <form action="update_product.php" method="POST">
            <input type="hidden" name="productId" value="<?= $productId ?>">

            <label>Category:</label>
            <select name="productCategory" required>
                <?php 
                mysqli_data_seek($category_result, 0);
                while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                    <option value="<?= $cat['C_ID'] ?>" <?= $cat['C_ID'] == $product['C_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['C_Name']) ?>
                    </option>
                <?php } ?>
            </select>

            <label>Product Name:</label>
            <input type="text" name="productName" value="<?= htmlspecialchars($product['P_Name']) ?>" required>

            <label>Product Price:</label>
            <input type="number" name="productPrice" step="0.01" min="0.01" value="<?= $product['P_Price'] ?>" required>

            <label>Product Description:</label>
            <textarea name="productDescription" required><?= htmlspecialchars($product['P_DES']) ?></textarea>

            <button type="submit" class="btn-primary">Update Product</button>
        </form>

        <!-- Image Management Section -->
        <div class="image-management">
            <!-- Primary Image Section -->
            <div class="image-section">
                <h2 class="section-title">Primary Image</h2>
                <div class="image-grid">
                    <?php if (!empty($product['P_Picture'])): 
                        $primaryImagePath = '../' . ltrim($product['P_Picture'], 'FYP/');
                    ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <?php if (file_exists($primaryImagePath)): ?>
                                    <img src="<?= htmlspecialchars($primaryImagePath) ?>" alt="Primary Image">
                                <?php else: ?>
                                    <span class="no-image">Image not found</span>
                                <?php endif; ?>
                            </div>
                            <div class="image-actions">
                                <form action="update_primary_image.php" method="POST" enctype="multipart/form-data" style="flex-grow:1;">
                                    <input type="hidden" name="productId" value="<?= $productId ?>">
                                    <input type="file" name="newPrimaryImage" accept="image/*" required>
                                    <button type="submit" class="btn-primary" style="width:100%;">
                                        <i class="fas fa-sync-alt"></i> Replace
                                    </button>
                                </form>
                                <form action="delete_primary_image.php" method="POST" id="deletePrimaryForm" style="flex-grow:1;">
                                    <input type="hidden" name="productId" value="<?= $productId ?>">
                                    <button type="submit" class="btn-danger" style="width:100%;">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <span class="no-image">No primary image set</span>
                            </div>
                            <form action="update_primary_image.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="productId" value="<?= $productId ?>">
                                <input type="file" name="newPrimaryImage" accept="image/*" required>
                                <button type="submit" class="btn-primary" style="width:100%;">
                                    <i class="fas fa-upload"></i> Set Primary Image
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Images Section -->
            <div class="image-section">
                <h2 class="section-title">Additional Images</h2>
                <div class="image-grid">
                    <?php 
                    if ($images_result->num_rows > 0):
                        while ($img = $images_result->fetch_assoc()): 
                            $image_path = '../' . ltrim($img['PRODUCT_IMAGE'], 'FYP/');
                    ?>
                            <div class="image-card">
                                <div class="image-preview">
                                    <?php if (file_exists($image_path)): ?>
                                        <img src="<?= htmlspecialchars($image_path) ?>" alt="Additional Image">
                                    <?php else: ?>
                                        <span class="no-image">Image not found</span>
                                    <?php endif; ?>
                                </div>
                                <div class="image-actions">
                                    <form action="delete_image.php" method="POST" class="deleteImageForm">
                                        <input type="hidden" name="productId" value="<?= $productId ?>">
                                        <input type="hidden" name="imageId" value="<?= $img['PI_ID'] ?>">
                                        <button type="submit" class="btn-danger" style="width:100%;">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <span class="no-image">No additional images</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form action="update_product_images.php" method="POST" enctype="multipart/form-data" class="image-upload-form">
                    <input type="hidden" name="productId" value="<?= $productId ?>">
                    <label>Upload Additional Images:</label>
                    <input type="file" name="newImages[]" multiple accept="image/*" required>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Upload Images
                    </button>
                </form>
            </div>
        </div>

        <!-- Variants Management Section -->
        <div class="variants-section">
            <h2 class="section-title">Product Variants</h2>
            <ul>
                <?php 
                if ($variant_result->num_rows > 0):
                    while ($variant = $variant_result->fetch_assoc()): 
                ?>
                    <li class="<?= ($editVariantId == $variant['PV_ID']) ? 'variant-editing' : '' ?>">
                        <?php if ($editVariantId == $variant['PV_ID']): ?>
                            <form action="update_variant.php" method="POST" style="flex-grow:1;">
                                <input type="hidden" name="variantId" value="<?= $variant['PV_ID'] ?>">
                                <input type="hidden" name="productId" value="<?= $productId ?>">

                                <label>Size:</label>
                                <input type="text" name="size" value="<?= htmlspecialchars($variant['P_Size']) ?>" required>

                                <label>Quantity:</label>
                                <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($variant['P_Quantity']) ?>" required>

                                <div class="variant-actions">
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <a href="edit_product.php?productId=<?= $productId ?>">
                                        <button type="button" class="btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </a>
                                </div>
                            </form>
                        <?php else: ?>
                            <span>
                                <strong>Size:</strong> <?= htmlspecialchars($variant['P_Size']) ?>, 
                                <strong>Quantity:</strong> <?= htmlspecialchars($variant['P_Quantity']) ?>
                            </span>
                            <div class="variant-actions">
                                <a href="edit_product.php?productId=<?= $productId ?>&editVariantId=<?= $variant['PV_ID'] ?>">
                                    <button type="button" class="btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </a>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php 
                    endwhile;
                else: ?>
                    <li>No variants found for this product</li>
                <?php endif; ?>
            </ul>

            <!-- Add Variant Form -->
            <form action="add_variant.php" method="POST">
                <input type="hidden" name="productId" value="<?= $productId ?>">

                <label>Size:</label>
                <input type="text" name="size" required>

                <label>Quantity:</label>
                <input type="number" name="quantity" min="0" required>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Variant
                </button>
            </form>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle primary image deletion
        const deletePrimaryForm = document.getElementById('deletePrimaryForm');
        if (deletePrimaryForm) {
            deletePrimaryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Primary Image?',
                    text: "This will remove the primary product image. Are you sure?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }

        // Handle additional images deletion
        const deleteImageForms = document.querySelectorAll('.deleteImageForm');
        deleteImageForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Image?',
                    text: "This will permanently remove this product image. Are you sure?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        // Show success/error messages from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: urlParams.get('success'),
                timer: 2000,
                showConfirmButton: false
            });
        }
        if (urlParams.has('error')) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: urlParams.get('error'),
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
</script>
</body>
</html>