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
                header("Location: category.php");
                exit();
            } else {
                // Set error message to be shown in SweetAlert
                $error = "Category name already exists.";
            }
        }
    }
}

// Fetch all categories
$categories = [];
$query = "SELECT * FROM CATEGORIES ORDER BY C_ID DESC";
$stmt = $conn->prepare($query);
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
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* ===== Global Styles ===== */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #f5f7fa;
        }
        
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 500px;
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

        /* ===== Category Table ===== */
        .category-view {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .category-view h3 {
            font-size: 22px;
            color: #2c3e50;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

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

        /* ===== Action Buttons ===== */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn {
            font-size: 14px;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            background-color: #3498db;
            color: #fff;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn:hover {
            background-color: #2980b9;
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
            .container {
                flex-direction: column;
            }
            
            .main-content {
                padding: 20px;
            }
            
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
                <h2><img src="https://img.icons8.com/ios-filled/24/category.png" alt="Category Icon"/> CATEGORY MANAGEMENT</h2>
                
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

            <!-- Category List -->
            <section class="category-view">
                <h3>
                    <img src="https://img.icons8.com/ios-filled/24/list.png" alt="List Icon"/>
                    All Categories
                </h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
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
                                    <div class="action-buttons">
                                        <a href="edit_category.php?id=<?= $category['C_ID'] ?>" class="edit-btn">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="empty-state">
                                    No categories found. Add your first category above.
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
</body>
</html>