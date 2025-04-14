<?php
include 'db.php'; // Connect to DB

// Add category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['categoryName'])) {
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

// Delete category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM CATEGORIES WHERE C_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: category.php");
    exit();
}

// Fetch categories
$result = $conn->query("SELECT * FROM CATEGORIES ORDER BY C_ID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Management</title>
    <link rel="stylesheet" href="category.css">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

            <!-- Existing Categories -->
            <section class="existing-categories">
                <h2>Existing Categories</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['C_ID']) ?></td>
                            <td><?= htmlspecialchars($row['C_Name']) ?></td>
                            <td>
                                <!-- Delete action with SweetAlert confirmation -->
                                <a href="javascript:void(0);" 
                                   class="delete-btn" 
                                   onclick="confirmDelete(<?= $row['C_ID'] ?>)">Delete</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Function to handle category deletion with SweetAlert confirmation
        function confirmDelete(categoryId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the delete URL if confirmed
                    window.location.href = `category.php?delete=${categoryId}`;
                }
            });
        }
    </script>
</body>
</html>
