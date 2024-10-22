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
$stmtuser = $conn->prepare($sql);
$stmtuser->execute([$user_id]);
$row = $stmtuser->fetch(PDO::FETCH_ASSOC);

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
        orders.quantt,
        orders.order_custom_no,
        orders.total,
        orders.status,
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
        orders.user_id = ?
   
";


// เตรียมและ execute คำสั่ง SQL ด้วย PDO
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$address = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($address) {
    $shippingCost = 40;
    $totalPrice = 0;

    // ลูปผ่านแต่ละแถวของผลลัพธ์
    foreach ($address as $row) {
        $totalPrice += floatval($row['product_price']) * intval($row['quantity']);
    }

    $totalPriceWithShipping = $totalPrice + $shippingCost;
} else {
}


$sql = "
    SELECT COUNT(*) AS totalstatus 
    FROM orders 
    WHERE user_id = :user_id AND status = 'รอตรวจสอบ'";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['totalstatus'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $count = 0; // ค่าเริ่มต้นหากเกิดข้อผิดพลาด
}


$sql = "
    SELECT COUNT(*) AS totalstatus 
    FROM orders 
    WHERE user_id = :user_id AND status = 'ผู้ส่งกำลังเตรียมจัดส่ง'";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count1 = $result['totalstatus'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $count1 = 0; // ค่าเริ่มต้นหากเกิดข้อผิดพลาด
}

$sql = "
    SELECT COUNT(*) AS totalstatus 
    FROM orders 
    WHERE user_id = :user_id AND status = 'อยู่ระหว่างจัดส่ง'";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count2 = $result['totalstatus'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $count2 = 0; // ค่าเริ่มต้นหากเกิดข้อผิดพลาด
}


$sql = "
    SELECT COUNT(*) AS totalstatus 
    FROM orders 
    WHERE user_id = :user_id AND status = 'จัดส่งสำเร็จ'";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count3 = $result['totalstatus'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $count3 = 0; // ค่าเริ่มต้นหากเกิดข้อผิดพลาด
}


//ผู้ส่งกำลังเตรียมจัดส่ง
//อยู่ระหว่างจัดส่ง
//จัดส่งสำเร็จ

// ดึงข้อมูลการสั่งซื้อทั้งหมดเฉพาะ user_id นี้
// $sql = "SELECT * FROM orders WHERE user_id = ?";
// $stmt = $conn->prepare($sql);
// $stmt->execute([$user_id]);
// $address = $stmt->fetchAll(PDO::FETCH_ASSOC);




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
//         orders.user_id = ?";

// // เตรียมและ execute คำสั่ง SQL ด้วย PDO
// $stmt = $conn->prepare($sql);
// $stmt->execute([$user_id]);
// $address = $stmt->fetchAll(PDO::FETCH_ASSOC);


// $sql = "SELECT * FROM orders WHERE user_id = ?";
// $stmt = $conn->prepare($sql);
// $stmt->execute([$user_id]);
// $address = $stmt->fetchAll(PDO::FETCH_ASSOC);

// if ($result) {
//     foreach ($result as $row) {
//         // แสดงข้อมูลการสั่งซื้อ
//         echo "Order ID: " . $row['order_id'] . "<br>";
//         echo "Full Name: " . $row['fullname'] . "<br>";
//         echo "Address: " . $row['address'] . "<br>";
//         echo "Phone Number: " . $row['phoneNumber'] . "<br>";
//         echo "Slip: " . $row['slip'] . "<br>";

