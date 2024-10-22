<?php
include '../Condatabase/database_shop.php';

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $description = $_POST["description"];
  $price = $_POST["price"];



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
        $_SESSION['error_message'] = "Upload failed.";
        header("Location: admin_product.php");
        exit();
      }
    } else {
        $_SESSION['error_message'] = "ไม่อนุญาตให้อัปโหลดไฟล์รูปภาพนามสกุลนี้.";
        header("Location: admin_product.php");
      exit();
    }
  } else {
    $_SESSION['error_message'] = "No file uploaded.";
        header("Location: admin_product.php");
    exit();
  }



  try {
    // อัปเดตข้อมูลสินค้า
    $sql = "UPDATE products SET name = :name, description = :description, image = :image, price = :price WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':image', $image, PDO::PARAM_STR);
    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      header("Location: admin_product.php");
      exit();
    } else {
      echo "Error updating product.";
    }
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
}

try {
  // ดึงข้อมูลสินค้าตาม id
  $sql = "SELECT * FROM products WHERE id = :id";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $product = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$product) {
    echo "Product not found.";
    exit();
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMK FIREWORKS</title>

  <link rel="stylesheet" href="https://poseidon-code.github.io/supacons/dist/supacons.all.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

  <!------AOS ANIMATION  -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <!------ SWIPER JS ------->


  <!----------FONT ----------->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=Itim&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/style.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>

  <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Edit Product</h1>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="name" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
      </div>
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="description" name="description"><?= htmlspecialchars($product['description']); ?></textarea>
      </div>
      <div>
        <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
        <input type="file" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="image" name="image" accept="image/*" onchange="previewImage(event)">
        <img id="image-preview" class="w-[300px] mt-5" src="../<?= htmlspecialchars($product['image']); ?>" alt="Image Preview">
      </div>
      <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
        <input type="number" step="0.01" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="price" name="price" value="<?= htmlspecialchars($product['price']); ?>" required>
      </div>
      <div class="flex justify-end">
  <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" onclick="confirmUpdate()">Update Product</button>
</div>




    </form>
  </div>


  <script src="assets/js/main.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  <script>
    function previewImage(event) {
      const imagePreview = document.getElementById('image-preview');
      const file = event.target.files[0];

      if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
          imagePreview.src = e.target.result; // ตั้งค่า src ของ img เพื่อแสดงไฟล์ใหม่
        }

        reader.readAsDataURL(file); // อ่านไฟล์เป็น data URL เพื่อใช้แสดงรูป
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  function confirmUpdate() {
    Swal.fire({
      title: 'Are you sure?',
      text: "Do you want to update this product?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, update it!'
    }).then((result) => {
      if (result.isConfirmed) {
        // ส่งแบบฟอร์ม
        document.querySelector('form').submit();

        // ใช้ setTimeout เพื่อรอให้ส่งแบบฟอร์มเสร็จ ก่อนที่จะเปลี่ยนเส้นทาง
        setTimeout(() => {
          window.location.href = 'admin_product.php'; // เปลี่ยนเส้นทางไปที่ admin_product.php
        }, 1000); // 1000 มิลลิวินาที (1 วินาที) เพื่อให้เวลาสำหรับส่งแบบฟอร์ม
      }
    })
  }
</script>



</body>

</html>