<?php
session_start();

include './Condatabase/database_shop.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบข้อมูล POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ารหัสผ่านใหม่และยืนยันรหัสผ่านตรงกันหรือไม่
    if ($new_password === $confirm_password) {
        // เข้ารหัสรหัสผ่านใหม่
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // สมมติว่ามี user_id ที่ได้มาจาก session
        $id = $_GET['id'];

        try {
            // เตรียมคำสั่ง SQL สำหรับอัปเดตรหัสผ่าน
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // ผูกค่าพารามิเตอร์กับคำสั่ง SQL
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $id);

            // ทำการ execute คำสั่ง
            if ($stmt->execute()) {
                // ส่งผลลัพธ์กลับเป็น JSON
                echo json_encode(['success' => true]);
            } else {
                // ส่งผลลัพธ์เมื่อเกิดข้อผิดพลาด
                echo json_encode(['success' => false, 'message' => 'Error updating password.']);
            }
        } catch (PDOException $e) {
            // จัดการข้อผิดพลาดเมื่อมีปัญหาในการเชื่อมต่อหรืออัปเดตรหัสผ่าน
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        // ส่งผลลัพธ์เมื่อรหัสผ่านไม่ตรงกัน
        echo json_encode(['success' => false, 'message' => 'Passwords do not match!']);
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล (Optional เพราะ PDO จะปิดอัตโนมัติเมื่อสคริปต์สิ้นสุด)
$conn = null;
?>
