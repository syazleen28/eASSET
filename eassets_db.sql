-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2026 at 01:31 AM
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
(1, 'NUR FARAH BINTI AZMI', 'admin@eassets.com', 'Administrator', 'AI220099', '$2y$10$xQpBBZhYoa8btm8jmyKH3.stBSwO3dmTy3ElnVz/OqSAQnJFP6oE2', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_code` varchar(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `asset_status` varchar(30) DEFAULT 'Available',
  `asset_name` varchar(150) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `serial_number` varchar(150) DEFAULT NULL,
  `supplier` varchar(150) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `assigned_user` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `os_version` varchar(50) DEFAULT NULL,
  `drive_info` text DEFAULT NULL,
  `spec` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_code`, `category_id`, `asset_status`, `asset_name`, `brand`, `serial_number`, `supplier`, `purchase_date`, `purchase_cost`, `manufacture_date`, `warranty`, `location`, `assigned_user`, `description`, `os`, `os_version`, `drive_info`, `spec`, `created_at`, `remark`) VALUES
(10, 'AST-0001', 27, 'In Use', 'CPU Acer TC-605', 'Acer', 'DTSRQSM0083460807B3000', 'ABC Technologies Sdn. Bhd', '2024-03-28', 2500.00, '2023-01-07', '3 Months', 'Staff Area Downstairs', 'Ahmad', 'Used for programming', 'Windows 11', 'Windows 11 Pro, 64-bit', '(C:) 143 GB free of 237 GB, (D:) 53.4 GB free of 149 GB, (E:) 763 GB free of 780 GB', '256 GB/4.00GB/12th Gen Intel® Core™ i5-12400 700 GB/4.00GB/Intel® Pentium® CPU G3220', '2026-01-12 01:53:15', NULL),
(11, 'AST-0002', 27, 'Available', 'PC Dell I7', 'Dell', 'DLI7-2026001', 'ABC Technologies Sdn. Bhd', '2026-01-01', 6700.00, '2025-12-28', '3 Months', 'Staff Area Downstair', NULL, NULL, 'Window', 'Windows 11 Pro 64-bit', '(C:) 100 GB free of 230 GB , (D:) 223 GB free of 244 GB', '1TB/12.0GB/Intel® Core™ i7-9700 CPU@3.00 GHZ', '2026-01-12 01:59:29', NULL),
(16, 'AST-0003', 27, 'Damaged', 'CPU HP Pavillion 7000 series', 'HP', '4CE1828WB', 'Alpha Electronics', '2025-12-29', NULL, '2025-12-11', '3 Months', 'Staff Area Downstair', NULL, NULL, 'Windows 11', 'Windows 11 Pro, 64-bit', '(C:) 97.5 GB free of 256 GB , (D:) 149 GB free of 209 GB A', '256 GB/4.00GB/12th Gen Intel® Core™ i5-12400 700 GB/4.00GB/Intel® Pentium® CPU G3220', '2026-01-12 02:05:42', 'Distrupted'),
(17, 'AST-0004', 27, 'Maintenance', 'CPU Asus i3', 'Asus', 'SM001229096814202', 'Alpha Electronics', '2025-12-03', NULL, '2025-08-21', '3 Months', 'Staff Area Downstair', 'Nur Aisyah', 'System Analyst', 'Windows 11', 'Windows 11 Pro, 64-bit', '(C:) 147 GB free of 237 GB, (D:) 930 GB free of 931 GB', '256 GB/4.00GB/12th Gen Intel® Core™ i5-12400 700 GB/4.00GB/Intel® Pentium® CPU G3220', '2026-01-12 02:10:04', NULL),
(18, 'AST-0005', 32, 'Available', 'Monitor Acer 20\"', 'Acer', 'MMLZ45500633059F92423', 'Beta Tech Services', '2025-07-15', 7000.00, '2025-07-02', '2 month', 'Meeting Room', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 02:16:48', NULL);

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
(27, 'Desktop Computer', 'A stationary computer used for office work, data processing, and daily operations. Examples: CPU', '2025-12-28 06:13:35'),
(28, 'Laptop / Notebook', 'A portable computer used for work, study, and on-the-go tasks. Examples: Dell Latitude, HP ProBook, MacBook.', '2025-12-28 06:13:35'),
(29, 'All-in-One PC', 'A computer with all components built into a single monitor unit to save space. Examples: iMac, HP All-in-One PC.', '2025-12-28 06:13:35'),
(30, 'Tablet', 'A touchscreen mobile device used for browsing, presentations, and basic tasks. Examples: iPad, Samsung Galaxy Tab.', '2025-12-28 06:13:35'),
(31, 'Smartphone / Mobile Device', 'A handheld device used for communication, apps, and mobile system access. Examples: Android phone, iPhone.', '2025-12-28 06:13:35'),
(32, 'Monitor / Display', 'A screen used to display visual output from computers or other devices. Examples: LCD monitor, LED display, curved monitor.', '2025-12-28 06:13:35'),
(33, 'Television / Large Display', 'A large screen used for presentations, meetings, or information display. Examples: Smart TV, LED TV, digital signage display.', '2025-12-28 06:13:35'),
(34, 'Camera & Photography Equipment', 'Devices used for capturing photos and videos for documentation or media purposes. Examples: DSLR camera, mirrorless camera, tripod.', '2025-12-28 06:13:35'),
(35, 'Audio & PA System', 'Equipment used for sound amplification during meetings, events, or announcements. Examples: microphone, speaker, amplifier, mixer.', '2025-12-28 06:13:35'),
(36, 'Networking & Security Equipment', 'Devices used to manage network connections and ensure system security. Examples: router, switch, firewall, CCTV camera.', '2025-12-28 06:13:35'),
(37, 'Server & Server Accessories', 'Hardware used to store, manage, and process data for multiple users or systems. Examples: rack server, NAS, server rack, UPS.', '2025-12-28 06:13:35'),
(38, 'Power & Electrical Equipment', 'Devices that supply, control, or protect electrical power for equipment. Examples: UPS, extension cord, power adapter.', '2025-12-28 06:13:35'),
(39, 'Accessories & Peripherals', 'Additional devices that support main equipment functions.Example: keyboard, mouse.', '2025-12-28 06:13:35');

-- --------------------------------------------------------

--
-- Table structure for table `asset_maintenance`
--

CREATE TABLE `asset_maintenance` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `issue_occurred` text NOT NULL,
  `issue_date` date NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `maintenance_location` varchar(150) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_taken` text DEFAULT NULL,
  `date_completed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_maintenance`
--

INSERT INTO `asset_maintenance` (`id`, `asset_id`, `issue_occurred`, `issue_date`, `reported_by`, `maintenance_location`, `additional_notes`, `created_at`, `action_taken`, `date_completed`) VALUES
(19, 17, 'cpu heat', '2026-01-14', 'NUR FARAH BINTI AZMI', 'cds. ddn bhd', 'panas', '2026-01-12 03:12:32', NULL, NULL);

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
(9, 'Global Tech Solutions Sdn. Bhd', '12, Jalan Bukit Indah, 81200 Johor Bahru, Johor', '012-3456789', '2025-12-28 07:25:39'),
(10, 'Prime Electronics Sdn. Bhd', '45, Jalan Tebrau, 80000 Johor Bahru, Johor', '013-9876543', '2025-12-28 07:25:39'),
(11, 'Maju IT Services', '78, Jalan Skudai, 81300 Johor Bahru, Johor', '014-1122334', '2025-12-28 07:25:39'),
(12, 'NextGen Computers', '22, Jalan Seri Alam, 81750 Masai, Johor', '011-5566778', '2025-12-28 07:25:39'),
(13, 'Innovative Hardware Sdn. Bhd', '5, Jalan Kempas Baru, 81200 Johor Bahru, Johor', '019-3344556', '2025-12-28 07:25:39'),
(14, 'TechWorld Sdn. Bhd', '10, Jalan Permas Jaya, 81750 Johor Bahru, Johor', '011-2233445', '2026-01-11 23:33:23'),
(15, 'Vision IT Solutions', '55, Jalan Molek 1/2, 81100 Johor Bahru, Johor', '012-3344556', '2026-01-11 23:33:23'),
(16, 'Alpha Electronics', '33, Jalan Dato Onn, 80000 Johor Bahru, Johor', '013-4455667', '2026-01-11 23:33:23'),
(17, 'Beta Tech Services', '21, Jalan Taman Sutera, 81300 Johor Bahru, Johor', '014-5566778', '2026-01-11 23:33:23'),
(18, 'Gamma IT Solutions', '77, Jalan Seri Alam, 81750 Masai, Johor', '019-6677889', '2026-01-11 23:33:23');

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
(48, 'RUHANIM BINTI KAMIN', 'AI112233', 'ai220099@student.uthm.edu.my', 'IT', 'AI112233', '$2y$10$nudehtxXOli..bqU5X4/S.lgMSz0wk6rhkY5y3gVwN/64CfnBYfGC', NULL, 'active', '2025-12-28 04:27:21'),
(53, 'NUR SYAZLEEN BINTI JALALUDDIN', 'AI112232', 'nursyazleen28032003@gmail.com', 'IT', 'AI112232', '$2y$10$591YW5WCCG/7lMEgplNzzOqYGxhuRL.KOKCVIeQKl68LKhf4HP7v6', NULL, 'active', '2025-12-28 06:02:14');

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
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD KEY `fk_assets_category` (`category_id`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asset_maintenance` (`asset_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_assets_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD CONSTRAINT `fk_asset_maintenance` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
