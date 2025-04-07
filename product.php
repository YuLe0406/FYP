<?php
include 'db.php';
include 'sidebar.php';

// Fetch categories
$category_query = "SELECT * FROM CATEGORIES";
$category_result = mysqli_query($conn, $category_query);

// Fetch products
$product_query = "
    SELECT p.P_ID, p.P_Name, p.P_Price, p.P_Picture, c.C_Name
    FROM PRODUCT p
    JOIN CATEGORIES c ON p.C_ID = c.C_ID
";
$product_result = mysqli_query($conn, $product_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="product.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .variant {
            margin-bottom: 10px;
        }

        .variant input {
            margin-right: 5px;
        }

        .remove-variant-btn {
            background: red;
            color: white;
            border: none;
            padding: 3px 6px;
            cursor: pointer;
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
            <form class="product-form" action="add_product.php" method="POST" enctype="multipart/form-data">
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
                    <input type="number" id="productPrice" name="productPrice" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="productImage">Product Image:</label>
                    <input type="file" id="productImage" name="productImage" accept="image/*" required>
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
                    <th>Product ID</th>
                    <th>Category</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Variants</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($product_result)) { ?>
                    <tr>
                        <td><?= $row['P_ID'] ?></td>
                        <td><?= htmlspecialchars($row['C_Name']) ?></td>
                        <td><?= htmlspecialchars($row['P_Name']) ?></td>
                        <td>RM<?= number_format($row['P_Price'], 2) ?></td>
                        <td><img src="<?= htmlspecialchars($row['P_Picture']) ?>" alt="Product Image" width="50"></td>
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
                            <button class="add-variants-btn" data-product-id="<?= $row['P_ID'] ?>">Add Variants</button>

                            <form action="delete_product.php" method="POST" style="display:inline;">
                                <input type="hidden" name="productId" value="<?= $row['P_ID'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>

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

<!-- Modal Template -->
<div id="variantModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Add Variants</h2>
        <form class="variants-form">
            <input type="hidden" id="modalProductId" name="productId">
            <div class="variant-fields">
                <div class="variant">
                    <input type="text" name="variantColor[]" placeholder="Color" required>
                    <input type="text" name="variantSize[]" placeholder="Size" required>
                    <input type="number" name="variantQuantity[]" placeholder="Quantity" required>
                    <button type="button" class="remove-variant-btn">Remove</button>
                </div>
            </div>
            <button type="button" class="add-variant-btn">Add Variant</button>
            <button type="submit" class="submit-btn">Save Variants</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('variantModal');
        const closeModal = modal.querySelector('.close-modal');

        document.querySelectorAll('.add-variants-btn').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                document.getElementById('modalProductId').value = productId;
                modal.style.display = 'block';
            });
        });

        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        modal.querySelector('.add-variant-btn').addEventListener('click', () => {
            const container = modal.querySelector('.variant-fields');
            const variant = document.createElement('div');
            variant.className = 'variant';
            variant.innerHTML = `
                <input type="text" name="variantColor[]" placeholder="Color" required>
                <input type="text" name="variantSize[]" placeholder="Size" required>
                <input type="number" name="variantQuantity[]" placeholder="Quantity" required>
                <button type="button" class="remove-variant-btn">Remove</button>
            `;
            container.appendChild(variant);
        });

        modal.querySelector('.variant-fields').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-variant-btn')) {
                e.target.parentElement.remove();
            }
        });

        modal.querySelector('.variants-form').addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Variants saved! [You can now hook this up to PHP to save to database]');
            modal.style.display = 'none';
        });
    });
</script>
</body>
</html>
