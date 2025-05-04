<?php
session_start();
include 'db.php'; // Connect to DB

// Add category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle add category
    if (isset($_POST['categoryName'])) {
        $name = trim($_POST['categoryName']);
        if (!empty($name)) {
            // Check for duplicate category name
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM CATEGORIES WHERE C_Name = ?");
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count == 0) {
                // Insert new category if not duplicate
                $stmt = $conn->prepare("INSERT INTO CATEGORIES (C_Name) VALUES (?)");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $stmt->close();
                header("Location: category.php?view=active");
                exit();
            } else {
                // Set error message to be shown in SweetAlert
                $error = "Category name already exists.";
            }
        }
    }
}

// Toggle category status
if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $view = isset($_GET['view']) ? $_GET['view'] : 'active';
    
    // Get current status
    $stmt = $conn->prepare("SELECT C_Status FROM CATEGORIES WHERE C_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($currentStatus);
    $stmt->fetch();
    $stmt->close();
    
    // Toggle status
    $newStatus = $currentStatus ? 0 : 1;
    
    $updateStmt = $conn->prepare("UPDATE CATEGORIES SET C_Status = ? WHERE C_ID = ?");
    $updateStmt->bind_param("ii", $newStatus, $id);
    $updateStmt->execute();
    $updateStmt->close();
    
    header("Location: category.php?view=" . ($newStatus ? 'inactive' : 'active'));
    exit();
}

// Determine which categories to show
$view = isset($_GET['view']) ? $_GET['view'] : 'active';
$status = ($view === 'inactive') ? 1 : 0;

// Fetch categories based on view
$categories = [];
$query = "SELECT * FROM CATEGORIES WHERE C_Status = ? ORDER BY C_ID DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Management</title>
    <link rel="stylesheet" href="category.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* ===== Category Management Styling ===== */
        .add-category {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .add-category h2 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .category-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 15px;
            color: #34495e;
            font-weight: 600;
        }

        .form-group input {
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            font-size: 15px;
            background: #f9f9f9;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.2);
            outline: none;
        }

        .submit-btn {
            background: #1abc9c;
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 150px;
        }

        .submit-btn:hover {
            background: #16a085;
            transform: translateY(-1px);
        }

        /* ===== Category View Selector ===== */
        .category-view {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .view-selector {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .view-selector h3 {
            font-size: 22px;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-dropdown {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dcdde1;
            background: #f9f9f9;
            font-size: 14px;
            cursor: pointer;
        }

        /* ===== Category Table ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table thead {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        table tbody tr:hover {
            background: #f8f9fa;
            transition: background 0.2s ease;
        }

        /* ===== Status Styling ===== */
        .status-active {
            color: #2ecc71;
            font-weight: 600;
        }

        .status-inactive {
            color: #e74c3c;
            font-weight: 600;
        }

        /* ===== Action Buttons ===== */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .activate-btn,
        .deactivate-btn {
            font-size: 14px;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .activate-btn {
            background-color: #2ecc71;
        }

        .activate-btn:hover {
            background-color: #27ae60;
            transform: translateY(-1px);
        }

        .deactivate-btn {
            background-color: #e74c3c;
        }

        .deactivate-btn:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 768px) {
            table thead {
                display: none;
            }

            table, table tbody, table tr, table td {
                display: block;
                width: 100%;
            }

            table tr {
                margin-bottom: 15px;
                border: 1px solid #ecf0f1;
                border-radius: 10px;
                background: #f8f9fa;
                padding: 10px;
            }

            table td {
                padding: 10px;
                text-align: right;
                position: relative;
            }

            table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: bold;
                color: #2c3e50;
                text-transform: capitalize;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <!-- Add Category -->
            <section class="add-category">
                <h2>CATEGORY MANAGEMENT</h2>
                
                <!-- Check for error and show SweetAlert -->
                <?php if (isset($error)) { ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: '<?= htmlspecialchars($error) ?>',
                            confirmButtonText: 'OK'
                        });
                    </script>
                <?php } ?>
                
                <form class="category-form" method="POST">
                    <div class="form-group">
                        <label for="categoryName">Category Name:</label>
                        <input type="text" id="categoryName" name="categoryName" placeholder="Enter category name" required>
                    </div>
                    <button type="submit" class="submit-btn">Add Category</button>
                </form>
            </section>

            <!-- Category View Selector -->
            <section class="category-view">
                <div class="view-selector">
                    <h3>
                        <img src="<?= $view === 'active' ? 'https://img.icons8.com/ios-filled/24/checkmark.png' : 'https://img.icons8.com/ios-filled/24/cancel.png' ?>" alt="View Icon"/>
                        <?= $view === 'active' ? 'Active Categories' : 'Inactive Categories' ?>
                    </h3>
                    <select class="view-dropdown" onchange="window.location.href='?view='+this.value">
                        <option value="active" <?= $view === 'active' ? 'selected' : '' ?>>Active Categories</option>
                        <option value="inactive" <?= $view === 'inactive' ? 'selected' : '' ?>>Inactive Categories</option>
                    </select>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['C_ID']) ?></td>
                                <td><?= htmlspecialchars($category['C_Name']) ?></td>
                                <td>
                                    <?php if ($category['C_Status'] == 0): ?>
                                        <span class="status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($category['C_Status'] == 0): ?>
                                            <a href="javascript:void(0);" 
                                               class="deactivate-btn" 
                                               onclick="confirmDeactivate(<?= $category['C_ID'] ?>, '<?= $view ?>')">Deactivate</a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);" 
                                               class="activate-btn" 
                                               onclick="confirmActivate(<?= $category['C_ID'] ?>, '<?= $view ?>')">Activate</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    No <?= $view === 'active' ? 'active' : 'inactive' ?> categories found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Function to handle category deactivation with SweetAlert confirmation
        function confirmDeactivate(categoryId, currentView) {
            Swal.fire({
                title: 'Deactivate this category?',
                text: "Products in this category won't be visible to customers!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Deactivate'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `category.php?toggle_status=${categoryId}&view=${currentView}`;
                }
            });
        }

        // Function to handle category activation with SweetAlert confirmation
        function confirmActivate(categoryId, currentView) {
            Swal.fire({
                title: 'Activate this category?',
                text: "Products in this category will become visible to customers.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2ecc71',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Activate'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `category.php?toggle_status=${categoryId}&view=${currentView}`;
                }
            });
        }
    </script>
</body>
</html>