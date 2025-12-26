-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 01:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eassets_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('active') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `email`, `position`, `user_id`, `password`, `status`) VALUES
(1, 'NUR SYAZLEEN', 'admin@eassets.com', 'Administrator', 'AI220099', '$2y$10$xQpBBZhYoa8btm8jmyKH3.stBSwO3dmTy3ElnVz/OqSAQnJFP6oE2', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_status` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `warranty` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `assigned_user` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `category_id`, `asset_name`, `asset_status`, `description`, `supplier`, `warranty`, `location`, `assigned_user`, `serial_number`, `brand`, `manufacture_date`, `purchase_date`, `purchase_cost`, `created_at`, `updated_at`) VALUES
(1, 17, 'Dell Inspiron 15', 'Available', 'WER', 'ABC Technologies Sdn. Bhd', '2 Years', 'IT department', 'NAZ', 'SN123456', 'Dell', '2025-12-03', '2025-12-16', 400.00, '2025-12-24 09:01:01', '2025-12-24 09:01:01'),
(2, 17, 'Dell Inspiron 15', 'Available', 'WER', 'ABC Technologies Sdn. Bhd', '2 Years', 'IT department', 'NAZ', 'SN123456', 'Dell', '2025-12-03', '2025-12-16', 400.00, '2025-12-24 09:10:09', '2025-12-24 09:10:09');

-- --------------------------------------------------------

--
-- Table structure for table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Laptop', 'All types of laptops used by staff', '2025-12-22 08:10:24'),
(17, 'kkk', '', '2025-12-23 22:43:40'),
(18, 'hhh', '', '2025-12-23 22:49:40'),
(19, 'mmm', '', '2025-12-23 22:54:12'),
(20, 'www', 'ss', '2025-12-24 01:39:04'),
(21, 'qqq', '', '2025-12-24 01:42:03'),
(22, 'eees', '', '2025-12-24 01:43:22');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `address`, `phone`, `created_at`) VALUES
(1, 'ABC Technologies Sdn. Bhd', '32, Jln Sentral 1, Taman Nusa Sentral, 79100 Iskandar Puteri, Johor Darul Ta\'zim', '011-12341234', '2025-12-24 08:22:56'),
(2, 'def Technologies Sdn. Bhd', '', '', '2025-12-24 08:26:27'),
(3, 'yut Technologies Sdn. Bhd', '', '', '2025-12-24 08:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `staff_name` varchar(100) DEFAULT NULL,
  `staff_id` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `activation_token` varchar(255) DEFAULT NULL,
  `status` enum('pending','active') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `staff_name`, `staff_id`, `email`, `position`, `user_id`, `password`, `activation_token`, `status`, `created_at`) VALUES
(19, 'Syazleen', 'AI112233', 'nursyazleen28032003@gmail.com', 'Programmer', 'AI112233', '$2y$10$sta1pryyjHPulpwPQWQnHOmNEyrb2Y0tjqupjQkmqqiF9BrJMsfCe', NULL, 'active', '2025-12-24 03:28:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
