<?php
session_start();

// ฟังก์ชันสำหรับอัปเดตจำนวนสินค้าในตะกร้า
function updateCart($productId, $quantity)
{
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    updateCart($productId, $quantity);
    header('Location: shoping_cart.php'); // เปลี่ยนเส้นทางไปยังหน้าตะกร้าสินค้า
    exit();
}
