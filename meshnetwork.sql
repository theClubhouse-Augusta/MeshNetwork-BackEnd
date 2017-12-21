-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2017 at 11:02 PM
-- Server version: 10.1.26-MariaDB
-- PHP Version: 7.1.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `meshnetwork`
--

-- --------------------------------------------------------

--
-- Table structure for table `appearances`
--

CREATE TABLE `appearances` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `spaceID` int(11) NOT NULL,
  `eventID` int(11) DEFAULT NULL,
  `occasion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookables`
--

CREATE TABLE `bookables` (
  `id` int(10) UNSIGNED NOT NULL,
  `spaceID` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `spaceID` int(11) NOT NULL,
  `bookablesID` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendars`
--

CREATE TABLE `calendars` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `calendars`
--

INSERT INTO `calendars` (`id`, `userID`, `eventID`, `created_at`, `updated_at`) VALUES
(23, 84, 40, '2017-12-21 06:08:44', '2017-12-21 06:08:44'),
(24, 82, 40, '2017-12-21 08:41:03', '2017-12-21 08:41:03');

-- --------------------------------------------------------

--
-- Table structure for table `eventdates`
--

CREATE TABLE `eventdates` (
  `id` int(10) UNSIGNED NOT NULL,
  `eventID` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `eventdates`
--

INSERT INTO `eventdates` (`id`, `eventID`, `start`, `end`, `created_at`, `updated_at`) VALUES
(12, 40, '2018-01-02 13:00:00', '2018-01-02 14:00:00', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `eventorganizers`
--

CREATE TABLE `eventorganizers` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `eventorganizers`
--

INSERT INTO `eventorganizers` (`id`, `userID`, `eventID`, `created_at`, `updated_at`) VALUES
(5, 82, 40, '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(6, 83, 40, '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(7, 84, 40, '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `spaceID` int(11) NOT NULL,
  `multiday` tinyint(1) NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `challenge` tinyint(1) NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `userID`, `spaceID`, `multiday`, `status`, `title`, `description`, `challenge`, `url`, `created_at`, `updated_at`) VALUES
(40, 84, 2, 0, 'pending', 'two', '<p>sdsdsdsd</p>', 1, 'http://two.com', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `eventskills`
--

CREATE TABLE `eventskills` (
  `id` int(10) UNSIGNED NOT NULL,
  `eventID` int(11) NOT NULL,
  `skillID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `eventskills`
--

INSERT INTO `eventskills` (`id`, `eventID`, `skillID`, `name`, `created_at`, `updated_at`) VALUES
(51, 40, 28, 'foo', '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(52, 40, 29, 'bar', '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(53, 40, 30, 'baz', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `path` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `userID`, `eventID`, `path`, `created_at`, `updated_at`) VALUES
(3, 84, 40, 'http://localhost:8000/storage/events/40/tumblr_n65lhaYx0T1s2wio8o1_1280.jpg', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `invites`
--

CREATE TABLE `invites` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `spaceID` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kiosks`
--

CREATE TABLE `kiosks` (
  `id` int(10) UNSIGNED NOT NULL,
  `spaceID` int(11) NOT NULL,
  `inputPlaceholder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci,
  `primaryColor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `secondaryColor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userWelcome` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userThanks` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `kiosks`
--

INSERT INTO `kiosks` (`id`, `spaceID`, `inputPlaceholder`, `logo`, `primaryColor`, `secondaryColor`, `userWelcome`, `userThanks`, `created_at`, `updated_at`) VALUES
(1, 1, 'Find yourself ☮', NULL, '#3399cc', '#f8991d', 'Hi', 'Here\'s what\'s happening @', '2017-12-22 01:01:22', '2017-12-22 01:01:22'),
(2, 2, 'Find yourself ☮', NULL, '#3399cc', '#f8991d', 'Hi', 'Here\'s what\'s happening @', '2017-12-22 01:03:04', '2017-12-22 01:03:04'),
(3, 3, 'Find yourself ☮', NULL, '#3399cc', '#f8991d', 'Hi', 'Here\'s what\'s happening @', '2017-12-22 01:04:15', '2017-12-22 01:07:15'),
(4, 4, 'Find yourself ☮', NULL, '#3399cc', '#f8991d', 'Hi', 'Here\'s what\'s happening @', '2017-12-22 01:05:03', '2017-12-22 01:06:40'),
(5, 5, 'Find yourself ☮', NULL, '#3399cc', '#f8991d', 'Hi', 'Here\'s what\'s happening @', '2017-12-22 01:09:30', '2017-12-22 01:09:30');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000001_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2014_11_12_000000_create_roles_table', 1),
(4, '2017_09_26_193901_workspaces', 1),
(5, '2017_09_26_194036_events', 1),
(6, '2017_09_26_194130_calendars', 1),
(7, '2017_09_26_194217_appearances', 1),
(8, '2017_09_26_194310_invites', 1),
(9, '2017_09_26_194407_bookables', 1),
(10, '2017_09_26_194600_bookings', 1),
(11, '2017_09_26_200959_skills', 1),
(12, '2017_09_26_201337_userskills', 1),
(13, '2017_09_26_205823_notifications', 1),
(14, '2017_09_26_210059_files', 1),
(15, '2017_09_26_210224_opts', 1),
(16, '2017_09_26_193902_workspaces', 2),
(17, '2017_09_26_193903_workspaces', 3),
(18, '2017_09_26_193904_workspaces', 4),
(19, '2017_11_27_153357_usertoevents', 5),
(20, '2017_12_06_161311_sponsers', 6),
(21, '2017_12_06_161312_sponsers', 7),
(22, '2017_12_07_015531_sponserevents', 8),
(23, '2017_12_07_015608_sponserspaces', 8),
(24, '2017_09_26_194037_events', 9),
(25, '2017_12_19_040840_eventdates', 9),
(26, '2017_12_19_052748_eventskills', 9),
(27, '2017_12_19_155642_eventorganizers', 10),
(28, '2017_12_19_155643_eventorganizers', 11),
(29, '2017_12_21_185809_kiosks', 12),
(30, '2017_12_21_185810_kiosks', 13),
(31, '2017_12_21_185811_kiosks', 14),
(32, '2017_12_21_185812_kiosks', 15);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `userID`, `body`, `read`, `created_at`, `updated_at`) VALUES
(1, 16, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(2, 1, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(3, 9, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(4, 20, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(5, 13, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(6, 4, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(7, 9, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(8, 18, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(9, 8, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(10, 14, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(11, 1, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(12, 4, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(13, 12, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(14, 19, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(15, 9, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(16, 13, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(17, 4, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(18, 14, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(19, 1, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17'),
(20, 13, 'this is body', 0, '2017-12-07 07:29:17', '2017-12-07 07:29:17');

-- --------------------------------------------------------

--
-- Table structure for table `opts`
--

CREATE TABLE `opts` (
  `id` int(10) UNSIGNED NOT NULL,
  `spaceID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `opts`
--

INSERT INTO `opts` (`id`, `spaceID`, `eventID`, `created_at`, `updated_at`) VALUES
(1, 2, 7, '2017-12-05 13:16:19', '2017-12-05 13:16:19'),
(2, 1, 7, '2017-12-05 13:16:48', '2017-12-05 13:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '2017-11-09 10:11:17', '2017-11-09 10:11:17'),
(2, 'organizer', '2017-11-09 10:11:41', '2017-11-09 10:11:41'),
(3, 'researcher', '2017-11-09 10:12:03', '2017-11-09 10:12:03'),
(4, 'member', '2017-11-09 10:12:20', '2017-11-09 10:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `created_at`, `updated_at`) VALUES
(25, 'html', '2017-12-21 05:43:10', '2017-12-21 05:43:10'),
(26, 'css', '2017-12-21 05:47:36', '2017-12-21 05:47:36'),
(27, 'foobar', '2017-12-21 05:49:16', '2017-12-21 05:49:16'),
(28, 'foo', '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(29, 'bar', '2017-12-21 06:05:12', '2017-12-21 06:05:12'),
(30, 'baz', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `sponserevents`
--

CREATE TABLE `sponserevents` (
  `id` int(10) UNSIGNED NOT NULL,
  `eventID` int(11) NOT NULL,
  `sponserID` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sponserevents`
--

INSERT INTO `sponserevents` (`id`, `eventID`, `sponserID`, `created_at`, `updated_at`) VALUES
(59, 40, 31, '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `sponsers`
--

CREATE TABLE `sponsers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sponsers`
--

INSERT INTO `sponsers` (`id`, `name`, `website`, `logo`, `created_at`, `updated_at`) VALUES
(31, 'two', 'http://two.com', 'http://localhost:8000/storage/logo/tumblr_nlw51sZkIW1qb6qi9o1_400.jpg', '2017-12-21 06:05:12', '2017-12-21 06:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `sponserspaces`
--

CREATE TABLE `sponserspaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `spaceID` int(11) NOT NULL,
  `sponserID` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `roleID` int(11) NOT NULL,
  `spaceID` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` char(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `searchOpt` tinyint(1) NOT NULL DEFAULT '0',
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phoneNumber` int(11) DEFAULT NULL,
  `bio` longtext COLLATE utf8_unicode_ci,
  `avatar` longtext COLLATE utf8_unicode_ci,
  `ban` tinyint(1) NOT NULL DEFAULT '0',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `subscriber` tinyint(1) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '100',
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `roleID`, `spaceID`, `email`, `password`, `title`, `name`, `searchOpt`, `company`, `website`, `phoneNumber`, `bio`, `avatar`, `ban`, `verified`, `subscriber`, `score`, `remember_token`, `created_at`, `updated_at`) VALUES
(82, 4, 2, 'mem@mail.com', '$2y$10$CMA93aHdp3qqE8COmEhnNOLm3oJtuyb/7RaAkcasyF68vp1IJMGpG', NULL, 'mem', 1, 'company', 'http://website.com', NULL, '\"<p>dhdjsdhjs</p>\\n\"', 'http://localhost:8000/storage/avatar/austin.jpg', 0, 0, 0, 100, NULL, '2017-12-21 05:43:10', '2017-12-21 05:43:10'),
(83, 4, 1, 'mem2@mail.com', '$2y$10$xBptgLp/O5G4sP6kK33TlOy6bU6dFCgnYSFjIePBJV3um78GvmKA.', NULL, 'mem2@mail.com', 1, 'company', 'http://google.com', NULL, '\"<p>dsdjksjdlksj</p>\\n\"', 'http://localhost:8000/storage/avatar/austin.jpg', 0, 0, 0, 100, NULL, '2017-12-21 05:47:36', '2017-12-21 05:47:36'),
(84, 4, 2, 'mem3@mail.com', '$2y$10$NmYx7kTofV1SYUA4eZ.nH.zSMJ0JtvIFHulMirjHS/kllMM0ODN.i', NULL, 'mem3', 1, 'company', 'http://google.com', NULL, '\"<p>dfdfdf</p>\\n\"', 'http://localhost:8000/storage/avatar/austin.jpg', 0, 0, 0, 100, NULL, '2017-12-21 05:49:16', '2017-12-21 05:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `userskills`
--

CREATE TABLE `userskills` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `skillID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `userskills`
--

INSERT INTO `userskills` (`id`, `userID`, `skillID`, `name`, `created_at`, `updated_at`) VALUES
(10, 82, 25, 'html', '2017-12-21 05:43:10', '2017-12-21 05:43:10'),
(11, 83, 25, 'html', '2017-12-21 05:47:36', '2017-12-21 05:47:36'),
(12, 83, 26, 'css', '2017-12-21 05:47:36', '2017-12-21 05:47:36'),
(13, 84, 26, 'css', '2017-12-21 05:49:16', '2017-12-21 05:49:16'),
(14, 84, 27, 'foobar', '2017-12-21 05:49:16', '2017-12-21 05:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `usertoevents`
--

CREATE TABLE `usertoevents` (
  `id` int(10) UNSIGNED NOT NULL,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workspaces`
--

CREATE TABLE `workspaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `userID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` int(11) NOT NULL,
  `lon` double(8,2) NOT NULL,
  `lat` double(8,2) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` int(11) NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `workspaces`
--

INSERT INTO `workspaces` (`id`, `userID`, `name`, `city`, `address`, `state`, `zipcode`, `lon`, `lat`, `email`, `website`, `phone_number`, `description`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'the clubhou.se', 'Augusta', '540 Telfair St', 'GA', 30904, -81.96, 33.47, 'one@mail.com', 'theclubhou.se', 1231231234, 'theClubhou.se inspires ideas, creates companies, and builds community. Founded in 2012, theClubhou.se is a division of Hack Augusta, inc., non-profit 501(c)3 dedicated to growing a culture of innovation and collaboration. We serve over 10,000 people in the Greater Augusta MSA throughout our events and programs. We have 80 members and have helped 60 entrepreneurs grow 32 companies that create 90 jobs and a $7,000,000 annual economic impact in our community.', 'http://127.0.0.1:8000/storage/logo/theclubhouselogo-1.png', 'pending', '2017-11-20 23:44:49', '2017-11-20 23:44:49'),
(2, 17, 'Four Athens', 'Athens', '345 W Hancock Ave', 'GA', 30904, -83.38, 33.96, 'one@mail.com', 'https://www.fourathens.com/', 1231231234, 'For all your startup needs, Four Athens is here to help you discover ideas, launch concepts, and build your customer base. We connect you to the people you need to hire, learn from, and raise money in order to see your business dreams become reality.', 'http://127.0.0.1:8000/storage/logo/theclubhouselogo-1.png', 'pending', '2017-11-20 23:48:43', '2017-11-20 23:48:43'),
(3, 31, 'ColumbusMakesIT', 'Columbus', '710-B Front Ave', 'GA', 30904, -84.99, 32.46, 'one@mail.com', 'http://columbusmakesit.com/', 1231231234, 'ColumbusMakesIT! (CMI) is a Creativity and Entrepreneurship center that provides the education, tools, technology and mentoring that support activities to encourage and inspire people to Learn IT, Make IT, Live IT and Love IT!\nColumbus Makes IT is a non-Profit Educational Outreach program.\nColumbusMakesIT is a donor and member supported non-profit educational outreach organization that accepts donations of funding and materials to help people learn how to make and create, build skills, and grow the regional economy.   People like you make this possible and are also afforded the tax benefits of donating to an IRS 501(c)(3) organization.\nWe are located across the street from the Coca-Cola Space Science Center in the heart of Columbus GA.   Stay updated on our progress as we build the new location and create the innovation experience Columbus, GA has been waiting for.', 'http://127.0.0.1:8000/storage/logo/theclubhouselogo-1.png', 'pending', '2017-11-20 23:52:34', '2017-11-20 23:52:34'),
(4, 42, 'makervillage', 'Rome', '252 N 5th Avenue', 'GA', 30904, -85.17, 34.26, 'one@mail.com', 'https://www.makervillage.org/', 1231231234, 'Our mission is to support the growth of creative and tech-driven entrepreneurs and artisans.\nWe believe that democratized technology and do-it-yourself culture of the Maker Movement disrupts every industry and creates countless opportunities for people of all kinds to grow sustainable businesses. Our community exists to nurture startups and innovators with access to resources, including physical space, equipment, infrastructure, democratized financing tools, as well as educational and inspiring events.\nOur members may include creative firms or consultants, artisan retailers, or technology businesses — our commonality is in our approach and beliefs. We are challenged and inspired by each other, and when possible, we look for ways to collaborate with one another, recognizing the increased value we can provide to our customers as an unstructured team.', 'http://127.0.0.1:8000/storage/logo/theclubhouselogo-1.png', 'pending', '2017-11-20 23:55:05', '2017-11-20 23:55:05'),
(5, 51, 'SparkMacon', 'Macon', '557 Cherry Street', 'GA', 30904, -83.63, 32.84, 'one@mail.com', 'https://www.sparkmacon.com/', 1231231234, 'Our mission is to support the growth of creative and tech-driven entrepreneurs and artisans.\nWe believe that democratized technology and do-it-yourself culture of the Maker Movement disrupts every industry and creates countless opportunities for people of all kinds to grow sustainable businesses. Our community exists to nurture startups and innovators with access to resources, including physical space, equipment, infrastructure, democratized financing tools, as well as educational and inspiring events.\nOur members may include creative firms or consultants, artisan retailers, or technology businesses — our commonality is in our approach and beliefs. We are challenged and inspired by each other, and when possible, we look for ways to collaborate with one another, recognizing the increased value we can provide to our customers as an unstructured team.', 'http://127.0.0.1:8000/storage/logo/theclubhouselogo-1.png', 'pending', '2017-11-20 23:56:24', '2017-11-20 23:56:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appearances`
--
ALTER TABLE `appearances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookables`
--
ALTER TABLE `bookables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eventdates`
--
ALTER TABLE `eventdates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eventorganizers`
--
ALTER TABLE `eventorganizers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eventskills`
--
ALTER TABLE `eventskills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invites`
--
ALTER TABLE `invites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kiosks`
--
ALTER TABLE `kiosks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opts`
--
ALTER TABLE `opts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponserevents`
--
ALTER TABLE `sponserevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponsers`
--
ALTER TABLE `sponsers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponserspaces`
--
ALTER TABLE `sponserspaces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `userskills`
--
ALTER TABLE `userskills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usertoevents`
--
ALTER TABLE `usertoevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workspaces`
--
ALTER TABLE `workspaces`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appearances`
--
ALTER TABLE `appearances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bookables`
--
ALTER TABLE `bookables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `calendars`
--
ALTER TABLE `calendars`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `eventdates`
--
ALTER TABLE `eventdates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `eventorganizers`
--
ALTER TABLE `eventorganizers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
--
-- AUTO_INCREMENT for table `eventskills`
--
ALTER TABLE `eventskills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `invites`
--
ALTER TABLE `invites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kiosks`
--
ALTER TABLE `kiosks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `opts`
--
ALTER TABLE `opts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `sponserevents`
--
ALTER TABLE `sponserevents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
--
-- AUTO_INCREMENT for table `sponsers`
--
ALTER TABLE `sponsers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
--
-- AUTO_INCREMENT for table `sponserspaces`
--
ALTER TABLE `sponserspaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
--
-- AUTO_INCREMENT for table `userskills`
--
ALTER TABLE `userskills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `usertoevents`
--
ALTER TABLE `usertoevents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `workspaces`
--
ALTER TABLE `workspaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
