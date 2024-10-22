<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Condatabase/database_shop.php';

if (!isset($_SESSION['id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// ดึงข้อมูลผู้ใช้ตาม ID
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลสินค้าทั้งหมด
$sql = "SELECT * FROM products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลผู้ใช้ทั้งหมด
$sql = "SELECT * FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$alluser = $stmt->fetchAll(PDO::FETCH_ASSOC);

//ดึงข้อมูลการสั่งซื้อและรายละเอียด
// $sql = "SELECT orders.id as order_id, orders.status as order_status, products.name as product_name, products.image as product_img, 
//                users.firstname as username, order_items.quantity as quantity, orders.total as total_price, 
//                orders.created_at as order_date, orders.order_custom_no as orderno
//         FROM orders
//         JOIN order_items ON orders.id = order_items.order_id
//         JOIN products ON order_items.product_id = products.id
//         JOIN users ON orders.user_id = users.id
//         WHERE orders.user_id IS NOT NULL";

// $stmt = $conn->prepare($sql);
// $stmt->execute();
// $orderResults = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ดึงข้อมูลการสั่งซื้อทั้งหมดเฉพาะ user_id นี้
$sql = "SELECT * FROM orders";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orderResults = $stmt->fetchAll(PDO::FETCH_ASSOC);



// นับจำนวนสินค้าทั้งหมด
$sqlProducts = "SELECT COUNT(*) as pCount FROM products";
$stmt = $conn->prepare($sqlProducts);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$pCountProducts = $row["pCount"];

// รวมยอดเงินทั้งหมดจากตาราง order_items
$sql = "SELECT SUM(total) AS totalNumsum FROM total_all";
$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalSum = $row["totalNumsum"];

// ใช้ number_format เพื่อแสดงตัวเลขพร้อมทศนิยม
$formattedTotalSum = number_format($totalSum, 2);

// รวมจำนวนสินค้าทั้งหมดจากตาราง order_items
$sql = "SELECT SUM(quantity) AS quantity_sum FROM order_items";
$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$quantitySum = $row["quantity_sum"];

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
</head>

<body>



    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">

            <div class="logo-name"><span>Admin</span></div>
        </a>
        <ul class="side-menu">
            <li class="active"><a href="admin.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="admin_product.php"><i class='bx bx-store-alt'></i>Products</a></li>
            <li><a href="admin_order.php"><i class='bx bxs-package'></i>Orders</a></li>


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
                            <?php echo $pCountProducts; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">ประเภทสินค้า</p>
                    </span>
                </li>

                <li><i class='bx bxs-checkbox-checked'></i>
                    <span class="info">
                        <h3>
                            <?php echo $quantitySum; ?><span
                                style="font-family: 'IBM Plex Sans Thai', sans-serif; font-size: 15px; font-weight: 400; margin-left: 10px;">
                                ชิ้น</span>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">สินค้าขายออก</p>
                    </span>
                </li>
                <li><i class='bx bx-dollar-circle'></i>
                    <span class="info">
                        <h3 style="display: flex;">

                            <i class="fa-solid fa-baht-sign"></i><?php echo $formattedTotalSum; ?>

                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">ยอดขาย</p>
                    </span>
                </li>
            </ul>
            <!-- End of Insights -->

            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Orders</h3>
                        <!-- <i class='bx bx-filter'></i>
                        <i class='bx bx-search'></i> -->
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>OrderNumber</th>
                                <th>Price</th>
                                <th>quantity</th>
                                <th>Status</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($orderResults as $row) : ?>
                                <tr>
                                    <td>
                                        <p><?php echo htmlspecialchars($row['fullname']); ?></p>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['order_custom_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total']); ?>THB</td>
                                    <td><?= htmlspecialchars($row["quantt"]) ?></td>
                                    <td><span class="status completed"><?= htmlspecialchars($row["status"]) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Reminders -->
                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-user'></i>
                        <h3>Member</h3>
                        <i class='bx bx-filter'></i>
                        <!-- <i class='bx bx-plus'></i> -->
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>email</th>
                                <th>role</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alluser as $row) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["id"]) ?></td>
                                    <td>
                                        <p><?= htmlspecialchars($row["firstname"]) ?></p>
                                    </td>
                                    <td><?= htmlspecialchars($row["email"]) ?></td>
                                    <td><?= htmlspecialchars($row["role"]) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

                <!-- End of Reminders-->

            </div>

        </main>


    </div>

    <script src="index.js"></script>
</body>

</html>