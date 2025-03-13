-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 12:15 AM
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
-- Database: `procurement`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreements`
--

CREATE TABLE `agreements` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `contract_name` varchar(255) NOT NULL,
  `signed_date` date NOT NULL,
  `expired_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agreements`
--

INSERT INTO `agreements` (`id`, `vendor_id`, `contract_name`, `signed_date`, `expired_date`, `created_by`, `created_at`) VALUES
(26, 40, 'cars233', '2024-11-20', '2024-11-14', 1, '2024-11-06 14:18:20'),
(18, 9, 'test', '2024-10-01', '2024-10-01', 1, '2024-10-22 07:50:08'),
(17, 9, 'test', '2024-10-01', '2024-10-01', 1, '2024-10-22 07:49:20'),
(27, 40, 'بيع سيارات', '2024-11-06', '2024-11-09', 1, '2024-11-08 22:57:11'),
(15, 28, '2', '2024-10-23', '2024-10-23', 1, '2024-10-22 07:39:29'),
(14, 9, 'test', '2024-10-02', '2024-10-13', 1, '2024-10-22 07:37:31'),
(23, 28, 'استئجار 20 لاب', '2024-10-30', '2025-02-28', 1, '2024-10-30 15:37:33'),
(24, 7, 'tetsetset', '2024-10-10', '2024-10-09', 1, '2024-10-31 20:05:30'),
(28, 37, 'kkkk', '2024-11-04', '2024-11-25', 1, '2024-11-09 16:02:31'),
(29, 47, 'car', '2024-11-10', '2024-11-18', 1, '2024-11-09 22:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_users`
--

