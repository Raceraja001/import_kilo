-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 01, 2025 at 01:08 PM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rajatm_staging`
--

-- --------------------------------------------------------

--
-- Table structure for table `stone_details`
--

CREATE TABLE `stone_details` (
  `sid` int(11) NOT NULL,
  `Tag Number` varchar(21) DEFAULT NULL,
  `Less Wt` varchar(3) DEFAULT NULL,
  `StoneName` varchar(53) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Stone ID` int(11) DEFAULT NULL,
  `Pieces` int(11) DEFAULT NULL,
  `Weight` decimal(10,3) DEFAULT NULL,
  `Unit` varchar(5) DEFAULT NULL,
  `CAL TYPE` varchar(6) DEFAULT NULL,
  `Rate/Gram` varchar(10) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stone_details`
--
ALTER TABLE `stone_details`
  ADD PRIMARY KEY (`sid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `stone_details`
--
ALTER TABLE `stone_details`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
