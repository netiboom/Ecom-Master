<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update order status
    $order_id = $_POST["order_id"];
    $number_order = $_POST["orderNumber"];
    $transport = $_POST['transport'];

    // Update database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ecom";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE orders SET orderNumber = ? , transport = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $number_order, $transport, $order_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo '<script>alert("อัพเดทสำเร็จ"); location.href="admin_order.php";</script>';
    } else {
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <!-- Add your Bootstrap and other CSS files here -->
</head>

<body>

</body>

</html>