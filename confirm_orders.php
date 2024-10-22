<?php
// เชื่อมต่อฐานข้อมูล
$conn = new PDO('mysql:host=localhost;dbname=ecom;charset=utf8', 'root', '');

// ตรวจสอบว่ามีข้อมูลในฟอร์มหรือไม่
if (isset($_POST['product_ids']) && isset($_POST['quantities']) && isset($_POST['product_names']) && isset($_POST['product_images']) && isset($_POST['product_prices']) && isset($_POST['user_id'])) {
    $productIds = $_POST['product_ids'];
    $quantities = $_POST['quantities'];
    $productNames = $_POST['product_names'];
    $productImages = $_POST['product_images'];
    $productPrices = $_POST['product_prices'];
    $userId = $_POST['user_id']; // รับ user_id

    // เตรียมคำสั่ง SQL เพื่อบันทึกคำสั่งซื้อ
    $stmt = $conn->prepare("INSERT INTO order_address (user_id, product_id, quantity, product_name, product_image, product_price, total_price) VALUES (:user_id, :product_id, :quantity, :product_name, :product_image, :product_price, :total_price)");

    for ($i = 0; $i < count($productIds); $i++) {
        $productId = $productIds[$i];
        $quantity = $quantities[$i];
        $productName = $productNames[$i];
        $productImage = $productImages[$i];
        $productPrice = (float)$productPrices[$i];
        $totalPrice = $productPrice * (int)$quantity;

        // บันทึกคำสั่งซื้อไปยังฐานข้อมูล
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
            ':product_name' => $productName,
            ':product_image' => $productImage,
            ':product_price' => $productPrice,
            ':total_price' => $totalPrice
        ]);
    }

    // เคลียร์ตะกร้าหลังจากบันทึกเสร็จ
    unset($_SESSION['cart']);

    // เปลี่ยนเส้นทางไปยังหน้าขอบคุณหรือหน้าหมายเลขคำสั่งซื้อ
    header('Location: checkout.php');
    exit();
} else {
    // หากไม่มีข้อมูลในฟอร์มให้เปลี่ยนเส้นทางไปยังหน้าตะกร้าหรือแสดงข้อผิดพลาด
    header('Location: shoping_cart.php');
    exit();
}
