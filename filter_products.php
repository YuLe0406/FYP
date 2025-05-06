<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include database connection
require_once 'db.php';

// Get filter and sort parameters
$filter = $_POST['filter'] ?? 'all';
$sort = $_POST['sort'] ?? 'default';

// Base query
$query = "SELECT * FROM PRODUCT";
$params = [];

// Apply filter if not 'all'
if ($filter !== 'all') {
    $query .= " WHERE C_ID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $filter);
} else {
    $stmt = mysqli_prepare($conn, $query);
}

// Apply sorting
switch ($sort) {
    case 'low-to-high':
        $query .= " ORDER BY P_Price ASC";
        break;
    case 'high-to-low':
        $query .= " ORDER BY P_Price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY P_ID DESC";
        break;
    default:
        $query .= " ORDER BY P_ID ASC";
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
foreach ($products as $product): ?>
    <div class="product-card">
        <div class="product-image-container">
            <img src="<?= htmlspecialchars($baseUrl . $product['P_Picture']) ?>" 
                 alt="<?= htmlspecialchars($product['P_Name']) ?>" 
                 loading="lazy">
            <button class="quick-view-btn" onclick="viewProduct(<?= $product['P_ID'] ?>)">
                <i class="fas fa-eye"></i> Quick View
            </button>
        </div>
        <h3><?= htmlspecialchars($product['P_Name']) ?></h3>
        <p>RM <?= number_format($product['P_Price'], 2) ?></p>
        <button onclick="viewProduct(<?= $product['P_ID'] ?>)">View Details</button>
    </div>
<?php endforeach;
?>