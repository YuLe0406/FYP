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
    SELECT p.*, c.C_Name, 
           (SELECT PRODUCT_IMAGE FROM PRODUCT_IMAGES WHERE P_ID = p.P_ID LIMIT 1) AS primary_image
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
    <style>
        /* Main Layout */
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f5f7fa;
        }
        
        section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h1, h2 {
            color: #2c3e50;
        }
        
        h2 {
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        /* View Selector */
        .view-selector {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .view-selector h3 {
            font-size: 22px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-dropdown {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Form Styles */
        .product-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 600px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .form-group label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #1abc9c;
            outline: none;
            box-shadow: 0 0 5px rgba(26, 188, 156, 0.3);
        }
        
        /* Buttons */
        .submit-btn {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            width: 200px;
        }
        
        .submit-btn:hover {
            background: #16a085;
        }
        
        .add-more-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .add-more-btn:hover {
            background: #2980b9;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #1abc9c;
            color: white;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        
        .btn-edit:hover {
            background: #e67e22;
        }
        
        .btn-deactivate {
            background: #e74c3c;
            color: white;
        }
        
        .btn-deactivate:hover {
            background: #c0392b;
        }
        
        .btn-activate {
            background: #2ecc71;
            color: white;
        }
        
        .btn-activate:hover {
            background: #27ae60;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Image Handling */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .preview-item {
            position: relative;
            width: 80px;
        }
        
        .preview-item img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        /* Variant List */
        .variant-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 120px;
            overflow-y: auto;
        }
        
        .variant-list li {
            padding: 4px 0;
            border-bottom: 1px dashed #eee;
            font-size: 13px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #1abc9c;
            color: white;
            border-color: #1abc9c;
        }
        
        .pagination .active {
            background: #1abc9c;
            color: white;
            border-color: #1abc9c;
            font-weight: bold;
        }
        
        .pagination .disabled {
            color: #aaa;
            pointer-events: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .product-form {
                width: 100%;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
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
                    <label>Product Images:</label>
                    <div id="imageInputs">
                        <input type="file" name="productImages[]" accept="image/*" required>
                    </div>
                    <button type="button" class="add-more-btn" id="addMoreImages">
                        <i class="fas fa-plus"></i> Add More Images
                    </button>
                    <div class="image-preview" id="imagePreview"></div>
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
        
        const imageInputs = document.querySelectorAll('input[name="productImages[]"]');
        let hasImage = false;
        imageInputs.forEach(input => {
            if (input.files.length > 0) hasImage = true;
        });
        
        if (!hasImage) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Images',
                text: 'Please upload at least one product image',
            });
            return false;
        }
        
        return true;
    }

    // Image Upload Handling
    document.getElementById('addMoreImages').addEventListener('click', () => {
        const container = document.getElementById('imageInputs');
        const newInput = document.createElement('input');
        newInput.type = 'file';
        newInput.name = 'productImages[]';
        newInput.accept = 'image/*';
        newInput.style.marginTop = '5px';
        container.appendChild(newInput);
    });

    // Image Preview
    document.getElementById('imageInputs').addEventListener('change', (e) => {
        const previewContainer = document.getElementById('imagePreview');
        previewContainer.innerHTML = '';
        
        const fileInputs = document.querySelectorAll('input[name="productImages[]"]');
        const files = [];
        fileInputs.forEach(input => {
            if (input.files.length > 0) files.push(...Array.from(input.files));
        });
        
        files.forEach(file => {
            if (!file.type.match('image.*')) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <span>${file.name}</span>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
</body>
</html>