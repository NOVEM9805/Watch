-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 09:25 PM
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
-- Database: `timeless_sight`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_throttle`
--

CREATE TABLE `auth_throttle` (
  `throttle_key` char(64) NOT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `window_start` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_throttle`
--

INSERT INTO `auth_throttle` (`throttle_key`, `attempts`, `window_start`) VALUES
('2c9adea18b8f534aea037c2056dd9b2fde2845542b37efa846718b08fab2f2de', 1, '2026-09-25 22:15:05'),
('2e9609169a71bc5c92d978269f1ec5f77fde671ddda2feb6b0fba8d98b4215d8', 1, '2026-09-26 00:12:25'),
('3d85132df783df21ca1b4c1704319409ff242fd274d6e8bdac53752dc949f4a3', 1, '2026-09-25 22:20:29'),
('630419d55d4c556cf1def01f6871b8ad2fb49e6a70e96c3986d09feb015f3258', 1, '2026-09-26 00:18:54'),
('9c91a653537df85375bb5bfc2608a9461a6c4bc5d55d62a12c64fb34f44191f4', 1, '2026-09-26 00:11:46'),
('f180d8fdcda98c61916f1e084ef4015e57b45722c0fa835a7a1504eeee1fc6bf', 4, '2026-09-25 19:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `description`, `active`, `created_at`) VALUES
(1, 'Casio', 'casio', 'Everyday and sport styles', 1, '2026-09-25 19:15:48'),
(2, 'Seiko', 'seiko', 'Classic Japanese watchmaking', 1, '2026-09-25 19:15:48'),
(3, 'Citizen', 'citizen', 'Modern classics', 1, '2026-09-25 19:15:48'),
(4, 'Orient', 'orient', 'Mechanical inspired designs', 1, '2026-09-25 19:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(26) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `recipient` varchar(120) NOT NULL,
  `phone` varchar(35) NOT NULL,
  `region` varchar(120) NOT NULL,
  `township` varchar(120) NOT NULL,
  `address` text NOT NULL,
  `landmark` varchar(220) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `delivery_fee` decimal(12,2) DEFAULT NULL,
  `status` enum('placed','confirmed','packing','shipped','delivered','cancelled') NOT NULL DEFAULT 'placed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `recipient`, `phone`, `region`, `township`, `address`, `landmark`, `notes`, `subtotal`, `delivery_fee`, `status`, `created_at`) VALUES
(1, 'TS260925F06C691DBC', 9, 'Naing Min Htet', '09779772319', 'DE', 'Wilmington', '1201N Orange St', 'parami road', '', 189000.00, 5000.00, 'delivered', '2026-09-25 23:08:53');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `watch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `brand_name` varchar(120) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `watch_id`, `name`, `brand_name`, `unit_price`, `quantity`) VALUES
(1, 1, 3, 'Casio A168WA', 'Casio', 189000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_challenges`
--

CREATE TABLE `otp_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `purpose` enum('register','login','reset') NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_challenges`
--

INSERT INTO `otp_challenges` (`id`, `user_id`, `purpose`, `code_hash`, `expires_at`, `attempts`, `consumed_at`, `created_at`) VALUES
(6, 9, 'register', '$2y$10$IoO8VCQMI2oU4.3CkT2zseQ/uUzli9nbtpSpH4/rKcKdG7UXWYKzu', '2026-09-25 22:30:34', 1, '2026-09-25 22:21:05', '2026-09-25 22:20:34'),
(9, 9, 'reset', '$2y$10$xS3UAISAvFmdNDUXMtBAX.Kx426Q6SjMGSTuJBNeBMfDke0/axfBu', '2026-09-25 23:15:32', 1, '2026-09-25 23:05:52', '2026-09-25 23:05:32'),
(10, 9, 'login', '$2y$10$y5FVWvkbiFnLVHMHEVzfkewi8.buJ4UMNfNHUevzBRcwBEUvcG5Oy', '2026-09-25 23:16:59', 1, '2026-09-25 23:07:11', '2026-09-25 23:06:59'),
(13, 9, 'reset', '$2y$10$FJhcvA7QBjB5Zb5jy5n7ueqEQx7/jgGW5ia/PRyJ2pTwPSSdDSR26', '2026-09-26 00:22:30', 1, '2026-09-26 00:12:46', '2026-09-26 00:12:30'),
(14, 9, 'login', '$2y$10$THd9ywLsCIFSuZbpvpskeum5mo.youqcPlCMLXfBjeYLbqbfhIhjS', '2026-09-26 00:24:09', 1, '2026-09-26 00:14:21', '2026-09-26 00:14:09'),
(16, 12, 'register', '$2y$10$NdWjj8Br4hxEyEbWxWompea5iMeP4m/8FB2Yfxa5MDZ34S7FX1Gqq', '2026-09-26 00:32:16', 1, '2026-09-26 00:22:50', '2026-09-26 00:22:16'),
(17, 9, 'login', '$2y$10$n5MyGPksv9jrsWEdX26R5.Ez/YFsZ6zylsVbqTqrzztw22fdaM11y', '2026-09-26 00:51:34', 1, '2026-09-26 00:41:49', '2026-09-26 00:41:34'),
(18, 12, 'login', '$2y$10$havB/MQpWuDA35xkRfLgp.ODf5Xhyxt.On2Dd87aVN2NAmK5FoYKW', '2026-09-26 01:04:25', 1, '2026-09-26 00:54:41', '2026-09-26 00:54:25'),
(19, 9, 'login', '$2y$10$hBhT5IfZJahp25spYvfoBuu.mWJm5o2ByqeYLH3HM01LnYQKXNwHW', '2026-09-26 01:08:07', 1, '2026-09-26 00:58:20', '2026-09-26 00:58:07'),
(20, 9, 'login', '$2y$10$iGO9bxSccNnQXkXe.HqnpOaK7dBH1yjJG0us3gj79m39iNhnHIfWC', '2026-09-26 01:46:30', 3, '2026-09-26 01:37:48', '2026-09-26 01:36:30'),
(21, 9, 'login', '$2y$10$BtiMtbLOjHwJ/hXZSEToJO5h3CVsrU0CnoQ3g7zY1uYwiJzGYPhk2', '2026-09-26 01:58:58', 1, '2026-09-26 01:49:12', '2026-09-26 01:48:58'),
(22, 12, 'login', '$2y$10$ghi7mTWkl4f5UVvWbyUMc.WRc4KDyhi.idH7KFxFyIQfUheF7KfZy', '2026-09-26 02:00:17', 1, '2026-09-26 01:50:33', '2026-09-26 01:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `subtitle` varchar(240) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(255) NOT NULL DEFAULT '/shop',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `subtitle`, `image_path`, `link_url`, `sort_order`, `active`, `created_at`) VALUES
(1, 'Time, thoughtfully chosen', 'Discover our watch collection', '/assets/images/slide-01.png', '/shop', 1, 1, '2026-09-25 19:15:48'),
(2, 'Everyday classics', 'Find the watch that fits your day', '/assets/images/slide-02.png', '/shop', 2, 1, '2026-09-25 19:15:48'),
(3, 'A lasting detail', 'Explore four watch brands', '/assets/images/slide-03.png', '/shop', 3, 1, '2026-09-25 19:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `watch_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `body` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `watch_id`, `rating`, `body`, `created_at`) VALUES
(1, 9, 3, 5, 'hiiiii', '2026-09-26 00:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('about_text', 'Timeless Sight brings together familiar everyday watches and expressive pieces from Casio, Seiko, Citizen and Orient. Browse by brand, compare models and save your favourites while you choose a watch that fits your routine or an occasion.\r\n\r\nEach watch page shows the model details and availability. You can place an order with cash on delivery and follow its progress through your account. Our team confirms the delivery fee for your address after the order is received.\r\n\r\nQuestions about a watch or an order? Reach us through the contact page.'),
('catalog_ready', '1'),
('contact_email', 'hnaingmin21@gmail.com'),
('contact_phone', '09779772319'),
('returns_text', 'Please contact Timeless Sight before placing an order to confirm warranty coverage, returns eligibility and delivery arrangements for your chosen watch.'),
('shipping_text', 'Cash on delivery is available. Delivery availability and the fee are confirmed after an order is placed. The final amount appears in your order details once the shop sets the delivery fee.'),
('social_facebook', ''),
('social_telegram', 'https://t.me/ashbie08'),
('social_tiktok', ''),
('social_x', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `session_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `session_version`, `role`, `verified_at`, `created_at`) VALUES
(9, 'Naing Min Htet', 'mgmamarry2030@gmail.com', '$2y$10$hONwviQDcuG4ZEqcLB9UwurEm81vBV1vsxAyiYANASHx2koZb4Bci', 3, 'customer', '2026-09-25 22:21:05', '2026-09-25 22:20:29'),
(12, 'Naing Min Htet', 'hnaingmin21@gmail.com', '$2y$10$X5NSXWbvuX6KFcMXDMSY9eelXoqW4EgDSln0W99WKIPT8L2jn4Dau', 1, 'admin', '2026-09-26 00:22:50', '2026-09-26 00:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `watches`
--

CREATE TABLE `watches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `model` varchar(100) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `watches`
--

INSERT INTO `watches` (`id`, `brand_id`, `name`, `model`, `description`, `price`, `stock`, `image_path`, `featured`, `active`, `created_at`) VALUES
(1, 1, 'Casio F-91W', 'F-91W', 'A lightweight Casio digital watch with an everyday resin strap, alarm, stopwatch and backlight. A simple choice for daily wear.', 149000.00, 5, '/uploads/watches/bca71e009bf92792c12a444638b26dee2b3a.png', 1, 1, '2026-09-25 19:15:48'),
(2, 1, 'Casio AE-1200WH', 'AE-1200WH', 'A Casio digital watch with a world time display, multiple alarms and a practical resin strap. Designed for people who track more than one time zone.', 239000.00, 5, '/uploads/watches/1efde82133eda2326de65767980b89e71b6c.png', 0, 1, '2026-09-25 19:15:48'),
(3, 1, 'Casio A168WA', 'A168WA', 'A retro Casio digital watch with a metal bracelet, illuminated display, alarm and stopwatch. A compact everyday style.', 189000.00, 4, '/uploads/watches/628dfbd0fcd2c38d686810793afb59ec0683.png', 0, 1, '2026-09-25 19:15:48'),
(4, 1, 'Casio MTP-V002D', 'MTP-V002D', 'A straightforward Casio analog watch with a stainless steel bracelet and date display. A clean look for work or daily wear.', 199000.00, 5, '/uploads/watches/2cb7f23a8b64235d1652d0436f25e7a05198.png', 0, 1, '2026-09-25 19:15:48'),
(5, 1, 'Casio G-SHOCK DW-5600', 'DW-5600', 'A square G-SHOCK digital watch with a shock resistant case, countdown timer and stopwatch. A durable sport inspired design.', 479000.00, 5, '/uploads/watches/533487907effaf74add7823964432d8248f4.png', 1, 1, '2026-09-25 19:15:48'),
(6, 2, 'Seiko 5 Sports SRPD55', 'SRPD55', 'A Seiko 5 Sports automatic watch with a rotating bezel and day date display. Its sport styling works with everyday outfits.', 1390000.00, 5, '/uploads/watches/0015a08c325602495549dc9e3631d7ab4dab.png', 1, 1, '2026-09-25 19:15:48'),
(7, 2, 'Seiko 5 Sports SRPE51', 'SRPE51', 'A Seiko 5 Sports automatic watch with a day date display and versatile round case. A restrained look for everyday wear.', 1290000.00, 5, '/uploads/watches/45b87d95a8584bab5fd076a43aa8bdcbb19a.png', 0, 1, '2026-09-25 19:15:48'),
(8, 2, 'Seiko Presage SRPB43', 'SRPB43', 'A Seiko Presage automatic watch with a dress inspired dial and date display. A refined piece for smarter occasions.', 1690000.00, 5, '/uploads/watches/779240715435e1bf6cacc011ede32a3e66e9.png', 0, 1, '2026-09-25 19:15:48'),
(9, 2, 'Seiko Prospex SRPE93', 'SRPE93', 'A Seiko Prospex automatic diver style watch with a rotating bezel and day date display. A bold choice for sport inspired wear.', 2050000.00, 5, '/uploads/watches/a87947b42e83f62718f659b8cd859c91f011.png', 0, 1, '2026-09-25 19:15:48'),
(10, 2, 'Seiko SUR311', 'SUR311', 'A Seiko quartz analog watch with a clear dial and date display. A classic option for a simple daily watch.', 1120000.00, 5, '/uploads/watches/59fd6276dd97f6ace2b97defb659641074db.png', 0, 1, '2026-09-25 19:15:48'),
(11, 3, 'Citizen BM8180-03E', 'BM8180-03E', 'A Citizen Eco-Drive watch powered by light, with a field inspired dial and day date display. Easy to pair with casual clothes.', 690000.00, 5, '/uploads/watches/e06227c67b9b0535a38039307ed5c4ed2633.png', 1, 1, '2026-09-25 19:15:48'),
(12, 3, 'Citizen AW1236-03A', 'AW1236-03A', 'A Citizen Eco-Drive analog watch powered by light. Its clean dial makes it a versatile everyday accessory.', 720000.00, 5, '/uploads/watches/46e72aada917890fc7c18960d79655a82473.png', 0, 1, '2026-09-25 19:15:48'),
(13, 3, 'Citizen BN0150-28E', 'BN0150-28E', 'A Citizen Promaster Dive watch with Eco-Drive light powered movement and a rotating bezel. A distinctive sport watch.', 1390000.00, 5, '/uploads/watches/740d887a7a83d75af941169adbbdee76f31a.png', 0, 1, '2026-09-25 19:15:48'),
(14, 3, 'Citizen CA0649-06X', 'CA0649-06X', 'A Citizen Eco-Drive chronograph powered by light, with subdials and a date display. A sporty style for daily use.', 1280000.00, 5, '/uploads/watches/9dad8a9ae5d5e8612e1ec26dc14c1500a9ae.png', 0, 1, '2026-09-25 19:15:48'),
(15, 3, 'Citizen NJ0150-56E', 'NJ0150-56E', 'A Citizen TSUYOSA automatic watch with an integrated look, metal bracelet and date display. A modern everyday style.', 1290000.00, 5, '/uploads/watches/4ae439e0aa9b80e9320476d1840170fa7c2b.png', 0, 1, '2026-09-25 19:15:48'),
(16, 4, 'Orient Bambino RA-AC0M03S', 'RA-AC0M03S', 'An Orient Bambino automatic watch with a classic dress watch profile. A fitting choice for formal and everyday outfits.', 1050000.00, 5, '/uploads/watches/3231c628a84fdd5f84a2aea510ec7fc5ec35.png', 1, 1, '2026-09-25 19:15:48'),
(17, 4, 'Orient Kamasu RA-AA0003R', 'RA-AA0003R', 'An Orient Kamasu automatic watch with a rotating bezel and bold dial. A sporty watch with a distinctive look.', 1290000.00, 5, '/uploads/watches/e01d6c2287d5a988c9aa138467df0f7894f1.png', 0, 1, '2026-09-25 19:15:48'),
(18, 4, 'Orient Mako III RA-AA0820R', 'RA-AA0820R', 'An Orient Mako III automatic watch with a rotating bezel and warm toned dial. A practical sport inspired style.', 1250000.00, 5, '/uploads/watches/b88e4933f1a1a66fecf874996538269edff2.png', 0, 1, '2026-09-25 19:15:48'),
(19, 4, 'Orient Defender RA-AK0401L', 'RA-AK0401L', 'An Orient Defender automatic watch with a field inspired design and layered display. An expressive everyday piece.', 1190000.00, 5, '/uploads/watches/269377274d31ee9111fc2fa5d6f6c8dcbc92.png', 0, 1, '2026-09-25 19:15:48'),
(20, 4, 'Orient Contemporary RA-AC0F07S', 'RA-AC0F07S', 'An Orient Contemporary automatic watch with a clean analog dial. A versatile option for smart casual outfits.', 1090000.00, 5, '/uploads/watches/3a29c23eede4aa553c5990ccff5a56fdc9b0.png', 0, 1, '2026-09-25 19:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `watch_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`user_id`, `watch_id`) VALUES
(9, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_throttle`
--
ALTER TABLE `auth_throttle`
  ADD PRIMARY KEY (`throttle_key`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`,`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `watch_id` (`watch_id`);

--
-- Indexes for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`purpose`,`created_at`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`watch_id`),
  ADD KEY `watch_id` (`watch_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `watches`
--
ALTER TABLE `watches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`active`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`user_id`,`watch_id`),
  ADD KEY `watch_id` (`watch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `watches`
--
ALTER TABLE `watches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`watch_id`) REFERENCES `watches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `otp_challenges`
--
ALTER TABLE `otp_challenges`
  ADD CONSTRAINT `otp_challenges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`watch_id`) REFERENCES `watches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `watches`
--
ALTER TABLE `watches`
  ADD CONSTRAINT `watches_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`watch_id`) REFERENCES `watches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