//         // แสดงรายละเอียดสินค้าที่สั่งซื้อ
//         echo "Product Name: " . $row['product_name'] . "<br>";
//         echo "Product Price: " . $row['product_price'] . "<br>";
//         echo "Quantity: " . $row['quantity'] . "<br>";
//         echo "<hr>";
//     }
// } else {
//     echo "ไม่มีข้อมูลการสั่งซื้อสำหรับ user_id นี้";
// }

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
                <li class="font-semibold text-neutral-800"><a href="#">Orders</a></li>



            </div>
        </ul>
    </header>



    <div class="max-w-[1090px] mx-auto mt-[70px] px-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6" style="font-family: 'Itim', cursive;"><span class="text-[#FF5733]">My Orders</span></h1>

        <div class="status-tab max-w-[1140px] my-[50px] mx-auto h-auto bg-[#FF5733] rounded-md shadow-lg p-4">
            <div class="con grid grid-cols-2 md:grid-cols-4 gap-4 mx-auto">

                <div class="status-list flex flex-col items-center justify-center w-full h-[100px] p-[10px] bg-white rounded-lg shadow-md relative">
                    <a href="#" class=""><i class="fa-solid fa-timer text-[24px]"></i></a>
                    <div class="box-status w-[25px] h-[25px] rounded-full bg-neutral-800 flex items-center justify-center absolute top-2 right-2">
                        <span class="text-white text-[10px]"><?= htmlspecialchars($count); ?></span>
                    </div>
                </div>

                <div class="status-list flex flex-col items-center justify-center w-full h-[100px] p-[10px] bg-white rounded-lg shadow-md relative">
                    <a href="#" class=""><i class="fa-solid fa-truck text-[24px]"></i></a>
                    <div class="box-status w-[25px] h-[25px] rounded-full bg-neutral-800 flex items-center justify-center absolute top-2 right-2">
                        <span class="text-white text-[10px]"><?= htmlspecialchars($count1); ?></span>
                    </div>
                </div>

                <div class="status-list flex flex-col items-center justify-center w-full h-[100px] p-[10px] bg-white rounded-lg shadow-md relative">
                    <a href="#" class=""><i class="fa-sharp fa-solid fa-truck-fast text-[24px]"></i></a>
                    <div class="box-status w-[25px] h-[25px] rounded-full bg-neutral-800 flex items-center justify-center absolute top-2 right-2">
                        <span class="text-white text-[10px]"><?= htmlspecialchars($count2); ?></span>
                    </div>
                </div>

                <div class="status-list flex flex-col items-center justify-center w-full h-[100px] p-[10px] bg-white rounded-lg shadow-md relative">
                    <a href="#" class=""><i class="fa-regular fa-square-check text-[24px]"></i></a>
                    <div class="box-status w-[25px] h-[25px] rounded-full bg-neutral-800 flex items-center justify-center absolute top-2 right-2">
                        <span class="text-white text-[10px]"><?= htmlspecialchars($count3); ?></span>
                    </div>
                </div>

            </div>
        </div>


        <?php if ($address): ?>
            <div class="grid grid-cols-2 sm:grid-cols-1 gap-4">
                <?php
                $shownOrders = []; // สร้างอาร์เรย์สำหรับเก็บค่า order_custom_no ที่เคยแสดงแล้ว
                foreach ($address as $row):
                    // ตรวจสอบว่า order_custom_no นี้ถูกแสดงไปแล้วหรือยัง
                    if (!in_array($row['order_custom_no'], $shownOrders)):
                        $shownOrders[] = $row['order_custom_no']; // ถ้าไม่เคยแสดง ให้บันทึกค่า order_custom_no ลงในอาร์เรย์
                ?>
                        <!-- Start of Order Card -->
                        <div class="bg-white shadow-md rounded-lg p-4 flex flex-col md:flex-row md:items-center" style="font-family: 'Itim', cursive;">
                            <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" class="w-full md:w-32 h-32 object-cover rounded-md">
                            <div class="flex-1 mt-4 md:mt-0 md:ml-4">
                                <h2 class="text-[14px] font-semibold text-[#FF5733]">orders :<?php echo htmlspecialchars($row['order_custom_no']); ?></h2>
                                <div class="flex items-center mt-2">
                                    <span class="text-gray-700 mr-[10px]">x <?php echo htmlspecialchars($row['quantt']); ?></span>
                                    <span class="text-gray-800 font-bold text-[12px]"><i class="fa-solid fa-baht-sign"></i> <?php echo number_format($row['total']); ?></span>
                                </div>
                                <span><?php echo htmlspecialchars($row['status']); ?></span>
                            </div>
                            <a href="view_order.php?id=<?php echo htmlspecialchars($row['order_id']); ?>" class="mt-4 md:mt-0 md:ml-4 bg-[#FF5733] text-white text-[13px] px-4 py-2 rounded-md hover:bg-blue-600 cursor-pointer">View</a>
                        </div>
                        <!-- End of Order Card -->
                <?php
                    endif;
                endforeach; ?>
            </div>
        <?php else: ?>
            <p>ไม่มีข้อมูลการสั่งซื้อ</p>
        <?php endif; ?>

    </div>





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