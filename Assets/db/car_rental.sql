-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 03:37 PM
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
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `additional_options`
--

CREATE TABLE `additional_options` (
  `option_id` int(11) NOT NULL,
  `option_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `additional_options`
--

INSERT INTO `additional_options` (`option_id`, `option_name`, `description`, `daily_cost`) VALUES
(1, 'GPS Navigation', 'Portable GPS device.', 5.00),
(2, 'Child Safety Seat', 'Comfortable child seat for young passengers.', 8.00),
(3, 'Additional Driver', 'Allows an extra driver for the rental period.', 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(10) UNSIGNED NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `rent_per_day` decimal(10,2) NOT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `car_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seat_count` int(11) NOT NULL,
  `max_speed` int(11) NOT NULL,
  `km_per_liter` decimal(5,2) NOT NULL,
  `logo_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `brand`, `model`, `rent_per_day`, `availability`, `car_picture`, `created_at`, `seat_count`, `max_speed`, `km_per_liter`, `logo_picture`) VALUES
(6, 'Toyota', 'Corolla', 40.00, 1, 'Assets/Images/uploads/car6.png', '2024-10-31 06:29:11', 5, 180, 15.50, 'Assets/Images/uploads/toyota.png'),
(7, 'BMW', 'X5', 90.00, 1, 'Assets/Images/uploads/car2.png', '2024-10-31 06:29:11', 5, 250, 8.50, 'Assets/Images/uploads/bmw.png'),
(8, 'Audi', 'A6', 85.00, 1, 'Assets/Images/uploads/car7.png', '2024-10-31 06:29:11', 5, 240, 9.50, 'Assets/Images/uploads/audi.png'),
(9, 'Mercedes', 'E-Class', 95.00, 1, 'Assets/Images/uploads/car4.png', '2024-10-31 06:29:11', 5, 260, 10.20, 'Assets/Images/uploads/benz.png'),
(10, 'Lexus', 'LC Series', 50.00, 1, 'Assets/Images/uploads/car5.png', '2024-10-31 06:29:11', 5, 200, 14.20, 'Assets/Images/uploads/lexus.png'),
(11, 'Tesla ', 'A2', 80.00, 1, 'Assets/Images/uploads/car3.png', '2024-10-31 06:40:14', 5, 250, 12.00, 'Assets/Images/uploads/tesla-removebg-preview.png'),
(12, 'BMW', 'A5', 95.00, 1, 'Assets/Images/uploads/car7.png', '2024-10-31 16:30:51', 4, 200, 10.00, 'Assets/Images/uploads/bmw.png'),
(15, 'Afrar', 'A01', 100.00, 1, 'Assets/Images/uploads/car2.png', '2024-11-23 17:56:09', 2, 350, 2.00, 'Assets/Images/uploads/audi.png');

-- --------------------------------------------------------

--
-- Table structure for table `confirmed_rentals`
--

