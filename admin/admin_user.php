<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../Condatabase/database_shop.php';

if (!isset($_SESSION['id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

try {


    // ค้นหาข้อมูลผู้ใช้
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ดึงข้อมูลสินค้าทั้งหมด
    $sql = "SELECT * FROM products";
    $stmt = $conn->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลทั้งหมดจากตาราง users
    $sql = "SELECT * FROM users";
    $stmt = $conn->query($sql);
    $alluser = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // นับจำนวนผู้ใช้
    $sqluser = "SELECT COUNT(*) as userCount FROM users";
    $stmt = $conn->query($sqluser);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $row["userCount"];



    // นับจำนวนผู้ใช้ที่มีบทบาทเป็น 'user'
    $sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $row['total_users'];

    // นับจำนวนผู้ใช้ที่มีบทบาทเป็น 'admin'
    $sql = "SELECT COUNT(*) as total_admin FROM users WHERE role = 'admin'";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totaladmin = $row['total_admin'];
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
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
            <li><a href="admin_order.php"><i class='bx bxs-package'></i>Orders</a></li>


            <li class="active"><a href="admin_user.php"><i class='bx bx-group'></i>Users</a></li>

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
                    <h1>All user</h1>
                    <ul class="breadcrumb">

                    </ul>
                </div>

            </div>

            <!-- Insights -->
            <ul class="insights">
                <li>
                    <i class='bx bxs-user'></i>
                    <span class="info">
                        <h3>
                            <?php echo $userCount; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">ผู้ใช้ทั้งหมด</p>
                    </span>
                </li>
                <li><i class='bx bxs-user-account'></i>
                    <span class="info">
                        <h3>
                            <?php echo $totalUsers; ?>
                        </h3>
                        <p>User</p>
                    </span>
                </li>
                <li><i class='bx bxs-user-voice'></i>
                    <span class="info">
                        <h3>
                            <?php echo $totaladmin; ?>
                        </h3>
                        <p style="font-family: 'IBM Plex Sans Thai', sans-serif;">Admin</p>
                    </span>
                </li>
            </ul>
            <!-- End of Insights -->

            <div class="bottom-data">


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
                                    <td><?= $row["id"] ?></td>
                                    <td>

                                        <p><?= $row["firstname"] ?></p>
                                    </td>
                                    <td><?= $row["email"] ?></td>
                                    <td><?= $row["role"] ?></td>
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