<?php
include 'db.php';
include 'sidebar.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch only active categories
$category_query = "SELECT * FROM CATEGORIES WHERE C_Status = 0";
$category_result = mysqli_query($conn, $category_query);

// Handle activation/deactivation via AJAX
if (isset($_GET['toggle_status'])) {
    header('Content-Type: application/json');
    
    try {
        // Verify database connection
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        // Sanitize inputs
        $productId = (int)$_GET['toggle_status'];
        $newStatus = (int)$_GET['new_status'];
        
        // Verify product exists
        $check_query = "SELECT P_ID FROM PRODUCT WHERE P_ID = $productId";
        $check_result = mysqli_query($conn, $check_query);
        
        if (!$check_result || mysqli_num_rows($check_result) === 0) {
            throw new Exception("Product not found (ID: $productId)");
        }
        
        // Update status using prepared statement
        $update_query = "UPDATE PRODUCT SET P_Status = ? WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $newStatus, $productId);
        $update_result = mysqli_stmt_execute($stmt);
        
        if ($update_result) {
            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully',
                'productId' => $productId,
                'newStatus' => $newStatus
            ]);
        } else {
            throw new Exception("Update failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
    exit();
}

// Fetch all products (both active and inactive)
$product_query = "
    SELECT p.*, c.C_Name, 
           (SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE P_ID = p.P_ID LIMIT 1) AS primary_image
    FROM PRODUCT p
    JOIN CATEGORIES c ON p.C_ID = c.C_ID
    WHERE c.C_Status = 0
    ORDER BY p.P_ID DESC";
$product_result = mysqli_query($conn, $product_query);

// Store categories for reuse in form
$categories = [];
while ($cat = mysqli_fetch_assoc($category_result)) {
    $categories[] = $cat;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <link rel="stylesheet" href="product.css">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['C_ID']) ?>">
                                <?= htmlspecialchars($cat['C_Name']) ?>
                            </option>
                        <?php endforeach; ?>
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
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                mysqli_data_seek($product_result, 0);
                while ($row = mysqli_fetch_assoc($product_result)): 
                    $image_path = $row['primary_image'];
                    if (strpos($image_path, 'FYP/') === 0) {
                        $image_path = substr($image_path, 4);
                    }
                    $image_path = '../' . $image_path;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['P_ID']) ?></td>
                        <td><?= htmlspecialchars($row['C_Name']) ?></td>
                        <td><?= htmlspecialchars($row['P_Name']) ?></td>
                        <td>RM<?= number_format($row['P_Price'], 2) ?></td>
                        <td>
                            <?php if ($row['primary_image'] && file_exists($image_path)): ?>
                                <img src="<?= htmlspecialchars($image_path) ?>" alt="Product Image" width="50">
                            <?php else: ?>
                                <span>No Image</span>
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
                                while ($variant = mysqli_fetch_assoc($variant_result)):
                                ?>
                                    <li>
                                        <strong>Color:</strong> <?= htmlspecialchars($variant['COLOR_NAME']) ?>, 
                                        <strong>Size:</strong> <?= htmlspecialchars($variant['P_Size']) ?>, 
                                        <strong>Quantity:</strong> <?= htmlspecialchars($variant['P_Quantity']) ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </td>
                        <td><?= $row['P_Status'] == 0 ? 'Active' : 'Deactivated' ?></td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="edit_product.php?productId=<?= $row['P_ID'] ?>">
                                    <button class="edit-btn">Edit</button>
                                </a>
                                <?php if ($row['P_Status'] == 0): ?>
                                    <button class="deactivate-btn" onclick="toggleProductStatus(<?= $row['P_ID'] ?>, 1)">
                                        Deactivate
                                    </button>
                                <?php else: ?>
                                    <button class="activate-btn" onclick="toggleProductStatus(<?= $row['P_ID'] ?>, 0)">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleProductStatus(productId, newStatus) {
        const action = newStatus == 0 ? 'activate' : 'deactivate';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${action} this product`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${action} it!`,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                const loadingSwal = Swal.fire({
                    title: 'Processing',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Make AJAX call to update status
                fetch(`product.php?toggle_status=${productId}&new_status=${newStatus}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        loadingSwal.close();
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        loadingSwal.close();
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: error.message,
                            footer: `Product ID: ${productId}`
                        });
                    });
            }
        });
    }

    function validateForm() {
        const price = document.getElementById('productPrice').value;
        if (parseFloat(price) < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Price',
                text: 'Product price cannot be negative',
            });
            return false;
        }
        return true;
    }

    // Image upload handling
    document.getElementById('addMoreImages').addEventListener('click', function () {
        const container = document.getElementById('imageInputs');
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'productImages[]';
        input.accept = 'image/*';
        container.appendChild(input);
    });

    document.getElementById('imageInputs').addEventListener('change', function (e) {
        const previewContainer = document.getElementById('imagePreview');
        previewContainer.innerHTML = '';
        const files = Array.from(document.querySelectorAll('input[type="file"][name="productImages[]"]'))
            .flatMap(input => Array.from(input.files));

        files.forEach(file => {
            if (!file.type.match('image.*')) continue;
            
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