<?php
session_start();

// ฟังก์ชันเพื่อเพิ่มสินค้าไปยังตะกร้า
function addToCart($productId, $quantity)
{
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// ฟังก์ชันเพื่อคำนวณจำนวนสินค้าในตะกร้า
function getCartItemCount()
{
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 1;
}

// เช็คคำขอ POST และจัดการเพิ่มสินค้าไปยังตะกร้า
if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $productId = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    addToCart($productId, $quantity);

    // ส่งข้อมูล JSON กลับไป
    //header('Content-Type: application/json');
    //echo json_encode(['itemCount' => getCartItemCount()]);
    //header("location: user.php");
    //exit();

    // รีไดเร็กไปยังหน้า user.php
    header("location: ../shoping_cart.php");
    exit();

   

}
