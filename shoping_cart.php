<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once './Condatabase/database_shop.php';

if (!isset($_SESSION['id'])) {
    // ถ้าไม่ได้เข้าสู่ระบบ ให้เด้งไปที่หน้า login.php
    header('Location: index.php');
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

// ฟังก์ชันสำหรับคำนวณราคาสินค้าทั้งหมด
function getTotalPrice($cart, $conn)
{
    $total = 0;
    foreach ($cart as $productId => $quantity) {
        // ค้นหาข้อมูลสินค้าจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) { // ตรวจสอบว่าคำค้นหาสำเร็จ
            $price = (float)$product['price']; // แปลงเป็นตัวเลขทศนิยม
            $total += $price * $quantity; // คำนวณราคา
        }
    }
    return $total;
}


include './Condatabase/database_shop.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecom</title>

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

    <style>
        .shoping-cart button {
            transition: .2s;
        }

        .shoping-cart button:hover {
            transition: ease-in-out .2s;
        }
    </style>

</head>

<body class="bg-[#F3F2EE] h-screen">

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

    <header class=" w-full h-auto z-10" style="transform: translateY(0px);">
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
                    <div class="menu-sec hidden md:flex space-x-[40px] items-center" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">
                        <a href="./user/user.php" class="text-gray-700  hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Home</a>
                        <a href="product_list.php" class="text-gray-700  hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Products</a>

                        <a href="contact.php" class="text-gray-700  hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Contact us</a>
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
                                <a href="user_order.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-white "><i class="fa-regular fa-bag-shopping"></i> My orders</a>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-white "><i class="fa-regular fa-user"></i> Profile</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-white "><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
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
                <div class="px-4 pt-2 pb-3 space-y-1 sm:px-3" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">
                    <a href="./user/user.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Home</a>
                    <a href="product_list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Products</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">About</a>
                    <a href="contact.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Contact us</a>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Logout</a>
                </div>
            </div>
        </nav>
        <ul class="breadcrumb">
            <div class="conbred mx-auto max-w-[1080px]" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                <li class="font-semibold text-neutral-800 ml-[20px]"><a href="./user/user.php">Home</a></li>
                <li class="font-semibold text-neutral-800"><a href="#">Shoping cart</a></li>



            </div>
        </ul>
    </header>




    <!-- Cart Container -->
    <div class="shoping-cart w-full">
        <div class="container max-w-[1140px] mx-auto p-6">
            <h2 class="text-3xl font-bold text-gray-800 mb-6" style="font-family: 'Itim', cursive;">Shopping Cart</h2>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <table class="min-w-full divide-y divide-gray-200" style="font-family: 'Itim', cursive;">
                            <thead class="bg-gray-50" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สินค้า</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รายละเอียด</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชิ้น</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ราคา</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ยอดรวม</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลบ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">

                                <?php
                                $grandTotal = 0; // สร้างตัวแปรสำหรับยอดรวมทั้งหมด
                                foreach ($_SESSION['cart'] as $productId => $quantity) {
                                    // ค้นหาข้อมูลสินค้าจากฐานข้อมูล
                                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                    $stmt->execute([$productId]);
                                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                                    if ($product) {
                                        $name = htmlspecialchars($product['name']);
                                        $image = htmlspecialchars($product['image']);
                                        $description = htmlspecialchars($product['description']);
                                        $price = (float)$product['price'];
                                        $total = $price * (int)$quantity;

                                        $grandTotal += $total; // เพิ่มยอดรวมของสินค้านี้เข้าไปใน grandTotal
                                ?>
                                        <!-- Cart Item -->
                                        <tr class="cart-item">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="pic-product w-[80px] h-[80px]">
                                                    <img src="<?php echo $image; ?>" class="" alt="Product Image">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                                                    <?php $max_length = 40;
                                                    if (strlen($name) > $max_length) {
                                                        $name = substr($name, 0, $max_length) . '...';
                                                    }
                                                    echo htmlspecialchars($name); ?>
                                                </div>
                                                <div class="text-sm text-gray-500" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                                                    <?php

                                                    $max_length = 40;
                                                    if (strlen($description) > $max_length) {
                                                        $description = substr($description, 0, $max_length) . '...';
                                                    }
                                                    echo htmlspecialchars($description);
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="update_cart.php" method="post">
                                                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                                    <input type="number" class="w-16 p-1 border rounded" name="quantity" value="<?php echo (int)$quantity; ?>" min="1"> <button type="submit">Update</button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($price, 2); ?>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($total, 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form action="remove_from_cart.php" method="post">
                                                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                                    <button class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                </div>
                <!-- Total Amount -->
                <div class="mt-6 flex justify-between items-center" style="font-family: 'Itim', cursive;">
                    <div class="text-lg font-semibold text-gray-800">Total:</div>
                    <div class="text-lg font-semibold text-gray-800"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($grandTotal, 2); ?></div>
                </div>

                <!-- Checkout Button -->
                <div class="mt-6">
                    <form action="add_myorders.php" method="post">
                        <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
                            <?php
                            // ดึงข้อมูลสินค้าจากฐานข้อมูล
                            $stmt_product = $conn->prepare("SELECT name, image, price FROM products WHERE id = ?");
                            $stmt_product->execute([$productId]);
                            $product = $stmt_product->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <input type="hidden" name="product_ids[]" value="<?php echo $productId; ?>">
                            <input type="hidden" name="quantities[]" value="<?php echo $quantity; ?>">
                            <input type="hidden" name="product_names[]" value="<?php echo htmlspecialchars($product['name']); ?>">
                            <input type="hidden" name="product_images[]" value="<?php echo htmlspecialchars($product['image']); ?>">
                            <input type="hidden" name="product_prices[]" value="<?php echo htmlspecialchars($product['price']); ?>"> <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="w-full bg-[#FF5733] text-white font-semibold py-2 px-4 rounded hover:bg-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:ring-opacity-50" style="font-family: 'IBM Plex Sans Thai', sans-serif;">ยืนยันคำสั่งซื้อ</button>
                    </form>
                </div>



            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>

            </div>
        </div>
    </div>





    <?php
    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn = null;
    ?>
    <script src="assets/js/main.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script>
        const topHead = document.getElementById('topHead');

        // เพิ่ม event listener สำหรับ scroll
        window.addEventListener('scroll', function() {
            // ตำแหน่งที่ต้องการให้ nav หายไป
            const scrollPosition = 200; // ตัวอย่างเช่น ต้องการให้หายไปเมื่อ scroll ลงมา 200px

            // ตรวจสอบ scroll position
            if (window.scrollY > scrollPosition) {
                // เปลี่ยน CSS property ของ nav เป็น none เพื่อให้หายไป
                topHead.style.display = 'none';
            } else {
                // เปลี่ยน CSS property ของ nav เป็น block เพื่อให้แสดงอีกครั้ง
                topHead.style.display = 'block';
            }
        });
    </script>
</body>

</html>