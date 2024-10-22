<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../Condatabase/database_shop.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];

            // ตรวจสอบว่าไม่มีการส่ง output ใด ๆ ก่อนหน้านี้
            if (!headers_sent()) {
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/admin.php");
                } else {
                    header("Location: ../user/user.php");
                }
                exit();
            }
        } else {
            $_SESSION['error_message'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง !";
            header("Location: ../index.php"); // Redirect ไปยังหน้าที่จะแสดง error
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
