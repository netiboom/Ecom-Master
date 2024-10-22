<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // เริ่ม session
require_once '../Condatabase/database_shop.php';

$registration_success = false;
$email_exists = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // กำหนดค่า role ถ้าไม่ได้ระบุ
    if (empty($role)) {
        $role = "user";
    }

    // ตรวจสอบว่าอีเมลมีอยู่แล้วในฐานข้อมูลหรือไม่
    $check_email_sql = "SELECT * FROM users WHERE email = :email";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $check_email_stmt->execute();

    if ($check_email_stmt->rowCount() > 0) {
        $_SESSION['email_exists'] = true; // เก็บค่าใน session
    } else {
        // ถ้าอีเมลไม่ซ้ำ ให้ทำการลงทะเบียน
        $sql = "INSERT INTO users (firstname, role, email, password) VALUES (:firstname, :role, :email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['registration_success'] = true; // เก็บค่าใน session
            header("Location: ../index.php");
            exit();
        }
    }
    header("Location: ../index.php");
    exit();
}
