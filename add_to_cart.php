<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $_SESSION['user_id'];
    $items = $data['items'];

    foreach ($items as $wishlist_id) {
        // Get wishlist item details
        $query = "SELECT * FROM wishlist WHERE id = $wishlist_id AND user_id = $user_id";
        $result = mysqli_query($conn, $query);
        $item = mysqli_fetch_assoc($result);

        if ($item) {
            // Add to cart
            $insert_query = "INSERT INTO cart (user_id, product_id, product_name, price, image) 
                             VALUES ($user_id, {$item['product_id']}, '{$item['product_name']}', {$item['price']}, '{$item['image']}')";
            mysqli_query($conn, $insert_query);

            // Remove from wishlist
            $delete_query = "DELETE FROM wishlist WHERE id = $wishlist_id AND user_id = $user_id";
            mysqli_query($conn, $delete_query);
        }
    }

    echo json_encode(["success" => true]);
}
?>
