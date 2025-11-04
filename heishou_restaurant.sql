-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 09:30 AM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `heishou_restaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `Booking_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Full_Name` varchar(100) DEFAULT NULL,
  `Phone_Number` varchar(20) DEFAULT NULL,
  `Booking_Date` date DEFAULT NULL,
  `Event_Type` varchar(100) DEFAULT NULL,
  `Number_of_People` int(11) DEFAULT NULL,
  `Booking_Time` varchar(11) NOT NULL,
  `Status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`Booking_ID`, `User_ID`, `Full_Name`, `Phone_Number`, `Booking_Date`, `Event_Type`, `Number_of_People`, `Booking_Time`, `Status`) VALUES
(7, 10, 'Sakura', '01123450666', '2025-11-07', 'family', 4, '20:16:00', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `Cart_Item_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Invoice_ID` int(11) DEFAULT NULL,
  `Menu_ID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT '1',
  `Subtotal` decimal(10,2) DEFAULT NULL,
  `Status` varchar(50) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`Cart_Item_ID`, `User_ID`, `Invoice_ID`, `Menu_ID`, `Quantity`, `Subtotal`, `Status`) VALUES
(16, 5, NULL, 146, 2, '12.00', 'active'),
(17, 5, NULL, 152, 1, '7.50', 'active'),
(18, 5, NULL, 129, 1, '21.00', 'active'),
(19, 5, NULL, 140, 2, '36.00', 'active'),
(20, 5, NULL, 127, 1, '15.90', 'active'),
(30, 10, 21, 147, 1, '9.90', 'ordered'),
(31, 10, 21, 146, 1, '6.00', 'ordered'),
(32, 10, 21, 163, 1, '10.50', 'ordered'),
(33, 10, 21, 152, 1, '7.50', 'ordered'),
(34, 10, 21, 126, 1, '22.50', 'ordered'),
(35, 10, 21, 153, 1, '9.90', 'ordered');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `Invoice_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Total_Price` varchar(255) NOT NULL,
  `Payment_Status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`Invoice_ID`, `User_ID`, `Total_Price`, `Payment_Status`, `created_at`) VALUES
(10, 9, '9.54', 'pending', '2025-10-31 05:04:44'),
(11, 5, '117.13', 'paid', '2025-10-31 05:04:44'),
(21, 10, 'RM 70.28', 'paid', '2025-10-31 08:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `Menu_ID` int(11) NOT NULL,
  `Item_Name` varchar(100) NOT NULL,
  `Description` text,
  `Price` decimal(10,2) NOT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Image` varchar(255) NOT NULL,
  `Status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`Menu_ID`, `Item_Name`, `Description`, `Price`, `Category`, `Image`, `Status`) VALUES
