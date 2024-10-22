-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 17, 2024 at 04:55 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecom`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE `bank` (
  `id` int NOT NULL,
  `number_bank` varchar(400) NOT NULL,
  `name_bank` varchar(400) NOT NULL,
  `user_bank` varchar(400) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `bank`
--

INSERT INTO `bank` (`id`, `number_bank`, `name_bank`, `user_bank`) VALUES
(1, '3113566630', 'กรุงไทย', 'อิทธิพล บัวลา'),
(2, '020339285429', 'ออมสิน', 'อิทธิพล บัวลา');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `category_name` varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category_name`) VALUES
(5, 'computer set'),
(6, 'Moniter'),
(7, 'Key bord'),
(8, 'Head Phone'),
(11, 'VGA'),
(12, 'Mouse');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `slip` varchar(255) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `order_custom_no` varchar(255) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT 'รอตรวจสอบ',
  `transport` varchar(255) DEFAULT 'กำลังรอผู้ขาย',
  `orderNumber` varchar(255) DEFAULT 'กำลังรอผู้ขาย',
  `total` varchar(255) NOT NULL,
  `quantt` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `fullname`, `address`, `phoneNumber`, `slip`, `user_id`, `order_custom_no`, `payment`, `status`, `transport`, `orderNumber`, `total`, `quantt`, `created_at`) VALUES
(21, 'aitthiphon buala', '10 ม.22 ต.นิค อ.อิทธิ จ.ศรีสะเกษ', '0827379394', '462358487_1678154006251592_7587154410707903834_n.jpg', 17, 'SMKD2F1DBFA', 'โอนผ่านบัญชีธนาคาร', 'ผู้ส่งกำลังเตรียมจัดส่ง', 'flash', 'TH445673', '930', '1', '2024-10-16 07:18:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_address`
--

CREATE TABLE `order_address` (
  `fullname` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `user_id` int NOT NULL,
  `slip` varchar(255) DEFAULT NULL,
  `name_product` varchar(255) DEFAULT NULL,
  `img_product` varchar(255) DEFAULT NULL,
  `quan_product` int DEFAULT NULL,
  `price_product` decimal(10,2) DEFAULT NULL,
  `total_product` decimal(10,2) DEFAULT NULL,
  `numberOrder` varchar(50) DEFAULT 'รอเลขพัสดุ',
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `order_address`
--

INSERT INTO `order_address` (`fullname`, `address`, `phone_number`, `payment_method`, `user_id`, `slip`, `name_product`, `img_product`, `quan_product`, `price_product`, `total_product`, `numberOrder`, `id`) VALUES
('gggr', 'rgrgrrg', '2222222222', 'Array', 17, NULL, 'EGA TYPE CMK1 (BLUE SWITCH) LAYOUT D', 'uploadimg/product19279_800.jpeg', 1, 890.00, 3.00, 'รอเลขพัสดุ', 5),
('test', 'rgrgrrg', '2222222222', 'Array', 17, NULL, '<br />\r\n<b>Warning</b>:  Undefined variable $order1 in <b>C:\\xampp\\htdocs\\smkfire\\checkout.php</b> on line <b>354</b><br />\r\n<br />\r\n<b>Warning</b>:  Trying to access array offset on value of type null in <b>C:\\xampp\\htdocs\\smkfire\\checkout.php</b> on lin', '<br />\r\n<b>Warning</b>:  Undefined variable $order1 in <b>C:\\xampp\\htdocs\\smkfire\\checkout.php</b> on line <b>355</b><br />\r\n<br />\r\n<b>Warning</b>:  Trying to access array offset on value of type null in <b>C:\\xampp\\htdocs\\smkfire\\checkout.php</b> on lin', 0, 0.00, 0.00, 'รอเลขพัสดุ', 6);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `quantity` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_image`, `product_name`, `product_price`, `quantity`) VALUES
(23, 21, 31, 'uploadimg/cb880503b5ef8c7351ea97714b164ca5.jpg_webp_720x720q80.jpg', 'MageGee 60 Mechanical Keyboard, Gaming Keyboard With Blue Switches And Sea Blue Backlit Small Compact 60 Percent Keyboard Mecha ', 890.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_list_user`
--

CREATE TABLE `order_list_user` (
  `id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_price` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `total_price` varchar(255) NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image`, `price`, `created_at`, `user_id`, `category`) VALUES
(26, 'GeForce RTX™ 4060 Ti GAMING X 8G', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy', 'uploadimg/1024.png', 18500.00, '2024-10-15 10:37:41', NULL, 'VGA'),
(28, 'GeForce RTX™ 4070 Ti SUPER 16G GAMING X SLIM', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy', 'uploadimg/1024 (1).png', 22000.00, '2024-10-15 10:46:35', NULL, 'VGA'),
(29, 'LOGITECH GAMING HEADSET G331', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy', 'uploadimg/d4ec21ff-ad47-4946-a717-d8cdd98b83d9.png', 3200.00, '2024-10-15 10:48:49', NULL, 'Head Phone'),
(30, 'คีย์บอร์ด Razer Huntsman V3 Pro TKL Gaming Keyboard (EN)', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy', 'uploadimg/razer-huntsman-v3-pro-tkl-mechanical-gaming-keyboard-top-view.jpg', 7990.00, '2024-10-15 10:51:18', NULL, 'Key bord'),
(31, 'MageGee 60 Mechanical Keyboard, Gaming Keyboard With Blue Switches And Sea Blue Backlit Small Compact 60 Percent Keyboard Mecha ', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy', 'uploadimg/cb880503b5ef8c7351ea97714b164ca5.jpg_webp_720x720q80.jpg', 890.00, '2024-10-15 10:52:45', NULL, 'Key bord'),
(32, 'Mouse test2', 'test2', 'uploadimg/por7hoob0ir5orkle8qo.jpg', 455.00, '2024-10-16 07:25:58', NULL, 'Mouse');

-- --------------------------------------------------------

--
-- Table structure for table `total_all`
--

CREATE TABLE `total_all` (
  `id` int NOT NULL,
  `total` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_all`
--

INSERT INTO `total_all` (`id`, `total`) VALUES
(1, '0'),
(11, '930');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `role`, `email`, `password`) VALUES
(17, 'aitthiphon', 'user', 'nicktoon@gmail.com', '$2y$10$h8KWCOuSRIjGgjLQMbYF5eaFlVtSQooo3z06Mx9zmLqULn4tYqbZG'),
(18, 'admin', 'admin', 'admin@gmail.com', '$2y$10$hSQ7xNwkNc9fYvZIndCOCuV1qXfQTqAE5kScEF0qv3/xEZjv614T2'),
(19, 'test3', 'user', 'test3@gmail.com', '$2y$10$K8H83s4p0t3z/8f9dFBbout39oN32bk0MJRb08.jm4SyZ5Mx0fbHK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_address`
--
ALTER TABLE `order_address`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_list_user`
--
ALTER TABLE `order_list_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `total_all`
--
ALTER TABLE `total_all`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_address`
--
ALTER TABLE `order_address`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_list_user`
--
ALTER TABLE `order_list_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `total_all`
--
ALTER TABLE `total_all`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
