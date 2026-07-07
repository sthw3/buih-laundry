-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2026 at 07:58 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buih_laundry`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `Appointment_ID` int(10) NOT NULL,
  `Customer_ID` int(10) NOT NULL,
  `Staff_ID` int(10) NOT NULL,
  `Appointment_Date` date NOT NULL,
  `Appointment_Time` time NOT NULL,
  `Appointment_Remark` varchar(50) NOT NULL,
  `Collection_Method` varchar(20) NOT NULL,
  `Appointment_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`Appointment_ID`, `Customer_ID`, `Staff_ID`, `Appointment_Date`, `Appointment_Time`, `Appointment_Remark`, `Collection_Method`, `Appointment_Status`) VALUES
(11, 1, 1, '2026-06-18', '09:00:00', 'Wash & fold 4kg clothes', '', 'Confirmed'),
(12, 2, 2, '2026-06-18', '10:30:00', 'Dry clean office suit', '', 'Completed'),
(13, 3, 3, '2026-06-19', '13:00:00', 'Ironing school uniforms', '', 'Completed'),
(14, 4, 2, '2026-06-19', '15:30:00', 'Bedsheet + towel wash', '', 'Pending'),
(15, 5, 1, '2026-06-20', '11:00:00', 'Express laundry service (same day)', '', 'Confirmed'),
(16, 6, 1, '2026-06-27', '14:30:00', 'No fabric softener', '', 'Pending'),
(17, 7, 3, '2026-06-28', '13:11:00', 'air dry only', '', 'Pending'),
(18, 8, 4, '2026-06-26', '14:15:00', 'no remark', '', 'Pending'),
(19, 9, 5, '2026-06-29', '10:30:00', 'Wash whites and colors separately', '', 'Pending'),
(20, 10, 1, '2026-07-08', '13:00:00', 'no remark', '', 'Pending'),
(21, 11, 2, '2026-07-06', '09:50:00', 'no remark', '', 'Completed'),
(22, 12, 3, '2026-07-07', '12:00:00', 'No fabric softener', '', 'Pending'),
(23, 11, 2, '2026-07-09', '13:30:00', 'Wash whites and colors separately', 'Pickup', 'Cancelled'),
(24, 14, 5, '2026-07-07', '12:08:00', 'no remark', 'Delivery', 'Pending'),
(25, 11, 2, '2026-07-09', '12:30:00', 'no remark', 'Delivery', 'Completed'),
(26, 14, 5, '2026-07-07', '13:50:00', '', 'Pickup', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `Appointment_ID` int(10) NOT NULL,
  `Service_ID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointment_service`
--

INSERT INTO `appointment_service` (`Appointment_ID`, `Service_ID`) VALUES
(11, 1),
(12, 2),
(13, 3),
(14, 4),
(15, 5),
(16, 1),
(17, 3),
(18, 1),
(18, 2),
(19, 1),
(19, 3),
(20, 1),
(20, 3),
(21, 4),
(22, 1),
(22, 3),
(23, 1),
(23, 3),
(24, 1),
(24, 3),
(25, 1),
(25, 2),
(26, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_ID` int(10) NOT NULL,
  `Customer_Name` varchar(50) NOT NULL,
  `Cust_PhoneNum` varchar(20) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Customer_Password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_ID`, `Customer_Name`, `Cust_PhoneNum`, `Email`, `Address`, `Customer_Password`) VALUES
(1, 'Ali Hassan', '0123456789', 'ali@gmail.com', '12 Jalan Lima, Arau, Perlis', ''),
(2, 'Siti Aisyah', '0135566778', 'aisyah@yahoo.com', 'No 11 Jalan Ria 4, Arau, Perlis', ''),
(3, 'John Lim', '0149988776', 'johnlim@hotmail.com', 'No 6 Jalan Ria 6, Arau, Perlis', ''),
(4, 'Nurul Huda', '0112233445', 'nurul@gmail.com', 'No 9 Jalan Harmoni Tiga, Kangar, Perlis', ''),
(5, 'Raj Kumar', '0167788990', 'rajkumar@gmail.com', 'No 22, Lorong 4 Taman seri Bintong Maju, Kangar, Perlis', ''),
(6, 'Qistina', '01121180839', 'qistina@gmail.com', 'Taman Bukit Kaya, Kangar, Perlis.', ''),
(7, 'Athirah', '0176247456', 'athirah@gmail.com', 'Taman Desa Wang, Arau, Perlis.', ''),
(8, 'Aiman ', '0132143244', 'aiman@gmail.com', 'Kg Behor Temak, Kangar, Perlis.', ''),
(9, 'Farhan', '0126452171', 'farhan@gmail.com', 'No12, Taman Wang, Arau, Perlis.', ''),
(10, 'Nina', '0195581792', 'nina@gmail.com', 'No 11, Taman Wang, Arau, 02100, Perlis.', ''),
(11, 'Ji Hyo', '0114893267', 'jihyo112@gmail.com', 'C13, Taman Kemboja, Arau, 02100, Perlis.', '$2y$10$bwKw7f7uNdfGmvoYNwbgieco5CWGWj69Y49xkHnV.53iXt2gV5PNe'),
(12, 'Alif Umar', '0164132597', 'alif@gmail.com', 'No21, Taman Merbau, 02100, Arau, Perlis.', '$2y$10$ibou0H4si79ldIiLD1YCreEfJonglHaFXg2JxNVfroykIlvJs/zqC'),
(13, 'Asyraf ', '0114973264', 'asyraf123@gmail.com', 'Taman Arau, 02100, Arau, Perlis.', '$2y$10$25W.uxwDpHpw5NzqptHBYO1vSbK4JvhJsKs1fuqAOMeSwUmgsATKy'),
(14, 'mamat', '73372848729', 'test@gmail.com', 'test123456', '$2y$10$qgCBroRuYntUCegTZK31Je0orH0Tq4EdRjpHqDvILMpGrnpB/Tj4W');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `Item_ID` int(10) NOT NULL,
  `Item_Type` varchar(50) NOT NULL,
  `Item_Weight` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`Item_ID`, `Item_Type`, `Item_Weight`) VALUES
