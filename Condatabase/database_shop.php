<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=ecom", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


// $servername = "localhost";
// $username = "smkfirew_smk";
// $password = "smk2024fireworks";

// try {
//     $conn = new PDO("mysql:host=$servername;dbname=smkfirew_smk", $username, $password);
//     // set the PDO error mode to exception
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     // echo "Connected successfully";
// } catch (PDOException $e) {
//     echo "Connection failed: " . $e->getMessage();
// }
