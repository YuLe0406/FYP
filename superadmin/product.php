<?php
include 'db.php';
include 'sidebar.php';

// Fetch only active categories
$category_query = "SELECT * FROM CATEGORIES WHERE C_Status = 0";
$category_result = mysqli_query($conn, $category_query);

// Fetch products with their first image from active categories
$product_query = "
    SELECT p.*, c.C_Name, 
           (SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE P_ID = p.P_ID LIMIT 1) AS primary_image
    FROM PRODUCT p
    JOIN CATEGORIES c ON p.C_ID = c.C_ID
    WHERE c.C_Status = 0
    ORDER BY p.P_ID DESC";
$product_result = mysqli_query($conn, $product_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <link rel="stylesheet" href="product.css">
</head>
<body>
<div class="container">
    <main class="main-content">
        <h1>Product Management</h1>

        <!-- Add Product Form -->
        <section class="add-product">
            <h2>Add New Product</h2>
            <form class="product-form" action="add_product.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="productCategory">Category:</label>
                    <select id="productCategory" name="productCategory" required>
                        <option value="">Select a category</option>
                        <?php while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                            <option value="<?= $cat['C_ID'] ?>"><?= htmlspecialchars($cat['C_Name']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productName">Product Name:</label>
                    <input type="text" id="productName" name="productName" required>
                </div>
                <div class="form-group">
                    <label for="productPrice">Product Price:</label>
                    <input type="number" id="productPrice" name="productPrice" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Product Images:</label>
                    <div id="imageInputs">
                        <input type="file" name="productImages[]" accept="image/*" required>
                    </div>
                    <button type="button" class="add-more-btn" id="addMoreImages">
                        <i>+</i> Add More
                    </button>
                    <small>Upload multiple images (first image will be used as primary)</small>
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </section>

        <!-- Existing Products Table -->
        <section class="existing-products">
            <h2>Existing Products</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Variants</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                // Reset the pointer for the product result
                mysqli_data_seek($product_result, 0);
                while ($row = mysqli_fetch_assoc($product_result)) { 
                    // Fix image path - remove FYP/ if it exists and prepend ../
                    $image_path = $row['primary_image'];
                    if (strpos($image_path, 'FYP/') === 0) {
                        $image_path = substr($image_path, 4); // Remove 'FYP/'
                    }
                    $image_path = '../' . $image_path; // Go up one level from superadmin
                ?>
                    <tr>
                        <td><?= $row['P_ID'] ?></td>
                        <td><?= htmlspecialchars($row['C_Name']) ?></td>
                        <td><?= htmlspecialchars($row['P_Name']) ?></td>
                        <td>RM<?= number_format($row['P_Price'], 2) ?></td>
                        <td>
                            <?php if ($row['primary_image'] && file_exists($image_path)): ?>
                                <img src="<?= htmlspecialchars($image_path) ?>" alt="Product Image" width="50">
                            <?php else: ?>
                                <span>No Image (Path: <?= htmlspecialchars($image_path) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <ul>
                                <?php
                                $pid = $row['P_ID'];
                                $variant_sql = "
                                    SELECT pv.P_Size, pv.P_Quantity, pc.COLOR_NAME
                                    FROM PRODUCT_VARIANTS pv
                                    JOIN PRODUCT_COLOR pc ON pv.PC_ID = pc.PC_ID
                                    WHERE pv.P_ID = $pid
                                ";
                                $variant_result = mysqli_query($conn, $variant_sql);
                                while ($variant = mysqli_fetch_assoc($variant_result)) {
                                    echo "<li><strong>Color:</strong> {$variant['COLOR_NAME']}, 
                                            <strong>Size:</strong> {$variant['P_Size']}, 
                                            <strong>Quantity:</strong> {$variant['P_Quantity']}</li>";
                                }
                                ?>
                            </ul>
                        </td>
                        <td>
                            <a href="edit_product.php?productId=<?= $row['P_ID'] ?>">
                                <button class="edit-btn">Edit</button>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<script>
    // Prevent form submission if price is negative
    function validateForm() {
        const price = document.getElementById('productPrice').value;
        if (parseFloat(price) < 0) {
            alert('Product price cannot be negative.');
            return false;
        }
        return true;
    }

    // Dynamically add more file input fields
    document.getElementById('addMoreImages').addEventListener('click', function () {
        const container = document.getElementById('imageInputs');
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'productImages[]';
        input.accept = 'image/*';
        container.appendChild(input);
    });

    // Preview images
    document.getElementById('imageInputs').addEventListener('change', function (e) {
        const previewContainer = document.getElementById('imagePreview');
        previewContainer.innerHTML = '';
        const files = Array.from(document.querySelectorAll('input[type="file"][name="productImages[]"]'))
            .flatMap(input => Array.from(input.files));

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.classList.add('image-preview-item');
                div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
</body>
</html>