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


$sql = "SELECT * FROM table_qrcode";
$stmt = $conn->prepare($sql);
$stmt->execute();
$table = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">


    <title>Admin Page</title>

    <style>
        /* Modal Container */
        .modal {
            display: none;
            position: fixed;
            z-index: 9990;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        /* Modal Content */
        .modal-content {
            position: absolute;
            transform: translate(-50%, -50%);
            top: 20%;
            left: 10%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 400px;
            position: relative;
        }

        /* Close Button */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #555;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Form Styles */
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }




        /* ตั้งค่าทั่วไป */
        .modal-overlay {
            display: none;
            /* เริ่มต้นปิด */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            position: absolute;
            transform: translate(-50%, -50%);
            left: 40%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: scale(0.95);
            transition: transform 0.3s ease-in-out;
        }

        .modal-footer {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .close-btn {
            background-color: #777;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .close-btn:hover {
            background-color: #555;
        }

        .modal-content h2 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
    </style>


</head>

<body>



    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">

            <div class="logo-name"><span>Ecommerc</span></div>
        </a>
        <ul class="side-menu">
            <li class="active"><a href="admin.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="admin_product.php"><i class='bx bx-store-alt'></i>Products</a></li>
            <li><a href="admin_order.php"><i class='bx bxs-package'></i>Orders</a></li>
            <li><a href="mybotchat.php"><i class='bx bx-bot'></i>Bot Chat</a></li>


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
                                <th>โต๊ะที่</th>
                                <th>ราคา</th>
                                <th>สถานะ</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($orderResults as $row) : ?>
                                <tr>
                                    <td>
                                        <p><?php echo htmlspecialchars($row['tableNumber']); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['total']); ?>THB</td>
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



            <div class="bottom-data">
                <div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">DataTables Example</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>โต๊ะที่</th>
                                            <th>จำนวนลูกค้า</th>
                                            <th>เวลา</th>
                                            <th>สถานะ</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($table as $row) : ?>
                                            <tr>
                                                <th><?= htmlspecialchars($row['id']); ?></th>
                                                <td>โต๊ะที่ : <?= htmlspecialchars($row['table_no']); ?></td>
                                                <td><?= htmlspecialchars($row['user_count']); ?></td>
                                                <td><?= htmlspecialchars($row['time']); ?></td>
                                                <td><?= htmlspecialchars($row['status_table']); ?></td>

                                                <td>
                                                    <button class="btn btn-warning" onclick="openForm(<?= htmlspecialchars($row['id']); ?>, '<?= htmlspecialchars($row['table_no']); ?>', '<?= htmlspecialchars($row['user_count']); ?>', '<?= htmlspecialchars($row['time']); ?>' , '<?= htmlspecialchars($row['status_table']); ?>')">เปิด</button>
                                                    <a href="reset_table.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear this table?');">เคลียโต๊ะ</a>
                                                    <a href="#" class="btn btn-success" onclick="openBillModal(<?= htmlspecialchars($row['table_no']); ?>)">เช็คบิล</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <!-- Modal สำหรับแสดงบิล -->



                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <!-- Modal เช็คบิล -->
            <div id="billModal" class="modal-overlay">
                <div class="modal-content">
                    <h2>บิลสั่งอาหาร</h2>
                    <div id="billContent"></div> <!-- เนื้อหาบิลจะถูกใส่ที่นี่ -->
                    <div class="modal-footer">
                        <button class="close-btn" onclick="closeBillModal()">ปิด</button>
                    </div>
                </div>
            </div>
            <!-- Popup Modal -->
            <div id="popupForm" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <form action="../generate_qrcode.php" method="post">
                        <input type="hidden" name="table_id" id="tableId">
                        <div class="form-group">
                            <label for="table_no">หมายเลขโต๊ะ:</label>
                            <input type="text" name="table_no" id="table_no" readonly>
                        </div>

                        <div class="form-group">
                            <label for="user_count">จำนวนผู้ใช้:</label>
                            <input type="text" name="user_count" id="user_count" required>
                        </div>

                        <div class="form-group">
                            <label for="time">เวลา:</label>
                            <input type="text" name="time" id="time" required>
                        </div>

                        <div class="form-group">
                            <label for="status_table">สถานะ</label>
                            <input type="text" name="status_table" id="status_table" required>
                        </div>

                        <button type="submit">บันทึก</button>
                    </form>
                </div>
            </div>

        </main>


    </div>

    <script src="index.js"></script>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
    <script>
        function openForm(id, table_no, user_count, time, status_table) {
            // เซ็ตค่าจากแถวที่เลือกลงในฟอร์ม
            document.getElementById('tableId').value = id;
            document.getElementById('table_no').value = table_no;
            document.getElementById('user_count').value = user_count;
            document.getElementById('time').value = time;
            document.getElementById('status_table').value = status_table;

            // แสดงฟอร์ม
            document.getElementById('popupForm').style.display = 'flex';
        }

        function closeForm() {
            // ซ่อนฟอร์ม
            document.getElementById('popupForm').style.display = 'none';
        }
    </script>

    <script>
        // ฟังก์ชันเปิด Modal และโหลดข้อมูลบิล
        function openBillModal(tableNo) {
            fetch('get_bill.php?table_no=' + tableNo)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('billContent').innerHTML = data;
                    const modal = document.getElementById('billModal');
                    modal.classList.add('show');
                });
        }

        // ฟังก์ชันปิด Modal
        function closeBillModal() {
            const modal = document.getElementById('billModal');
            modal.classList.remove('show');
        }
    </script>
</body>

</html>