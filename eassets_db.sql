-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 10:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(4, 'Desktop PCs / CPUs', 'Fixed computers used for office or personal use.', '2025-12-23 08:48:07'),
(5, 'Laptops / Notebooks / Ultrabooks', 'Portable computers used for work.', '2025-12-23 08:48:26'),
(6, 'All-in-One PC', 'Computers with built-in monitor and system unit.', '2025-12-23 08:48:42'),
(7, 'Tablets', 'Portable touchscreen devices larger than smartphones.', '2025-12-23 08:49:03'),
(8, 'Monitors', 'Computer screens used for work and display output.', '2025-12-23 08:49:12'),
(9, 'TVs / Display Screens', 'Screens used for displaying video and visual content.', '2025-12-23 08:49:23'),
(10, 'Camera & Multimedia Equipment', 'Devices used for capturing photos, videos, and media.', '2025-12-23 08:49:39'),
(11, 'Network & Server Equipment', 'Devices used to manage networks and servers.', '2025-12-23 08:49:58'),
(12, 'Audio & PA Systems', 'Equipment used for sound output and public announcements.', '2025-12-23 08:50:09'),
(13, 'Mobile Devices / Smartphones', 'Handheld mobile phones used for communication and apps.', '2025-12-23 08:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `userid` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `userid`, `password`, `created_at`) VALUES
(1, 'NUR SYAZLEEN ', 'nursyazleen28032003@gmail.com', 'AI220099', '$2y$10$i6lkZhrIuToUs6hF1jGq2uOwqQbr7Q/zqNnbuoe6OlO5C91.womUK', '2025-12-21 06:39:04'),
(4, 'naz', 'naz@gmail.com', 'ai12', '$2y$10$rjKc.caLh2LvOMDmNrowa.3mFZypDwc6ucmX78aARV3IlWnN.DFRC', '2025-12-22 06:33:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `userid` (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
