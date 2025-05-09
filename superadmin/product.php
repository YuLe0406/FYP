<?php
// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/php_errors.log');

include 'db.php';

// Handle activation/deactivation via AJAX - MUST BE FIRST
if (isset($_GET['toggle_status'])) {
    header('Content-Type: application/json');
    
    try {
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        $productId = (int)$_GET['toggle_status'];
        $newStatus = (int)$_GET['new_status'];
        $view = isset($_GET['view']) ? $_GET['view'] : 'active';
        
        // Verify product exists
        $check_query = "SELECT P_ID FROM PRODUCT WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) === 0) {
            throw new Exception("Product not found (ID: $productId)");
        }
        
        // Update status
        $update_query = "UPDATE PRODUCT SET P_Status = ? WHERE P_ID = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $newStatus, $productId);
        $update_result = mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => $update_result,
            'message' => $update_result ? 'Status updated successfully' : mysqli_error($conn),
            'productId' => $productId,
            'newStatus' => $newStatus,
            'redirect' => "product.php?view=" . ($newStatus ? 'inactive' : 'active')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
    exit();
}

// Only proceed with HTML for non-AJAX requests
include 'sidebar.php';

// Determine which products to show
$view = isset($_GET['view']) ? $_GET['view'] : 'active';
$status = ($view === 'inactive') ? 1 : 0;

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $per_page;

// Fetch all categories
$category_query = "SELECT * FROM CATEGORIES";
$category_result = mysqli_query($conn, $category_query);

// Get total number of products for pagination
$count_query = "SELECT COUNT(*) as total FROM PRODUCT WHERE P_Status = $status";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $per_page);

// Fetch products based on view with pagination
$product_query = "
    SELECT p.*, c.C_Name, p.P_Picture AS primary_image
    FROM PRODUCT p
    JOIN CATEGORIES c ON p.C_ID = c.C_ID
    WHERE p.P_Status = $status
    ORDER BY p.P_ID DESC
    LIMIT $start, $per_page";
$product_result = mysqli_query($conn, $product_query);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                    <input type="number" id="productPrice" name="productPrice" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label for="productDescription">Product Description:</label>
                    <textarea id="productDescription" name="productDescription" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="primaryImage">Primary Image (Required):</label>
                    <input type="file" id="primaryImage" name="primaryImage" accept="image/*" required>
                    <div class="image-preview" id="primaryImagePreview"></div>
                </div>
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </section>

        <!-- Product View Selector -->
        <section class="product-view">
            <div class="view-selector">
                <h3>
                    <i class="fas <?= $view === 'active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                    <?= $view === 'active' ? 'Active Products' : 'Inactive Products' ?>
                </h3>
                <select class="view-dropdown" onchange="window.location.href='?view='+this.value">
                    <option value="active" <?= $view === 'active' ? 'selected' : '' ?>>Active Products</option>
                    <option value="inactive" <?= $view === 'inactive' ? 'selected' : '' ?>>Inactive Products</option>
                </select>
            </div>
            
            <div class="table-responsive">
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
                    <?php if (mysqli_num_rows($product_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($product_result)): 
                            $image_path = $row['primary_image'] ? '../' . ltrim($row['primary_image'], 'FYP/') : '';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['P_ID']) ?></td>
                                <td><?= htmlspecialchars($row['C_Name']) ?></td>
                                <td><?= htmlspecialchars($row['P_Name']) ?></td>
                                <td>RM<?= number_format($row['P_Price'], 2) ?></td>
                                <td>
                                    <?php if ($image_path && file_exists($image_path)): ?>
                                        <img src="<?= htmlspecialchars($image_path) ?>" alt="Product Image" width="50">
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <ul class="variant-list">
                                        <?php
                                        $variant_sql = "SELECT P_Size, P_Quantity 
                                                       FROM PRODUCT_VARIANTS 
                                                       WHERE P_ID = ".$row['P_ID'];
                                        $variant_result = mysqli_query($conn, $variant_sql);
                                        while ($variant = mysqli_fetch_assoc($variant_result)):
                                        ?>
                                            <li>
                                                <strong>Size:</strong> <?= htmlspecialchars($variant['P_Size']) ?>, 
                                                <strong>Qty:</strong> <?= htmlspecialchars($variant['P_Quantity']) ?>
                                            </li>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($variant_result) == 0): ?>
                                            <li>No variants</li>
                                        <?php endif; ?>
                                    </ul>
                                </td>
                                <td>
                                    <span class="status-badge <?= $row['P_Status'] == 0 ? 'active' : 'inactive' ?>">
                                        <?= $row['P_Status'] == 0 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_product.php?productId=<?= $row['P_ID'] ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if ($row['P_Status'] == 0): ?>
                                            <button class="btn btn-deactivate" onclick="toggleProductStatus(<?= $row['P_ID'] ?>, 1, '<?= $view ?>')">
                                                <i class="fas fa-times-circle"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-activate" onclick="toggleProductStatus(<?= $row['P_ID'] ?>, 0, '<?= $view ?>')">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                No <?= $view === 'active' ? 'active' : 'inactive' ?> products found
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <!-- Previous Button -->
                    <?php if ($page > 1): ?>
                        <a href="?view=<?= $view ?>&page=<?= $page - 1 ?>">&lt;</a>
                    <?php else: ?>
                        <span class="disabled">&lt;</span>
                    <?php endif; ?>
                    
                    <!-- First Page -->
                    <?php if ($page > 3): ?>
                        <a href="?view=<?= $view ?>&page=1">1</a>
                        <?php if ($page > 4): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($page + 2, $total_pages);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?view=<?= $view ?>&page=<?= $i ?>" <?= $i == $page ? 'class="active"' : '' ?>>
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <!-- Last Page -->
                    <?php if ($page < $total_pages - 2): ?>
                        <?php if ($page < $total_pages - 3): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <a href="?view=<?= $view ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <?php if ($page < $total_pages): ?>
                        <a href="?view=<?= $view ?>&page=<?= $page + 1 ?>">&gt;</a>
                    <?php else: ?>
                        <span class="disabled">&gt;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toggle Product Status
    function toggleProductStatus(productId, newStatus, currentView) {
        const action = newStatus == 0 ? 'activate' : 'deactivate';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${action} this product.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                const loadingSwal = Swal.fire({
                    title: 'Processing',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`product.php?toggle_status=${productId}&new_status=${newStatus}&view=${currentView}&t=${Date.now()}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error(`Invalid response: ${text.substring(0, 100)}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    loadingSwal.close();
                    if (data?.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        throw new Error(data?.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    loadingSwal.close();
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: error.message.includes('Invalid response') ? 
                            'Server returned invalid response' : error.message,
                        footer: `Product ID: ${productId}`
                    });
                });
            }
        });
    }

    // Form Validation
    function validateForm() {
        const price = parseFloat(document.getElementById('productPrice').value);
        if (isNaN(price) || price <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Price',
                text: 'Please enter a valid price greater than 0',
            });
            return false;
        }
        
        // Check primary image is selected
        const primaryImage = document.getElementById('primaryImage');
        if (primaryImage.files.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Primary Image',
                text: 'Please upload the primary product image',
            });
            return false;
        }
        
        return true;
    }

    // Image Preview for primary image only
    document.getElementById('primaryImage').addEventListener('change', (e) => {
        const previewContainer = document.getElementById('primaryImagePreview');
        previewContainer.innerHTML = '';
        
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (!file.type.match('image.*')) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="max-width: 200px;">
                    <span>${file.name}</span>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>