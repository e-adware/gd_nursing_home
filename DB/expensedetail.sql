-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 10, 2023 at 12:15 PM
-- Server version: 10.3.38-MariaDB-0ubuntu0.20.04.1
-- PHP Version: 7.4.3-4ubuntu2.19

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
-- Table structure for table `expensedetail`
--

CREATE TABLE `expensedetail` (
  `slno` int(11) NOT NULL,
  `branch` int(11) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `cheque_no` varchar(20) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `description` varchar(550) NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  `expense_date` date NOT NULL,
  `entry_date` date NOT NULL,
  `entry_time` varchar(50) NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expensedetail`
--

INSERT INTO `expensedetail` (`slno`, `branch`, `mode`, `invoice_no`, `ledger_id`, `cheque_no`, `bank_name`, `description`, `Amount`, `expense_date`, `entry_date`, `entry_time`, `user`) VALUES
(1, 1, 'Cash', '55555', 8, '', '', 'electric fittings for OPD room 2', '2000.00', '2023-09-27', '2023-09-27', '11:43:33 AM', 102),
(3, 1, 'Cash', '1', 14, '', '', 'Stationary items', '1000.00', '2023-09-19', '2023-09-19', '01:21:17 PM', 101),
(35, 1, 'Cash', '1025', 11, '', '', 'test', '100.00', '2023-10-05', '2023-09-26', '10:49:07 AM', 102),
(39, 1, 'Cash', '1E-22569', 8, '', '', 'Light Bill', '4590.00', '2023-10-03', '2023-10-09', '01:01:23 PM', 102),
(42, 2, 'Cash', '22EB6', 10, '', '', 'bought table', '3500.00', '2023-10-09', '2023-10-09', '04:56:22 PM', 102);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `expensedetail`
--
ALTER TABLE `expensedetail`
  ADD PRIMARY KEY (`slno`),
  ADD KEY `ledger_id` (`ledger_id`),
  ADD KEY `expense_date` (`expense_date`),
  ADD KEY `entry_date` (`entry_date`),
  ADD KEY `user` (`user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expensedetail`
--
ALTER TABLE `expensedetail`
  MODIFY `slno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