(125, 'Chicken Teriyaki Bento', 'Grilled chicken glazed with sweet soy teriyaki sauce, served with rice and vegetables.', '18.90', 'Bento', '1761819866_chicken teriyaki bento.png', 'available'),
(126, 'Salmon Teriyaki Bento', 'Pan-seared salmon topped with teriyaki glaze, served with steamed rice and salad.', '22.50', 'Bento', '1761820359_salmon bento.png', 'available'),
(127, 'Tofu Katsu ', 'Bento Crispy fried tofu cutlets with tangy katsu sauce, served with rice and pickles.', '15.90', 'Bento', '1761819952_tofu katsu.png', 'available'),
(128, 'Chicken Katsu Bento ', 'Breaded chicken cutlet drizzled with Japanese katsu sauce and served with rice.', '17.50', 'Bento', '1761819852_chicken katsu bento.png', 'available'),
(129, 'Beef Yakiniku Bento ', 'Stir-fried beef with onion and sweet soy sauce, served with rice and sesame seeds.', '21.00', 'Bento', '1761819835_beef yakiniku bento.png', 'available'),
(130, 'Spicy Karaage Bento ', 'Fried chicken chunks coated in spicy sauce, paired with cabbage and rice.', '19.00', 'Bento', '1761819911_spicy karaage bento.png', 'available'),
(131, 'Tempura Mix Bento ', 'Crispy shrimp and vegetable tempura served with rice and dipping sauce.', '20.90', 'Bento', '1761819937_tempura mix bento.png', 'available'),
(132, 'Shoyu Chicken Ramen ', 'Light soy-based broth with chicken slices, noodles, and soft-boiled egg.', '16.90', 'Ramen ', '1761820190_shoyu chicken ramen.png', 'available'),
(133, 'Spicy Miso Ramen ', 'Miso broth with chili paste, minced chicken, and ramen noodles.', '17.50', 'Ramen ', '1761820218_spicy miso ramen.png', 'available'),
(134, 'Tempura Udon ', 'Thick udon noodles in light broth topped with shrimp and vegetable tempura.', '18.20', 'Udon', '1761719035_tempura udon.png', 'available'),
(135, 'Curry Udon ', 'Japanese curry broth with udon noodles, carrots, and chicken.', '17.90', 'Udon', '1761819814_curry udon.png', 'available'),
(136, 'Shio Ramen ', 'Clear chicken broth ramen with green onions, bamboo shoots, and egg.', '15.50', 'Ramen ', '1761820174_shio ramen.png', 'available'),
(137, 'Chicken Katsu Ramen ', 'Crispy chicken katsu served on top of ramen in savory broth.', '18.50', 'Ramen ', '1761820135_crispy katsu ramen.png', 'available'),
(138, 'Garlic Butter Ramen ', 'Creamy ramen infused with garlic butter and chicken slices.', '19.00', 'Ramen ', '1761820159_garlic butter ramen.png', 'available'),
(139, 'Chicken Katsu Don ', 'Crispy chicken cutlet simmered with egg and onions over rice.', '15.90', 'Donburi', '1761819984_chicken katsu don.png', 'available'),
(140, 'Beef Gyudon', 'Beef slices simmered in sweet soy sauce, served over steamed rice.', '18.00', 'Donburi', '1761819967_beef gyudon.png', 'available'),
(141, 'Oyako Don ', 'Tender chicken and egg simmered in broth, served over rice.', '16.50', 'Donburi', '1761820053_oyako don.png', 'available'),
(142, 'Spicy Chicken Don ', 'Fried chicken tossed in spicy sauce, served with rice and sesame.', '16.90', 'Donburi', '1761820070_spicy chicken don.png', 'available'),
(143, 'Ebi Don ', 'Fried prawns over rice, topped with sweet katsu sauce.', '17.90', 'Donburi', '1761820004_ebi don.png', 'available'),
(144, 'Tofu Don ', 'Fried tofu cubes with teriyaki glaze served on warm rice.', '14.50', 'Donburi', '1761820083_tofu don.png', 'available'),
(145, 'Karaage Don', 'Japanese-style fried chicken served with mayo and shredded cabbage on rice.', '17.00', 'Donburi', '1761820021_karaage don.png', 'available'),
(146, 'Edamame ', 'Lightly salted boiled soybeans, a healthy starter.', '6.00', 'Appetizers', '1761743544_edamame.png', 'available'),
(147, 'Chicken Gyoza (5 pcs) ', 'Pan-fried dumplings stuffed with halal chicken and cabbage.', '9.90', 'Appetizers', '1761743534_chicken gyoza.png', 'available'),
(148, 'Takoyaki (Octopus/Chicken)', 'Soft balls filled with octopus and chicken, topped with bonito flakes.', '10.00', 'Appetizers', '1761743594_takoyaki.png', 'available'),
(149, 'Agedashi Tofu', 'Deep-fried tofu cubes served in light soy broth with radish.', '8.90', 'Appetizers', '1761743511_agedashi tofu.png', 'unavailable'),
(150, 'Miso Soup', 'Classic Japanese soup with tofu, seaweed, and spring onions.', '5.00', 'Appetizers', '1761743565_miso soup.png', 'available'),
(151, 'Seaweed Salad ', 'Fresh wakame salad with sesame dressing.', '7.00', 'Appetizers', '1761743577_seaweed salad.png', 'available'),
(152, 'Goma Spinach', 'Blanched spinach dressed with roasted sesame sauce.', '7.50', 'Appetizers', '1761743555_goma spinach.png', 'available'),
(153, 'Matcha Latte', 'Creamy latte made with pure matcha.', '9.90', 'Drinks', '1761820096_matcha latte.png', 'available'),
(154, 'Peach Soda', 'Sparkling peach-flavored drink.', '8.00', 'Drinks', '1761885801_peach soda.png', 'available'),
(155, 'Iced Green Tea', 'Classic unsweetened Japanese green tea.', '5.00', 'Drinks', '1761885837_grean tea.png', 'available'),
(156, 'Honey Lemon', 'Warm drink with honey and lemon.', '6.50', 'Drinks', '1761885871_iced lemon  tea.png', 'available'),
(157, 'Matcha Mochi', 'Soft rice cake filled with sweet matcha paste.', '8.50', 'Desserts', '1761885989_matcha mochi.png', 'available'),
(158, 'Red Bean Mochi', 'Chewy mochi with red bean filling.', '8.00', 'Desserts', '1761886026_red bean mochi.png', 'available'),
(159, 'Dorayaki', 'Pancake sandwich filled with red bean paste.', '9.00', 'Desserts', '1761886054_dorayaki.png', 'available'),
(160, 'Matcha Ice Cream', 'Creamy green tea-flavored ice cream.', '7.50', 'Desserts', '1761886082_matcha ice cream.png', 'available'),
(161, 'Black Sesame Ice Cream', 'Nutty-flavored Japanese black sesame ice cream.', '8.00', 'Desserts', '1761886168_black sesame.png', 'available'),
(162, 'Taiyaki', 'Fish-shaped cake filled with red bean or custard.', '8.90', 'Desserts', '1761886191_taiyaki.png', 'available'),
(163, 'Matcha Cheesecake', 'Japanese-style fluffy cheesecake with matcha flavor.', '10.50', 'Desserts', '1761886241_matcha cheesecake.png', 'available'),
(164, 'Yuzu Soda', 'Refreshing soda with Japanese citrus flavor.', '8.90', 'Drinks', '1761886385_yuzu soda.png', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `Full_Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `Full_Name`, `Email`, `Password`, `Role`) VALUES
(5, 'Ali', 'ali@gmail.com', '$2y$10$i4lAoiiIw/s2WhjqB8.hoO2Cp1ATIj7e44NEgY8mOQsmIfL.5rMhC', 'user'),
(7, 'Admin', 'admin@gmail.com', '$2y$10$STf4fDERc/9LETr0SXZok.YaxIRZA9RL/D.K97BYhwewjHTgU34gi', 'admin'),
(8, 'Amira', 'amira@gmail.com', '$2y$10$dMRuLBItpFV0SZOZsWnrrOGW0e8Bev3vuTnW4uZHtZf3lff5RVQzS', 'user'),
(9, 'Abu', 'abu@gmail.com', '$2y$10$DfqHpFv/MbWBgEvwzSXhtu2eZ.ocV991aCWW6fQV7UJDA2i5CbRVm', 'user'),
(10, 'Sakura', 'sakura@gmail.com', '$2y$10$X/ZV9MflM.1ULRXctSwfheLkGOkdzxm.YJu3Lf8HjHn5iu1yXXG4m', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`Cart_Item_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Menu_ID` (`Menu_ID`),
  ADD KEY `fk_cart_item_invoice` (`Invoice_ID`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`Invoice_ID`),
  ADD KEY `invoice_ibfk_1` (`User_ID`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`Menu_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `Cart_Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `Menu_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`Menu_ID`) REFERENCES `menu` (`Menu_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_item_invoice` FOREIGN KEY (`Invoice_ID`) REFERENCES `invoice` (`Invoice_ID`) ON DELETE SET NULL;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