(1, 'Shirt', 1),
(2, 'Pants', 1),
(3, 'Bedsheet', 3),
(4, 'Jacket', 2),
(5, 'Towel', 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_service`
--

CREATE TABLE `item_service` (
  `Item_ID` int(10) NOT NULL,
  `Service_ID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `item_service`
--

INSERT INTO `item_service` (`Item_ID`, `Service_ID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `login_staff`
--

CREATE TABLE `login_staff` (
  `Staff_Email` varchar(50) NOT NULL,
  `Staff_Pass` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_staff`
--

INSERT INTO `login_staff` (`Staff_Email`, `Staff_Pass`) VALUES
('sshakira.staff@gmail.com', '123'),
('ainsyah.staff@yahoo.com', '456'),
('snfaridah.staff@hotmail.com', '789'),
('enatasha.staff@gmail.com', '321'),
('hasnani.staff@gmail.com', '098');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(10) NOT NULL,
  `Payment_Method` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Payment_Method`) VALUES
(1, 'Cash'),
(2, 'Online Banking'),
(3, 'Debit Card'),
(4, 'Credit Card'),
(5, 'E-Wallet');

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

CREATE TABLE `receipt` (
  `Receipt_ID` int(10) NOT NULL,
  `Appointment_ID` int(10) NOT NULL,
  `Payment_ID` int(10) NOT NULL,
  `Total_Amount` int(10) NOT NULL,
  `Issued_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `receipt`
--

INSERT INTO `receipt` (`Receipt_ID`, `Appointment_ID`, `Payment_ID`, `Total_Amount`, `Issued_Date`) VALUES
(1, 11, 1, 15, '2026-06-27'),
(2, 12, 2, 25, '2026-06-24'),
(3, 13, 3, 12, '2026-06-30'),
(4, 14, 4, 30, '2026-06-30'),
(5, 15, 5, 20, '2026-07-01'),
(6, 21, 4, 10, '2026-07-08'),
(7, 25, 3, 8, '2026-07-10'),
(8, 26, 0, 5, '2026-07-06');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `Service_ID` int(10) NOT NULL,
  `Service_Name` varchar(50) NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`Service_ID`, `Service_Name`, `Price`) VALUES
(1, 'Wash & Fold', 5),
(2, 'Ironing Service', 3),
(3, 'Dry Cleaning', 12),
(4, 'Express Laundry (Same Day)', 10),
(5, 'Blanket / Comforter Wash', 15);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `Staff_ID` int(10) NOT NULL,
  `Staff_Name` varchar(50) NOT NULL,
  `Staff_PhoneNum` varchar(50) NOT NULL,
  `Staff_Email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `Staff_Name`, `Staff_PhoneNum`, `Staff_Email`) VALUES
(1, 'Siti Nur Shakira', '0123344556', 'sshakira.staff@gmail.com'),
(2, 'Siti Ainsyah', '0137788990', 'ainsyah.staff@yahoo.com'),
(3, 'Siti Nur Faridah', '0145566778', 'snfaridah.staff@hotmail.com'),
(4, 'Erna Natasha', '0112233556', 'enatasha.staff@gmail.com'),
(5, 'Umi Hasnani', '0169988776', 'hasnani.staff@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`Appointment_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Staff_ID` (`Staff_ID`);

--
-- Indexes for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD PRIMARY KEY (`Appointment_ID`,`Service_ID`),
  ADD KEY `Service_ID` (`Service_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`Item_ID`);

--
-- Indexes for table `item_service`
--
ALTER TABLE `item_service`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `Service_ID` (`Service_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`);

--
-- Indexes for table `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`Receipt_ID`),
  ADD KEY `Appointment_ID` (`Appointment_ID`),
  ADD KEY `Payment_ID` (`Payment_ID`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`Service_ID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appointment_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `Appointment_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `Item_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `item_service`
--
ALTER TABLE `item_service`
  MODIFY `Item_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `receipt`
--
ALTER TABLE `receipt`
  MODIFY `Receipt_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `Service_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD CONSTRAINT `appointment_service_ibfk_1` FOREIGN KEY (`Appointment_ID`) REFERENCES `appointment` (`Appointment_ID`),
  ADD CONSTRAINT `appointment_service_ibfk_2` FOREIGN KEY (`Service_ID`) REFERENCES `service` (`Service_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
