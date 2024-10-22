<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ตรวจสอบว่ามี user_id ใน session หรือไม่
if (!isset($_SESSION['id'])) {
    echo "You must be logged in to view order history.";
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin_order.php');
    exit;
}

// 1. สร้าง connection กับฐานข้อมูล
include '../Condatabase/database_shop.php'; // ตรวจสอบว่าไฟล์นี้เปิด connection ในแบบ PDO แล้วหรือไม่

// 2. ใช้ order_id จาก GET
$product_id = $_GET['id'];

$result = []; // กำหนดค่าเริ่มต้นให้เป็น array ว่าง

try {
    // 3. เขียนคำสั่ง SQL ในการดึงข้อมูลจากตาราง orders, order_items และ products
    $sql = "
    SELECT 
        orders.id AS order_id, 
        orders.fullname, 
        orders.address, 
        orders.phoneNumber, 
        orders.created_at,
        orders.total,
        orders.slip, 
        orders.status,
        orders.payment,
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
            $totalPrice += floatval($row['product_price']);
        }

        $totalPriceWithShipping = $totalPrice + $shippingCost;

        // แสดงผลรวมทั้งหมด
        // echo "Total Price: " . number_format($totalPrice, 2) . "<br>";
        // echo "Shipping Cost: " . number_format($shippingCost, 2) . "<br>";
        // echo "Total Price with Shipping: " . number_format($totalPriceWithShipping, 2) . "<br>";
    } else {
        echo "ไม่มีข้อมูลการสั่งซื้อสำหรับ user_id นี้";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// ฟังก์ชันคำนวณจำนวนสินค้าในตะกร้า
function get_total_items_in_cart()
{
    if (isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    } else {
        return 0;
    }
}

$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$product_id]);
$address = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ปิด connection
$conn = null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <!-- Add Bootstrap 5 CSS -->

    <link rel="stylesheet" href="https://poseidon-code.github.io/supacons/dist/supacons.all.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

    <!------AOS ANIMATION  -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!----------FONT ----------->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=Itim&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Style สำหรับ Lightbox */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
        }
    </style>

</head>

<body>


    <div class="w-full h-full">
        <div class=" py-14 px-4 md:px-6 2xl:px-20 2xl:container 2xl:mx-auto">

            <div class="max-w-[1070px] h-auto mx-auto flex justify-start item-start space-y-2 flex-col" style="font-family: 'Itim', cursive;">
                <a class="btn w-[100px]" href="admin_order.php">กลับ</a>
                <?php foreach ($address as $row): ?>
                    <h1 class="text-xl dark:text-white lg:text-2xl font-semibold leading-7 lg:leading-9 text-gray-800">Order #<?php echo htmlspecialchars($row['order_custom_no']); ?></h1>
                <?php endforeach ?>

            </div>
            <div class="max-w-[1140px] h-auto mx-auto mt-10 flex flex-col xl:flex-row jusitfy-center items-stretch w-full xl:space-x-8 space-y-4 md:space-y-6 xl:space-y-0">
                <div class="flex flex-col justify-start items-start w-full space-y-4 md:space-y-6 xl:space-y-8">
                    <?php if ($result): ?>
                        <div class="flex flex-col justify-start items-start dark:bg-gray-800 bg-gray-50 px-4 py-4 md:py-6 md:p-6 xl:p-8 w-full">

                            <p class="text-lg md:text-xl dark:text-white font-semibold leading-6 xl:leading-5 text-gray-800" style="font-family: 'Itim', cursive;">Customer’s Cart</p>
                            <?php foreach ($result as $row): ?>

                                <div class="mt-4 md:mt-6 flex flex-col md:flex-row justify-start items-start md:items-center md:space-x-6 xl:space-x-8 w-full" style="font-family: 'Itim', cursive;">
                                    <div class="pb-4 md:pb-8 w-full md:w-40">
                                        <img class="w-full hidden md:block" src="../<?php echo htmlspecialchars($row['product_image']); ?>" alt="dress" />
                                        <img class="w-full md:hidden" src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="dress" />
                                    </div>
                                    <div class="border-b border-gray-200 md:flex-row flex-col flex justify-between items-start w-full pb-8 space-y-4 md:space-y-0">
                                        <div class="w-full flex flex-col justify-start items-start space-y-5">
                                            <h3 class="text-xl dark:text-white xl:text-xl font-semibold leading-6 text-gray-800" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                            <div class="flex justify-start items-start flex-col space-y-2">
                                                <p class="text-base dark:text-white xl:text-lg font-semibold leading-6 text-gray-800"><i class="fa-solid fa-baht-sign"></i> <?php echo htmlspecialchars($row['product_price']); ?></p>
                                                <p class="text-sm dark:text-white leading-none text-gray-800"><span class="dark:text-gray-400 text-gray-300" style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: bold;"></span> x <?php echo htmlspecialchars($row['quantity']); ?></p>

                                            </div>
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
                                            <?php foreach ($address as $row): ?>
                                                <p class="text-lg leading-6 dark:text-white font-semibold text-gray-800">
                                                <form action="update_number_order.php" method="POST">
                                                    <input class="h-[30px] border border-black" type="text" name="transport" value="<?php echo htmlspecialchars($row['transport']); ?>">
                                                    (ขนส่ง)
                                                    <br />
                                                    <span class="font-normal text-[15px]">
                                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                        <input class="h-[30px] border border-black mt-2" type="text" name="orderNumber" value="<?php echo htmlspecialchars($row['orderNumber']); ?>">
                                                        (เลขพัสดุ)
                                                        <button class="btn mt-2 bg-slate-900 text-white" type="submit">อัปเดต</button>
                                                    </span>

                                                </form>

                                                </p>
                                            <?php endforeach ?>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                </div>

                <?php foreach ($address as $row): ?>
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

                                    <!-- รูปภาพหลัก -->
                                    <div class="flex justify-center items-center flex-col space-y-4">
                                        <img src="../slip/<?php echo htmlspecialchars($row['slip']); ?>" alt="Slip Image" class="w-full max-w-xs md:max-w-md lg:max-w-lg cursor-pointer rounded-lg shadow-md" onclick="showImage(this.src)">
                                    </div>

                                    <!-- Lightbox สำหรับแสดงรูปภาพขยาย -->
                                    <div id="imageViewer" class="hidden" onclick="closeImage()">
                                        <div class="lightbox">
                                            <img id="lightboxImage" src="" alt="Expanded Image">
                                        </div>
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


<?php else: ?>
    <p>ไม่มีข้อมูลการสั่งซื้อสำหรับ user_id นี้</p>
<?php endif; ?>


<script>
    function showImage(src) {
        document.getElementById('imageViewer').classList.remove('hidden');
        document.getElementById('lightboxImage').src = src;
    }

    function closeImage() {
        document.getElementById('imageViewer').classList.add('hidden');
    }
</script>
<script src="index.js"></script>
<script src="./assets/js/main.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>
</body>

</html>