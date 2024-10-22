<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../Condatabase/database_shop.php';

if (!isset($_SESSION['id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// แสดงข้อความแจ้งเตือนถ้ามี
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']); // ลบข้อความแจ้งเตือนหลังจากแสดงแล้ว
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
    $resultallproduct = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลผู้ใช้ทั้งหมด
    $sql = "SELECT * FROM users";
    $stmt = $conn->query($sql);
    $alluser = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลจากตาราง orders, order_items และ products
    $sql = "SELECT orders.id as order_id, orders.status as order_status, products.name as product_name, products.image as product_img, users.firstname as username, order_items.quantity as quantity, orders.created_at as order_date
            FROM orders
            JOIN order_items ON orders.id = order_items.order_id
            JOIN products ON order_items.product_id = products.id
            JOIN users ON orders.user_id = users.id
            WHERE orders.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // นับจำนวนสินค้า
    $sqlProducts = "SELECT COUNT(*) as pCount FROM products";
    $stmt = $conn->query($sqlProducts);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pCountProducts = $row["pCount"];

    // รวมจำนวนเงินทั้งหมดในคอลัมน์ total_price


    // รวมจำนวนสินค้า
    $sql = "SELECT SUM(quantity) AS quantity_sum FROM order_items";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $quantitySum = $row["quantity_sum"];
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST["category"];
    try {
        // เตรียมคำสั่ง SQL ด้วย PDO
        $sql = "INSERT INTO category (category_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category]);

        // เปลี่ยนเส้นทางไปยัง admin.php ถ้าการดำเนินการสำเร็จ
        header("Location: admin_product.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}





$results_per_page = isset($_GET['results_per_page']) ? (int)$_GET['results_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $results_per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the query for pagination and searching
$sql = "SELECT * FROM products WHERE name LIKE :search LIMIT :start, :results_per_page";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':results_per_page', $results_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of products
$total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE name LIKE :search");
$total_stmt->bindValue(':search', "%$search%");
$total_stmt->execute();
$total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_products = $total_row['total'];

// Calculate total pages
$total_pages = ceil($total_products / $results_per_page);


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
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    



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
            <li class="active"><a href="admin_product.php"><i class='bx bx-store-alt'></i>Products</a></li>
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
                    <h1>Products</h1>
                    <ul class="breadcrumb">

                    </ul>
                </div>

            </div>

            <!-- Insights -->

            <!-- End of Insights -->

            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Products</h3>

                        <form action="admin_product.php" method="post">
                            <label for="category" style="font-family: 'IBM Plex Sans Thai', sans-serif;">หมวดหมู่</label>
                            <input type="text" name="category" id="category" required>
                            <input style="font-family: 'IBM Plex Sans Thai', sans-serif; font-weight: 400;" type="submit" class="btn btn-primary" value="เพิ่มหมวดหมู่">
                        </form>
                        <a href="add_product.php" style="text-decoration: none; color: #000; transform: translateY(3px);"><i class='bx bx-plus-circle'></i></a>
                    </div>

                    <!-- Search Form -->


                    <!-- Display Count Selector -->

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
                                                <th>Name</th>
                                                <th>Image</th>
                                                <th>Price</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($products as $row) : ?>
                                                <tr>
                                                    <th><?= htmlspecialchars($row['id']); ?></th>
                                                    <td>
                                                        <?php
                                                        $name = $row["name"];
                                                        $max_length = 20;
                                                        if (strlen($name) > $max_length) {
                                                            $name = substr($name, 0, $max_length) . '...';
                                                        }
                                                        echo htmlspecialchars($name);
                                                        ?>
                                                    </td>
                                                    <td><img src="../<?= htmlspecialchars($row['image']); ?>" alt="img" style="width: 70px; height: 70px; object-fit: cover;"></td>
                                                    <td><?= number_format($row['price'], 2); ?> THB</td>
                                                    <td>
                                                        <?php
                                                        $description = $row["description"];
                                                        $max_length = 20;
                                                        if (strlen($description) > $max_length) {
                                                            $description = substr($description, 0, $max_length) . '...';
                                                        }
                                                        echo htmlspecialchars($description);
                                                        ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['category']); ?></td>
                                                    <td>
                                                        <a href="edit_product.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-warning">Edit</a>
                                                        <a href="delete_product.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger" onclick="confirmDelete(event, '<?= htmlspecialchars($row['id']); ?>')">Delete</a>

                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>


            </div>


        </main>


    </div>

    <script src="index.js"></script>

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmDelete(event, id) {
    event.preventDefault(); // ป้องกันการเปลี่ยนหน้าอัตโนมัติ

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        // ถ้าผู้ใช้ยืนยัน ให้เปลี่ยนหน้าไปยังลิงก์ลบ
        window.location.href = 'delete_product.php?id=' + id;
      }
    });
  }
</script>

</body>

</html>