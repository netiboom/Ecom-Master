<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Condatabase/database_shop.php';

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
  <title>Ecom</title>

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

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  


  <!----------FONT ----------->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=Itim&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/style.css">


  <style>
    @import url('https://fonts.googleapis.com/css2?family=Itim&display=swap');

    .typing-animation {
      white-space: nowrap;
      overflow: hidden;
      border-right: 3px solid;
      font-family: 'Itim', cursive;
      animation: typing 5s steps(30, end) infinite, blink-caret 0.75s step-end infinite;
    }

    @keyframes typing {
      from {
        width: 0;
      }

      to {
        width: 100%;
      }
    }

    @keyframes blink-caret {

      from,
      to {
        border-color: transparent;
      }

      50% {
        border-color: #000;
      }
    }
  </style>

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

  <header class="fixed w-full h-auto z-10" style="transform: translateY(-100px);">
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
            <a href="user.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Home</a>
            <a href="../product_list.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Products</a>

            <a href="../contact.php" class="text-gray-700 hover:text-[#FFF] hover:bg-[#FF5733] h-full w-[100px] flex items-center justify-center">Contact us</a>
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
                <a href="../user_order.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-regular fa-bag-shopping"></i> My orders</a>
                <a href="../profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-regular fa-user"></i> Profile</a>
                <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FF5733] hover:text-[#FFF] "><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
              </div>
            </div>
            <a href="../shoping_cart.php" class="relative w-[70px] h-full flex items-center justify-center bg-[#FF5733] text-white hover:text-[#000]">
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
          <a href="user.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Home</a>
          <a href="../product_list.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Product</a>
          <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">About</a>
          <a href="../contact.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Contact us</a>
          <a href="../logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-[#FF5733]">Logout</a>
        </div>
      </div>
    </nav>
  </header>



  <section class="bg-white py-12 w-full h-[500px] mt-[100px]">
    <div class="banner-con max-w-[1140px] mx-auto px-6 lg:flex lg:items-center lg:justify-between">
      <!-- Text Section -->
      <div class="lg:w-1/2 lg:pr-12" data-aos="fade-up" data-aos-duration="500">
        <h2 class="text-4xl font-bold text-gray-800 max-w-[300px] typing-animation">
        HI <span style="color: #FF5733;">Pomeranian</span>
        </h2>
        <p class="mt-4 text-gray-600" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;">
        ปอมเมอเรเนียน (Pomeranian) เป็นสุนัขที่จัดอยู่ในกลุ่ม Toy Group แม้ว่าจะมีขนาดตัวเล็กแต่ปอมเมอเรเนียนกลับครองใจเจ้าของผู้เลี้ยงมาอย่างยาวนาน แถมยังเป็นสายพันธุ์สุดโปรดของทั้งเจ้าของอย่างเราและเชื้อพระวงศ์ชั้นสูง
        </p>
        <a href="../product_list.php" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;" class="mt-6 inline-block bg-[#FF5733] text-white px-7 py-3 shadow-[#FF5733] hover:bg-purple-700 transition ease-in-out duration-300">
          เลือกซื้อสินค้า
        </a>
      </div>

      <!-- Image Section -->
      <div class="lg:w-1/2 mt-6 lg:mt-0" data-aos="fade-up" data-aos-duration="1000">
        <img src="https://img.lovepik.com/bg/20231220/Fall-Foliage-Pomeranian-Dogs-Enjoying-the-Scenic-Beauty-of-Home_2660061_wh860.jpg!/fw/860" alt="Banner Image" class="rounded-lg shadow-lg w-full">
      </div>
    </div>
  </section>



  <div class="w-full h-auto bg-[#FFF] mt-[150px]">
    <section class="max-w-[1140px] h-auto category_list mx-auto" style="font-family: 'Itim', system-ui;">



      <div class="headtextcate px-5 w-full h-[30px] flex items-center justify-between">

        <h1 class="text-black text-[30px] font-extrabold"><span class="text-[#FF5733]">Hot </span>sale <i class="fa-solid fa-grid-2-plus text-sm text-[#FF5733]"></i></h1>


        <div class="h-auto w-[50px] flex items-center justify-between">
          <div class="leftclick cursor-pointer w-[25px] text-white bg-[#FF5733] flex items-center justify-center px-[10px] py-[5px]"><i class="fa-solid fa-chevron-left"></i></div>
          <div class="rightclick cursor-pointer w-[25px] text-white bg-[#FF5733] flex items-center justify-center ml-[10px] px-[10px] py-[5px]"><i class="fa-solid fa-chevron-right"></i></div>
        </div>


      </div>





      <div class="swiper mySwiper w-full h-auto mt-[30px]">
        <div class="swiper-wrapper py-5">

          <?php while ($row = $result2->fetch(PDO::FETCH_ASSOC)): ?>

            <div class="swiper-slide item__product relative m-4 flex w-full max-w-xs flex-col overflow-hidden rounded-lg border border-gray-100 bg-white shadow-md" data-aos="fade-up" data-aos-duration="1000">
              <a class="relative mx-3 mt-3 flex h-60 overflow-hidden rounded-xl" href="#">
                <img class="object-cover" src="../<?= htmlspecialchars($row['image']); ?>" alt="product image" />
              </a>
              <div class="mt-4 px-5 pb-5">
                <a href="#">
                  <h5 class="text-md tracking-tight text-slate-900 font-semibold hover:text-[#FF5733]" style="font-family: 'IBM Plex Sans Thai', sans-serif;">

                    <?php
                    $name = $row["name"];
                    $max_length = 20;
                    if (strlen($name) > $max_length) {
                      $name = substr($name, 0, $max_length) . '...';
                    }
                    echo htmlspecialchars($name);
                    ?>

                  </h5>
                </a>
                <span style="font-family: 'IBM Plex Sans Thai', sans-serif;">

                  <?php
                  $description = $row["description"];
                  $max_length = 40;
                  if (strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                  }
                  echo htmlspecialchars($description);
                  ?>
                </span>
                <div class="mt-2 mb-5 flex items-center justify-between" style="font-family: 'Itim', cursive;">
                  <p>
                    <span class="text-lg text-slate-900"><i class="fa-regular fa-baht-sign"></i> <?= htmlspecialchars($row['price']); ?></span>
                  </p>


                </div>
                <form action="cart.php" method="post" class="add-to-cart-form">
                  <input type="hidden" name="action" value="add_to_cart">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']); ?>">
                  <button type="submit" class=" w-full" style="border:none; border-radius:50%; background:none;">
                    <a style="font-family: 'Itim', cursive;" class="flex items-center justify-center rounded-md bg-[#FF5733] hover:bg-slate-900 px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4 focus:ring-blue-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      Add to cart
                    </a>
                  </button>
                </form>
              </div>

              <div class="bannerhotsale bg-[#FF5733] w-6 h-9 flex items-center justify-center" style="clip-path: polygon(0% 0%, 100% 0, 100% 100%, 50% 78%, 0 100%); position: absolute ; right: 0;">
                <i class="fa-regular fa-fire text-white"></i>
              </div>
            </div>

          <?php endwhile; ?>

          <!-- เพิ่มไอเท็มอื่นๆ ได้ตามต้องการ -->
        </div>
        <!-- ปุ่มการเลื่อน -->
      </div>
    </section>
  </div>


  <!----------------------------------------- Product  ----------------------->
  <section class="auction-today w-full my-[100px]">
    <div class="head-auc max-w-[1140px] h-[50px] mx-auto mb-[70px] px-6" style="font-family: 'IBM Plex Sans Thai', sans-serif;" data-aos="fade-up" data-aos-duration="1000">
      <h1 class="text-[30px]" style="font-family: 'Itim', cursive; font-weight:bold ;"><span class="text-[#FF5733]">Pomeranian</span> Products</h1>
      <p class="max-w-[550px]">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Assumenda laborum alias facere accusamus doloremque atque qui in eveniet harum! Repellat.</p>
    </div>
    <div class="auc-con max-w-[1140px] px-6 mx-auto mt-[100px] grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-items-center">

      <?php
      $products_per_page = 20;
      $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

      try {
        // คำนวณจำนวนสินค้าทั้งหมด
        $sql = "SELECT COUNT(*) FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $products_total = $stmt->fetchColumn();

        $total_pages = ceil($products_total / $products_per_page);
        $offset = ($current_page - 1) * $products_per_page;

        // ดึงข้อมูลสินค้าตามหน้าปัจจุบัน
        $sql = "SELECT * FROM products LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $resultsearch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products_total > 0) {
          $count = 0;
          foreach ($resultsearch as $row) {
            $count++;

            // ข้ามสินค้าก่อนหน้าที่อยู่ในหน้านี้
            if ($count <= $offset) {
              continue;
            }

            // หยุดแสดงสินค้าหลังจากถึงขีดจำกัดสำหรับหน้าปัจจุบัน
            if ($count > ($offset + $products_per_page)) {
              break;
            }
      ?>

            <div class=" item__product relative m-4 flex w-full max-w-xs flex-col overflow-hidden rounded-lg border border-gray-100 bg-white shadow-md" data-aos="fade-up" data-aos-duration="1000">
              <a class="relative mx-3 mt-3 flex h-60 overflow-hidden rounded-xl" href="#">
                <img class="object-cover" src="../<?= htmlspecialchars($row['image']); ?>" alt="product image" />
              </a>
              <div class="mt-4 px-5 pb-5">
                <a href="#">
                  <h5 class="text-md tracking-tight text-slate-900 font-semibold hover:text-[#FF5733]" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                    <?php
                    $name = $row["name"];
                    $max_length = 20;
                    if (strlen($name) > $max_length) {
                      $name = substr($name, 0, $max_length) . '...';
                    }
                    echo htmlspecialchars($name);
                    ?>
                  </h5>
                </a>
                <span style="font-family: 'IBM Plex Sans Thai', sans-serif;">
                  <?php
                  $description = $row["description"];
                  $max_length = 40;
                  if (strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                  }
                  echo htmlspecialchars($description);
                  ?>
                </span>
                <div class="mt-2 mb-5 flex items-center justify-between" style="font-family: 'Itim', cursive;">
                  <p>
                    <span class="text-lg text-slate-900"><i class="fa-regular fa-baht-sign"></i> <?= number_format($row['price'], 2); ?></span>
                  </p>
                </div>

                <form action="cart.php" method="post" class="add-to-cart-form">
                  <input type="hidden" name="action" value="add_to_cart">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['id']); ?>">
                  <button type="submit" class=" w-full" style="border:none; border-radius:50%; background:none;">
                    <a style="font-family: 'Itim', cursive;" class="flex items-center justify-center rounded-md bg-[#FF5733] hover:bg-slate-900 px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4 focus:ring-blue-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      Add to cart
                    </a>
                  </button>
                </form>


              </div>
            </div>

      <?php
          }
        } else {
          echo "ไม่มีสินค้าที่จะแสดง";
        }
      } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
      }
      ?>




      <!-- Duplicate the product-box divs for additional products -->

      <!-- Additional product-box divs for more products... -->

    </div>
    <div class="pagination mt-10 flex justify-center">
      <div class="join grid grid-cols-2 max-w-[140px] mx-auto" style="font-family: 'Itim', cursive;">
        <?php if ($current_page > 1) : ?>
          <a href="?page=<?= $current_page - 1; ?>" class="join-item bg-neutral-900 btn btn-outline text-white text-[10px] hover:bg-[#E85C0D]"><i class="fa-solid fa-chevron-left"></i> Prev</a>
        <?php endif; ?>
        <?php if ($current_page < $total_pages) : ?>
          <a href="?page=<?= $current_page + 1; ?>" class="join-item bg-neutral-900 btn btn-outline text-white text-[10px] hover:bg-[#E85C0D]">Next <i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </section>




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
            <span class="ml-2 text-xl font-bold tracking-wide text-gray-800 uppercase">Lorem ipsum dolor .</span>
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

  <script src="../assets/js/main.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  <script>
    function addToCart(productId, quantity) {
      fetch('cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'action': 'add_to_cart',
            'product_id': productId,
            'quantity': quantity
          })
        })
        .then(response => response.json())
        .then(data => {
          console.log(data); // ตรวจสอบข้อมูลที่ได้รับ
          // รีไดเร็กไปยังหน้าตะกร้าสินค้า
          window.location.href = '../shoping_cart.php';
        })
        .catch(error => console.error('Error:', error));
    }
  </script>

  <script>
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 4, // จำนวนไอเท็มที่แสดงในแนวนอน
      spaceBetween: 10, // ช่องว่างระหว่างไอเท็ม
      navigation: {
        nextEl: ".rightclick",
        prevEl: ".leftclick",
      },
      loop: true, // เลื่อนไปเรื่อยๆ ไม่มีสิ้นสุด
      breakpoints: {

        375: {
          slidesPerView: 1,
          spaceBetween: 10
        },
        640: {
          slidesPerView: 2,
          spaceBetween: 10
        },
        // เมื่อหน้าจอมีความกว้างตั้งแต่ 1024px ขึ้นไป (แท็บเล็ตและเดสก์ท็อป)
        1024: {
          slidesPerView: 4,
          spaceBetween: 20
        }
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