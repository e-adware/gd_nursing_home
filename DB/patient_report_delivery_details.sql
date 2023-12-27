-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 18, 2023 at 10:16 AM
-- Server version: 10.3.38-MariaDB-0ubuntu0.20.04.1
-- PHP Version: 7.4.3-4ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `penguin_his`
--

-- --------------------------------------------------------

--
-- Table structure for table `patient_report_delivery_details`
--

CREATE TABLE `patient_report_delivery_details` (
  `slno` int(11) NOT NULL,
  `patient_id` varchar(50) NOT NULL,
  `opd_id` varchar(50) NOT NULL,
  `ipd_id` varchar(50) NOT NULL,
  `batch_no` int(11) NOT NULL,
  `testid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `remarks` varchar(100) NOT NULL,
  `user` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patient_report_delivery_details`
--
ALTER TABLE `patient_report_delivery_details`
  ADD PRIMARY KEY (`slno`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `opd_id` (`opd_id`),
  ADD KEY `ipd_id` (`ipd_id`),
  ADD KEY `batch_no` (`batch_no`),
  ADD KEY `testid` (`testid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patient_report_delivery_details`
--
ALTER TABLE `patient_report_delivery_details`
  MODIFY `slno` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