CREATE TABLE `confirmed_rentals` (
  `rental_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `car_id` int(10) UNSIGNED DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('confirmed','completed') DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_overdue` tinyint(1) DEFAULT 0,
  `fine` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `confirmed_rentals`
--

INSERT INTO `confirmed_rentals` (`rental_id`, `user_id`, `car_id`, `date_from`, `date_to`, `total_cost`, `status`, `created_at`, `is_overdue`, `fine`) VALUES
(1, 2, 7, '2024-10-02', '2024-10-06', 360.00, 'confirmed', '2024-10-02 03:30:00', 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rental_id` int(11) DEFAULT NULL,
  `car_model` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `total_fees` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `car_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `car_id`, `rating`, `comments`, `feedback_date`) VALUES
(1, 1, NULL, 5, 'Great car, smooth rental experience!', '2024-10-28 12:23:15'),
(2, 2, NULL, 4, 'Car was clean and comfortable.', '2024-10-28 12:23:15'),
(3, 1, NULL, 5, 'Highly recommend the Tesla Model 3!', '2024-10-28 12:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `fine_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `date_applied` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insurance_options`
--

CREATE TABLE `insurance_options` (
  `insurance_id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `insurance_options`
--

INSERT INTO `insurance_options` (`insurance_id`, `plan_name`, `description`, `daily_cost`) VALUES
(1, 'Standard Coverage', 'Basic insurance plan covering damages.', 15.00),
(2, 'Full Coverage', 'Comprehensive insurance covering all damages.', 30.00),
(3, 'Third-Party Only', 'Covers damages to third-party vehicles only.', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','debit_card') NOT NULL,
  `payment_status` enum('completed','failed') DEFAULT 'completed',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `car_id` int(10) UNSIGNED DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `status` enum('completed','pending','confirmed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_overdue` tinyint(1) DEFAULT 0,
  `fine` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `user_id`, `car_id`, `date_from`, `date_to`, `total_cost`, `status`, `created_at`, `is_overdue`, `fine`) VALUES
(1, 1, 6, '2024-10-01', '2024-10-05', 200.00, 'completed', '2024-10-01 02:30:00', 0, 0.00),
(3, 1, 8, '2024-10-10', '2024-10-15', 450.00, 'completed', '2024-10-10 03:00:00', 1, 50.00),
(4, 2, 9, '2024-10-12', '2024-10-17', 425.00, 'completed', '2024-10-12 04:00:00', 0, 0.00),
(5, 1, 10, '2024-10-18', '2024-10-22', 240.00, 'completed', '2024-10-18 03:15:00', 0, 0.00),
(6, 2, 8, '2024-10-22', '2024-10-26', 320.00, 'completed', '2024-10-22 05:30:00', 1, 25.00),
(7, 1, 6, '2024-10-01', '2024-10-05', 200.00, 'completed', '2024-09-30 21:00:00', 0, 0.00),
(10, 1, 6, '2024-10-01', '2024-10-05', 200.00, 'completed', '2024-09-30 21:00:00', 0, 0.00),
(13, 1, 8, '2024-10-12', '2024-10-17', 450.00, 'completed', '2024-10-11 22:30:00', 1, 50.00),
(16, 1, 8, '2024-11-05', '2024-11-10', 450.00, 'completed', '2024-10-31 05:30:00', 0, 0.00),
(17, 2, 10, '2024-11-07', '2024-11-12', 520.00, 'completed', '2024-10-31 05:45:00', 0, 0.00),
(18, 1, 9, '2024-11-08', '2024-11-15', 400.00, 'completed', '2024-10-31 06:00:00', 0, 0.00),
(19, 2, 7, '2024-11-10', '2024-11-18', 600.00, 'completed', '2024-10-31 06:15:00', 0, 0.00),
(20, 2, 10, '2024-11-20', '2024-11-25', 350.00, 'completed', '2024-11-18 03:00:00', 0, 0.00),
(21, 1, 6, '2024-11-22', '2024-11-27', 400.00, 'completed', '2024-11-18 03:30:00', 0, 0.00),
(22, 2, 7, '2024-11-23', '2024-11-29', 450.00, 'completed', '2024-11-18 04:00:00', 0, 0.00),
(24, 1, 8, '2024-11-25', '2024-12-01', 420.00, 'completed', '2024-11-18 05:00:00', 0, 0.00),
(25, 4, 6, '2024-10-31', '2024-11-01', 45.00, 'completed', '2024-10-31 17:48:59', 0, 0.00),
(26, 4, 9, '2024-10-31', '2024-11-06', 575.00, 'pending', '2024-10-31 18:23:25', 0, 0.00),
(27, 4, 7, '2024-11-06', '2024-12-06', 2725.00, 'completed', '2024-11-06 16:05:24', 0, 0.00),
(28, 4, 7, '2024-11-06', '2024-11-07', 98.00, 'pending', '2024-11-06 17:27:49', 0, 0.00),
(29, 4, 10, '2024-11-06', '2024-11-06', 8.00, 'completed', '2024-11-06 18:29:26', 0, 0.00),
(30, 4, 8, '2024-11-07', '2024-11-15', 685.00, 'completed', '2024-11-07 06:20:04', 0, 0.00),
(31, 4, 8, '2024-11-07', '2024-11-07', 0.00, 'pending', '2024-11-07 06:21:55', 0, 0.00),
(32, 4, 8, '2024-11-07', '2024-11-07', 0.00, 'pending', '2024-11-07 06:23:49', 0, 0.00),
(33, 4, 9, '2024-11-07', '2024-11-07', 5.00, 'pending', '2024-11-07 06:24:06', 0, 0.00),
(34, 4, 6, '2024-11-12', '2024-11-14', 93.00, 'completed', '2024-11-07 06:40:59', 0, 0.00),
(35, 4, 6, '2024-11-23', '2024-11-24', 57.00, 'completed', '2024-11-23 17:44:46', 0, 0.00),
(36, 4, 7, '2024-12-12', '2024-12-13', 110.00, 'completed', '2024-12-12 05:51:45', 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `rental_accessories`
--

CREATE TABLE `rental_accessories` (
  `accessory_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `accessory_name` varchar(50) NOT NULL,
  `extra_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_history`
--

CREATE TABLE `rental_history` (
  `history_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `total_fees` decimal(10,2) NOT NULL,
  `car_model` varchar(50) NOT NULL,
  `car_brand` varchar(50) NOT NULL,
  `seat_count` int(11) NOT NULL,
  `max_speed` int(11) NOT NULL,
  `km_per_liter` decimal(5,2) NOT NULL,
  `fine_details` text DEFAULT NULL,
  `additional_options` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_history`
--

INSERT INTO `rental_history` (`history_id`, `rental_id`, `customer_id`, `date_from`, `date_to`, `total_fees`, `car_model`, `car_brand`, `seat_count`, `max_speed`, `km_per_liter`, `fine_details`, `additional_options`, `created_at`) VALUES
(1, 1, 1, '2024-01-10', '2024-01-15', 500.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Late return - $20', 'GPS Navigation - $5 per day, Child Seat - $8 per day', '2024-10-31 08:42:45'),
(2, 2, 2, '2024-02-01', '2024-02-07', 800.00, 'X5', 'BMW', 5, 250, 8.50, 'Smoking inside - $50', 'Additional Driver - $12 per day', '2024-10-31 08:42:45'),
(3, 18, 1, '2024-11-08', '2024-11-15', 400.00, 'E-Class', 'Mercedes', 5, 260, 10.20, NULL, NULL, '2024-10-31 13:56:09'),
(4, 1, 1, '2024-10-01', '2024-10-05', 200.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Total Fine: $0', NULL, '2024-10-31 14:14:11'),
(5, 21, 1, '2024-11-22', '2024-11-27', 500.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Total Fine: $100.00', NULL, '2024-10-31 14:16:41'),
(6, 20, 2, '2024-11-20', '2024-11-25', 350.00, 'LC Series', 'Lexus', 5, 200, 14.20, 'Total Fine: $0', NULL, '2024-11-06 16:07:50'),
(7, 24, 1, '2024-11-25', '2024-12-01', 420.00, 'A6', 'Audi', 5, 240, 9.50, 'Total Fine: $0', NULL, '2024-11-06 18:27:14'),
(8, 3, 1, '2024-10-10', '2024-10-15', 450.00, 'A6', 'Audi', 5, 240, 9.50, 'Total Fine: $0', NULL, '2024-11-06 18:27:40'),
(9, 25, 4, '2024-10-31', '2024-11-01', 45.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Total Fine: $0', NULL, '2024-11-07 06:34:19'),
(10, 34, 4, '2024-11-12', '2024-11-14', 143.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Total Fine: $50.00', NULL, '2024-11-09 15:51:20'),
(11, 35, 4, '2024-11-23', '2024-11-24', 57.00, 'Corolla', 'Toyota', 5, 180, 15.50, 'Total Fine: $0', NULL, '2024-11-23 17:50:35'),
(12, 27, 4, '2024-11-06', '2024-12-06', 2725.00, 'X5', 'BMW', 5, 250, 8.50, 'Total Fine: $0', NULL, '2024-11-23 17:53:36'),
(13, 30, 4, '2024-11-07', '2024-11-15', 685.00, 'A6', 'Audi', 5, 240, 9.50, 'Total Fine: $0', NULL, '2024-11-23 17:54:29'),
(14, 29, 4, '2024-11-06', '2024-11-06', 8.00, 'LC Series', 'Lexus', 5, 200, 14.20, 'Total Fine: $0', NULL, '2024-11-23 17:54:32'),
(15, 22, 2, '2024-11-23', '2024-11-29', 450.00, 'X5', 'BMW', 5, 250, 8.50, 'Total Fine: $0', NULL, '2024-12-12 05:48:15'),
(16, 36, 4, '2024-12-12', '2024-12-13', 125.00, 'X5', 'BMW', 5, 250, 8.50, 'Total Fine: $15.00', NULL, '2024-12-12 05:56:44');

-- --------------------------------------------------------

--
-- Table structure for table `rental_insurance`
--

CREATE TABLE `rental_insurance` (
  `rental_id` int(11) NOT NULL,
  `insurance_id` int(11) NOT NULL,
  `insurance_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_options`
--

CREATE TABLE `rental_options` (
  `rental_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `nic_number`, `password`, `role`, `created_at`) VALUES
(1, 'John Doe', 'johndoe@example.com', '123456789', 'NIC123456', 'password123', 'customer', '2024-10-28 12:20:03'),
(2, 'Jane Smith', 'janesmith@example.com', '987654321', 'NIC987654', 'password123', 'customer', '2024-10-28 12:20:03'),
(3, 'Alice Brown', 'alicebrown@example.com', '456123789', 'NIC456123', 'password123', 'admin', '2024-10-28 12:20:03'),
(4, 'Shukry', 'abcd@gmail.com', '0786543033', '200123103816', '$2y$10$15gBgzX98KraU74niSLWjebJAidEbJc4n/DdlGcDZWZYCD/4BmuUq', 'customer', '2024-10-31 14:43:34'),
(5, 'Hiruththikan', 'abc@gmail.com', '0756546038', '992233220V', '$2y$10$wFXQoU2CDt65JnPDwnee8.85jDxQl.usHruiJjivJ4cmwM/nI3nuS', 'admin', '2024-11-06 15:55:21'),
(6, 'Kirupan', 'aaa@gmail.com', '0776565655', '20012233890', '$2y$10$kErSxJCro7rJvz6cAKvC/ey8qqlpZnKJm.nW8oZWqyERG.OUOwE1K', 'customer', '2024-11-06 18:34:32'),
(7, 'hiruththikan', 'a.@gmail.com', '0754796021', '16667891730', '$2y$10$XmjHycTZ9P23nAWT/MR8jeAS2j7luk3WaPLlcJOXxHB1X3OVZZ6Pi', 'customer', '2024-11-07 06:30:15'),
(8, 'Athiyya ', 'athiyya111@gmail.com', '0769978500', '200267102816', '$2y$10$eZWpQ7pO8I/rJ2x5NGUqc.ywEo48K07fAzvfE.EtYqh0hvRvmTHQ6', 'customer', '2025-09-07 13:15:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additional_options`
--
ALTER TABLE `additional_options`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`);

--
-- Indexes for table `confirmed_rentals`
--
ALTER TABLE `confirmed_rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `fk_fines_rental_id` (`rental_id`);

--
-- Indexes for table `insurance_options`
--
ALTER TABLE `insurance_options`
  ADD PRIMARY KEY (`insurance_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `rental_accessories`
--
ALTER TABLE `rental_accessories`
  ADD PRIMARY KEY (`accessory_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `rental_history`
--
ALTER TABLE `rental_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `rental_insurance`
--
ALTER TABLE `rental_insurance`
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `insurance_id` (`insurance_id`);

--
-- Indexes for table `rental_options`
--
ALTER TABLE `rental_options`
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `option_id` (`option_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nic_number` (`nic_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `additional_options`
--
ALTER TABLE `additional_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `confirmed_rentals`
--
ALTER TABLE `confirmed_rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `insurance_options`
--
ALTER TABLE `insurance_options`
  MODIFY `insurance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `rental_accessories`
--
ALTER TABLE `rental_accessories`
  MODIFY `accessory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rental_history`
--
ALTER TABLE `rental_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `confirmed_rentals`
--
ALTER TABLE `confirmed_rentals`
  ADD CONSTRAINT `confirmed_rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `confirmed_rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON DELETE SET NULL;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fines_rental_id` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON DELETE SET NULL;

--
-- Constraints for table `rental_accessories`
--
ALTER TABLE `rental_accessories`
  ADD CONSTRAINT `rental_accessories_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_history`
--
ALTER TABLE `rental_history`
  ADD CONSTRAINT `rental_history_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_insurance`
--
ALTER TABLE `rental_insurance`
  ADD CONSTRAINT `rental_insurance_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_insurance_ibfk_2` FOREIGN KEY (`insurance_id`) REFERENCES `insurance_options` (`insurance_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_options`
--
ALTER TABLE `rental_options`
  ADD CONSTRAINT `rental_options_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_options_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `additional_options` (`option_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
