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

if (!isset($_GET['id'])) {
    header('Location: ./user/user.php');
    exit;
}

$product_id = $_GET['id'];

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


// ดึงข้อมูลการสั่งซื้อและรายละเอียดสินค้าเฉพาะ user_id ของคุณ
$sql = "
    SELECT 
        orders.id AS order_id, 
        orders.fullname, 
        orders.address, 
        orders.phoneNumber, 
        orders.slip, 
        orders.total,
        order_items.product_name, 
        order_items.product_price, 
        order_items.quantity,
        order_items.product_image
    FROM 
        orders
    JOIN 
        order_items 
    ON 
        orders.id = order_items.order_id
    WHERE 
        order_items.order_id = ?";

// เตรียมและ execute คำสั่ง SQL ด้วย PDO
$stmt = $conn->prepare($sql);
$stmt->execute([$product_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($result) {
    $shippingCost = 40;
    $totalPrice = 0;

    // ลูปผ่านแต่ละแถวของผลลัพธ์
    foreach ($result as $row) {
        $totalPrice += $row['product_price'] * $row['quantity'];  // คำนวณราคารวมของแต่ละรายการ
    }

    // ลบค่าจัดส่งจากราคารว

    // คำนวณราคารวมรวมค่าจัดส่ง
    $totalPriceWithShipping = $totalPrice + $shippingCost;

    // แสดงผลรวมทั้งหมด

} else {
    echo "ไม่มีข้อมูลการสั่งซื้อสำหรับ order_id นี้";
}


// $sql = "
//     SELECT 
//         orders.id AS order_id, 
//         orders.fullname, 
//         orders.address, 
//         orders.order_custom_no,
//         orders.transport,
//         orders.payment,
//         orders.orderNumber,
//         orders.phoneNumber, 
//         orders.slip, 
//         order_items.product_name, 
//         order_items.product_price, 
//         order_items.quantity,
//         order_items.product_image
//     FROM 
//         orders
//     JOIN 
//         order_items 
//     ON 
//         orders.id = order_items.order_id
//     WHERE 
//          order_items.order_id = ?";

// // เตรียมและ execute คำสั่ง SQL ด้วย PDO
// $stmt = $conn->prepare($sql);
// $stmt->execute([$product_id]);
// $address = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$product_id]);
$address = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

</head>

<body>

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
                        <?php echo $user["firstname"] ?>
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
                <li class="font-semibold text-neutral-800"><a href="#">Order</a></li>



            </div>
        </ul>
    </header>




    <div class="w-full h-full">
        <div class=" py-14 px-4 md:px-6 2xl:px-20 2xl:container 2xl:mx-auto">

            <div class="max-w-[1070px] h-auto mx-auto flex justify-start item-start space-y-2 flex-col" style="font-family: 'Itim', cursive;">
                <?php foreach ($address as $row) : ?>
                    <h1 class="text-xl dark:text-white lg:text-2xl font-semibold leading-7 lg:leading-9 text-gray-800">Order #<?php echo htmlspecialchars($row['order_custom_no']); ?></h1>
                <?php endforeach ?>
            </div>
            <div class="max-w-[1140px] h-auto mx-auto mt-10 flex flex-col xl:flex-row jusitfy-center items-stretch w-full xl:space-x-8 space-y-4 md:space-y-6 xl:space-y-0">
                <div class="flex flex-col justify-start items-start w-full space-y-4 md:space-y-6 xl:space-y-8">
                    <?php if ($result) : ?>
                        <div class="flex flex-col justify-start items-start dark:bg-gray-800 bg-gray-50 px-4 py-4 md:py-6 md:p-6 xl:p-8 w-full">

                            <p class="text-lg md:text-xl dark:text-white font-semibold leading-6 xl:leading-5 text-gray-800" style="font-family: 'Itim', cursive;">Customer’s Cart</p>
                            <?php foreach ($result as $row) : ?>

                                <div class="mt-4 md:mt-6 flex flex-col md:flex-row justify-start items-start md:items-center md:space-x-6 xl:space-x-8 w-full" style="font-family: 'Itim', cursive;">
                                    <div class="pb-4 md:pb-8 w-full md:w-40">
                                        <img class="w-full hidden md:block" src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="dress" />
                                        <img class="w-full md:hidden" src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="dress" />
                                    </div>
                                    <div class="border-b border-gray-200 md:flex-row flex-col flex justify-between items-start w-full pb-8 space-y-4 md:space-y-0">
                                        <div class="w-full flex flex-col justify-start items-start space-y-8">
                                            <h3 class="text-xl dark:text-white xl:text-xl font-semibold leading-6 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                            <div class="flex justify-start items-start flex-col space-y-2">
                                                <p class="text-sm dark:text-white leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;"></span> x <?php echo htmlspecialchars($row['quantity']); ?></p>

                                            </div>
                                        </div>
                                        <div class="flex space-x-8 items-end w-full">
                                            <p class="text-base dark:text-white xl:text-lg font-semibold leading-6 text-gray-800"><i class="fa-solid fa-baht-sign"></i> <?php echo htmlspecialchars($row['product_price']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>



                        </div>

                        <div class="flex justify-center flex-col md:flex-row flex-col items-stretch w-full space-y-4 md:space-y-0 md:space-x-6 xl:space-x-8">
                            <div class="flex flex-col px-4 py-6 md:p-6 xl:p-8 w-full bg-gray-50 dark:bg-gray-800 space-y-6">
                                <h3 class="text-xl dark:text-white font-semibold leading-5 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">ยอดรวม</h3>
                                <div class="flex justify-center items-center w-full space-y-4 flex-col border-gray-200 border-b pb-4">
                                    <div class="flex justify-between w-full">
                                        <p class="text-base dark:text-white leading-4 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">ราคา</p>
                                        <p class="text-base dark:text-gray-300 leading-4 text-gray-600" style="font-family: 'Itim', cursive;"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($totalPrice, 2); ?></p>
                                    </div>

                                    <div class="flex justify-between items-center w-full">
                                        <p class="text-base dark:text-white leading-4 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">ค่าจัดส่ง</p>
                                        <p class="text-base dark:text-gray-300 leading-4 text-gray-600" style="font-family: 'Itim', cursive;"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($shippingCost, 2); ?></p>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center w-full">
                                    <p class="text-base dark:text-white font-semibold leading-4 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">รวม</p>
                                    <p class="text-base dark:text-gray-300 font-semibold leading-4 text-gray-600" style="font-family: 'Itim', cursive;"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($totalPriceWithShipping, 2); ?></p>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center px-4 py-6 md:p-6 xl:p-8 w-full bg-gray-50 dark:bg-gray-800 space-y-6" style="font-family: 'Itim', cursive;">
                                <h3 class="text-xl dark:text-white font-semibold leading-5 text-gray-800">Shipping</h3>
                                <div class="flex justify-between items-start w-full">
                                    <div class="flex justify-center items-center space-x-4">
                                        <div class="w-8 h-8">
                                            <img class="w-full h-full" alt="logo" src="https://i.ibb.co/L8KSdNQ/image-3.png" />
                                        </div>
                                        <div class="flex flex-col justify-start items-center">
                                            <?php foreach ($address as $row) : ?>
                                                <p class="text-lg leading-6 dark:text-white font-semibold text-gray-800"><?php echo htmlspecialchars($row['transport']); ?>(ขนส่ง)<br /><span class="font-normal text-[15px]"><?php echo htmlspecialchars($row['orderNumber']); ?></span></p>
                                            <?php endforeach ?>
                                        </div>
                                    </div>

                                </div>
                                <div class="w-full flex justify-center items-center">
                                    <button onclick="window.open('https://maayoung.com/', '_blank')" class="hover:bg-black dark:bg-white dark:text-gray-800 dark:hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 py-5 w-96 md:w-full bg-gray-800 text-base font-medium leading-4 text-white">
                                        View Carrier Details
                                    </button>
                                </div>


                            </div>
                        </div>
                </div>

                <?php foreach ($address as $row) : ?>
                    <div class="bg-gray-50 dark:bg-gray-800 w-full xl:w-96 flex justify-between items-center md:items-start px-4 py-6 md:p-6 xl:p-8 flex-col" style="font-family: 'Itim', cursive;">
                        <h3 class="text-xl dark:text-white font-semibold leading-5 text-gray-800">Address</h3>

                        <div class="flex flex-col md:flex-row xl:flex-col justify-start items-stretch h-full w-full md:space-x-6 lg:space-x-8 xl:space-x-0">

                            <div class="flex flex-col justify-start items-start flex-shrink-0">
                                <div class="flex justify-center text-gray-800 dark:text-white md:justify-start items-center space-x-4 py-4 border-b border-gray-200 w-full">

                                    <p class="cursor-pointer text-xl leading-5 "><?php echo htmlspecialchars($row['fullname']); ?></p>
                                </div>
                            </div>

                            <div class="flex justify-between xl:h-full items-stretch w-full flex-col mt-6 md:mt-0">
                                <div class="flex justify-center md:justify-start xl:flex-col flex-col md:space-x-6 lg:space-x-8 xl:space-x-0 space-y-4 xl:space-y-12 md:space-y-0 md:flex-row items-center md:items-start">
                                    <div class="flex justify-center md:justify-start items-center md:items-start flex-col space-y-4 xl:mt-8">
                                        <p class="text-base dark:text-white font-semibold leading-4 text-center md:text-left text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">ที่อยู่</p>
                                        <p class="w-48 lg:w-full dark:text-gray-300 xl:w-48 text-center md:text-left text-md leading-5 text-gray-600" style="font-family: 'IBM Plex Sans Thai', sans-serif;"><?php echo htmlspecialchars($row['address']); ?><br><?php echo htmlspecialchars($row['phoneNumber']); ?></p>
                                    </div>

                                    <div class="flex justify-center md:justify-start items-center md:items-start flex-col space-y-4 xl:mt-8">
                                        <p class="text-base dark:text-white font-semibold leading-4 text-center md:text-left text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">ช่องทางการชำระ</p>
                                        <p class="w-48 lg:w-full dark:text-gray-300 xl:w-48 text-center md:text-left text-md leading-5 text-gray-600" style="font-family: 'IBM Plex Sans Thai', sans-serif;"><?php echo htmlspecialchars($row['payment']); ?></p>
                                    </div>

                                </div>

                            </div>
                        </div>



                    </div>
                <?php endforeach ?>

            </div>

            <div class="state w-full h-auto" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                <div class="con-state max-w-[1000px] mx-auto mt-10">
                    <ul class="steps">

                    </ul>
                </div>
            </div>


        </div>

    </div>


<?php else : ?>
    <p>ไม่มีข้อมูลการสั่งซื้อสำหรับ user_id นี้</p>
<?php endif; ?>





<script src="./assets/js/main.js"></script>
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