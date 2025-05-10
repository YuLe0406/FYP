<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include database connection
require_once 'db.php';

// Get filter and sort parameters
$filter = $_POST['filter'] ?? 'all';
$sort = $_POST['sort'] ?? 'default';

// Base query - only active products
$query = "SELECT p.* FROM PRODUCT p WHERE p.P_Status = 0";

// Apply filter if not 'all'
if ($filter !== 'all') {
    $query .= " AND p.C_ID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $filter);
} else {
    $stmt = mysqli_prepare($conn, $query);
}

// Apply sorting
switch ($sort) {
    case 'low-to-high':
        $query .= " ORDER BY p.P_Price ASC";
        break;
    case 'high-to-low':
        $query .= " ORDER BY p.P_Price DESC";
        break;
    default:
        $query .= " ORDER BY p.P_ID ASC";
}

// Execute query
if ($filter !== 'all') {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Base URL for images
$baseUrl = 'http://localhost/FYP/';

// Output the filtered/sorted products
foreach ($products as $product): 
    // Check stock availability
    $stock_query = "SELECT SUM(P_Quantity) as total_qty FROM PRODUCT_VARIANTS WHERE P_ID = " . $product['P_ID'];
    $stock_result = mysqli_query($conn, $stock_query);
    $stock = mysqli_fetch_assoc($stock_result)['total_qty'];
    $is_out_of_stock = ($stock <= 0);
?>
    <div class="product-card <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
        <?php if ($is_out_of_stock): ?>
            <div class="out-of-stock-badge">OUT OF STOCK</div>
        <?php endif; ?>
        <div class="product-image-container">
            <img src="<?= htmlspecialchars($baseUrl . $product['P_Picture']) ?>" 
                 alt="<?= htmlspecialchars($product['P_Name']) ?>" 
                 loading="lazy">
        </div>
        <h3><?= htmlspecialchars($product['P_Name']) ?></h3>
        <p>RM <?= number_format($product['P_Price'], 2) ?></p>
        <button class="view-details-btn" onclick="viewProduct(<?= $product['P_ID'] ?>)">
            View Details
        </button>
    </div>
<?php endforeach;

// Close connection
mysqli_close($conn);
?>