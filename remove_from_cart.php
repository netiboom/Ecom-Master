<?php
session_start();

// ฟังก์ชันสำหรับลบสินค้าออกจากตะกร้า
function removeFromCart($productId)
{
    unset($_SESSION['cart'][$productId]);
}

if (isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    removeFromCart($productId);
    header('Location: shoping_cart.php'); // เปลี่ยนเส้นทางไปยังหน้าตะกร้าสินค้า
    exit();
}
