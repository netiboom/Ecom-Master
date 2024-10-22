<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include './Condatabase/database_shop.php';

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

function get_total_items_in_cart()
{
    if (isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    } else {
        return 0;
    }
}

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
    <title>Document</title>

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
                    <a href="product_list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Prducts</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">About</a>
                    <a href="contact.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Contact us</a>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Logout</a>
                </div>
            </div>
        </nav>
        <ul class="breadcrumb">
            <div class="conbred mx-auto max-w-[1080px]" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                <li class="font-semibold text-neutral-800 ml-[20px]"><a href="./user/user.php">Home</a></li>
                <li class="font-semibold text-neutral-800"><a href="#">Contact us</a></li>



            </div>
        </ul>
    </header>






    <div class="contact-page w-full h-auto">
        <div class="container max-w-[1140px] h-full my-12 mx-auto px-2 md:px-4">

            <div class="map w-full rounded-sm overflow-hidden">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d248190.4929769031!2d100.53936158885742!3d13.598250433544736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e2a18f2f065f59%3A0x4195afd5edd8fc0a!2z4Liq4Lih4Li44LiX4Lij4Lib4Lij4Liy4LiB4Liy4Lij!5e0!3m2!1sth!2sth!4v1729434669881!5m2!1sth!2sth" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <section class="mt-10 shadow-lg p-10">

                <div class="flex justify-center">
                    <div class="text-center md:max-w-xl lg:max-w-3xl">
                        <h2 class="mb-12 px-6 text-3xl font-bold">
                            Contact us
                        </h2>
                    </div>
                </div>

                <div class="flex flex-wrap">

                    <form class="mb-12 w-full shrink-0 grow-0 basis-auto md:px-3 lg:mb-0 lg:w-5/12 lg:px-6">

                        <div class="mb-3 w-full">
                            <label class="block font-medium mb-[2px] text-[#FF5733]" htmlFor="exampleInput90">
                                Name
                            </label>
                            <input type="text" class="px-2 py-2 border w-full outline-none rounded-md" id="exampleInput90" placeholder="Name" />
                        </div>

                        <div class="mb-3 w-full">
                            <label class="block font-medium mb-[2px] text-[#FF5733]" htmlFor="exampleInput90">
                                Email
                            </label>
                            <input type="email" class="px-2 py-2 border w-full outline-none rounded-md" id="exampleInput90"
                                placeholder="Enter your email address" />
                        </div>

                        <div class="mb-3 w-full">
                            <label class="block font-medium mb-[2px] text-[#FF5733]" htmlFor="exampleInput90">
                                Message
                            </label>
                            <textarea class="px-2 py-2 border rounded-[5px] w-full outline-none" name="" id=""></textarea>
                        </div>

                        <button type="button"
                            class="mb-6 inline-block w-full rounded bg-[#FF5733] px-6 py-2.5 font-medium uppercase leading-normal text-white hover:shadow-md hover:bg-[#FF5733]">
                            Send
                        </button>

                    </form>

                    <div class="w-full shrink-0 grow-0 basis-auto lg:w-7/12">
                        <div class="flex flex-wrap">
                            <div class="mb-12 w-full shrink-0 grow-0 basis-auto md:w-6/12 md:px-3 lg:px-6">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="inline-block rounded-md bg-teal-400-100 p-4 text-[#FF5733]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 014.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 00-.38 1.21 12.035 12.035 0 007.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 011.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 01-2.25 2.25h-2.25z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6 grow">
                                        <p class="mb-2 font-bold">
                                            Technical support
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum dolor sit,
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum, dolor sit
                                        </p>
                                    </div>
                                </div>
                            </div>



                            <div class="mb-12 w-full shrink-0 grow-0 basis-auto md:w-6/12 md:px-3 lg:px-6">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="inline-block rounded-md bg-teal-400-100 p-4 text-[#FF5733]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 014.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 00-.38 1.21 12.035 12.035 0 007.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 011.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 01-2.25 2.25h-2.25z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6 grow">
                                        <p class="mb-2 font-bold">
                                            Technical support
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum dolor sit,
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum, dolor sit
                                        </p>
                                    </div>
                                </div>
                            </div>



                            <div class="mb-12 w-full shrink-0 grow-0 basis-auto md:w-6/12 md:px-3 lg:px-6">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="inline-block rounded-md bg-teal-400-100 p-4 text-[#FF5733]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 014.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 00-.38 1.21 12.035 12.035 0 007.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 011.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 01-2.25 2.25h-2.25z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6 grow">
                                        <p class="mb-2 font-bold">
                                            Technical support
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum dolor sit,
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum, dolor sit
                                        </p>
                                    </div>
                                </div>
                            </div>



                            <div class="mb-12 w-full shrink-0 grow-0 basis-auto md:w-6/12 md:px-3 lg:px-6">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="inline-block rounded-md bg-teal-400-100 p-4 text-[#FF5733]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 014.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 00-.38 1.21 12.035 12.035 0 007.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 011.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 01-2.25 2.25h-2.25z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6 grow">
                                        <p class="mb-2 font-bold">
                                            Technical support
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum dolor sit,
                                        </p>
                                        <p class="text-neutral-500 ">
                                            Lorem ipsum, dolor sit
                                        </p>
                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>

                </div>
            </section>
        </div>


    </div>


    <footer class="bg-[#F3F2EE]" style="font-family: 'Itim', cursive;">
        <div class="px-4 pt-16 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div class="grid gap-10 row-gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <a href="/" aria-label="Go home" title="Company" class="inline-flex items-center">
                        <svg class="w-8 text-deep-purple-accent-400" viewBox="0 0 24 24" stroke-linejoin="round" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" stroke="currentColor" fill="none">
                            <rect x="3" y="1" width="7" height="12"></rect>
                            <rect x="3" y="17" width="7" height="6"></rect>
                            <rect x="14" y="1" width="7" height="6"></rect>
                            <rect x="14" y="11" width="7" height="12"></rect>
                        </svg>
                        <span class="ml-2 text-xl font-bold tracking-wide text-gray-800 uppercase">Lorem, ipsum dolor.</span>
                    </a>

                </div>


            </div>
            <div class="flex flex-col-reverse justify-between pt-5 pb-10 border-t lg:flex-row">
                <p class="text-sm text-gray-600">
                    © Copyright 2020 Lorem Inc. All rights reserved.
                </p>
                <ul class="flex flex-col mb-3 space-y-2 lg:mb-0 sm:space-y-0 sm:space-x-5 sm:flex-row">
                    <li>
                        <a href="/" class="text-sm text-gray-600 transition-colors duration-300 hover:text-deep-purple-accent-400">F.A.Q</a>
                    </li>
                    <li>
                        <a href="/" class="text-sm text-gray-600 transition-colors duration-300 hover:text-deep-purple-accent-400">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="/" class="text-sm text-gray-600 transition-colors duration-300 hover:text-deep-purple-accent-400">Terms &amp; Conditions</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>