-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 01:05 PM
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
-- Database: `wbms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing_list`
--

CREATE TABLE `billing_list` (
  `id` int(30) NOT NULL,
  `client_id` int(30) NOT NULL,
  `meter_code` varchar(50) NOT NULL,
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `reading` float(12,2) NOT NULL DEFAULT 0.00,
  `arrears` double(10,2) DEFAULT 0.00,
  `previous` float(12,2) NOT NULL DEFAULT 0.00,
  `rate` float(12,2) NOT NULL DEFAULT 0.00,
  `total` float(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0= pending,\r\n1= paid',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_list`
--

INSERT INTO `billing_list` (`id`, `client_id`, `meter_code`, `reading_date`, `due_date`, `reading`, `arrears`, `previous`, `rate`, `total`, `status`, `date_created`, `date_updated`) VALUES
(1, 1, 'MTR-001', '2026-01-01', '2026-02-01', 5.00, 0.00, 0.00, 5.00, 25.00, 1, '2026-04-20 13:11:41', '2026-04-20 13:16:41'),
(2, 1, 'MTR-001', '2026-02-01', '2026-03-01', 10.00, 0.00, 5.00, 5.00, 25.00, 1, '2026-04-20 13:13:03', '2026-04-20 13:28:31'),
(3, 1, 'MTR-001', '2026-03-01', '2026-04-01', 20.00, 0.00, 10.00, 5.00, 50.00, 1, '2026-04-20 13:16:04', '2026-04-20 13:30:39'),
(4, 1, 'MTR-001', '2026-04-01', '2026-05-01', 30.00, 0.00, 20.00, 5.00, 100.00, 1, '2026-04-20 13:30:14', '2026-04-20 13:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `category_list`
--

CREATE TABLE `category_list` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rate` float(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_list`
--

INSERT INTO `category_list` (`id`, `name`, `status`, `delete_flag`, `date_created`, `date_updated`, `rate`) VALUES
(1, 'Residential', 1, 0, '2022-05-02 15:13:02', '2026-04-17 14:54:16', 5.00),
(2, 'Commercial', 1, 0, '2022-05-02 15:13:09', '2026-04-17 14:54:30', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `client_list`
--

CREATE TABLE `client_list` (
  `id` int(30) NOT NULL,
  `code` varchar(100) NOT NULL,
  `category_id` int(30) NOT NULL,
  `firstname` text NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` text NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact` text NOT NULL,
  `address` text NOT NULL,
  `purok` varchar(50) DEFAULT NULL,
  `first_reading` float(12,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_list`
--

INSERT INTO `client_list` (`id`, `code`, `category_id`, `firstname`, `middlename`, `lastname`, `gender`, `birthdate`, `contact`, `address`, `purok`, `first_reading`, `status`, `delete_flag`, `date_created`, `date_updated`) VALUES
(1, '202205020001', 1, 'Rashel', 'Baga', 'Dapula', 'Female', '2026-04-20', '12345678901', 'Himos-onan, Saint Bernard, Southern Leyte', 'Purok 1', 0.00, 1, 0, '2026-04-20 13:01:32', '2026-04-20 13:01:32'),
(2, '202205020002', 2, 'Zenmar ', 'May', 'Anduyan', 'Female', '2026-04-21', '12345678902', 'Himos-onan, Saint Bernard, Southern Leyte', 'Purok 2', 0.00, 1, 0, '2026-04-20 13:03:18', '2026-04-20 13:03:18'),
(3, '202205020003', 1, 'Mark ', 'John', 'Caayohan ', 'Male', '2026-04-22', '12345678903', 'Himos-onan, Saint Bernard, Southern Leyte', 'Purok 3', 0.00, 1, 0, '2026-04-20 13:03:52', '2026-04-20 13:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(30) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'Water Billing Management System'),
(6, 'short_name', 'WBMS - PHP'),
(11, 'logo', 'uploads/logo.png?v=1651282049'),
(13, 'user_avatar', 'uploads/user_avatar.jpg'),
(14, 'cover', 'uploads/cover.png?v=1651282061'),
(15, 'rate', '10.75');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `avatar` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`) VALUES
(1, 'Adminstrator', NULL, 'Admin', 'admin', '0192023a7bbd73250516f069df18b500', NULL, NULL, 1, '2026-04-13 21:21:41', '2026-04-13 21:21:41'),
(2, 'Carmelle', NULL, '', 'secretary', 'ad31b430bcdcd1aeb0dc3a10069e229c', NULL, NULL, 2, '2026-04-15 04:45:05', '2026-04-16 14:50:32'),
(4, 'Sophie', 'Mondragon', 'Marcos', 'secretary', '4cd4fb021617493c4a77e9dfb5e40b6c', NULL, NULL, 2, '2026-04-20 13:53:51', '2026-04-20 13:53:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing_list`
--
ALTER TABLE `billing_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_list`
--
ALTER TABLE `client_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_list`
--
ALTER TABLE `billing_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client_list`
--
ALTER TABLE `client_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
