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

//ตรวจสอบว่ามีข้อมูลใน session หรือไม่
if (isset($_SESSION['order_data'])) {
  $orderDatatest = $_SESSION['order_data'];

  $totalOrderPrice = 0; // ตัวแปรสำหรับเก็บผลรวมราคาสุทธิของสินค้า

  // แสดงข้อมูลที่เก็บใน session
  foreach ($orderDatatest['product_ids'] as $index => $productId) {
    $quantity = $orderDatatest['quantities'][$index];
    $productPrice = $orderDatatest['product_prices'][$index];
    $totalPrice = $quantity * $productPrice;  // คำนวณราคาสุทธิของสินค้าแต่ละชิ้น

    // แสดงข้อมูลของแต่ละสินค้า
    // echo "Product ID: " . htmlspecialchars($productId) . "<br>";
    // echo "Quantity: " . htmlspecialchars($quantity) . "<br>";
    // echo "Product Name: " . htmlspecialchars($orderDatatest['product_names'][$index]) . "<br>";
    // echo "Product Image: <img src='" . htmlspecialchars($orderDatatest['product_images'][$index]) . "' alt='Product Image'><br>";
    // echo "Product Price: " . htmlspecialchars($productPrice) . "<br>";
    // echo "Total Price for this Product: " . htmlspecialchars($totalPrice) . "<br><br>";  // แสดงผลราคาสุทธิ

    // เพิ่มราคาสุทธิของสินค้าแต่ละตัวไปยังผลรวม
    $totalOrderPrice += $totalPrice;
  }

  // แสดงผลรวมราคาสุทธิของสินค้าทั้งหมด
  // echo "Total Order Price: " . htmlspecialchars($totalOrderPrice) . "<br>";
  // echo "User ID: " . htmlspecialchars($orderDatatest['user_id']) . "<br>";
} else {
  echo "No order data available.";
}

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


