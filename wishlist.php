<?php
session_start();
include 'db.php';  // Database connection
include 'header.php';      // Header file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | CTRL+X</title>
    <link rel="stylesheet" href="wishlist.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

 <!-- Discount Label -->
 <div class="discount-label">
        <p>🔥 20% OFF on all items! | Free shipping for orders above RM250! 🔥</p>
    </div>


<main>
    <section class="wishlist-container">
        <h1>My Wishlist</h1>
        <div id="wishlist-items">
            <?php
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $query = "SELECT * FROM wishlist WHERE user_id = $user_id";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "
                            <div class='wishlist-item'>
                                <input type='checkbox' class='wishlist-checkbox' data-id='{$row['id']}'>
                                <img src='{$row['image']}' alt='{$row['product_name']}'>
                                <p>{$row['product_name']}</p>
                                <p>RM {$row['price']}</p>
                            </div>
                        ";
                    }
                } else {
                    echo "<p>Your wishlist is empty.</p>";
                }
            } else {
                echo "<p>Please <a href='login.php'>login</a> to view your wishlist.</p>";
            }
            ?>
        </div>
        <button id="add-to-cart-btn" disabled>Add Selected to Cart</button>
    </section>
</main>

<?php include 'footer.php'; ?>  <!-- Include Footer -->
<script src="wishlist.js"></script>
</body>
</html>
