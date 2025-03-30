<?php
session_start();
if (isset($_POST['id']) && isset($_POST['size'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) {
        return !($item['id'] == $_POST['id'] && $item['size'] == $_POST['size']);
    });

    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['quantity'] * 69.90; // Assume price (or fetch from DB)
    }

    echo json_encode(["success" => true, "cart_total" => number_format($total_price, 2)]);
}
?>
