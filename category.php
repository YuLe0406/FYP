<?php
include 'db.php'; // Connect to DB

// Add category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['categoryName'])) {
    $name = trim($_POST['categoryName']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO CATEGORIES (C_Name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: category.php");
    exit();
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
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <h1>Category Management</h1>

            <!-- Add Category -->
            <section class="add-category">
                <h2>Add New Category</h2>
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
                                <!-- For simplicity, only delete action included -->
                                <a href="?delete=<?= $row['C_ID'] ?>" class="delete-btn" onclick="return confirm('Delete this category?')">Delete</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
