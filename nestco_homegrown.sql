--
-- Database: `nestco_homegrown`
--
CREATE DATABASE IF NOT EXISTS `nestco_homegrown` ;
USE `nestco_homegrown`;
-- --------------------------------------------------------

--
-- Table structure for table `Cart`
--

CREATE TABLE `Cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Cart`
--

INSERT INTO `Cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(2, 2, 4, 1, '2024-11-28 18:14:23', '2024-12-01 21:14:49'),
(15, 5, 1, 1, '2024-11-29 01:05:24', '2024-11-29 01:05:24'),
(17, 6, 1, 1, '2024-11-29 01:44:25', '2024-11-29 01:44:25'),
(18, 6, 2, 1, '2024-11-29 01:44:54', '2024-11-29 01:44:54'),
(20, 7, 5, 1, '2024-11-29 21:10:30', '2024-11-29 21:10:30'),
(24, 8, 2, 1, '2024-11-30 05:05:15', '2024-11-30 05:05:15'),
(25, 2, 6, 1, '2024-12-01 21:13:01', '2024-12-01 21:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `OrderDetails`
--

CREATE TABLE `OrderDetails` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `OrderDetails`
--

INSERT INTO `OrderDetails` (`order_detail_id`, `order_id`, `product_name`, `price`, `quantity`, `total`) VALUES
(1, 1, 'Mango Juice', 10.00, 1, 10.00),
(2, 2, 'Mango Juice', 10.00, 1, 10.00),
(3, 3, 'Mango', 5.00, 1, 5.00),
(4, 3, 'Mango Juice', 10.00, 1, 10.00),
(5, 3, 'Pineaple', 18.00, 2, 36.00),
(6, 4, 'Mango', 5.00, 1, 5.00),
(7, 4, 'Mango Juice', 10.00, 1, 10.00),
(8, 4, 'Pineaple', 18.00, 2, 36.00),
(9, 5, 'Mango Juice', 10.00, 1, 10.00),
(10, 5, 'Mango', 5.00, 1, 5.00),
(11, 6, 'Cabbage', 7.00, 1, 7.00),
(12, 7, 'Mango Juice', 10.00, 1, 10.00),
(13, 7, 'Mango', 5.00, 1, 5.00),
(14, 8, 'Mango Juice', 10.00, 1, 10.00),
(15, 8, 'Mango', 5.00, 1, 5.00),
(16, 9, 'Apple', 22.00, 1, 22.00),
(17, 9, 'Kales', 19.00, 1, 19.00);

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE `Orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Orders`
--

INSERT INTO `Orders` (`order_id`, `user_id`, `total_amount`, `order_date`) VALUES
(1, 1, 10.00, '2024-11-28 15:47:53'),
(2, 5, 10.00, '2024-11-29 02:07:15'),
(3, 1, 51.00, '2024-11-29 02:10:17'),
(4, 1, 51.00, '2024-11-29 02:30:06'),
(5, 6, 15.00, '2024-11-29 02:45:29'),
(6, 7, 7.00, '2024-11-29 22:11:14'),
(7, 8, 15.00, '2024-11-30 06:01:50'),
(8, 8, 15.00, '2024-11-30 06:02:58'),
(9, 2, 41.00, '2024-12-02 01:50:09');

-- Update existing orders to have a status
UPDATE `Orders` SET `status` = 'pending' WHERE `status` IS NULL;

-- --------------------------------------------------------

--
-- Table structure for table `Products`
--

CREATE TABLE `Products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Products`
--

INSERT INTO `Products` (`product_id`, `name`, `category`, `description`, `old_price`, `new_price`, `quantity`, `image_url`, `created_at`) VALUES
(1, 'Mango Juice', 'Drinks', 'Fresh', 12.00, 10.00, 45, 'mango juice.webp', '2024-11-28 11:01:43'),
(2, 'Mango', 'Fruits', 'Healthy', 7.00, 5.00, 34, 'mango.jpg', '2024-11-28 11:23:45'),
(3, 'Pineaple', 'Fruit', 'Very Ripe Pineaple', 19.00, 18.00, 66, 'pineaple.jpg', '2024-11-28 11:24:46'),
(4, 'Apple', 'Fruit', 'Apple Fruit', 25.00, 22.00, 7, 'apple.jpg', '2024-11-28 11:25:22'),
(5, 'Cabbage', 'Vegetables', 'Fresh White Cabbage', 9.00, 7.00, 23, 'cabbage.jpg', '2024-11-28 11:26:08'),
(6, 'Kales', 'Vegetables', 'Fresh Veges From the Farm', 21.00, 19.00, 39, 'kales.jpg', '2024-11-28 11:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `Shipping`
--

CREATE TABLE `Shipping` (
  `shipping_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Shipping`
--

INSERT INTO `Shipping` (`shipping_id`, `user_id`, `name`, `phone`, `address`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tonny Ouma', '+1234567889', 'Cambodia', 'Cambodia', 'Cambodia', '11223', 'USA', '2024-11-28 12:44:23', '2024-11-29 01:10:14'),
(2, 2, 'Tonny Ouma', '0742942435', '551', 'Oyugis', 'Nyanza', '40222', 'Kenya', '2024-11-28 18:15:17', '2024-11-28 18:15:26'),
(3, 5, 'daj', '1234567890', 'adc, apt204', 'kansas', 'kansas', '61661', 'usa', '2024-11-29 01:06:59', '2024-11-29 01:06:59'),
(4, 6, 'asi', '1234567890', 'asas', 'asa', 'asa', '12345', 'asa', '2024-11-29 01:45:21', '2024-11-29 01:45:21'),
(5, 7, 'Vd', 'Fd', 'Ff', '24', 'Frf', 'Ccvc', 'Vfa', '2024-11-29 21:11:10', '2024-11-29 21:11:10'),
(6, 8, 'ss', '13254795', 'vvvvv', 'ks', 'ms', '52004', 'usa', '2024-11-30 05:01:46', '2024-11-30 05:01:46');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$qFwS97rP/0GgoWiE6jTRP.9/mQnfWZADxU.B2svldLeUR9OH6cpU6', 'admin', '2024-11-28 10:26:27'),
(2, 'user1', 'user@user.com', '$2y$10$CptGh0C26ZBg.Lor1fyOJ.C2ND71rHZExbQH.Pp1mu45CvZ4Wprvm', 'user', '2024-11-28 13:07:09'),
(4, 'User2', 'user1@user1.com', '$2y$10$KRnoyCvBrS0w1owo8jLWI.0ZgrLstZXAAB52kGh3Cjh5rgnKJEm.W', 'user', '2024-11-28 17:44:57'),
(5, 'sivateja', 'siva@gmail.com', '$2y$10$ZyL9C4fuosfXyq.pFw8jQOJ6bXkCQxaWXDSJK/jhEG5oyIqbDJDxO', 'user', '2024-11-28 19:47:40'),
(6, 'asi', 'asi@gmail.com', '$2y$10$2Oc.7kkv.BGh3T25swG9w.d.Z1BShRcTLT5ou6EtCNUJ2iFArqm02', 'user', '2024-11-29 01:44:05'),
(7, 'Ram', 'code518exam@gmail.com', '$2y$10$1ciLv373ep6sHxXTfTwE8OdlVQbXEFRhCg9ob15aXtKaLmSMDCYPG', 'user', '2024-11-29 21:08:43'),
(8, 'Sssa', 'qwerty@gmail.com', '$2y$10$GlGBcX8SpwmhtoFwuou8sOFt5ne64c5iyq2AjLXYeuC9ipl3KFUOm', 'user', '2024-11-30 03:33:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `OrderDetails`
--
ALTER TABLE `OrderDetails`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Products`
--
ALTER TABLE `Products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `Shipping`
--
ALTER TABLE `Shipping`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Cart`
--
ALTER TABLE `Cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `OrderDetails`
--
ALTER TABLE `OrderDetails`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Orders`
--
ALTER TABLE `Orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `Products`
--
ALTER TABLE `Products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Shipping`
--
ALTER TABLE `Shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `Cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `Cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Products` (`product_id`);

--
-- Constraints for table `OrderDetails`
--
ALTER TABLE `OrderDetails`
  ADD CONSTRAINT `OrderDetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`order_id`);

--
-- Constraints for table `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `Orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Shipping`
--
ALTER TABLE `Shipping`
  ADD CONSTRAINT `Shipping_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

-- Add missing columns to Users table if they don't exist
ALTER TABLE `Users` ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) DEFAULT NULL AFTER `email`;

ALTER TABLE `Users` ADD COLUMN IF NOT EXISTS `shipping_address` TEXT DEFAULT NULL AFTER `phone`;

-- Admin username : admin@admin.com
-- Admin password : admin123
UPDATE Users 
SET password = '$2b$12$sgvUPW4MxSkRKviHciuw..MxE5U90tNSF0Ya/hGDqUsKoPRNQVs7W',
    username = 'Admin',
    role = 'admin'
WHERE email = 'admin@admin.com';

-- Create HelpTickets table
CREATE TABLE IF NOT EXISTS `HelpTickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `HelpTickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  CONSTRAINT `HelpTickets_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 