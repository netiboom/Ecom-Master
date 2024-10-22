<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=ecom", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // เช็คสิทธิ์การเข้าถึง
    if (!isset($_SESSION['id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['id'];

    // ค้นหาข้อมูลผู้ใช้
    $sqlUser = "SELECT * FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // ดึงข้อมูลสินค้าทั้งหมด
    $sqlProducts = "SELECT * FROM products";
    $stmtProducts = $conn->query($sqlProducts);
    $resultallproduct = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลจากตาราง bank
    $sqlBank = "SELECT * FROM bank";
    $stmtBank = $conn->query($sqlBank);
    $bank = $stmtBank->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลจากตาราง orders, order_items และ products
    $sqlOrders = "SELECT * FROM orders
               ";
    $stmtOrders = $conn->prepare($sqlOrders);
    $stmtOrders->execute();
    $result = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);



    // นับจำนวนคำสั่งซื้อทั้งหมด
    $sqlCountOrders = "SELECT COUNT(*) as pCount FROM orders";
    $stmtCountOrders = $conn->query($sqlCountOrders);
    $rowCountOrders = $stmtCountOrders->fetch(PDO::FETCH_ASSOC);
    $CountProducts = $rowCountOrders["pCount"];

    // รวมจำนวนเงินทั้งหมดในคอลัมน์ total_price
    $sqlTotalSum = "SELECT SUM(total) AS total_sum FROM orders";
    $stmtTotalSum = $conn->query($sqlTotalSum);
    $rowTotalSum = $stmtTotalSum->fetch(PDO::FETCH_ASSOC);
    $totalSum = $rowTotalSum["total_sum"];

    // รวมจำนวนสินค้า
    $sqlQuantitySum = "SELECT SUM(quantity) AS quantity_sum FROM order_items";
    $stmtQuantitySum = $conn->query($sqlQuantitySum);
    $rowQuantitySum = $stmtQuantitySum->fetch(PDO::FETCH_ASSOC);
    $quantitySum = $rowQuantitySum["quantity_sum"];

    // จำนวนคำสั่งซื้อที่รอตรวจสอบ
    $sqlPendingOrders = "SELECT COUNT(orders.id) as total_pending_orders
                         FROM orders
                         WHERE orders.status = 'รอตรวจสอบ'";
    $stmtPendingOrders = $conn->query($sqlPendingOrders);
    $rowPendingOrders = $stmtPendingOrders->fetch(PDO::FETCH_ASSOC);
    $totalPendingOrders = $rowPendingOrders['total_pending_orders'];

    // จำนวนคำสั่งซื้อที่รอจัดส่ง
    $sqlWaitOrders = "SELECT COUNT(orders.id) as total_wait_orders
                      FROM orders
                      WHERE orders.status = 'ผู้ส่งกำลังเตรียมจัดส่ง'";
    $stmtWaitOrders = $conn->query($sqlWaitOrders);
    $rowWaitOrders = $stmtWaitOrders->fetch(PDO::FETCH_ASSOC);
    $totalwaitOrders = $rowWaitOrders['total_wait_orders'];

    // จำนวนคำสั่งซื้อที่อยู่ระหว่างจัดส่ง
    $sqlCheckOrders = "SELECT COUNT(orders.id) as total_check_orders
                       FROM orders
                       WHERE orders.status = 'อยู่ระหว่างจัดส่ง'";
    $stmtCheckOrders = $conn->query($sqlCheckOrders);
    $rowCheckOrders = $stmtCheckOrders->fetch(PDO::FETCH_ASSOC);
    $totalcheckOrders = $rowCheckOrders['total_check_orders'];

    // จำนวนคำสั่งซื้อที่จัดส่งสำเร็จ
    $sqlSuccessOrders = "SELECT COUNT(orders.id) as total_success_orders
                         FROM orders
                         WHERE orders.status = 'จัดส่งสำเร็จ'";
    $stmtSuccessOrders = $conn->query($sqlSuccessOrders);
    $rowSuccessOrders = $stmtSuccessOrders->fetch(PDO::FETCH_ASSOC);
    $totalsuccessOrders = $rowSuccessOrders['total_success_orders'];

    // จำนวนคำสั่งซื้อที่ยกเลิก
    $sqlCancleOrders = "SELECT COUNT(orders.id) as total_cancle_orders
                        FROM orders
                        WHERE orders.status = 'ยกเลิก'";
    $stmtCancleOrders = $conn->query($sqlCancleOrders);
    $rowCancleOrders = $stmtCancleOrders->fetch(PDO::FETCH_ASSOC);
    $totalcancleOrders = $rowCancleOrders['total_cancle_orders'];

    // กำหนดคลาส CSS ตามจำนวนคำสั่งซื้อที่ยกเลิก
    $statusClass = ($totalcancleOrders > 0) ? 'completed red-text' : 'completed';
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn = null;

?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://poseidon-code.github.io/supacons/dist/supacons.all.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai&display=swap" rel="stylesheet">


    <title>Admin Page</title>

    <style>

    </style>
</head>

<body>



    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">

            <div class="logo-name"><span>Admin</span></div>
        </a>
        <ul class="side-menu">
            <li><a href="admin.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="admin_product.php"><i class='bx bx-store-alt'></i>Products</a></li>
            <li class="active"><a href="admin_order.php"><i class='bx bxs-package'></i>Orders</a></li>


            <li><a href="admin_user.php"><i class='bx bx-group'></i>Users</a></li>

        </ul>
        <ul class="side-menu">
            <li>
                <a href="../logout.php" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    <!-- End of Sidebar -->

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <i class='bx bx-menu'></i>
        </nav>

        <!-- End of Navbar -->

        <main id="admin">
            <div class="header">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">

                    </ul>
                </div>

            </div>

            <!-- Insights -->
            <ul class="insights">
                <li>
                    <i class='bx bxs-basket'></i>
                    <span class="info">
                        <h3>
                            <?php echo $CountProducts; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">ออเดอร์ทั้งหมด</p>
                    </span>
                </li>
                <li><i class='bx bx-show-alt'></i>
                    <span class="info">
                        <h3>
                            <?php echo $totalPendingOrders; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">รอตรวจสอบ</p>
                    </span>
                </li>
                <li><i class='bx bx-package'></i>
                    <span class="info">
                        <h3>
                            <?php echo $totalwaitOrders; ?><span
                                style="font-family: 'IBM Plex Sans Thai', sans-serif; font-size: 15px; font-weight: 400; margin-left: 10px;"></span>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">เตรียมจัดส่ง</p>
                    </span>
                </li>
                <li><i class='bx bxs-car-garage'></i>
                    <span class="info">
                        <h3 style="display: flex;">

                            <?php echo $totalcheckOrders; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">อยู่ระหว่างจัดส่ง</p>
                    </span>
                </li>

                <li><i class='bx bx-check-square'></i>
                    <span class="info">
                        <h3 style="display: flex;">

                            <?php echo $totalsuccessOrders; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">จัดส่งสำเร็จ</p>
                    </span>
                </li>
            </ul>
            <!-- End of Insights -->

            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Orders</h3>
                        <form action="update_order_status.php" method="post">
                            <label for="order_id">Order Uid:</label>
                            <input style="width: 100px;" type="number" id="order_id" name="order_id" required>


                            <select id="order_status" name="order_status" required>
                                <option value="รอตรวจสอบ">รอตรวจสอบ</option>
                                <option value="ผู้ส่งกำลังเตรียมจัดส่ง">ผู้ส่งกำลังเตรียมจัดส่ง</option>
                                <option value="อยู่ระหว่างจัดส่ง">อยู่ระหว่างจัดส่ง</option>
                                <option value="จัดส่งสำเร็จ">จัดส่งสำเร็จ</option>

                            </select>

                            <button type="submit" class=" btn btn-primary">Update</button>
                        </form>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Uid</th>
                                <th>User</th>
                                <th>product</th>
                                <th>Price</th>
                                <th>quantity</th>
                                <th>Status</th>
                                <th>Check</th>



                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) : ?>

                                <tr>
                                    <td><?= $row["id"] ?></td>
                                    <td>

                                        <p><?= $row["fullname"] ?></p>
                                    </td>
                                    <td><?= $row["order_custom_no"] ?></td>
                                    <td><?= $row["total"] ?> THB</td>
                                    <td><?= $row["quantt"] ?></td>
                                    <td>
                                        <span class="status 
                                            <?php if ($row["status"] === "ยกเลิก"): ?>
                                                cancle
                                            <?php elseif ($row["status"] === "รอตรวจสอบ"): ?>
                                                wait
                                            <?php elseif ($row["status"] === "ผู้ส่งกำลังเตรียมจัดส่ง"): ?>
                                                process
                                                <?php elseif ($row["status"] === "อยู่ระหว่างจัดส่ง"): ?>
                                                 pending
                                                 <?php elseif ($row["status"] === "จัดส่งสำเร็จ"): ?>
                                                 completed
                                            <?php else: ?>
                                                <!-- คลาสเริ่มต้นหรือคลาสที่คุณต้องการในกรณีอื่น ๆ -->
                                                default
                                            <?php endif; ?>
                                        ">
                                            <?= $row["status"] ?>
                                        </span>
                                    </td>

                                    <td><a href="order_history.php?id=<?= $row['id']; ?>" style=" color: #000;"><i
                                                class="fa-solid fa-eye"></i></a>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Reminders -->


                <!-- End of Reminders-->

            </div>

        </main>


    </div>

    <script src="index.js"></script>
    <script>
        // เรียก Modal เมื่อคลิกที่ลิงก์
        document.getElementById('openModal').addEventListener('click', function() {
            $('#myModal').modal('show');
        });
    </script>

    <!-- เพิ่ม jQuery ต่อท้ายส่วน body ของเอกสาร -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>




</body>

</html>