if (isset($_SESSION['order_data'])) {
  $orderData = $_SESSION['order_data'];

  // กำหนดค่าจัดส่ง
  $shippingCost = 40;

  // คำนวณราคาทั้งหมดของสินค้า
  $totalPrice = 0;
  foreach ($orderData['product_prices'] as $price) {
    $totalPrice += floatval($price);
  }

  // กำหนดตัวแปรสำหรับการรวมจำนวนสินค้า
  $totalQuantity = 0;

  // รวมจำนวนสินค้า
  foreach ($orderData['quantities'] as $quan) {
    $totalQuantity += intval($quan);
  }

  // คำนวณราคารวมพร้อมค่าจัดส่ง
  $totalPriceWithShipping = $totalOrderPrice + $shippingCost;
}

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
        <li class="font-semibold text-neutral-800"><a href="#">Check out</a></li>



      </div>
    </ul>
  </header>


  <div class="checkout w-full h-full">
    <div class="con-check max-w-[1340px] mx-auto">
      <div class=" mt-[50px] grid sm:px-10 lg:grid-cols-2 lg:px-20 xl:px-32">
        <div class="px-4 pt-8" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
          <p class="text-xl font-medium">สินค้าในตะกร้าของคุณ</p>
          <p class="text-gray-400">เช็คสินค้าก่อนยืนยันการสั่งซื้อ</p>
          <?php foreach ($orderData['product_ids'] as $index => $productId): ?>
            <?php
            $quantity = $orderData['quantities'][$index];
            $productPrice = $orderData['product_prices'][$index];
            $totalPrice = $quantity * $productPrice;  // คำนวณราคาสุทธิของสินค้าแต่ละตัว
            ?>

            <div class="mt-8 space-y-3 rounded-lg border bg-white px-2 py-4 sm:px-6">
              <div class="flex flex-col rounded-lg bg-white sm:flex-row">
                <img class="m-2 h-24 w-28 rounded-md border object-cover object-center" src="<?php echo htmlspecialchars($orderData['product_images'][$index]); ?>" alt="" />
                <div class="flex w-full flex-col px-4 py-4">
                  <span class="font-semibold"><?php echo htmlspecialchars($orderData['product_names'][$index]); ?></span>

                  <span class="float-right text-gray-600">x <?php echo htmlspecialchars($orderData['quantities'][$index]); ?></span>
                  <p class="text-lg font-bold"><i class="fa-solid fa-baht-sign"></i> <?php echo htmlspecialchars($totalPrice); ?></p> <!-- แสดงผลราคาสุทธิ -->
                </div>
              </div>
            </div>
          <?php endforeach ?>


          <p class="mt-8 text-lg font-medium">ช่องทางการชำระ</p>
          <form class="mt-5 grid gap-6">
            <div class="relative">
              <input class="peer hidden" id="radio_1" type="radio" name="radio" checked />
              <span class="peer-checked:border-gray-700 absolute right-4 top-1/2 box-content block h-3 w-3 -translate-y-1/2 rounded-full border-8 border-gray-300 bg-white"></span>
              <label class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-gray-50 flex cursor-pointer select-none rounded-lg border border-gray-300 p-4" for="radio_1">
                <i class="fa-regular fa-truck-fast"></i>
                <div class="ml-5">
                  <span class="mt-2 font-semibold">เก็บปลายทาง</span>

                </div>
              </label>
            </div>
            <div class="relative">
              <input class="peer hidden" id="radio_2" type="radio" name="radio" checked />
              <span class="peer-checked:border-gray-700 absolute right-4 top-1/2 box-content block h-3 w-3 -translate-y-1/2 rounded-full border-8 border-gray-300 bg-white"></span>
              <label class="peer-checked:border-2 peer-checked:border-gray-700 peer-checked:bg-gray-50 flex cursor-pointer select-none rounded-lg border border-gray-300 p-4" for="radio_2">
                <i class="fa-regular fa-qrcode"></i>
                <div class="ml-5">
                  <span class="mt-2 font-semibold">โอนผ่านบัญชีธนาคาร ( เเนบสลิป )</span>
                </div>
              </label>
            </div>
          </form>

          <a href="clear_session.php" class="btn mt-2 mb-8 w-full rounded-md bg-gray-900 px-6 py-3 font-medium text-white">Cancle</a>
        </div>

        <!-- เก็บปลายทาง -->
        <div id="destination" class="mt-10 bg-gray-50 px-4 pt-8 lg:mt-0" style="font-family: 'IBM Plex Sans Thai', sans-serif; display: none;">
          <form action="process_payment.php" method="post">
            <div class="mt-10 bg-gray-50 px-4 pt-8 lg:mt-0" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
              <p class="text-xl font-medium">รายละเอียด การสั่งซื้อ</p>
              <p class="text-gray-400">เช็คที่อยู่เบอร์โทรเเละยืนยันการสั่งซื้อ (จะไม่สามารถเเก้ไขที่อยู่ได้)</p>
              <div class="">
                <label for="fullname" class="mt-4 mb-2 block text-sm font-medium">ชื่อ</label>
                <div class="relative">
                  <input type="text" id="fullname" name="fullname[]" class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="ใส่ชื่อ - นามสกุล" />
                  <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                    <i class="fa-solid fa-user"></i>
                  </div>
                </div>
                <label for="address" class="mt-4 mb-2 block text-sm font-medium">ที่อยู่การจัดส่ง</label>
                <div class="relative">

                  <input type="textarea" id="address" name="address[]" class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm uppercase shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="กรอกที่อยู่ของคุณ" />
                  <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                </div>


                <?php foreach ($orderDatatest['product_ids'] as $index => $productId): ?>
                  <input type="hidden" name="product_ids[]" value="<?php echo htmlspecialchars($productId); ?>">
                  <input type="hidden" name="product_image[]" value="<?php echo htmlspecialchars($orderDatatest['product_images'][$index]); ?>">
                  <input type="hidden" name="product_names[]" value="<?php echo htmlspecialchars($orderDatatest['product_names'][$index]); ?>">
                  <input type="hidden" name="product_prices[]" value="<?php echo htmlspecialchars($orderDatatest['product_prices'][$index]); ?>">
                  <input type="hidden" name="quantities[]" value="<?php echo htmlspecialchars($orderDatatest['quantities'][$index]); ?>">
                <?php endforeach; ?>

                <input type="hidden" name="quantt[]" value="<?php echo number_format($totalQuantity); ?>">
                <input type="hidden" name="payment_method[]" value="เก็บปลายทาง">
                <input type="hidden" name="total[]" value="<?php echo htmlspecialchars($totalPriceWithShipping); ?>">
                <input type="hidden" name="total2" value="<?php echo htmlspecialchars($totalPriceWithShipping); ?>">

                <label for="phoneNumber" class="mt-4 mb-2 block text-sm font-medium">เบอร์ติดต่อ</label>
                <div class="flex">
                  <div class="relative w-7/12 flex-shrink-0">
                    <input type="text" id="phoneNumber" name="phoneNumber[]" class="w-full rounded-md border border-gray-200 px-2 py-3 pl-11 text-sm shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="xxx-xxx-xxxx" />
                    <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                      <i class="fa-solid fa-phone"></i>
                    </div>
                  </div>

                </div>


                <!-- Total -->
                <div class="mt-6 border-t border-b py-2" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">ราคาทั้งหมด</p>
                    <p class="font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                      <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($totalOrderPrice, 2); ?>
                    </p>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">ค่าจัดส่ง</p>
                    <p class="font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                      <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($shippingCost, 2); ?>
                    </p>
                  </div>
                </div>
                <div class="mt-6 flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900">รวม</p>
                  <p class="text-2xl font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                    <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($totalPriceWithShipping, 2); ?>
                  </p>
                </div>
              </div>
              <button type="submit" class="mt-4 mb-8 w-full rounded-md bg-gray-900 px-6 py-3 font-medium text-white">สั่งซื้อเลย</button>
            </div>
          </form>
        </div>

        <!-- โอนผ่านบัญชีธนาคาร -->
        <div id="bankTransfer" class="mt-10 bg-gray-50 px-4 pt-8 lg:mt-0" style="font-family: 'IBM Plex Sans Thai', sans-serif; display: none;">
          <form action="process_payment.php" method="post" enctype="multipart/form-data">
            <div class="mt-10 bg-gray-50 px-4 pt-8 lg:mt-0" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
              <p class="text-xl font-medium">รายละเอียด การสั่งซื้อ</p>
              <p class="text-gray-400">เช็คที่อยู่เบอร์โทรเเละยืนยันการสั่งซื้อ (จะไม่สามารถเเก้ไขที่อยู่ได้)</p>
              <div class="">
                <label for="fullname" class="mt-4 mb-2 block text-sm font-medium">ชื่อ</label>
                <div class="relative">
                  <input type="text" id="fullname" name="fullname[]" class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="ใส่ชื่อ - นามสกุล" />
                  <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                    <i class="fa-solid fa-user"></i>
                  </div>
                </div>
                <label for="address" class="mt-4 mb-2 block text-sm font-medium">ที่อยู่การจัดส่ง</label>
                <div class="relative">
                  <input type="text" id="address" name="address[]" class="w-full rounded-md border border-gray-200 px-4 py-3 pl-11 text-sm uppercase shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="กรอกที่อยู่ของคุณ" />
                  <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                </div>

                <?php foreach ($orderData['product_ids'] as $index => $productId): ?>
                  <input type="hidden" name="product_ids[]" value="<?php echo htmlspecialchars($productId); ?>">
                  <input type="hidden" name="product_image[]" value="<?php echo htmlspecialchars($orderData['product_images'][$index]); ?>">
                  <input type="hidden" name="product_names[]" value="<?php echo htmlspecialchars($orderData['product_names'][$index]); ?>">
                  <input type="hidden" name="product_prices[]" value="<?php echo htmlspecialchars($orderData['product_prices'][$index]); ?>">
                  <input type="hidden" name="quantities[]" value="<?php echo htmlspecialchars($orderData['quantities'][$index]); ?>">
                <?php endforeach; ?>

                <input type="hidden" name="quantt[]" value="<?php echo number_format($totalQuantity); ?>">
                <input type="hidden" name="payment_method[]" value="โอนผ่านบัญชีธนาคาร">
                <input type="hidden" name="total[]" value="<?php echo htmlspecialchars($totalPriceWithShipping); ?>">
                <input type="hidden" name="total2" value="<?php echo htmlspecialchars($totalPriceWithShipping); ?>">


                <label for="phoneNumber" class="mt-4 mb-2 block text-sm font-medium">เบอร์ติดต่อ</label>
                <div class="flex">
                  <div class="relative w-7/12 flex-shrink-0">
                    <input type="text" id="phoneNumber" name="phoneNumber[]" class="w-full rounded-md border border-gray-200 px-2 py-3 pl-11 text-sm shadow-sm outline-none focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="xxx-xxx-xxxx" />
                    <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">
                      <i class="fa-solid fa-phone"></i>
                    </div>
                  </div>

                </div>


                <!-- You can open the modal using ID.showModal() method -->
                <!-- Button to open the modal -->
                <a class="btn mt-5" onclick="my_modal_3.showModal()">
                  <i class="fa-regular fa-qrcode"></i> แสกนคิวอาร์โค้ด
                </a>

                <!-- Modal -->
                <dialog id="my_modal_3" class="modal fixed inset-0 flex items-center justify-center z-50">
                  <div class="modal-box w-[400px] bg-white rounded-lg shadow-lg p-6 relative">
                    <div method="dialog" class="hidden">
                      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-gray-500 hover:text-gray-800" id="closeModal">✕</button>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">Qr Payment</h3>
                    <div class="box-qr-pay flex items-center">
                      <img src="./slip/462358487_1678154006251592_7587154410707903834_n.jpg" class="w-[180px] h-[180px] object-cover rounded-lg shadow-sm" alt="QR Code">
                      <div class="text-bank ml-6">
                        <h1 class="text-lg font-medium text-gray-700">นายทดลอง เดโม่</h1>
                        <p class="text-gray-600 mt-2"></p>
                        <span class="block text-gray-500 mt-1">ธนาคาร เดโม่</span>
                      </div>
                    </div>
                  </div>
                </dialog>

                <label for="card-holder" class="mt-4 mb-2 block text-sm font-medium">เเนบสลิป</label>
                <div class="relative">
                  <input type="file" name="slip" class="file-input file-input-bordered w-full max-w-xs" />
                  <div class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center px-3">

                  </div>
                </div>


                <!-- Total -->
                <div class="mt-6 border-t border-b py-2" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">ราคาทั้งหมด</p>
                    <p class="font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                      <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($totalOrderPrice, 2); ?>
                    </p>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">ค่าจัดส่ง</p>
                    <p class="font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                      <i class="fa-solid fa-baht-sign"></i> <?php echo number_format($shippingCost, 2); ?>
                    </p>
                  </div>
                </div>
                <div class="mt-6 flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900">รวม</p>
                  <p class="text-2xl font-semibold text-gray-900" style="font-family: 'Itim', cursive;">
                    <i class="fa-solid fa-baht-sign"></i><?php echo number_format($totalPriceWithShipping, 2); ?>
                  </p>
                </div>
              </div>
              <button type="submit" class="mt-4 mb-8 w-full rounded-md bg-gray-900 px-6 py-3 font-medium text-white">สั่งซื้อเลย</button>
            </div>
          </form>
        </div>



      </div>
    </div>
  </div>







  <script src="assets/js/main.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const radio1 = document.getElementById('radio_1');
      const radio2 = document.getElementById('radio_2');
      const destinationSection = document.getElementById('destination');
      const bankTransferSection = document.getElementById('bankTransfer');

      // Function to toggle sections based on radio selection
      function toggleSections() {
        if (radio1.checked) {
          destinationSection.style.display = 'block';
          bankTransferSection.style.display = 'none';
        } else if (radio2.checked) {
          destinationSection.style.display = 'none';
          bankTransferSection.style.display = 'block';
        }
      }

      // Initial toggle on page load
      toggleSections();

      // Add event listeners to radios
      radio1.addEventListener('change', toggleSections);
      radio2.addEventListener('change', toggleSections);
    });
  </script>

  <script>
    const modal = document.getElementById('my_modal_3');
    const closeModalButton = document.getElementById('closeModal');

    // Close the modal when clicking the close button
    closeModalButton.addEventListener('click', () => {
      modal.close();
    });

    // Close the modal when clicking outside the modal content
    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        modal.close();
      }
    });
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