-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 01, 2025 at 01:32 AM
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
-- Table structure for table `tag_details`
--

CREATE TABLE `tag_details` (
  `id` int(11) NOT NULL,
  `PRODUCTID` varchar(10) DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Product Name` varchar(100) DEFAULT NULL,
  `Design Name` varchar(100) DEFAULT NULL,
  `Sub Design Name` varchar(100) DEFAULT NULL,
  `Sub Design ID` int(11) DEFAULT NULL,
  `Purity` decimal(10,2) DEFAULT NULL,
  `Pieces` int(11) DEFAULT NULL,
  `Gross Wt` decimal(10,3) DEFAULT NULL,
  `Net Wt` decimal(10,3) DEFAULT NULL,
  `Less Wt` decimal(10,3) DEFAULT NULL,
  `sales_type` int(10) NOT NULL DEFAULT 2,
  `calculation_based_on` int(10) NOT NULL DEFAULT 2,
  `Wastage Per` decimal(10,2) DEFAULT NULL,
  `Wastage Wt` decimal(10,3) DEFAULT NULL,
  `MC Type` varchar(20) DEFAULT NULL,
  `MC Value` decimal(10,2) DEFAULT NULL,
  `Sales Value` decimal(10,2) DEFAULT NULL,
  `RATEPERGRAM` decimal(10,2) DEFAULT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `MFR Code` varchar(100) DEFAULT NULL,
  `Style Code` varchar(100) DEFAULT NULL,
  `Tag Number` varchar(30) DEFAULT NULL,
  `Certification No` varchar(100) DEFAULT NULL,
  `Purchase Cost` decimal(10,2) DEFAULT NULL,
  `Tag Date` varchar(20) DEFAULT NULL,
  `HUID 1` varchar(20) DEFAULT NULL,
  `HUID 2` varchar(20) DEFAULT NULL,
  `K ID` int(11) DEFAULT NULL,
  `KARIGAR` varchar(190) DEFAULT NULL,
  `Counter` varchar(124) DEFAULT NULL,
  `TRANSIT NO` varchar(100) DEFAULT NULL,
  `IMAGE FILE` varchar(200) DEFAULT NULL,
  `size` varchar(60) DEFAULT NULL,
  `SALEMODE` varchar(10) DEFAULT NULL,
  `TAGTYPE` varchar(20) DEFAULT NULL,
  `METALID` varchar(20) DEFAULT NULL,
  `MCPERGRAM` decimal(10,2) NOT NULL DEFAULT 0.00,
  `FMC` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tag_details`
--
ALTER TABLE `tag_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tag_details`
--
ALTER TABLE `tag_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
