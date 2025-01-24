-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2025 at 10:51 PM
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
-- Database: `school`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `admin_email`, `admin_password`, `admin_name`) VALUES
(1, 'admin@gmail.com', 'admin123', 'admin'),
(2, 'admin1@gmail.com', 'admin1122', 'admin1122');

-- --------------------------------------------------------

--
-- Table structure for table `challans`
--

CREATE TABLE `challans` (
  `challan_id` int(11) NOT NULL,
  `session` varchar(255) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `challan_month` varchar(50) NOT NULL,
  `due_date` date NOT NULL,
  `fee_type` varchar(50) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challans`
--

INSERT INTO `challans` (`challan_id`, `session`, `class_id`, `section_id`, `challan_month`, `due_date`, `fee_type`, `fee_amount`, `payment_status`) VALUES
(1, '2025-2026', 17, 7, 'February', '2025-01-07', 'Quarterly', 2000.00, 'unpaid'),
(2, '2025-2026', 8, 5, 'January', '2025-01-09', 'Quarterly', 1500.00, 'unpaid'),
(3, '2025-2026', 8, 5, 'February', '2025-01-14', 'Monthly', 1500.00, 'unpaid'),
(4, '2025-2026', 8, 5, 'March', '2025-01-15', 'Quarterly', 1500.00, 'unpaid'),
(5, '2025-2026', 17, 4, 'February', '2025-01-09', 'Monthly', 2000.00, 'unpaid'),
(6, '2025-2026', 8, 5, 'January', '2025-01-13', 'Yearly', 1500.00, 'unpaid'),
(7, '2025-2026', 11, 5, 'January', '2025-01-02', 'Quarterly', 1500.00, 'unpaid'),
(8, '2025-2026', 17, 7, 'January', '2025-01-08', 'Monthly', 2000.00, 'unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `standard_monthly_fee` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `standard_monthly_fee`, `created_at`) VALUES
(5, 'Play Group', 1300.00, '2025-01-11 15:34:28'),
(6, 'Nursery', 1400.00, '2025-01-11 15:34:28'),
(7, 'Prep', 1400.00, '2025-01-11 15:34:28'),
(8, 'One', 1200.00, '2025-01-11 15:34:28'),
(9, 'Two', 1500.00, '2025-01-11 15:34:28'),
(10, 'Three', 1500.00, '2025-01-11 15:34:28'),
(11, '4th', 1500.00, '2025-01-11 15:34:28'),
(12, '5th', 1500.00, '2025-01-11 15:34:28'),
(13, '6th', 1600.00, '2025-01-11 15:34:28'),
(14, '7th', 1600.00, '2025-01-11 15:34:28'),
(15, 'Pre9th', 1700.00, '2025-01-11 15:34:28'),
(16, '9th', 2000.00, '2025-01-11 15:34:28'),
(17, '10th', 2000.00, '2025-01-11 15:34:28');

-- --------------------------------------------------------

--
-- Table structure for table `feechallans`
--

CREATE TABLE `feechallans` (
  `challan_id` int(11) NOT NULL,
  `session_year` varchar(9) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `challan_month` varchar(20) NOT NULL,
  `due_date` date DEFAULT NULL,
  `fee_type` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `challan_id` int(11) DEFAULT NULL,
  `payment_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `payment_date` date DEFAULT NULL,
  `payment_amount` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `challan_id`, `payment_status`, `payment_date`, `payment_amount`) VALUES
