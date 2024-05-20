-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 20, 2024 at 12:06 AM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
CREATE TABLE IF NOT EXISTS `answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `user_id` int NOT NULL,
  `answer_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `user_id`, `answer_text`, `is_correct`, `created_at`) VALUES
(35, 9, 1, 'পর্যায়কাল / Period =?	', 0, '2024-02-04 16:56:57'),
(57, 8, 1, '100', 1, '2024-02-05 09:25:11'),
(34, 9, 1, '3', 0, '2024-02-04 16:56:57'),
(33, 8, 1, '200', 0, '2024-02-04 16:55:50'),
(73, 8, 1, '300', 0, '2024-02-05 09:33:14'),
(131, 16, 1, 'hew2', 0, '2024-02-12 09:49:35'),
(29, 7, 1, '12 টি	', 0, '2024-02-04 16:54:48'),
(28, 7, 1, '23 জোড়া', 0, '2024-02-04 16:54:48'),
(27, 7, 1, '46টি 	', 0, '2024-02-04 16:54:48'),
(26, 7, 1, '22 জোড়া', 0, '2024-02-04 16:54:48'),
(123, 15, 1, 'soikot', 0, '2024-02-07 19:26:21'),
(124, 15, 1, 'sarfarazz', 0, '2024-02-07 19:26:21'),
(125, 15, 1, 'nayeem', 0, '2024-02-07 19:26:21'),
(22, 6, 1, '746 j', 0, '2024-02-04 16:54:19'),
(36, 9, 1, 'বেগ / Velocity = ?	', 0, '2024-02-04 16:56:57'),
(37, 9, 1, ' গতিশক্তি/ Kinetic Energy ?', 0, '2024-02-04 16:56:57'),
(38, 10, 1, 'বেগ', 0, '2024-02-04 16:57:32'),
(39, 10, 1, 'সময়', 0, '2024-02-04 16:57:32'),
(40, 10, 1, 'দুরত্ব', 1, '2024-02-04 16:57:32'),
(41, 10, 1, 'গতি', 0, '2024-02-04 16:57:32'),
(42, 11, 1, 'অসীম', 1, '2024-02-04 16:57:58'),
(43, 11, 1, 'শূন্য', 0, '2024-02-04 16:57:58'),
(44, 11, 1, '৩২০ সেকেন্ড', 0, '2024-02-04 16:57:58'),
(45, 11, 1, '২৪ ঘন্টা', 0, '2024-02-04 16:57:58'),
(46, 12, 1, '5C', 1, '2024-02-04 16:58:34'),
(47, 12, 1, '10C', 0, '2024-02-04 16:58:34'),
(48, 12, 1, '90C', 0, '2024-02-04 16:58:34'),
(49, 12, 1, '45C', 0, '2024-02-04 16:58:34'),
(50, 13, 1, 'A1 tr', 1, '2024-02-05 08:43:29'),
(51, 13, 1, 'A2', 0, '2024-02-05 08:43:29'),
(52, 13, 1, 'A3', 0, '2024-02-05 08:43:29'),
(53, 13, 1, 'A55', 0, '2024-02-05 08:43:29'),
(54, 13, 1, 'A6 tr', 1, '2024-02-05 08:43:29'),
(55, 13, 1, 'A7', 0, '2024-02-05 08:43:29'),
(132, 16, 1, ' ASDSAD', 1, '2024-02-12 09:49:35'),
(82, 8, 1, 'CTTTT', 0, '2024-02-05 09:35:11'),
(80, 8, 1, 'ATTT', 1, '2024-02-05 09:35:11'),
(81, 8, 1, 'BC', 1, '2024-02-05 09:35:11'),
(130, 16, 1, 'hew', 0, '2024-02-12 09:49:35'),
(129, 16, 1, 'he1', 0, '2024-02-12 09:49:35'),
(89, 8, 1, 'aD', 1, '2024-02-05 09:41:34'),
(101, 7, 1, 'A', 0, '2024-02-05 09:49:34'),
(102, 7, 1, 'BTre', 1, '2024-02-05 09:49:34'),
(103, 7, 1, 'C', 0, '2024-02-05 09:49:34'),
(104, 7, 1, 'D', 0, '2024-02-05 09:49:34'),
(105, 7, 1, 'FTrue', 0, '2024-02-05 09:49:34'),
(106, 7, 1, 'A', 0, '2024-02-05 09:49:44'),
(107, 7, 1, 'BTre', 1, '2024-02-05 09:49:44'),
(108, 7, 1, 'C', 0, '2024-02-05 09:49:44'),
(109, 7, 1, 'D', 0, '2024-02-05 09:49:44'),
(110, 7, 1, 'FTrue', 0, '2024-02-05 09:49:44'),
(122, 14, 1, 'bolbo na', 1, '2024-02-06 02:25:44'),
(121, 14, 1, 'very good', 0, '2024-02-06 02:25:44'),
(120, 14, 1, 'fucking good', 0, '2024-02-06 02:25:44'),
(119, 14, 1, 'good', 0, '2024-02-06 02:25:44'),
(117, 6, 1, 'g', 0, '2024-02-05 09:50:45'),
(118, 6, 1, 'h', 1, '2024-02-05 09:50:45'),
(126, 15, 1, 'Khalifa', 1, '2024-02-07 19:26:21'),
(127, 15, 1, 'Leone', 1, '2024-02-07 19:26:21'),
(133, 17, 1, 'he1', 0, '2024-02-12 09:50:12'),
(134, 17, 1, 'hew', 0, '2024-02-12 09:50:12'),
(135, 17, 1, 'hew2', 0, '2024-02-12 09:50:12'),
(136, 17, 1, ' ASDSAD', 1, '2024-02-12 09:50:12'),
(141, 22, 1, 'Mr Y', 0, '2024-02-12 13:14:41'),
(140, 22, 1, 'Mr X', 0, '2024-02-12 13:14:41'),
(138, 19, 1, 'ABCww', 0, '2024-02-12 09:52:42'),
(139, 8, 1, '33', 1, '2024-02-12 09:54:03'),
(142, 23, 1, 'A1', 0, '2024-02-12 18:32:19'),
(143, 23, 1, 'A2', 1, '2024-02-12 18:32:19'),
(144, 23, 1, 'A4', 0, '2024-02-12 18:32:19'),
(145, 23, 1, 'A5', 0, '2024-02-12 18:32:19'),
(146, 24, 1, '1', 0, '2024-02-13 04:39:34'),
(147, 24, 1, '2', 0, '2024-02-13 04:39:34'),
(148, 24, 1, '3', 0, '2024-02-13 04:39:34'),
(149, 24, 1, '4', 1, '2024-02-13 04:39:34'),
(150, 24, 1, '5', 0, '2024-02-13 04:39:34'),
(151, 27, 1, 'Rangpur', 0, '2024-02-21 19:06:48'),
(152, 27, 1, 'Dhaka', 1, '2024-02-21 19:06:48'),
(153, 27, 1, 'Khulna', 0, '2024-02-21 19:06:48'),
(154, 27, 1, 'Dinajpur', 0, '2024-02-21 19:06:48'),
(155, 28, 1, '1', 0, '2024-02-21 19:07:06'),
(156, 28, 1, '2', 0, '2024-02-21 19:07:06'),
(157, 28, 1, '3', 0, '2024-02-21 19:07:06'),
(158, 28, 1, '4', 1, '2024-02-21 19:07:06'),
(159, 29, 1, '6', 0, '2024-02-24 16:27:54'),
(160, 29, 1, '7', 0, '2024-02-24 16:27:54'),
(161, 29, 1, '8', 1, '2024-02-24 16:27:54'),
(162, 29, 1, '9', 0, '2024-02-24 16:27:54'),
(163, 30, 1, '9', 1, '2024-03-03 07:47:06'),
(164, 30, 1, '10 ', 0, '2024-03-03 07:47:06'),
(165, 30, 1, '11', 0, '2024-03-03 07:47:06'),
(166, 30, 1, '12', 0, '2024-03-03 07:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

DROP TABLE IF EXISTS `evaluation`;
CREATE TABLE IF NOT EXISTS `evaluation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `paper_id` int NOT NULL,
  `Qdata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Adata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluation`
--

INSERT INTO `evaluation` (`id`, `user_id`, `paper_id`, `Qdata`, `Adata`, `result`, `timestamp`) VALUES
(4, 1, 8, '22@141@140#19@138#24@146@148@149@147@150#24@146@147@149@150@148#22@141@140', '{\"22\":{\"140\":\"140\",\"141\":\"141\"},\"19\":{\"138\":\"138\"},\"24\":{\"150\":\"150\",\"147\":\"147\"}}', '[0,5,0]', '2024-03-02 20:03:19'),
(3, 1, 11, '19@138#16@130@132@131@129#24@149@147@148@150@146#8@139@89@57@80@82@73@33@81#17@136@134@135@133#15@124@126@125@127@123#18#23@143@144@145@142#22@141@140#28@155@157@156@158#20', '{\"19\":{\"138\":\"138\"},\"16\":{\"132\":\"132\",\"131\":\"131\"},\"8\":{\"80\":\"80\"}}', '[0,3,8]', '2024-03-02 19:42:13'),
(5, 1, 7, '21#19@138#20#22@140@141#23@145@143@144@142#17@136@135@133@134#18#23@144@145@143@142#15@125@124@127@126@123', '{\"22\":{\"140\":\"140\"}}', '[0,1,8]', '2024-03-03 07:41:33'),
(6, 1, 2, '24@150@146@147@148@149#22@140@141', '{\"24\":{\"149\":\"149\"},\"22\":{\"141\":\"141\"}}', '[1,1,0]', '2024-03-03 07:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `meta_data`
--

DROP TABLE IF EXISTS `meta_data`;
CREATE TABLE IF NOT EXISTS `meta_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meta_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `referece_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meta_data`
--

INSERT INTO `meta_data` (`id`, `meta_name`, `meta_value`, `referece_id`, `timestamp`) VALUES
(1, 'meta_cat', '511,513,102,352', 8, '2024-02-12 18:19:13'),
(2, 'meta_typ', '66', 8, '2024-02-12 18:19:13'),
(3, 'meta_cat', 'AAA,102', 23, '2024-02-12 18:32:19'),
(4, 'meta_typ', 'BBB', 23, '2024-02-12 18:32:19'),
(5, 'meta_cat', '512,513,101', 24, '2024-02-13 04:39:34'),
(6, 'meta_typ', '513', 24, '2024-02-13 04:39:34'),
(7, 'meta_cat', '5130', 25, '2024-02-13 04:44:53'),
(8, 'meta_typ', '2', 25, '2024-02-13 04:44:53'),
(9, 'meta_cat', '103', 15, '2024-02-20 08:55:19'),
(10, 'meta_typ', '', 15, '2024-02-20 08:55:19'),
(11, 'meta_cat', '101', 22, '2024-02-20 10:14:27'),
(12, 'meta_typ', '', 22, '2024-02-20 10:14:27'),
(13, 'meta_cat', '102', 21, '2024-02-20 10:14:32'),
(14, 'meta_typ', '', 21, '2024-02-20 10:14:32'),
(15, 'meta_cat', '103', 20, '2024-02-20 10:14:36'),
(16, 'meta_typ', '', 20, '2024-02-20 10:14:36'),
(17, 'meta_cat', '101', 19, '2024-02-20 10:14:40'),
(18, 'meta_typ', '', 19, '2024-02-20 10:14:40'),
(19, 'meta_cat', '102', 18, '2024-02-20 10:14:45'),
(20, 'meta_typ', '', 18, '2024-02-20 10:14:45'),
(21, 'meta_cat', '101', 17, '2024-02-20 10:14:50'),
(22, 'meta_typ', '', 17, '2024-02-20 10:14:50'),
(23, 'meta_cat', '101', 16, '2024-02-20 10:14:54'),
(24, 'meta_typ', '', 16, '2024-02-20 10:14:54'),
(25, 'meta_cat', '', 26, '2024-02-20 15:20:55'),
(26, 'meta_typ', '', 26, '2024-02-20 15:20:55'),
(27, 'meta_cat', '110', 27, '2024-02-21 19:06:48'),
(28, 'meta_typ', '', 27, '2024-02-21 19:06:48'),
(29, 'meta_cat', '110', 28, '2024-02-21 19:07:06'),
(30, 'meta_typ', '', 28, '2024-02-21 19:07:06'),
(31, 'meta_cat', '101', 29, '2024-02-24 16:27:54'),
(32, 'meta_typ', '', 29, '2024-02-24 16:27:54'),
(33, 'meta_cat', '101', 30, '2024-03-03 07:47:06'),
(34, 'meta_typ', '', 30, '2024-03-03 07:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `user_id`, `question_text`, `created_at`) VALUES
(8, 1, 'পৃথিবীর এক পাশ থেকে অন্য পাশে সুরঙ্গ করে , একটি বলকে সুরঙ্গের একপাশ থেকে ছেড়ে দিলে অপর পাশে যেতে কতটা সময় লাগবে?2', '2024-02-04 16:55:50'),
(7, 1, 'মানবদেহে ক্রোমোজোম সংখ্যা: ', '2024-02-04 16:54:48'),
(6, 1, '1 hp  =  ', '2024-02-04 16:54:19'),
(9, 1, '10kg ভরের একটি কৃত্রিম উপগ্রহ 12000 km উচ্চতায় থাকলে , তার\r\n', '2024-02-04 16:56:57'),
(10, 1, ' আলোকবর্ষ কিসের একক?', '2024-02-04 16:57:32'),
(11, 1, 'পৃথিবী থেকে চাদে কোন শব্দ পৌছাতে  কত সময় লাগবে?', '2024-02-04 16:57:58'),
(12, 1, '50C এবং -40C এর দুটো সমান গোলকের চার্জ স্পর্শ করালে প্রত্যেকটির  চার্জ কত হবে?\r\n', '2024-02-04 16:58:34'),
(13, 1, 'Sample question add option', '2024-02-05 08:43:29'),
(14, 1, 'how are u', '2024-02-06 02:25:44'),
(15, 1, 'What is my baby name ? ', '2024-02-07 19:26:21'),
(16, 1, 'Hello ', '2024-02-12 09:49:35'),
(17, 1, 'Hello ', '2024-02-12 09:50:12'),
(18, 1, 'পৃথিবীর এক পাশ থেকে অন্য পাশে সুরঙ্গ করে , একটি বলকে সুরঙ্গের একপাশ থেকে ছেড়ে দিলে অপর পাশে যেতে কতটা সময় লাগবে?', '2024-02-12 09:52:29'),
(19, 1, 'পৃথিবীর এক পাশ থেকে অন্য পাশে সুরঙ্গ করে , একটি বলকে সুরঙ্গের একপাশ থেকে ছেড়ে দিলে অপর পাশে যেতে কতটা সময় লাগবে?', '2024-02-12 09:52:42'),
(20, 1, 'পৃথিবীর এক পাশ থেকে অন্য পাশে সুরঙ্গ করে , একটি বলকে সুরঙ্গের একপাশ থেকে ছেড়ে দিলে অপর পাশে যেতে কতটা সময় লাগবে?w', '2024-02-12 09:53:11'),
(21, 1, 'পৃথিবীর এক পাশ থেকে অন্য পাশে সুরঙ্গ করে , একটি বলকে সুরঙ্গের একপাশ থেকে ছেড়ে দিলে অপর পাশে যেতে কতটা সময় লাগবে?', '2024-02-12 09:53:13'),
(22, 1, 'What is your name ? Tell me your name ; Okay ? \r\n\r\nListen to me very carefully. okay ?\r\n ', '2024-02-12 13:14:22'),
(23, 1, 'Question', '2024-02-12 18:32:19'),
(24, 1, 'What is 2+2?', '2024-02-13 04:39:34'),
(25, 1, 'What you name ? ', '2024-02-13 04:44:53'),
(26, 1, '222', '2024-02-20 15:20:55'),
(27, 1, 'What is the Capital of Bangladesh', '2024-02-21 19:06:48'),
(28, 1, 'What is 2+2 ? ', '2024-02-21 19:07:06'),
(29, 1, 'what is 9-1?', '2024-02-24 16:27:54'),
(30, 1, 'What is 4+5 ?', '2024-03-03 07:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `question_papers`
--

DROP TABLE IF EXISTS `question_papers`;
CREATE TABLE IF NOT EXISTS `question_papers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_papers`
--

INSERT INTO `question_papers` (`id`, `title`, `value`, `user`, `timestamp`) VALUES
(1, 'AA', '24#22', 1, '2024-02-20 15:15:12'),
(2, 'AA', '24#22', 1, '2024-02-20 15:16:47'),
(3, 'Sample Question 1', '24#22#20#15#24', 1, '2024-02-20 16:22:34'),
(4, 'Sample Question 1', '24#22#20#15#23', 1, '2024-02-20 16:22:44'),
(5, 'Sample Question 1', '24#23#20#15#23', 1, '2024-02-20 16:23:00'),
(6, 'Sample Question 1', 'Array', 1, '2024-02-20 16:24:36'),
(7, 'Sample 20', '23#22#19#17#23#21#18#20#15', 1, '2024-02-20 16:26:38'),
(8, 'Hello World', '24#22#19#24#22', 1, '2024-02-20 16:49:51'),
(9, '111', '16#24#19#17#22#23#18#8#21#15#20', 1, '2024-02-21 10:45:29'),
(10, 'Question Evo', '28#27', 1, '2024-02-21 19:07:39'),
(11, 'Phy 1st', '17#16#22#24#19#23#18#8#20#15#28', 1, '2024-02-23 16:57:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `mail`, `password`, `key1`, `role`) VALUES
(1, 'arnob', 'arnob@arnob.com', '$2y$10$441r9moyIOmFZT2Kz9ApX.u1uRXAhPAKgeUMuT8fmTMPWbmbT1AX2', '2n38b8#O@*B@GDSJHSHDi', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
