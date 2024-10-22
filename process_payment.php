<?php
// เชื่อมต่อฐานข้อมูล
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "ecom");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['id'])) {
    // ถ้าไม่ได้เข้าสู่ระบบ ให้เด้งไปที่หน้า login.php
    header('Location: index.php');
    exit;
}

// รับข้อมูลจากฟอร์ม
$fullnames = $_POST['fullname'];
$addresses = $_POST['address'];
$phoneNumbers = $_POST['phoneNumber'];
$productIds = $_POST['product_ids'];
$productImage = $_POST['product_image'];
$productNames = $_POST['product_names'];
$productPrices = $_POST['product_prices'];
$payment_method = $_POST['payment_method'];
$total = $_POST['total'];
$total2 = $_POST['total2'];
$quantt = $_POST['quantt'];
$quantities = $_POST['quantities'];
$slip = $_FILES['slip']['name'];

$orderCustomNo = 'SMK' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// อัปโหลดสลิปการชำระเงิน
$targetDir = "slip/";
$targetFile = $targetDir . basename($_FILES["slip"]["name"]);
move_uploaded_file($_FILES["slip"]["tmp_name"], $targetFile);

// สมมติว่าคุณดึงค่า user_id จาก session
$user_id = $_SESSION['id'];

// เตรียมคำสั่ง SQL สำหรับบันทึกข้อมูลการสั่งซื้อ
$sql = "INSERT INTO orders (fullname, address, phoneNumber, slip, user_id, payment, order_custom_no, total, quantt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// บันทึกข้อมูลการสั่งซื้อ
foreach ($fullnames as $index => $fullname) {
    $stmt->bind_param("ssssissss", $fullnames[$index], $addresses[$index], $phoneNumbers[$index], $slip, $user_id, $payment_method[$index], $orderCustomNo, $total[$index], $quantt[$index]);
    $stmt->execute();

    // ดึง id ของออร์เดอร์ล่าสุด
    $orderId = $conn->insert_id;

    // เตรียมคำสั่ง SQL สำหรับบันทึกรายละเอียดสินค้าในตาราง order_items
    $sqlItems = "INSERT INTO order_items (order_id, product_id, product_image, product_name, product_price, quantity) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtItems = $conn->prepare($sqlItems);

    // บันทึกรายละเอียดสินค้าสำหรับออร์เดอร์ปัจจุบัน
    foreach ($productIds as $prodIndex => $productId) {
        // ตรวจสอบว่าข้อมูลในแต่ละ array ที่เกี่ยวกับสินค้าเป็นของคำสั่งซื้อเดียวกัน
        $stmtItems->bind_param("iissis", $orderId, $productIds[$prodIndex], $productImage[$prodIndex], $productNames[$prodIndex], $productPrices[$prodIndex], $quantities[$prodIndex]);
        $stmtItems->execute();
    }

    $stmtItems->close(); // ปิด statement หลังจากใช้เสร็จ
}


$sqlTotal = "INSERT INTO total_all (total) VALUES (?)";
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("d", $total2); // ใช้ "d" ถ้าค่า total เป็นจำนวนทศนิยม
$stmtTotal->execute();
$stmtTotal->close(); // ปิด statement หลังจากใช้เสร็จ

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();

unset($_SESSION['cart']);

header('Location: user_order.php');
exit();
