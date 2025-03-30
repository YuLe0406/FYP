<?php
session_start();
if (isset($_POST['id']) && isset($_POST['size']) && isset($_POST['quantity'])) {
    $id = $_POST['id'];
    $size = $_POST['size'];
    $quantity = max(1, intval($_POST['quantity'])); // Prevent negative or zero quantity

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id && $item['size'] == $size) {
            $item['quantity'] = $quantity;
            break;
        }
    }

    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['quantity'] * 69.90; // Assume price (or fetch from DB)
    }

    echo json_encode(["success" => true, "new_total" => number_format($quantity * 69.90, 2), "cart_total" => number_format($total_price, 2)]);
}
?>
