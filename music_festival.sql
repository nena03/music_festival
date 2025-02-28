-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 27, 2025 at 01:09 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `music_festival`
--
CREATE DATABASE IF NOT EXISTS `music_festival` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `music_festival`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `user_id_2` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`) VALUES
(1, 19);

-- --------------------------------------------------------

--
-- Table structure for table `ankete`
--

DROP TABLE IF EXISTS `ankete`;
CREATE TABLE IF NOT EXISTS `ankete` (
  `id` int NOT NULL AUTO_INCREMENT,
  `naziv` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `naziv` (`naziv`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ankete`
--

INSERT INTO `ankete` (`id`, `naziv`, `link`, `user_id`) VALUES
(1, 'Efikasnost sajta', '', 19),
(7, 'Pitamo Vas', '', 19),
(9, 'Zanima nas?', '', 19);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `performer_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `event_id` int NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `performer_id` (`performer_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `performer_id`, `user_id`, `content`, `created_at`, `event_id`) VALUES
(9, 7, 3, 'superrr', '2025-02-06 23:36:01', 1),
(8, 7, 3, 'odlican trubac', '2025-02-05 00:21:59', 1),
(10, 8, 3, 'prelepa devojka', '2025-02-25 22:27:56', 1),
(7, 10, 3, 'odlicni', '2025-01-24 18:39:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `event_time` datetime NOT NULL,
  `artist_id` int DEFAULT NULL,
  `scene` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `organizator_id` int DEFAULT NULL,
  `festival_id` int NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `artist_id` (`artist_id`),
  KEY `organizator_id` (`organizator_id`),
  KEY `fk_festival` (`festival_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `name`, `description`, `event_time`, `artist_id`, `scene`, `organizator_id`, `festival_id`) VALUES
(1, 'Narodno veče - Otvaranje festivala', 'Dočekaće Vas, niz spektakularnih izvodjača uz najlepse pesme i najbolji okestar Srbije.', '2025-08-02 19:00:00', 1, 'Main Stage', 1, 2),
(2, 'Koncert trubaca Srbije', 'Predstavljanje trubača i njihovih bendova.', '2025-08-03 20:30:00', 2, 'Main Stage', 1, 2),
(3, 'Pobedničko veče i Goran Bregović', 'Nezaboravni Goran Bregović sa svojim orkestrom ulepšaće završno veče, nakon dodele zlatne trube.', '2025-08-04 22:00:00', 3, 'Main Stage', 1, 2),
(9, 'Rock Night', 'Veče rock muzike sa lokalnim bendovima.', '2025-07-15 20:00:00', 1, 'Main Stage', 2, 3),
(10, 'Pop Night', 'Nastup poznatih pop izvođača.', '2025-08-20 18:30:00', 3, 'Pop Stage', 3, 4),
(11, 'EDM Party', 'Najbolji DJ-evi na elektronskoj žurci.', '2025-07-16 22:00:00', 5, 'Dance Arena', 2, 3),
(12, 'Popduo', 'Nastup poznatih pop izvođača.', '2025-08-21 18:30:00', 3, 'Pop Stage', 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `event_performers`
--

DROP TABLE IF EXISTS `event_performers`;
CREATE TABLE IF NOT EXISTS `event_performers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `performer_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_performer` (`event_id`,`performer_id`),
  KEY `performer_id` (`performer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_performers`
--

INSERT INTO `event_performers` (`id`, `event_id`, `performer_id`) VALUES
(58, 1, 21),
(50, 1, 7),
(49, 1, 8),
(48, 2, 9),
(47, 3, 10),
(46, 1, 12),
(71, 2, 31),
(53, 1, 19),
(75, 9, 35),
(74, 11, 34),
(73, 3, 33),
(72, 2, 32),
(66, 10, 25),
(67, 12, 26),
(68, 9, 27),
(69, 11, 28),
(76, 12, 36),
(77, 10, 37),
(78, 9, 38),
(79, 1, 38);

-- --------------------------------------------------------

--
-- Table structure for table `event_ratings`
--

DROP TABLE IF EXISTS `event_ratings`;
CREATE TABLE IF NOT EXISTS `event_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int DEFAULT NULL,
  `user_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `rating_count` int DEFAULT '0',
  `average_rating` float DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_ratings`
--

INSERT INTO `event_ratings` (`id`, `event_id`, `user_name`, `rating`, `created_at`, `rating_count`, `average_rating`) VALUES
(1, 1, '', 4.00, '2025-01-17 22:58:17', 27, 3.03704),
(2, 1, '', 4.00, '2025-01-17 22:58:24', 27, 3.03704),
(3, 1, '', 4.00, '2025-01-17 23:06:13', 27, 3.03704),
(4, 1, '', 4.00, '2025-01-17 23:10:40', 27, 3.03704),
(5, 1, '', 3.00, '2025-01-17 23:22:54', 27, 3.03704),
(6, 1, '', 3.00, '2025-01-17 23:26:01', 27, 3.03704),
(7, 1, '', 5.00, '2025-01-17 23:26:06', 27, 3.03704),
(8, 1, '', 4.00, '2025-01-17 23:26:12', 27, 3.03704),
(9, 1, '', 4.00, '2025-01-17 23:30:11', 27, 3.03704),
(10, 1, '', 5.00, '2025-01-17 23:30:18', 27, 3.03704),
(11, 1, '', 3.00, '2025-01-17 23:30:24', 27, 3.03704),
(12, 1, '', 3.00, '2025-01-17 23:55:24', 27, 3.03704),
(13, 3, '', 1.00, '2025-01-17 23:59:54', 2, 1),
(14, 3, '', 1.00, '2025-01-18 00:06:59', 2, 1),
(15, 1, '', 3.00, '2025-01-19 00:37:28', 27, 3.03704);

-- --------------------------------------------------------

--
-- Table structure for table `favourite_performers`
--

DROP TABLE IF EXISTS `favourite_performers`;
CREATE TABLE IF NOT EXISTS `favourite_performers` (
  `user_id` int NOT NULL,
  `performer_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`performer_id`),
  KEY `performer_id` (`performer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite_performers`
--

INSERT INTO `favourite_performers` (`user_id`, `performer_id`) VALUES
(3, 7),
(3, 11);

-- --------------------------------------------------------

--
-- Table structure for table `festivals`
--

DROP TABLE IF EXISTS `festivals`;
CREATE TABLE IF NOT EXISTS `festivals` (
  `festival_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `organizator_id` int DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`festival_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `festivals`
--

INSERT INTO `festivals` (`festival_id`, `name`, `location`, `start_date`, `end_date`, `description`, `organizator_id`, `image_url`) VALUES
(2, 'Guca', 'Guča, Serbia', '2025-08-01', '2025-08-05', 'Najpoznatiji festival trube u Srbiji.', 1, 'https://setv.rs/wp-content/uploads/2024/07/Guca-2024.jpg'),
(3, 'Exit', 'Novi Sad, Serbia', '2025-08-01', '2025-08-05', 'Najpoznatiji festival rock muzike u Srbiji.', 2, 'https://www.exitfest.org/wp-content/uploads/2022/04/51299565578_5f8d908210_o-1.jpg'),
(4, 'Popfest', 'Belgrade, Serbia', '2025-08-01', '2025-08-05', 'Najpoznatiji festival pop muzike u Srbiji.', 3, 'https://i.imgur.com/r4AOhYX.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `festival_visits`
--

DROP TABLE IF EXISTS `festival_visits`;
CREATE TABLE IF NOT EXISTS `festival_visits` (
  `festival_id` int NOT NULL,
  `visit_count` int DEFAULT '0',
  `last_visit` datetime DEFAULT NULL,
  PRIMARY KEY (`festival_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `festival_visits`
--

INSERT INTO `festival_visits` (`festival_id`, `visit_count`, `last_visit`) VALUES
(3, 24, '2025-02-27 01:58:58');

-- --------------------------------------------------------

--
-- Table structure for table `odgovori`
--

DROP TABLE IF EXISTS `odgovori`;
CREATE TABLE IF NOT EXISTS `odgovori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anketa_id` int NOT NULL,
  `pitanje_id` int NOT NULL,
  `opcija_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `anketa_id` (`anketa_id`),
  KEY `pitanje_id` (`pitanje_id`),
  KEY `opcija_id` (`opcija_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `odgovori`
--

INSERT INTO `odgovori` (`id`, `anketa_id`, `pitanje_id`, `opcija_id`, `user_id`) VALUES
(6, 1, 1, 1, 3),
(8, 9, 19, 70, 3);

-- --------------------------------------------------------

--
-- Table structure for table `opcije`
--

DROP TABLE IF EXISTS `opcije`;
CREATE TABLE IF NOT EXISTS `opcije` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pitanje_id` int NOT NULL,
  `opcija` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pitanje_id` (`pitanje_id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opcije`
--

INSERT INTO `opcije` (`id`, `pitanje_id`, `opcija`) VALUES
(1, 1, 'Odlicno'),
(2, 1, 'Dobro'),
(42, 1, 'Lose'),
(45, 1, 'Super'),
(41, 1, 'Top'),
(66, 17, 'Rock muziku'),
(65, 17, 'Pop muziku'),
(64, 17, 'Narodnu'),
(71, 19, 'Pop'),
(70, 19, 'Narodni'),
(72, 19, 'Rock');

-- --------------------------------------------------------

--
-- Table structure for table `organizator`
--

DROP TABLE IF EXISTS `organizator`;
CREATE TABLE IF NOT EXISTS `organizator` (
  `organizator_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`organizator_id`),
  UNIQUE KEY `organizator_id` (`organizator_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizator`
--

INSERT INTO `organizator` (`organizator_id`, `user_id`) VALUES
(1, 9),
(2, 14),
(3, 13);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `user_id` (`user_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performers`
--

DROP TABLE IF EXISTS `performers`;
CREATE TABLE IF NOT EXISTS `performers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `genre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performers`
--

INSERT INTO `performers` (`id`, `name`, `genre`, `bio`, `image_url`) VALUES
(12, 'Marina Stankic', 'Narodno', 'Pevacica Narodne muzike', 'https://cdn-images.dzcdn.net/images/cover/caf3c716da6b30f3ef9c2efb07374ff9/500x500-000000-80-0-0.jpg'),
(7, 'Dejan Petrovic', 'Narodno', 'Dejan Petrovic i Big Band.', 'https://zoomue.rs/wp-content/uploads/2024/07/Dejan-Petrovic.jpg'),
(8, 'Danica Krstić', 'Etno', 'Pevacica predivne boje glasa, ocarace vas svojim nastupom. ', 'https://ethnocloud.com/image.php?mode=photo_image&band_id=6867&image_id=6798&crop_width=500&skin=Nova_modified'),
(9, 'Miroslav Ilic', 'Narodno', 'Jedan od najpoznatijih pevaca srpske muzike.', 'https://savacentar.rs/wp-content/uploads/2024/05/image00009-1024x769.jpeg'),
(10, 'Dejan Lazarevic', 'Narodno', 'Dejan Lazarevic nastupice sa svojim trumpet orkestrom.', 'https://ocdn.eu/pulscms-transforms/1/VU_k9kpTURBXy81MTdjZmExOTM3M2YxZTJhZTUzZTQ3YWE1M2VhYjU3NS5qcGeRkwLNAxYA3gABoTAF'),
(19, 'Lepa Brena', 'Narodno', 'Pevacica narodne muzike', 'https://www.kudaveceras.rs/images/kcfinder/image/Blog/2024/Lepa%20Brena/lepa-brena-nova-godina.jpg'),
(20, 'Hanka Paldum', 'Narodno', 'Pevacica narodne muzike, sevdalinki!', 'https://gale-s3-bucket.s3.eu-central-1.amazonaws.com/c8fb119c-2ecf-4506-96e6-9bb8c67b7ac8.jpeg'),
(21, 'Jelena Tomasevic', 'Narodno', 'Pevacica etno srpskih pesama!', 'https://www.tomasevicjelena.com/media/sigplus/preview/de70dbbf2d9a6dbc931d7eb02a1e50c3.jpg'),
(25, 'Zdravko Colic', 'Pop', 'Pevac pop i rok muzike.', 'https://www.vamedia.info/wp-content/uploads/2023/08/Zdravko-Colic-photo-Aleksandar-Kerekes-Keki-scaled.jpg'),
(26, 'Vlado Georgiev', 'Pop', 'Pevac pop muzike.', 'https://i.scdn.co/image/ab67616d0000b273d64423f92b7821646fb9781f'),
(31, 'Stefan Mladenovic Trumpet Orchestra', 'Narodno', 'Sjajan Stefan sa svojim orkestrom, pobednik proslogodisnjeg sabora trubaca', 'https://www.politika.rs/thumbs//old/uploads/rubrike/335345/i/1///677z381_Guca---orkestar-Nenada-Mladenovica-slavi-pobedu---G.jpg'),
(27, 'Bajaga', 'Rok', 'Pevac rok muzike.', 'https://highwaystarmagazine.org/wp-content/uploads/2024/08/channels4_profile-2.jpg'),
(28, 'Galija', 'Rok', 'Galija bend rok muzike.', 'https://barikada.com/wp-content/uploads/2018/06/Galija-Bend.jpg'),
(33, 'Goran Bregovic', 'Rock', 'Goran Bregovic & his Wedding and Funeral Band', 'https://storage.moldova1.md/images/b6c5b741-7832-43c0-9601-29c3a096956d.jpg'),
(32, 'Timocki veseljaci Trumpet Orchestra', 'Narodno', 'Razveselice Vam vece i ulepsati dan', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSkUvUJsW6bBe6ee238IoMXS8yS0Ul5dNSONg&s'),
(34, 'Željko Joksimović', 'Rock', 'Srpski je kantautor, kompozitor i preduzetnik. Svira 12 muzičkih instrumenata, poput harmonike, klavira, ...', 'https://www.radiobijelopolje.me/images/01-showbusiness-2023-3/26-12-zeljjj.jpg'),
(35, 'Goca Tržan', 'Rock', 'Singer ,TV host, ‍ Actress, Makeup artist', 'https://radiojat.rs/wp-content/uploads/sites/4/2022/10/Goca-Trzan.jpg'),
(36, 'Saša Kovačević', 'Rock', 'Singer', 'https://www.kudaveceras.rs/images/bands/1585816699-sk-1.jpg'),
(37, 'Ceca', 'Rock', 'Singer, najpopularnija pevacica', 'https://www.glas-javnosti.rs/uploads/images/1/2024_06_14/image_1718396038_542854.jpg'),
(38, 'Aca Lukas', 'Rock', 'Singer', 'https://xdn.tf.rs/2019/08/25/1566715325859-aca-lukas-foto-a-nalbantjan-5-830x553.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `performer_notifications`
--

DROP TABLE IF EXISTS `performer_notifications`;
CREATE TABLE IF NOT EXISTS `performer_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `performer_id` int NOT NULL,
  `event_id` int NOT NULL,
  `notified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `performer_id` (`performer_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performer_notifications`
--

INSERT INTO `performer_notifications` (`id`, `user_id`, `performer_id`, `event_id`, `notified`, `created_at`) VALUES
(1, 3, 11, 1, 1, '2025-01-22 18:59:29'),
(2, 3, 10, 1, 1, '2025-01-24 15:44:05'),
(3, 3, 7, 1, 1, '2025-02-05 00:01:22'),
(4, 3, 7, 1, 1, '2025-02-05 00:18:30'),
(5, 3, 7, 1, 1, '2025-02-07 00:05:38'),
(6, 3, 7, 1, 1, '2025-02-07 00:08:48'),
(7, 3, 7, 1, 1, '2025-02-22 00:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `pitanja`
--

DROP TABLE IF EXISTS `pitanja`;
CREATE TABLE IF NOT EXISTS `pitanja` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anketa_id` int NOT NULL,
  `pitanje` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `anketa_id` (`anketa_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pitanja`
--

INSERT INTO `pitanja` (`id`, `anketa_id`, `pitanje`) VALUES
(1, 1, 'Kako Vam se svidja uredjenost sajta?'),
(17, 7, 'Koju muziku najvise volite?'),
(19, 9, 'Za koji ste festival najvise zainteresovani?');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `reservation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `seat_count` int NOT NULL,
  `seat_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `event_id`, `status`, `reservation_date`, `seat_count`, `seat_type`, `event_name`) VALUES
(3, 3, 1, 'pending', '2025-02-17 23:58:37', 1, 'srednja', 'Narodno veče - Otvaranje festivala'),
(2, 3, 1, 'pending', '2025-02-17 23:47:56', 2, 'niza', 'Narodno veče - Otvaranje festivala');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('reserved','paid','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'reserved',
  `purchase_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `seat_count` int NOT NULL,
  `seat_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `user_id`, `event_id`, `price`, `status`, `purchase_date`, `seat_count`, `seat_type`, `event_name`) VALUES
(4, 3, 1, 10000.00, 'reserved', '2025-02-17 23:47:31', 2, 'VIP', 'Narodno veče - Otvaranje festivala'),
(5, 3, 10, 10000.00, 'reserved', '2025-02-17 23:50:22', 2, 'VIP', 'Pop Night'),
(6, 3, 10, 10000.00, 'reserved', '2025-02-17 23:51:19', 2, 'VIP', 'Pop Night');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('visitor','artist','organizer','admin') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'nensi', 'ndjokic928@gmail.com', '$2y$10$PLHL.gdfnSEYA/efE/AfcuPevCmDd8dY4ToFvrka4adWsEF5K/nJW', 'visitor', '2025-01-17 00:24:24'),
(2, 'djokic', 'nevenadjokic@outlook.com', '$2y$10$6as8D4NhhNwyruDPo3x8d.B/s29jORfjB/x63WnnSWutUncr7SkKK', 'visitor', '2025-01-17 00:28:29'),
(3, 'miki', 'ngjoo@gmail.com', '$2y$10$MAQZCoOInxx4n40h/zq7AuCfqbUkUQRhGTgpjXeqqMGBj8CUNV/Aa', 'visitor', '2025-01-17 01:11:20'),
(4, 'djuka', 'hahah@gmail.com', '$2y$10$fBxNeI4d0B1nPL/Oa47.E.9cqms8VrvLCS9wJ3dZL.gqEdZ65dHqi', 'visitor', '2025-01-17 19:49:23'),
(6, 'maki', 'maki@gmail.com', '$2y$10$IKikmCejL8dUGyF3MWaJQ.CbW5s9lLaMEf4mUg..WuTNBzCt/Gfwi', 'visitor', '2025-01-21 19:34:31'),
(7, 'maja', 'maja@gmail.com', '$2y$10$galFpRq3vhD4V.GTCQ6toeSNvMQuNeLwAulGsvCJQSSrqSRAgD3Pi', 'artist', '2025-01-24 19:17:05'),
(8, 'nena', 'nena@gmail.com', '$2y$10$jvPGWIE6zt/cFjbZwHS7FOXs7t356QY/TksFLD.3SBy.Vu0BzMjDy', 'admin', '2025-01-24 19:19:13'),
(9, 'jovan', 'jova@gmail.com', '$2y$10$Nbm4/106lqmVBAy0Y1EIxu4N7oh9E2tIpbrZwo0R5y48hJJQxgv/6', 'organizer', '2025-01-27 23:46:09'),
(10, 'Lepa Brena', 'lepabrena@gmail.com', '$2y$10$k0uPoTxIOy.Q5I0Q9kAaeuoYXwZXXQf8ALwsvOGBw8BjBmct50PaW', 'artist', '2025-01-28 23:07:58'),
(11, 'Hanka Paldum', 'hanka@gmail.com', '$2y$10$H.V7dcb7SkRiCGWcOH/sE.A5G46XCqMXTBtlL6LEyzsMrjTgY92Ra', 'artist', '2025-01-28 23:28:13'),
(12, 'Jelena Tomasevic', 'jeca@gmail.com', '$2y$10$TQQwqX73wFxM3cKK.Jxyj.ROsLmLL7MFTp0dm3/FTbe2wklxIoyJW', 'artist', '2025-01-29 00:00:48'),
(13, 'mica', 'mica@gmail.com', '$2y$10$AOgwlzfhyUiMohbpoH9LBuhPuct4.8BL0vodRDXUUL3QCU4W078a2', 'organizer', '2025-01-30 20:19:07'),
(14, 'milos', 'milos@gmail.com', '$2y$10$/RjSustp9SlnGIBk/BCvCOR/LLWlwcr/.iinKVn5n31GhC4LgOl6m', 'organizer', '2025-01-30 20:19:53'),
(15, 'Zdravko Colic', 'cola@gmail.com', '$2y$10$ltcBMLcy65AFCm8UKLyLv.TLWxDT3FPOuEso6XHjJqE/ef.8cZVbO', 'artist', '2025-02-02 17:35:29'),
(16, 'Vlado Georgiev', 'vlado@gmail.com', '$2y$10$yRQ24inxsdSpKXBcps3e7.pxQNXE.ocj99fH25PljQU/lnn3Ubiam', 'artist', '2025-02-02 17:36:53'),
(17, 'Bajaga', 'bajaga@gmail.com', '$2y$10$sDLK54ZiEHS27bREAsFLZObwewvF3zFdb3teQ3gryddWb5JwyB.qO', 'artist', '2025-02-02 17:37:44'),
(18, 'Galija', 'galija@gmail.com', '$2y$10$MFx5lSszmCnnd/Ss.MxRpuudNR7mKmsdfM0kZCah.4xMHScSOW/9q', 'artist', '2025-02-02 17:40:13'),
(19, 'admin', 'admin@gmail.com', '$2y$10$J0yhErqrDsWhCgFTngrCRuQgmctSm3IgJ4JiLDiID3QnyWWhrLzGK', 'admin', '2025-02-09 19:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_organizator`
--

DROP TABLE IF EXISTS `user_organizator`;
CREATE TABLE IF NOT EXISTS `user_organizator` (
  `user_id` int NOT NULL,
  `organizator_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`organizator_id`),
  KEY `organizator_id` (`organizator_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_organizator`
--

INSERT INTO `user_organizator` (`user_id`, `organizator_id`) VALUES
(9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_performer`
--

DROP TABLE IF EXISTS `user_performer`;
CREATE TABLE IF NOT EXISTS `user_performer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `performer_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_performer` (`user_id`,`performer_id`),
  KEY `performer_id` (`performer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_performer`
--

INSERT INTO `user_performer` (`id`, `user_id`, `performer_id`, `created_at`) VALUES
(1, 7, 12, '2025-01-25 20:53:26'),
(3, 10, 19, '2025-01-28 23:15:31'),
(5, 11, 20, '2025-01-28 23:34:27'),
(7, 12, 21, '2025-01-29 00:20:46'),
(8, 15, 25, '2025-02-02 18:19:49'),
(9, 16, 26, '2025-02-02 18:22:31'),
(10, 17, 27, '2025-02-02 18:26:03'),
(11, 18, 28, '2025-02-02 18:27:08');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
