<?php
include 'db.php';

if (!isset($_GET['productId'])) {
    die("Product ID not specified.");
}

$productId = intval($_GET['productId']);
$editVariantId = isset($_GET['editVariantId']) ? intval($_GET['editVariantId']) : null;

$stmt = $conn->prepare("SELECT * FROM PRODUCT WHERE P_ID = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

$category_result = mysqli_query($conn, "SELECT * FROM CATEGORIES");

$variant_result = mysqli_query($conn, "
    SELECT pv.PV_ID, pv.P_Size, pv.P_Quantity, pc.COLOR_NAME, pc.PC_ID
    FROM PRODUCT_VARIANTS pv
    JOIN PRODUCT_COLOR pc ON pv.PC_ID = pc.PC_ID
    WHERE pv.P_ID = $productId
");

$img_stmt = $conn->prepare("SELECT * FROM PRODUCT_IMAGES WHERE P_ID = ?");
$img_stmt->bind_param("i", $productId);
$img_stmt->execute();
$images_result = $img_stmt->get_result();
$img_stmt->close();

$colors_result = mysqli_query($conn, "SELECT * FROM PRODUCT_COLOR");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="edit_product.css">
</head>
<body>
<div class="container">
    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Edit Product</h1>
            <a href="product.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Product List
            </a>
        </div>

        <!-- Product Update Form -->
        <form action="update_product.php" method="POST">
            <input type="hidden" name="productId" value="<?= $productId ?>">

            <label>Category:</label>
            <select name="productCategory" required>
                <?php while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                    <option value="<?= $cat['C_ID'] ?>" <?= $cat['C_ID'] == $product['C_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['C_Name']) ?>
                    </option>
                <?php } ?>
            </select>

            <label>Product Name:</label>
            <input type="text" name="productName" value="<?= htmlspecialchars($product['P_Name']) ?>" required>

            <label>Product Price:</label>
            <input type="number" name="productPrice" step="0.01" value="<?= $product['P_Price'] ?>" required>

            <button type="submit">Update Product</button>
        </form>

        <hr>

        <!-- Product Images -->
        <h2>Product Images</h2>
        <div style="display:flex; flex-wrap: wrap;">
            <?php while ($img = mysqli_fetch_assoc($images_result)) { ?>
                <div style="margin: 5px; text-align: center;">
                    <img src="<?= htmlspecialchars($img['PRODUCT_IMAGE']) ?>" width="100" alt="Product Image">
                    <form action="delete_image.php" method="POST" onsubmit="return confirm('Delete this image?')">
                        <input type="hidden" name="productId" value="<?= $productId ?>">
                        <input type="hidden" name="imageId" value="<?= $img['PI_ID'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>

        <form action="update_product_images.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="productId" value="<?= $productId ?>">
            <input type="file" name="newImages[]" multiple accept="image/*">
            <button type="submit">Upload Images</button>
        </form>

        <hr>

        <!-- Product Variants -->
        <h2>Variants</h2>
        <ul>
            <?php while ($variant = mysqli_fetch_assoc($variant_result)) { ?>
                <li class="<?= ($editVariantId == $variant['PV_ID']) ? 'variant-editing' : '' ?>">
                    <?php if ($editVariantId == $variant['PV_ID']) { ?>
                        <form action="update_variant.php" method="POST" style="flex-grow:1;">
                            <input type="hidden" name="variantId" value="<?= $variant['PV_ID'] ?>">
                            <input type="hidden" name="productId" value="<?= $productId ?>">

                            <label>Color:</label>
                            <select name="colorId" required>
                                <?php mysqli_data_seek($colors_result, 0); ?>
                                <?php while ($color = mysqli_fetch_assoc($colors_result)) { ?>
                                    <option value="<?= $color['PC_ID'] ?>" <?= $color['PC_ID'] == $variant['PC_ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($color['COLOR_NAME']) ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <label>Size:</label>
                            <input type="text" name="size" value="<?= htmlspecialchars($variant['P_Size']) ?>" required>

                            <label>Quantity:</label>
                            <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($variant['P_Quantity']) ?>" required>

                            <div class="variant-actions">
                                <button type="submit">Save</button>
                                <a href="edit_product.php?productId=<?= $productId ?>"><button type="button">Cancel</button></a>
                            </div>
                        </form>
                    <?php } else { ?>
                        <span>Color: <?= htmlspecialchars($variant['COLOR_NAME']) ?>, Size: <?= htmlspecialchars($variant['P_Size']) ?>, Quantity: <?= htmlspecialchars($variant['P_Quantity']) ?></span>
                        <div class="variant-actions">
                            <a href="edit_product.php?productId=<?= $productId ?>&editVariantId=<?= $variant['PV_ID'] ?>">
                                <button type="button">Edit</button>
                            </a>
                            <form action="delete_variant.php" method="POST" onsubmit="return confirm('Delete this variant?')">
                                <input type="hidden" name="variantId" value="<?= $variant['PV_ID'] ?>">
                                <input type="hidden" name="productId" value="<?= $productId ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>

        <!-- Add Variant Form -->
        <form action="add_variant.php" method="POST">
            <input type="hidden" name="productId" value="<?= $productId ?>">

            <label>Color:</label>
            <select name="colorId" required>
                <?php mysqli_data_seek($colors_result, 0); ?>
                <?php while ($color = mysqli_fetch_assoc($colors_result)) { ?>
                    <option value="<?= $color['PC_ID'] ?>"><?= htmlspecialchars($color['COLOR_NAME']) ?></option>
                <?php } ?>
            </select>

            <label>Size:</label>
            <input type="text" name="size" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" min="0" required>

            <button type="submit">Add Variant</button>
        </form>
    </main>
</div>
</body>
</html>
