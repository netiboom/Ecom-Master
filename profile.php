<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include './Condatabase/database_shop.php';

if (!isset($_SESSION['id'])) {
    // ถ้าไม่ได้เข้าสู่ระบบ ให้เด้งไปที่หน้า login.php
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['id'];

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ไม่พบข้อมูลผู้ใช้";
    exit;
}

// ดึงข้อมูลเพิ่มเติม
$sql = "SELECT id, firstname, role, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "ไม่พบข้อมูลผู้ใช้";
    exit;
}

function get_total_items_in_cart()
{
    if (isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    } else {
        return 0;
    }
}


$sql = "SELECT * FROM products";
$result2 = $conn->query($sql);

// ดึงหมวดหมู่
$sql = "SELECT * FROM category";
$stmt = $conn->query($sql);
$resultcategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงสินค้าทั้งหมด
$sql = "SELECT * FROM products";
$stmt = $conn->query($sql);
$resultsearch = $stmt->fetchAll(PDO::FETCH_ASSOC);

// กรองสินค้าตามหมวดหมู่
$sql = "SELECT * FROM products WHERE 1=1";

if (isset($_GET['category'])) {
    $category = $_GET['category'];
    $sql .= " AND category LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['%' . $category . '%']);
    $resultsearch = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->query($sql);
    $resultsearch = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://poseidon-code.github.io/supacons/dist/supacons.all.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

    <!------AOS ANIMATION  -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!------ SWIPER JS ------->

    <!-- นำเข้า Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <!-- นำเข้า Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>



    <!----------FONT ----------->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=Itim&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="">

    <?php
    // ฟังก์ชันสำหรับเพิ่มสินค้าไปยังตะกร้า
    function addToCart($productId, $quantity = 1)
    {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity; // เพิ่มจำนวนถ้าสินค้าอยู่ในตะกร้าแล้ว
        } else {
            $_SESSION['cart'][$productId] = $quantity; // เพิ่มสินค้าใหม่
        }
    }

    // ฟังก์ชันสำหรับลดจำนวนสินค้าลง
    function updateCart($productId, $quantity)
    {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]); // ลบสินค้าออกจากตะกร้าถ้าจำนวนเป็น 0 หรือ น้อยกว่า 0
        } else {
            $_SESSION['cart'][$productId] = $quantity; // อัปเดตจำนวนสินค้า
        }
    }

    // ฟังก์ชันสำหรับลบสินค้าออกจากตะกร้า
    function removeFromCart($productId)
    {
        unset($_SESSION['cart'][$productId]);
    }
    function getCartItemCount()
    {
        if (!empty($_SESSION['cart'])) {
            return count($_SESSION['cart']); // นับจำนวนรายการในตะกร้า
        }
        return 0; // ถ้าตะกร้าว่าง
    }
    // เรียกใช้ฟังก์ชัน
    $itemCount = getCartItemCount();
    // ตรวจสอบการเรียกใช้งานเพื่อเพิ่มสินค้าลงในตะกร้า
    if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
        $productId = $_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        addToCart($productId, $quantity);
    }
    ?>

    <header class="fixed w-full h-auto z-10" style="transform: translateY(-150px);">
        <nav id="topHead" class="top-head w-full h-[38px] bg-[#FF5733]" style="font-family: 'Itim', cursive;">
            <div class="top-head-con max-w-[1100px] h-[38px] mx-auto flex justify-between items-center px-4 text-white">
                <div class="text-name text-[10px] sm:text-[15px]">
                    <h6>PomShop</h6>
                </div>
                <div class="icon-user">
                    <a href="#">
                        <?php echo $row["firstname"] ?>
                        <i class="fa-solid fa-user ml-[10px]"></i>
                    </a>
                </div>
            </div>
        </nav>


        <nav class="bg-white border-b border-gray-200  w-full z-10">
            <div class="max-w-[1140px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo Section -->
                    <div class="flex items-center" style="font-family: 'Itim', cursive;">
                        <a href="#" class="text-xl sm:text-2xl font-bold text-gray-900">Welcome</a>
                    </div>

                    <!-- Menu Section -->
                    <div class="menu-sec hidden md:flex space-x-[40px] items-center" style="font-family: 'Itim', cursive; font-weight: bold;">
                        <a href="./user/user.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Home</a>
                        <a href="product_list.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Products</a>

                        <a href="contact.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Contact us</a>
                    </div>

                    <!-- Cart Icon Section -->
                    <div class="flex items-center space-x-4" style="font-family: 'Itim', cursive;">
                        <div class="relative group">
                            <button id="services-btn" class="text-gray-700 hover:text-[#FF5733] focus:outline-none">
                                account
                                <svg class="w-5 h-5 ml-1 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 01.11.93l-.11.12L10 12.38l4.77-4.77a.75.75 0 01.98-.08l.09.08c.27.27.3.7.07.99l-.08.09-5.5 5.5a.75.75 0 01-.98.07l-.09-.07-5.5-5.5a.75.75 0 01.98-1.13z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div id="dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md py-2 z-20 hidden group-hover:block">
                                <a href="user_order.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-regular fa-bag-shopping"></i> My orders</a>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-regular fa-user"></i> Profile</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                            </div>
                        </div>
                        <a href="shoping_cart.php" class="relative w-[70px] h-full flex items-center justify-center bg-[#FF5733] text-white hover:text-[#000]">
                            <i class="fa-regular fa-cart-shopping relative text-[20px]"></i>
                            <span class="text-black flex items-center justify-center text-xs font-bold absolute w-[7px] h-[7px] p-2 bg-white rounded-full" style="top: 10px; right: 10px"><?php echo $itemCount; ?></span>
                        </a>
                        <!-- Toggle Button -->
                        <button class="md:hidden focus:outline-none text-gray-700 hover:text-[#FF5733]" id="navbar-toggle">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">
                    <a href="./user/user.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Home</a>
                    <a href="product_list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Product</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">About</a>
                    <a href="contact.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Contact us</a>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Logout</a>
                </div>
            </div>
        </nav>
    </header>


    <!-- Profile Section -->
    <div class="container mx-auto mt-[150px]">
        <div class="max-w-xl mx-auto bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">User Profile</h2>

            <!-- Profile Information -->
            <div class="mb-4">
                <label class="block text-gray-600 text-sm font-semibold mb-2">First Name</label>
                <div class="bg-gray-100 p-2 rounded-md border border-gray-300"> <?php echo $row["firstname"] ?></div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-600 text-sm font-semibold mb-2">Email</label>
                <div class="bg-gray-100 p-2 rounded-md border border-gray-300"> <?php echo $row["email"] ?></div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-600 text-sm font-semibold mb-2">Role</label>
                <div class="bg-gray-100 p-2 rounded-md border border-gray-300"> <?php echo $row["role"] ?></div>
            </div>

            <!-- Change Password -->
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h3>
            <form id="changePasswordForm" action="update_password.php?id=<?php echo $row["id"] ?>" method="POST" onsubmit="return handleFormSubmit(event);">
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-semibold mb-2">New Password</label>
                    <input type="password" name="new_password" class="w-full p-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-600 text-sm font-semibold mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" class="w-full p-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" class="w-full bg-[#FF5733] text-white py-2 rounded-md hover:bg-[#e04d2d] transition-colors">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script>
    function handleFormSubmit(event) {
        event.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งข้อมูลแบบปกติ

        const form = event.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // แสดง SweetAlert เมื่อเปลี่ยนรหัสผ่านสำเร็จ
                swal("สำเร็จ!", "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว!", "success").then(() => {
                    window.location.href = "profile.php"; // รีไดเร็กไปยังหน้าโปรไฟล์
                });
            } else {
                // แสดงข้อความผิดพลาด
                swal("ผิดพลาด!", data.message, "error");
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

</body>



</html>