CREATE TABLE `procurement_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `account_locked` tinyint(4) DEFAULT 0,
  `lock_time` datetime DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `procurement_users`
--

INSERT INTO `procurement_users` (`id`, `username`, `password`, `profile_picture`, `created_at`, `login_attempts`, `account_locked`, `lock_time`, `role`) VALUES
(1, 'admin', '$2y$10$zOqHda20/aBQavWatmTJO.kmr19YiX0aaXXMDNFTEprpY1GslcVFa', 'uploads/44a404b971.jpg', '2024-10-06 08:03:14', 0, 0, NULL, 'admin'),
(17, 'Abdalla@gmail.com', '$2y$10$l1YkPS0DcMTdGIyqCLnmC.72h5vmJZUmgRWg9SHBUMUpBlsWeBROy', '', '2024-11-04 14:59:32', 2, 0, NULL, 'user'),
(19, 'Abdallaa@gmail.com', '$2y$10$h56gh649FrCYbXcy5Ufy7eTEkZIkeW.B7RkD.ewgQ99oqmyB0eY/C', '', '2024-11-04 15:11:14', 0, 0, NULL, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `authorized_signatory` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `vendor_field` enum('Suppliers','Consultant','Contractors','Sub-contractor') NOT NULL,
  `email` varchar(100) NOT NULL,
  `experience` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `account_locked` tinyint(4) DEFAULT 0,
  `lock_time` datetime DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `pic_id` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `company_name`, `authorized_signatory`, `phone_number`, `address`, `vendor_field`, `email`, `experience`, `password`, `created_at`, `login_attempts`, `account_locked`, `lock_time`, `verified`, `profile_picture`, `group_id`, `pic_id`) VALUES
(4, 'Paltech', '', '', '', '', '', NULL, '$2y$10$o60i/SXaA9cZHYtsFmQ2ren5W7WyYT2B6x3mvPGB8Y3Wc/pa0G.ju', '2024-10-07 12:57:32', 0, 0, NULL, NULL, 'uploads/Paltech.jpg', 1, NULL),
(5, 'Carmel', '', '', '', 'Suppliers', '', NULL, '$2y$10$j52rXlgnZDkjkaqnh2ycu.8R/WsJtCUin5krJwbbhwwynTU0j43Aa', '2024-10-09 07:21:48', 0, 0, NULL, NULL, 'uploads/Carmel.jpg', 1, NULL),
(6, 'watani mall', '', '', '', 'Suppliers', '', NULL, '$2y$10$Ez0YivSlCxcWKL9mMnpqYeHIduPOSIfLbBK3xGugaLR4WjspngcAy', '2024-10-09 07:45:24', 0, 0, NULL, NULL, 'uploads/watani mall.jpg', 1, NULL),
(7, 'bcif', '', '', '', 'Suppliers', 'bci@bci.ps', NULL, '$2y$10$DkVDw5DL6t824j8m2tlGsuY/HxvShRh5XNO0OTfqIJxH5z67CYAki', '2024-10-09 11:03:15', 0, 0, NULL, 1, 'uploads/bcif.jpg', 1, NULL),
(8, 'Vendor A2', '', '', '', 'Suppliers', '', NULL, 'password1', '2024-10-09 12:55:03', 0, 0, NULL, NULL, NULL, 1, NULL),
(9, 'Vendor B', 'Bob Johnson2', '234-567-8901', '456 Oak St, Townsville', 'Consultant', 'vendorB@example.comb', '10 years in IT consultingw', 'password2', '2024-10-09 12:55:03', 0, 0, NULL, 0, NULL, 0, NULL),
(10, 'Vendor C', 'Charlie Brown', '345-678-901211', '789 Pine St, Villageville11', 'Contractors', 'vendorC@example.com', '8 years in constructionnb', 'password3', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, 19, NULL),
(11, 'Vendor D', 'David Wilsonooo', '456-789-01230', '101 Maple St, Hamletville', 'Sub-contractor', 'vendorD@example.com', '3 years in subcontracting', 'password4', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, 0, NULL),
(12, 'Vendor E', 'Eva Green', '567-890-1234', '202 Birch St, Metropoliswwdw', 'Suppliers', 'vendorE@example.com', '4 years in wholesale', 'password5', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, 0, NULL),
(13, 'Vendor F', 'Frank White', '678-901-2345', '303 Cedar St, Capital City', 'Consultant', 'vendorF@example.com', '12 years in project management', 'password6', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(14, 'Vendor G', 'Grace Black', '789-012-3456', '404 Spruce St, Countyville', 'Contractors', 'vendorG@example.com', '6 years in renovation', 'password7', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(15, 'Vendor H', 'Henry Gray', '890-123-4567', '505 Walnut St, Boroughville', 'Sub-contractor', 'vendorH@example.com', '2 years in ', 'password8', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(16, 'Vendor I222', 'Isabella Blue', '901-234-5678', '606 Fir St, Districtville', 'Suppliers', 'vendorI@example.com', '7 years in manufacturing', 'password9', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(17, 'Vendor J', 'James Green', '012-345-6789', '707 Ash St, Regionville', 'Consultant', 'vendorJ@example.com', '15 years in business analysis', 'password10', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(18, 'Vendor K', 'Kate Red', '123-456-7891', '808 Hickory St, Stateville', 'Contractors', 'vendorK@example.com', '9 years in civil engineering', 'password11', '2024-10-09 12:55:03', 0, 0, NULL, 0, NULL, NULL, NULL),
(19, 'Vendor L', 'Leo Orange', '234-567-8902', '909 Chestnut St, Provinceville', 'Sub-contractor', 'vendorL@example.com', '5 years in roofing', 'password12', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(20, 'Vendor M', 'Mia Pink', '345-678-9013', '111 Sycamore St, Territoryville', 'Suppliers', 'vendorM@example.com', '11 years in agriculture', 'password13', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(21, 'Vendor N', 'Noah Brown', '456-789-0124', '222 Willow St, AreaVille', 'Consultant', 'vendorN@example.com', '13 years in environmental consultings', 'password14', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, 0, NULL),
(22, 'Vendor O', 'Olivia Black', '567-890-1235', '333 Pineapple St, ZoneVille', 'Contractors', 'vendorO@example.com', '14 years in general contracting', 'password15', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(23, 'Vendor P', '', '', '', 'Sub-contractor', '', NULL, 'password16', '2024-10-09 12:55:03', 0, 0, NULL, NULL, NULL, NULL, NULL),
(24, 'Vendor Q', 'Quinn Green', '789-012-3457', '555 Blueberry St, PartVille', 'Suppliers', 'vendorQ@example.com', '6 years in logistics', 'password17', '2024-10-09 12:55:03', 0, 0, NULL, 0, NULL, NULL, NULL),
(25, 'Vendor R', 'Rachel Purpleee', '890-123-4568', '666 Peach St, SuburbVille', 'Consultant', 'vendorR@example.com', '10 years in software development', 'password18', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(26, 'Vendor S', 'Sam Orange', '901-234-5679', '777 Apricot St, EdgeVille', 'Contractors', 'vendorS@example.com', '3 years in construction management', 'password19', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(27, 'Vendor T', 'Tina Gold', '012-345-6780', '888 Grapefruit St, EndVille', 'Sub-contractor', 'vendorT@example.com', '7 years in excavation', 'password20', '2024-10-09 12:55:03', 0, 0, NULL, 1, NULL, NULL, NULL),
(28, 'Shaher2', 'Shaher', '0599999999', 'Ramallah121212', 'Suppliers', 'Shaher@gmail.com', '10 year ', '$2y$10$U2S6cQtN4KTezxC6xD57uuAGFk9fb/RnOWQF5NJenu/ptuQzxyZy6', '2024-10-16 13:10:39', 0, 0, NULL, 1, NULL, 1, NULL),
(30, 'test', 'test', 'test', 'test', 'Suppliers', 'test@asd', '', '$2y$10$LDrRvhERyvP6FzgHLByviuqrlYdh2mOuFBnqhPS9F4Jt9VtciQ9iu', '2024-10-30 15:55:54', 0, 0, NULL, 1, NULL, NULL, NULL),
(31, 'test1', 'test1', 'test1', 'test1', 'Suppliers', 'test1@raswer', 'test1', '$2y$10$PsjjZeyeB2/2.J2byAewS.2BZlrN5ahpDqrz9Zai/UpKs9JitITd.', '2024-10-30 15:58:00', 0, 0, NULL, 1, NULL, NULL, NULL),
(40, 'abdallaaa@gmail.com', 'asd', '654654', 'abdall122@gmail.com', 'Contractors', 'abdallaaa@gmail.com', 'asdas', '$2y$10$9HRq4/D3fj4.ZI0p9WK1HuK6jb/1z7fuYNajXipJgZavUVvy8U412', '2024-11-06 13:18:48', 1, 0, NULL, 1, 'uploads/4cb2e4ab5b.jpg', 1, 'uploads/6f2f8c1373.jpg'),
(37, 'abdallaa@gmail.com', 'asd', '654654', 'asds', 'Contractors', 'abdallaa@gmail.com', '11111', '$2y$10$BJBPZQkvYvXCBMeBHT9yz.lJhwO4.mZwS8UjfH1M1dLViZvnZitse', '2024-11-06 12:54:42', 1, 0, NULL, 1, 'uploads/abdallaa@gmail.com.jpg', 0, 'uploads/abdallaa@gmail.com2.jpg'),
(41, 'abdallaaaa@gmail.com', 'asd', '654654', 'abdallaaaa@gmail.com', 'Consultant', 'abdallaaaa@gmail.com', 'asdas', '$2y$10$Wjz2ggdmsnNdmn0tbKSx6O.s44lqDhdHV8My7ssEkspcz1kixJdWu', '2024-11-08 22:58:08', 0, 0, NULL, 0, 'uploads/3cdc01b2e5.jpg', NULL, NULL),
(42, 'abdalla2@gmail.com', 'asd', '345-678-901211', 'abdalla2@gmail.com', 'Sub-contractor', 'abdalla2@gmail.com', '11', '$2y$10$J3uGLEvhW7SmfNp7ZKPYN.i53lRSW8wQiNC9B36/9myVyXxzVmuEK', '2024-11-08 23:00:54', 0, 0, NULL, 0, 'uploads/eb255ea2e4.jpg', NULL, 'uploads/f4217da0f0.jpg'),
(43, 'abdalla3@gmail.com', 'asda', '345-678-901211', 'abdalla3@gmail.com', 'Contractors', 'abdalla3@gmail.com', '1111', '$2y$10$3qk6fnb9YWG7tBv14b6WB.C009vBDqtBLzRuOvAV/dyeV3cBFHRCW', '2024-11-08 23:03:02', 0, 0, NULL, 0, 'uploads/4f54d2c34b.jpg', NULL, 'uploads/033889ddd8.jpg'),
(46, 'aa', 'aa', 'mm', 'asd', 'Consultant', 'aaa@gmail.com', 'aaa@gmail.com', '$2y$10$3ZywulqMELWDPKFzqalreuisBnXluABcStqZQBdZU.WQ7Z7eeEyii', '2024-11-09 00:15:46', 0, 0, NULL, 1, 'uploads/aa.jpg', NULL, 'uploads/a71a50f3fe.jpg'),
(45, 'aa', 'aa', 'aa', 'asdfgh', 'Consultant', 'aa@gmail.com', 'aa@gmail..com', '$2y$10$6fsZPDFUrOGhyR8xzp1QR.pEm/BxTTRXaA0srYiXX60jYYPOeAVgu', '2024-11-09 00:04:35', 4, 0, NULL, 1, 'uploads/aa.jpg', 0, 'uploads/ededcc270e.jpg'),
(47, 'dd', 'dd', 'dddd', 'dddd', 'Suppliers', 'dd@gmail.com', 'dd@gmail.com', '$2y$10$gQAM7j8Zbx1DrvC8QMm8nugo/dQ1toq1tcwpzOTjKF32OXJg7bktm', '2024-11-09 22:41:52', 0, 0, NULL, 1, 'uploads/dd.jpg', 0, 'uploads/a1d4ecb439.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_groups`
--

CREATE TABLE `vendor_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_groups`
--

INSERT INTO `vendor_groups` (`id`, `group_name`, `description`) VALUES
(1, 'IT', 'Vendor IT'),
(5, 'developer', 'Web developer'),
(6, 'developer', 'Web developer'),
(7, 'developer', 'Web developer'),
(18, 'developeraa', 'Web developer'),
(17, 'developeraa', 'Web developer'),
(19, 'users', 'asdfghjk');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreements`
--
ALTER TABLE `agreements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `procurement_users`
--
ALTER TABLE `procurement_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vendor_group` (`group_id`);

--
-- Indexes for table `vendor_groups`
--
ALTER TABLE `vendor_groups`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreements`
--
ALTER TABLE `agreements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `procurement_users`
--
ALTER TABLE `procurement_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `vendor_groups`
--
ALTER TABLE `vendor_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
