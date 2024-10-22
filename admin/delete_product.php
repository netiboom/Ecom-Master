<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../Condatabase/database_shop.php';

$id = $_GET['id'];

try {
  // เริ่มต้นการลบข้อมูลใน order_items
  $sql_delete_order_items = "DELETE FROM order_items WHERE product_id = :id";
  $stmt_delete_order_items = $conn->prepare($sql_delete_order_items);
  $stmt_delete_order_items->bindParam(':id', $id, PDO::PARAM_INT);

  if ($stmt_delete_order_items->execute()) {
    // เมื่อลบข้อมูลใน order_items สำเร็จ ลบข้อมูลใน products
    $sql_delete_product = "DELETE FROM products WHERE id = :id";
    $stmt_delete_product = $conn->prepare($sql_delete_product);
    $stmt_delete_product->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt_delete_product->execute()) {
      header("Location: admin_product.php");
      exit();
    } else {
      echo "Error deleting product: " . $stmt_delete_product->errorInfo()[2];
    }
  } else {
    echo "Error deleting order items: " . $stmt_delete_order_items->errorInfo()[2];
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