(1, 15, NULL, 'unpaid', '2025-01-13', 1000),
(2, 15, NULL, 'unpaid', '2025-01-13', 500),
(3, 16, NULL, 'unpaid', '2025-01-13', 500),
(4, 16, NULL, 'unpaid', '2025-01-13', 200),
(5, 16, NULL, 'unpaid', '2025-01-13', 1300),
(6, 17, NULL, 'unpaid', '2025-01-13', 700),
(7, 17, NULL, 'unpaid', '2025-01-13', 800),
(8, 18, NULL, 'unpaid', '2025-01-13', 200),
(9, 18, NULL, 'unpaid', '2025-01-13', 1300),
(10, 22, NULL, 'unpaid', '2025-01-16', 1400),
(11, 22, NULL, 'unpaid', '2025-01-16', 100),
(12, 19, NULL, 'unpaid', '2025-01-16', 100);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `created_at`) VALUES
(4, 'pink', '2025-01-11 15:51:44'),
(5, 'green', '2025-01-11 15:51:44'),
(6, 'red', '2025-01-11 15:51:44'),
(7, 'blue', '2025-01-11 15:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `session_name`, `start_date`, `end_date`, `created_at`) VALUES
(1, '2025 - 2026', '2025-01-01', '2026-01-01', '2025-01-10 10:21:04'),
(2, '2015 - 2016', '2015-01-01', '2016-01-01', '2025-01-10 10:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `family_code` varchar(20) DEFAULT NULL,
  `student_name` varchar(100) NOT NULL,
  `session` int(11) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `gr_no` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `date_of_admission` date DEFAULT NULL,
  `status` enum('active','struck_off') DEFAULT 'active',
  `whatsapp_number` varchar(15) DEFAULT NULL,
  `father_cell_no` varchar(15) DEFAULT NULL,
  `mother_cell_no` varchar(15) DEFAULT NULL,
  `home_cell_no` varchar(15) DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_image` text NOT NULL,
  `admission_fee` decimal(10,2) NOT NULL,
  `monthly_fee` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `family_code`, `student_name`, `session`, `class_id`, `section_id`, `gr_no`, `gender`, `religion`, `dob`, `date_of_admission`, `status`, `whatsapp_number`, `father_cell_no`, `mother_cell_no`, `home_cell_no`, `place_of_birth`, `state`, `city`, `email`, `father_name`, `mother_name`, `home_address`, `created_at`, `student_image`, `admission_fee`, `monthly_fee`) VALUES
(15, '1', 'balach', 2, 8, 5, NULL, 'male', 'islam', '2025-01-06', '2025-01-28', 'struck_off', '03313345084', '03313345084', '03313345084', '03313345084', 'hospital', 'sindh', 'Karachi', 'balachzehr7@gmail.com', 'nazir', 'hameeda', 'G-6 APSARA APPARTMENT BLOCK 16 GULSHAN-E-IQBAL KARACHI', '2025-01-11 15:55:24', 'uploads/WhatsApp_Image_2025-01-04_at_00.44.19_fba5e1aa-removebg-preview.png', 0.00, 0.00),
(16, '1', 'jokhay', 1, 17, 4, NULL, 'male', 'islam', '2025-01-15', '2025-01-28', 'struck_off', '03313345084', '03313345084', '03313345084', '03313345084', 'hospital', 'sindh', 'Karachi', 'balachzehr7@gmail.com', 'nazir', 'hameeda', 'G-6 APSARA APPARTMENT BLOCK 16 GULSHAN-E-IQBAL KARACHI', '2025-01-13 13:01:16', 'uploads/backend developer.jpg', 0.00, 0.00),
(17, '2', 'muhammad hadi', 1, 8, 5, NULL, 'male', 'islam', '2003-10-26', '2025-01-13', 'active', '03153897198', '03213897198', '03312208885', '4822634', 'karachi', 'sindh', 'Karachi', 'hadi.habib315@gmail.com', 'muhammad habib', 'sabeen habib', 'Karachi', '2025-01-13 14:31:16', 'uploads/Untitled.png', 0.00, 0.00),
(18, '3', 'Muhammad Ahmed', 1, 11, 5, NULL, 'male', 'islam', '2025-06-09', '2025-01-13', 'active', '123456', '00099887', '7766566', '23456', 'karachi', 'sindh', 'Karachi', 'ahmed123@gmail.com', 'hadi', 'eesha', 'Karachi', '2025-01-13 15:29:01', 'uploads/Untitled.png', 0.00, 0.00),
(19, '4', 'talha anjum', 1, 8, 4, NULL, 'male', 'Islam', '2025-01-17', '2025-01-21', 'active', NULL, '03213897198', '03312208885', NULL, NULL, 'sindh', 'Karachi', 'balachzehr7@gmail.com', 'younus', 'anjum', 'G-6 APSARA APPARTMENT BLOCK 16 GULSHAN-E-IQBAL KARACHI', '2025-01-15 06:31:23', 'uploads/pexels-catiamatos-1072179.jpg', 4500.00, 1300.00),
(22, '5', 'balach', 1, 11, 7, NULL, 'male', 'Islam', '2025-01-15', '2025-01-16', 'active', NULL, '03313345084', '03313345084', NULL, NULL, 'sindh', 'Karachi', 'balachzehr7@gmail.com', 'younus', 'anjum', 'G-6 APSARA APPARTMENT BLOCK 16 GULSHAN-E-IQBAL KARACHI', '2025-01-16 06:15:34', 'uploads/pexels-catiamatos-1072179.jpg', 0.00, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `challans`
--
ALTER TABLE `challans`
  ADD PRIMARY KEY (`challan_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `feechallans`
--
ALTER TABLE `feechallans`
  ADD PRIMARY KEY (`challan_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `challan_id` (`challan_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `fk_session` (`session`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `challan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `feechallans`
--
ALTER TABLE `feechallans`
  MODIFY `challan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `challans`
--
ALTER TABLE `challans`
  ADD CONSTRAINT `challans_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `challans_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `feechallans`
--
ALTER TABLE `feechallans`
  ADD CONSTRAINT `feechallans_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `feechallans_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `feechallans_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`challan_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_session` FOREIGN KEY (`session`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
