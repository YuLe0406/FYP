<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <script src="wishlist.js"></script>
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

<main>
        <h1>Your Wishlist</h1>
        <a href="shop.php" class="back-to-shop">← Continue Shopping</a>     

        <section id="wishlist-container">
            <div id="wishlist-items">
            </div>
        
        <div id="wishlist-summary">
        <button id="add-selected-to-cart">Add Selected to Cart</button></div>
    </section>
    </main>

<?php include 'footer.php'; ?>  <!-- Include Footer -->

</body>
</html>
