<?php

// $conn = new PDO('mysql:host=localhost;dbname=ecom;charset=utf8', 'root', '');

// if (isset($_POST['product_ids']) && isset($_POST['quantities']) && isset($_POST['product_names']) && isset($_POST['product_images']) && isset($_POST['product_prices']) && isset($_POST['user_id'])) {
//     $productIds = $_POST['product_ids'];
//     $quantities = $_POST['quantities'];
//     $productNames = $_POST['product_names'];
//     $productImages = $_POST['product_images'];
//     $productPrices = $_POST['product_prices'];
//     $userId = $_POST['user_id'];


//     $stmt = $conn->prepare("INSERT INTO order_list_user (user_id, product_id, quantity, product_name, product_image, product_price, total_price) VALUES (:user_id, :product_id, :quantity, :product_name, :product_image, :product_price, :total_price)");

//     for ($i = 0; $i < count($productIds); $i++) {
//         $productId = $productIds[$i];
//         $quantity = $quantities[$i];
//         $productName = $productNames[$i];
//         $productImage = $productImages[$i];
//         $productPrice = (float)$productPrices[$i];
//         $totalPrice = $productPrice * (int)$quantity;


//         $stmt->execute([
//             ':user_id' => $userId,
//             ':product_id' => $productId,
//             ':quantity' => $quantity,
//             ':product_name' => $productName,
//             ':product_image' => $productImage,
//             ':product_price' => $productPrice,
//             ':total_price' => $totalPrice
//         ]);
//     }


//     unset($_SESSION['cart']);


//     header('Location: checkout.php');
//     exit();
// } else {

//     header('Location: shoping_cart.php');
//     exit();
// }


session_start(); // เริ่มต้น session

// เช็คว่าฟอร์มถูกส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เก็บข้อมูลใน session
    $_SESSION['order_data'] = [
        'product_ids' => $_POST['product_ids'],
        'quantities' => $_POST['quantities'],
        'product_names' => $_POST['product_names'],
        'product_images' => $_POST['product_images'],
        'product_prices' => $_POST['product_prices'],
        'user_id' => $_POST['user_id']
    ];

    // คุณสามารถใช้ redirect ไปยังหน้าอื่นที่ต้องการแสดงข้อมูล
    header('Location: checkout.php');
    exit();
}
