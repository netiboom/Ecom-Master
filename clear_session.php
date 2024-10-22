<?php
session_start(); // เริ่มต้น session

// ตรวจสอบว่ามีข้อมูล order_data หรือไม่
if (isset($_SESSION['order_data'])) {
    // ลบข้อมูล order_data เท่านั้น
    unset($_SESSION['order_data']);
}

// เปลี่ยนเส้นทางไปยังหน้าอื่นหลังจากลบข้อมูล
header('Location: shoping_cart.php'); // เปลี่ยนเส้นทางไปยังหน้าแรกหรือหน้าอื่น
exit();
