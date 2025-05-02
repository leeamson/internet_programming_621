-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 02, 2025 at 10:21 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `PayPal` double NOT NULL,
  `Cryptocurrency` double NOT NULL,
  `CreditCard` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `user_id`, `PayPal`, `Cryptocurrency`, `CreditCard`) VALUES
(16, 4, 100000, 150000, 120000),
(17, 5, 200000, 180000, 150000),
(18, 6, 20000, 34980, 30000);

-- --------------------------------------------------------

--
-- Table structure for table `payment_type`
--

CREATE TABLE `payment_type` (
  `id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_fee` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_type`
--

INSERT INTO `payment_type` (`id`, `payment_method`, `transaction_fee`) VALUES
(1, 'PayPal', 15),
(2, 'CreditCard', 5),
(5, 'Cryptocurrency', 20);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `amount` double NOT NULL,
  `payment_method` int(11) NOT NULL,
  `date_created` date NOT NULL,
  `descriptions` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user`, `amount`, `payment_method`, `date_created`, `descriptions`, `transaction_id`) VALUES
(61, 4, 100000, 1, '2025-05-02', 'Deposit', '20250502214022'),
(62, 4, 150000, 2, '2025-05-02', 'Deposit', '20250502214022'),
(63, 4, 120000, 5, '2025-05-02', 'Deposit', '20250502214022'),
(64, 4, 20000, 2, '2025-05-02', 'Vehicle Purchase', '20250502214038'),
(65, 4, 5, 2, '2025-05-02', 'Transaction Fee', '20250502214038'),
(66, 5, 200000, 1, '2025-05-02', 'Deposit', '20250502214321'),
(67, 5, 180000, 2, '2025-05-02', 'Deposit', '20250502214321'),
(68, 5, 150000, 5, '2025-05-02', 'Deposit', '20250502214321'),
(69, 6, 20000, 1, '2025-05-02', 'Deposit', '20250502215453'),
(70, 6, 40000, 2, '2025-05-02', 'Deposit', '20250502215453'),
(71, 6, 30000, 5, '2025-05-02', 'Deposit', '20250502215453'),
(72, 6, 5000, 5, '2025-05-02', 'Camera', '20250502215549'),
(73, 6, 20, 5, '2025-05-02', 'Transaction Fee', '20250502215549'),
(74, 4, 20005, 2, '2025-05-02', 'Refund of transaction_id 20250502214038', '20250502220445');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `f_name` varchar(15) NOT NULL,
  `s_name` varchar(15) NOT NULL,
  `paypal_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `f_name`, `s_name`, `paypal_status`) VALUES
(4, 'Alice ', 'Doe', 1),
(5, 'Bob', 'Marley', 0),
(6, 'Charlie', 'Charplin', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_type`
--
ALTER TABLE `payment_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payment_type`
--
ALTER TABLE `payment_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
