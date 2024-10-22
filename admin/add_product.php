<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../Condatabase/database_shop.php';

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $category = $_POST["category"];


    if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $upload_dir = "../uploadimg/"; // เส้นทางสำหรับอัปโหลดจริง
        $foldername = "uploadimg/"; // ชื่อโฟลเดอร์ที่ต้องการเพิ่มหน้าชื่อไฟล์ในฐานข้อมูล
        $file_name = basename($_FILES["image"]["name"]); // รับเฉพาะชื่อไฟล์
        $upload_file = $upload_dir . $file_name;
        $file_extension = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

        // ตรวจสอบนามสกุลไฟล์
        if (in_array($file_extension, $allowed_extensions)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_file)) {
                $image = $foldername . $file_name;  // เพิ่มชื่อโฟลเดอร์ uploadimg/ ก่อนชื่อไฟล์สำหรับบันทึกในฐานข้อมูล
            } else {
                echo "Upload failed.";
                exit();
            }
        } else {
            echo "ไม่อนุญาตให้อัปโหลดไฟล์รูปภาพนามสกุลนี้.";
            exit();
        }
    } else {
        echo "No file uploaded.";
        exit();
    }

    try {
        // เตรียมคำสั่ง SQL ด้วย PDO
        $sql = "INSERT INTO products (name, description, price, image, category) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $description, $price, $image, $category]);

        // เปลี่ยนเส้นทางไปยัง admin.php ถ้าการดำเนินการสำเร็จ
        header("Location: admin.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// ดึงข้อมูลหมวดหมู่
try {
    $sql = "SELECT * FROM category";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://poseidon-code.github.io/supacons/dist/supacons.all.css">

    <title>Product Management System</title>
</head>

<body>


    <div class="container mt-5">
        <h1>Add Product</h1>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label"
                    style="font-family: 'IBM Plex Sans Thai', sans-serif;">รูปภาพ:</label>
                <input type="file" name="image" id="image" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category">
                    <?php foreach ($categories as $row) : ?>

                        <option value="<?= $row["category_name"]; ?>"><?= $row["category_name"]; ?></option>

                    <?php endforeach; ?>

                </select>
            </div>


            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>


</body>

</html>