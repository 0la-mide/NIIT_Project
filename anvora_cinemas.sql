-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 11:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anvora_cinemas`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password_hash`, `full_name`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin@anvoracinemas.com', '$2y$10$V2zg5VzwYxJmXCTdeeRneujRsI0rJFwoUIHtllu41x12u2T8FrOIy', 'Admin User', '2025-04-18 17:19:57', '2025-08-21 00:46:55');

-- --------------------------------------------------------

--
-- Table structure for table `auditoriums`
--

CREATE TABLE `auditoriums` (
  `auditorium_id` int(11) NOT NULL,
  `cinema_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `screen_type` enum('Standard','IMAX','4DX','VIP') DEFAULT 'Standard',
  `seat_map` text DEFAULT NULL COMMENT 'JSON representation of seat layout'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditoriums`
--

INSERT INTO `auditoriums` (`auditorium_id`, `cinema_id`, `name`, `capacity`, `screen_type`, `seat_map`) VALUES
(3, 4, 'Premiere', 72, 'VIP', '{\"rows\":6,\"cols\":12,\"map\":{\"A1\":{\"type\":\"regular\",\"status\":\"available\"},\"A2\":{\"type\":\"regular\",\"status\":\"available\"},\"A3\":{\"type\":\"regular\",\"status\":\"available\"},\"A4\":{\"type\":\"regular\",\"status\":\"available\"},\"A5\":{\"type\":\"regular\",\"status\":\"available\"},\"A6\":{\"type\":\"regular\",\"status\":\"available\"},\"A7\":{\"type\":\"regular\",\"status\":\"available\"},\"A8\":{\"type\":\"regular\",\"status\":\"available\"},\"A9\":{\"type\":\"regular\",\"status\":\"available\"},\"A10\":{\"type\":\"regular\",\"status\":\"available\"},\"A11\":{\"type\":\"regular\",\"status\":\"available\"},\"A12\":{\"type\":\"regular\",\"status\":\"available\"},\"B1\":{\"type\":\"regular\",\"status\":\"available\"},\"B2\":{\"type\":\"regular\",\"status\":\"available\"},\"B3\":{\"type\":\"regular\",\"status\":\"available\"},\"B4\":{\"type\":\"regular\",\"status\":\"available\"},\"B5\":{\"type\":\"regular\",\"status\":\"available\"},\"B6\":{\"type\":\"regular\",\"status\":\"available\"},\"B7\":{\"type\":\"regular\",\"status\":\"available\"},\"B8\":{\"type\":\"regular\",\"status\":\"available\"},\"B9\":{\"type\":\"regular\",\"status\":\"available\"},\"B10\":{\"type\":\"regular\",\"status\":\"available\"},\"B11\":{\"type\":\"regular\",\"status\":\"available\"},\"B12\":{\"type\":\"regular\",\"status\":\"available\"},\"C1\":{\"type\":\"regular\",\"status\":\"available\"},\"C2\":{\"type\":\"regular\",\"status\":\"available\"},\"C3\":{\"type\":\"regular\",\"status\":\"available\"},\"C4\":{\"type\":\"regular\",\"status\":\"available\"},\"C5\":{\"type\":\"regular\",\"status\":\"available\"},\"C6\":{\"type\":\"regular\",\"status\":\"available\"},\"C7\":{\"type\":\"regular\",\"status\":\"available\"},\"C8\":{\"type\":\"regular\",\"status\":\"available\"},\"C9\":{\"type\":\"regular\",\"status\":\"available\"},\"C10\":{\"type\":\"regular\",\"status\":\"available\"},\"C11\":{\"type\":\"regular\",\"status\":\"available\"},\"C12\":{\"type\":\"regular\",\"status\":\"available\"},\"D1\":{\"type\":\"regular\",\"status\":\"available\"},\"D2\":{\"type\":\"regular\",\"status\":\"available\"},\"D3\":{\"type\":\"regular\",\"status\":\"available\"},\"D4\":{\"type\":\"regular\",\"status\":\"available\"},\"D5\":{\"type\":\"regular\",\"status\":\"available\"},\"D6\":{\"type\":\"regular\",\"status\":\"available\"},\"D7\":{\"type\":\"regular\",\"status\":\"available\"},\"D8\":{\"type\":\"regular\",\"status\":\"available\"},\"D9\":{\"type\":\"regular\",\"status\":\"available\"},\"D10\":{\"type\":\"regular\",\"status\":\"available\"},\"D11\":{\"type\":\"regular\",\"status\":\"available\"},\"D12\":{\"type\":\"regular\",\"status\":\"available\"},\"E1\":{\"type\":\"regular\",\"status\":\"available\"},\"E2\":{\"type\":\"regular\",\"status\":\"available\"},\"E3\":{\"type\":\"regular\",\"status\":\"available\"},\"E4\":{\"type\":\"regular\",\"status\":\"available\"},\"E5\":{\"type\":\"regular\",\"status\":\"available\"},\"E6\":{\"type\":\"regular\",\"status\":\"available\"},\"E7\":{\"type\":\"regular\",\"status\":\"available\"},\"E8\":{\"type\":\"regular\",\"status\":\"available\"},\"E9\":{\"type\":\"regular\",\"status\":\"available\"},\"E10\":{\"type\":\"regular\",\"status\":\"available\"},\"E11\":{\"type\":\"regular\",\"status\":\"available\"},\"E12\":{\"type\":\"regular\",\"status\":\"available\"},\"F1\":{\"type\":\"regular\",\"status\":\"available\"},\"F2\":{\"type\":\"regular\",\"status\":\"available\"},\"F3\":{\"type\":\"regular\",\"status\":\"available\"},\"F4\":{\"type\":\"regular\",\"status\":\"available\"},\"F5\":{\"type\":\"regular\",\"status\":\"available\"},\"F6\":{\"type\":\"regular\",\"status\":\"available\"},\"F7\":{\"type\":\"regular\",\"status\":\"available\"},\"F8\":{\"type\":\"regular\",\"status\":\"available\"},\"F9\":{\"type\":\"regular\",\"status\":\"available\"},\"F10\":{\"type\":\"regular\",\"status\":\"available\"},\"F11\":{\"type\":\"regular\",\"status\":\"available\"},\"F12\":{\"type\":\"regular\",\"status\":\"available\"}}}'),
(4, 2, '4DX Screen', 100, '4DX', '{\"rows\":8,\"cols\":12,\"accessible\":[{\"row\":1,\"seats\":[1,2]}]}'),
(5, 3, '3D Screen', 200, 'Standard', '{\"rows\":10,\"cols\":20,\"map\":{\"A1\":{\"type\":\"regular\",\"status\":\"available\"},\"A2\":{\"type\":\"regular\",\"status\":\"available\"},\"A3\":{\"type\":\"regular\",\"status\":\"available\"},\"A4\":{\"type\":\"regular\",\"status\":\"available\"},\"A5\":{\"type\":\"regular\",\"status\":\"available\"},\"A6\":{\"type\":\"regular\",\"status\":\"available\"},\"A7\":{\"type\":\"regular\",\"status\":\"available\"},\"A8\":{\"type\":\"regular\",\"status\":\"available\"},\"A9\":{\"type\":\"regular\",\"status\":\"available\"},\"A10\":{\"type\":\"regular\",\"status\":\"available\"},\"A11\":{\"type\":\"regular\",\"status\":\"available\"},\"A12\":{\"type\":\"regular\",\"status\":\"available\"},\"A13\":{\"type\":\"regular\",\"status\":\"available\"},\"A14\":{\"type\":\"regular\",\"status\":\"available\"},\"A15\":{\"type\":\"regular\",\"status\":\"available\"},\"A16\":{\"type\":\"regular\",\"status\":\"available\"},\"A17\":{\"type\":\"regular\",\"status\":\"available\"},\"A18\":{\"type\":\"regular\",\"status\":\"available\"},\"A19\":{\"type\":\"regular\",\"status\":\"available\"},\"A20\":{\"type\":\"regular\",\"status\":\"available\"},\"B1\":{\"type\":\"regular\",\"status\":\"available\"},\"B2\":{\"type\":\"regular\",\"status\":\"available\"},\"B3\":{\"type\":\"regular\",\"status\":\"available\"},\"B4\":{\"type\":\"regular\",\"status\":\"available\"},\"B5\":{\"type\":\"regular\",\"status\":\"available\"},\"B6\":{\"type\":\"regular\",\"status\":\"available\"},\"B7\":{\"type\":\"regular\",\"status\":\"available\"},\"B8\":{\"type\":\"regular\",\"status\":\"available\"},\"B9\":{\"type\":\"regular\",\"status\":\"available\"},\"B10\":{\"type\":\"regular\",\"status\":\"available\"},\"B11\":{\"type\":\"regular\",\"status\":\"available\"},\"B12\":{\"type\":\"regular\",\"status\":\"available\"},\"B13\":{\"type\":\"regular\",\"status\":\"available\"},\"B14\":{\"type\":\"regular\",\"status\":\"available\"},\"B15\":{\"type\":\"regular\",\"status\":\"available\"},\"B16\":{\"type\":\"regular\",\"status\":\"available\"},\"B17\":{\"type\":\"regular\",\"status\":\"available\"},\"B18\":{\"type\":\"regular\",\"status\":\"available\"},\"B19\":{\"type\":\"regular\",\"status\":\"available\"},\"B20\":{\"type\":\"regular\",\"status\":\"available\"},\"C1\":{\"type\":\"regular\",\"status\":\"available\"},\"C2\":{\"type\":\"regular\",\"status\":\"available\"},\"C3\":{\"type\":\"regular\",\"status\":\"available\"},\"C4\":{\"type\":\"regular\",\"status\":\"available\"},\"C5\":{\"type\":\"regular\",\"status\":\"available\"},\"C6\":{\"type\":\"regular\",\"status\":\"available\"},\"C7\":{\"type\":\"regular\",\"status\":\"available\"},\"C8\":{\"type\":\"regular\",\"status\":\"available\"},\"C9\":{\"type\":\"regular\",\"status\":\"available\"},\"C10\":{\"type\":\"regular\",\"status\":\"available\"},\"C11\":{\"type\":\"regular\",\"status\":\"available\"},\"C12\":{\"type\":\"regular\",\"status\":\"available\"},\"C13\":{\"type\":\"regular\",\"status\":\"available\"},\"C14\":{\"type\":\"regular\",\"status\":\"available\"},\"C15\":{\"type\":\"regular\",\"status\":\"available\"},\"C16\":{\"type\":\"regular\",\"status\":\"available\"},\"C17\":{\"type\":\"regular\",\"status\":\"available\"},\"C18\":{\"type\":\"regular\",\"status\":\"available\"},\"C19\":{\"type\":\"regular\",\"status\":\"available\"},\"C20\":{\"type\":\"regular\",\"status\":\"available\"},\"D1\":{\"type\":\"regular\",\"status\":\"available\"},\"D2\":{\"type\":\"regular\",\"status\":\"available\"},\"D3\":{\"type\":\"regular\",\"status\":\"available\"},\"D4\":{\"type\":\"regular\",\"status\":\"available\"},\"D5\":{\"type\":\"regular\",\"status\":\"available\"},\"D6\":{\"type\":\"regular\",\"status\":\"available\"},\"D7\":{\"type\":\"regular\",\"status\":\"available\"},\"D8\":{\"type\":\"regular\",\"status\":\"available\"},\"D9\":{\"type\":\"regular\",\"status\":\"available\"},\"D10\":{\"type\":\"regular\",\"status\":\"available\"},\"D11\":{\"type\":\"regular\",\"status\":\"available\"},\"D12\":{\"type\":\"regular\",\"status\":\"available\"},\"D13\":{\"type\":\"regular\",\"status\":\"available\"},\"D14\":{\"type\":\"regular\",\"status\":\"available\"},\"D15\":{\"type\":\"regular\",\"status\":\"available\"},\"D16\":{\"type\":\"regular\",\"status\":\"available\"},\"D17\":{\"type\":\"regular\",\"status\":\"available\"},\"D18\":{\"type\":\"regular\",\"status\":\"available\"},\"D19\":{\"type\":\"regular\",\"status\":\"available\"},\"D20\":{\"type\":\"regular\",\"status\":\"available\"},\"E1\":{\"type\":\"regular\",\"status\":\"available\"},\"E2\":{\"type\":\"regular\",\"status\":\"available\"},\"E3\":{\"type\":\"regular\",\"status\":\"available\"},\"E4\":{\"type\":\"regular\",\"status\":\"available\"},\"E5\":{\"type\":\"regular\",\"status\":\"available\"},\"E6\":{\"type\":\"regular\",\"status\":\"available\"},\"E7\":{\"type\":\"regular\",\"status\":\"available\"},\"E8\":{\"type\":\"regular\",\"status\":\"available\"},\"E9\":{\"type\":\"regular\",\"status\":\"available\"},\"E10\":{\"type\":\"regular\",\"status\":\"available\"},\"E11\":{\"type\":\"regular\",\"status\":\"available\"},\"E12\":{\"type\":\"regular\",\"status\":\"available\"},\"E13\":{\"type\":\"regular\",\"status\":\"available\"},\"E14\":{\"type\":\"regular\",\"status\":\"available\"},\"E15\":{\"type\":\"regular\",\"status\":\"available\"},\"E16\":{\"type\":\"regular\",\"status\":\"available\"},\"E17\":{\"type\":\"regular\",\"status\":\"available\"},\"E18\":{\"type\":\"regular\",\"status\":\"available\"},\"E19\":{\"type\":\"regular\",\"status\":\"available\"},\"E20\":{\"type\":\"regular\",\"status\":\"available\"},\"F1\":{\"type\":\"regular\",\"status\":\"available\"},\"F2\":{\"type\":\"regular\",\"status\":\"available\"},\"F3\":{\"type\":\"regular\",\"status\":\"available\"},\"F4\":{\"type\":\"regular\",\"status\":\"available\"},\"F5\":{\"type\":\"regular\",\"status\":\"available\"},\"F6\":{\"type\":\"regular\",\"status\":\"available\"},\"F7\":{\"type\":\"regular\",\"status\":\"available\"},\"F8\":{\"type\":\"regular\",\"status\":\"available\"},\"F9\":{\"type\":\"regular\",\"status\":\"available\"},\"F10\":{\"type\":\"regular\",\"status\":\"available\"},\"F11\":{\"type\":\"regular\",\"status\":\"available\"},\"F12\":{\"type\":\"regular\",\"status\":\"available\"},\"F13\":{\"type\":\"regular\",\"status\":\"available\"},\"F14\":{\"type\":\"regular\",\"status\":\"available\"},\"F15\":{\"type\":\"regular\",\"status\":\"available\"},\"F16\":{\"type\":\"regular\",\"status\":\"available\"},\"F17\":{\"type\":\"regular\",\"status\":\"available\"},\"F18\":{\"type\":\"regular\",\"status\":\"available\"},\"F19\":{\"type\":\"regular\",\"status\":\"available\"},\"F20\":{\"type\":\"regular\",\"status\":\"available\"},\"G1\":{\"type\":\"regular\",\"status\":\"available\"},\"G2\":{\"type\":\"regular\",\"status\":\"available\"},\"G3\":{\"type\":\"regular\",\"status\":\"available\"},\"G4\":{\"type\":\"regular\",\"status\":\"available\"},\"G5\":{\"type\":\"regular\",\"status\":\"available\"},\"G6\":{\"type\":\"regular\",\"status\":\"available\"},\"G7\":{\"type\":\"regular\",\"status\":\"available\"},\"G8\":{\"type\":\"regular\",\"status\":\"available\"},\"G9\":{\"type\":\"regular\",\"status\":\"available\"},\"G10\":{\"type\":\"regular\",\"status\":\"available\"},\"G11\":{\"type\":\"regular\",\"status\":\"available\"},\"G12\":{\"type\":\"regular\",\"status\":\"available\"},\"G13\":{\"type\":\"regular\",\"status\":\"available\"},\"G14\":{\"type\":\"regular\",\"status\":\"available\"},\"G15\":{\"type\":\"regular\",\"status\":\"available\"},\"G16\":{\"type\":\"regular\",\"status\":\"available\"},\"G17\":{\"type\":\"regular\",\"status\":\"available\"},\"G18\":{\"type\":\"regular\",\"status\":\"available\"},\"G19\":{\"type\":\"regular\",\"status\":\"available\"},\"G20\":{\"type\":\"regular\",\"status\":\"available\"},\"H1\":{\"type\":\"regular\",\"status\":\"available\"},\"H2\":{\"type\":\"regular\",\"status\":\"available\"},\"H3\":{\"type\":\"regular\",\"status\":\"available\"},\"H4\":{\"type\":\"regular\",\"status\":\"available\"},\"H5\":{\"type\":\"regular\",\"status\":\"available\"},\"H6\":{\"type\":\"regular\",\"status\":\"available\"},\"H7\":{\"type\":\"regular\",\"status\":\"available\"},\"H8\":{\"type\":\"regular\",\"status\":\"available\"},\"H9\":{\"type\":\"regular\",\"status\":\"available\"},\"H10\":{\"type\":\"regular\",\"status\":\"available\"},\"H11\":{\"type\":\"regular\",\"status\":\"available\"},\"H12\":{\"type\":\"regular\",\"status\":\"available\"},\"H13\":{\"type\":\"regular\",\"status\":\"available\"},\"H14\":{\"type\":\"regular\",\"status\":\"available\"},\"H15\":{\"type\":\"regular\",\"status\":\"available\"},\"H16\":{\"type\":\"regular\",\"status\":\"available\"},\"H17\":{\"type\":\"regular\",\"status\":\"available\"},\"H18\":{\"type\":\"regular\",\"status\":\"available\"},\"H19\":{\"type\":\"regular\",\"status\":\"available\"},\"H20\":{\"type\":\"regular\",\"status\":\"available\"},\"I1\":{\"type\":\"regular\",\"status\":\"available\"},\"I2\":{\"type\":\"regular\",\"status\":\"available\"},\"I3\":{\"type\":\"regular\",\"status\":\"available\"},\"I4\":{\"type\":\"regular\",\"status\":\"available\"},\"I5\":{\"type\":\"regular\",\"status\":\"available\"},\"I6\":{\"type\":\"regular\",\"status\":\"available\"},\"I7\":{\"type\":\"regular\",\"status\":\"available\"},\"I8\":{\"type\":\"regular\",\"status\":\"available\"},\"I9\":{\"type\":\"regular\",\"status\":\"available\"},\"I10\":{\"type\":\"regular\",\"status\":\"available\"},\"I11\":{\"type\":\"regular\",\"status\":\"available\"},\"I12\":{\"type\":\"regular\",\"status\":\"available\"},\"I13\":{\"type\":\"regular\",\"status\":\"available\"},\"I14\":{\"type\":\"regular\",\"status\":\"available\"},\"I15\":{\"type\":\"regular\",\"status\":\"available\"},\"I16\":{\"type\":\"regular\",\"status\":\"available\"},\"I17\":{\"type\":\"regular\",\"status\":\"available\"},\"I18\":{\"type\":\"regular\",\"status\":\"available\"},\"I19\":{\"type\":\"regular\",\"status\":\"available\"},\"I20\":{\"type\":\"regular\",\"status\":\"available\"},\"J1\":{\"type\":\"regular\",\"status\":\"available\"},\"J2\":{\"type\":\"regular\",\"status\":\"available\"},\"J3\":{\"type\":\"regular\",\"status\":\"available\"},\"J4\":{\"type\":\"regular\",\"status\":\"available\"},\"J5\":{\"type\":\"regular\",\"status\":\"available\"},\"J6\":{\"type\":\"regular\",\"status\":\"available\"},\"J7\":{\"type\":\"regular\",\"status\":\"available\"},\"J8\":{\"type\":\"regular\",\"status\":\"available\"},\"J9\":{\"type\":\"regular\",\"status\":\"available\"},\"J10\":{\"type\":\"regular\",\"status\":\"available\"},\"J11\":{\"type\":\"regular\",\"status\":\"available\"},\"J12\":{\"type\":\"regular\",\"status\":\"available\"},\"J13\":{\"type\":\"regular\",\"status\":\"available\"},\"J14\":{\"type\":\"regular\",\"status\":\"available\"},\"J15\":{\"type\":\"regular\",\"status\":\"available\"},\"J16\":{\"type\":\"regular\",\"status\":\"available\"},\"J17\":{\"type\":\"regular\",\"status\":\"available\"},\"J18\":{\"type\":\"regular\",\"status\":\"available\"},\"J19\":{\"type\":\"regular\",\"status\":\"available\"},\"J20\":{\"type\":\"regular\",\"status\":\"available\"}}}'),
(13, 1, 'IMAX Screen', 100, 'Standard', '{\"rows\":10,\"cols\":10,\"map\":{\"A1\":{\"type\":\"regular\",\"status\":\"available\"},\"A2\":{\"type\":\"regular\",\"status\":\"available\"},\"A3\":{\"type\":\"regular\",\"status\":\"available\"},\"A4\":{\"type\":\"regular\",\"status\":\"available\"},\"A5\":{\"type\":\"regular\",\"status\":\"available\"},\"A6\":{\"type\":\"regular\",\"status\":\"available\"},\"A7\":{\"type\":\"regular\",\"status\":\"available\"},\"A8\":{\"type\":\"regular\",\"status\":\"available\"},\"A9\":{\"type\":\"regular\",\"status\":\"available\"},\"A10\":{\"type\":\"regular\",\"status\":\"available\"},\"B1\":{\"type\":\"regular\",\"status\":\"available\"},\"B2\":{\"type\":\"regular\",\"status\":\"available\"},\"B3\":{\"type\":\"regular\",\"status\":\"available\"},\"B4\":{\"type\":\"regular\",\"status\":\"available\"},\"B5\":{\"type\":\"regular\",\"status\":\"available\"},\"B6\":{\"type\":\"regular\",\"status\":\"available\"},\"B7\":{\"type\":\"regular\",\"status\":\"available\"},\"B8\":{\"type\":\"regular\",\"status\":\"available\"},\"B9\":{\"type\":\"regular\",\"status\":\"available\"},\"B10\":{\"type\":\"regular\",\"status\":\"available\"},\"C1\":{\"type\":\"regular\",\"status\":\"available\"},\"C2\":{\"type\":\"regular\",\"status\":\"available\"},\"C3\":{\"type\":\"regular\",\"status\":\"available\"},\"C4\":{\"type\":\"regular\",\"status\":\"available\"},\"C5\":{\"type\":\"regular\",\"status\":\"available\"},\"C6\":{\"type\":\"regular\",\"status\":\"available\"},\"C7\":{\"type\":\"regular\",\"status\":\"available\"},\"C8\":{\"type\":\"regular\",\"status\":\"available\"},\"C9\":{\"type\":\"regular\",\"status\":\"available\"},\"C10\":{\"type\":\"regular\",\"status\":\"available\"},\"D1\":{\"type\":\"regular\",\"status\":\"available\"},\"D2\":{\"type\":\"regular\",\"status\":\"available\"},\"D3\":{\"type\":\"regular\",\"status\":\"available\"},\"D4\":{\"type\":\"regular\",\"status\":\"available\"},\"D5\":{\"type\":\"regular\",\"status\":\"available\"},\"D6\":{\"type\":\"regular\",\"status\":\"available\"},\"D7\":{\"type\":\"regular\",\"status\":\"available\"},\"D8\":{\"type\":\"regular\",\"status\":\"available\"},\"D9\":{\"type\":\"regular\",\"status\":\"available\"},\"D10\":{\"type\":\"regular\",\"status\":\"available\"},\"E1\":{\"type\":\"regular\",\"status\":\"available\"},\"E2\":{\"type\":\"regular\",\"status\":\"available\"},\"E3\":{\"type\":\"regular\",\"status\":\"available\"},\"E4\":{\"type\":\"regular\",\"status\":\"available\"},\"E5\":{\"type\":\"regular\",\"status\":\"available\"},\"E6\":{\"type\":\"regular\",\"status\":\"available\"},\"E7\":{\"type\":\"regular\",\"status\":\"available\"},\"E8\":{\"type\":\"regular\",\"status\":\"available\"},\"E9\":{\"type\":\"regular\",\"status\":\"available\"},\"E10\":{\"type\":\"regular\",\"status\":\"available\"},\"F1\":{\"type\":\"regular\",\"status\":\"available\"},\"F2\":{\"type\":\"regular\",\"status\":\"available\"},\"F3\":{\"type\":\"regular\",\"status\":\"available\"},\"F4\":{\"type\":\"regular\",\"status\":\"available\"},\"F5\":{\"type\":\"regular\",\"status\":\"available\"},\"F6\":{\"type\":\"regular\",\"status\":\"available\"},\"F7\":{\"type\":\"regular\",\"status\":\"available\"},\"F8\":{\"type\":\"regular\",\"status\":\"available\"},\"F9\":{\"type\":\"regular\",\"status\":\"available\"},\"F10\":{\"type\":\"regular\",\"status\":\"available\"},\"G1\":{\"type\":\"regular\",\"status\":\"available\"},\"G2\":{\"type\":\"regular\",\"status\":\"available\"},\"G3\":{\"type\":\"regular\",\"status\":\"available\"},\"G4\":{\"type\":\"regular\",\"status\":\"available\"},\"G5\":{\"type\":\"regular\",\"status\":\"available\"},\"G6\":{\"type\":\"regular\",\"status\":\"available\"},\"G7\":{\"type\":\"regular\",\"status\":\"available\"},\"G8\":{\"type\":\"regular\",\"status\":\"available\"},\"G9\":{\"type\":\"regular\",\"status\":\"available\"},\"G10\":{\"type\":\"regular\",\"status\":\"available\"},\"H1\":{\"type\":\"regular\",\"status\":\"available\"},\"H2\":{\"type\":\"regular\",\"status\":\"available\"},\"H3\":{\"type\":\"regular\",\"status\":\"available\"},\"H4\":{\"type\":\"regular\",\"status\":\"available\"},\"H5\":{\"type\":\"regular\",\"status\":\"available\"},\"H6\":{\"type\":\"regular\",\"status\":\"available\"},\"H7\":{\"type\":\"regular\",\"status\":\"available\"},\"H8\":{\"type\":\"regular\",\"status\":\"available\"},\"H9\":{\"type\":\"regular\",\"status\":\"available\"},\"H10\":{\"type\":\"regular\",\"status\":\"available\"},\"I1\":{\"type\":\"regular\",\"status\":\"available\"},\"I2\":{\"type\":\"regular\",\"status\":\"available\"},\"I3\":{\"type\":\"regular\",\"status\":\"available\"},\"I4\":{\"type\":\"regular\",\"status\":\"available\"},\"I5\":{\"type\":\"regular\",\"status\":\"available\"},\"I6\":{\"type\":\"regular\",\"status\":\"available\"},\"I7\":{\"type\":\"regular\",\"status\":\"available\"},\"I8\":{\"type\":\"regular\",\"status\":\"available\"},\"I9\":{\"type\":\"regular\",\"status\":\"available\"},\"I10\":{\"type\":\"regular\",\"status\":\"available\"},\"J1\":{\"type\":\"regular\",\"status\":\"available\"},\"J2\":{\"type\":\"regular\",\"status\":\"available\"},\"J3\":{\"type\":\"regular\",\"status\":\"available\"},\"J4\":{\"type\":\"regular\",\"status\":\"available\"},\"J5\":{\"type\":\"regular\",\"status\":\"available\"},\"J6\":{\"type\":\"regular\",\"status\":\"available\"},\"J7\":{\"type\":\"regular\",\"status\":\"available\"},\"J8\":{\"type\":\"regular\",\"status\":\"available\"},\"J9\":{\"type\":\"regular\",\"status\":\"available\"},\"J10\":{\"type\":\"regular\",\"status\":\"available\"}}}');

-- --------------------------------------------------------

--
-- Table structure for table `booked_seats`
--

CREATE TABLE `booked_seats` (
  `booking_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `seat_type` enum('standard','premium','accessible') DEFAULT 'standard',
  `price` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for guest bookings',
  `guest_email` varchar(100) DEFAULT NULL COMMENT 'For guest bookings',
  `guest_name` varchar(100) DEFAULT NULL COMMENT 'For guest bookings',
  `showtime_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(8,2) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `status` enum('confirmed','cancelled','completed') DEFAULT 'confirmed',
  `guest_phone` varchar(20) DEFAULT NULL,
  `seat_number` text NOT NULL COMMENT 'JSON array of booked seats',
  `tickets_subtotal` decimal(10,2) NOT NULL COMMENT 'Price before fees',
  `service_fee` decimal(10,2) NOT NULL DEFAULT 200.00 COMMENT 'Fixed service charge',
  `food_subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `food_items` text DEFAULT NULL,
  `cinema_id` int(11) DEFAULT NULL COMMENT 'Reference to cinema location',
  `auditorium_id` int(11) DEFAULT NULL COMMENT 'Reference to auditorium',
  `movie_title` varchar(255) DEFAULT NULL COMMENT 'Cached movie title for reference',
  `showtime_datetime` datetime DEFAULT NULL COMMENT 'Cached showtime date/time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `guest_email`, `guest_name`, `showtime_id`, `booking_date`, `total_amount`, `booking_reference`, `status`, `guest_phone`, `seat_number`, `tickets_subtotal`, `service_fee`, `food_subtotal`, `food_items`, `cinema_id`, `auditorium_id`, `movie_title`, `showtime_datetime`) VALUES
(1, NULL, 'olamideoladokun150@gmail.com', 'OLAMIDE OLADOKUN', 3, '2025-07-28 01:47:53', 7200.00, 'BK6886D6C92BCD9', 'confirmed', '09061225268', '[\"A1\"]', 7000.00, 200.00, 0.00, NULL, 1, 13, 'Sinners', '2025-07-28 10:00:00'),
(2, NULL, 'olamideoladokun150@gmail.com', 'olamide oladokun', 3, '2025-07-28 02:11:14', 14200.00, 'BK6886DC429A498', 'confirmed', '09061225268', '[\"A2\",\"A3\"]', 14000.00, 200.00, 0.00, NULL, 1, 13, 'Sinners', '2025-07-28 10:00:00'),
(3, NULL, 'olamideoladokun150@gmail.com', 'olamide oladokun', 5, '2025-07-28 02:15:20', 7700.00, 'BK6886DD38D18F0', 'confirmed', '09061225268', '[\"A1\"]', 7500.00, 200.00, 0.00, NULL, 3, 5, ' Sonic the Hedgehog 3', '2025-07-28 10:00:00'),
(4, NULL, 'olamideoladokun150@gmail.com', 'olamide oladokun', 3, '2025-07-28 02:20:26', 7200.00, 'BK6886DE6A25846', 'confirmed', '09061225268', '[\"F5\"]', 7000.00, 200.00, 0.00, NULL, 1, 13, 'Sinners', '2025-07-28 10:00:00'),
(5, NULL, 'olamideoladokun150@gmail.com', 'olamide oladokun', 5, '2025-07-29 01:17:34', 7700.00, 'BK6888212E10E80', 'confirmed', '09061225268', '[\"A2\"]', 7500.00, 200.00, 0.00, NULL, 3, 5, ' Sonic the Hedgehog 3', '2025-07-29 10:00:00'),
(6, NULL, 'olamideoladokun150@gmail.com', 'OLAMIDE OLADOKUN', 8, '2025-08-17 23:38:07', 15200.00, 'BK68A267DF27684', 'confirmed', '09061225268', '[\"A1\",\"A2\"]', 15000.00, 200.00, 0.00, NULL, 1, 13, 'Mission Impossible - The final reckoning', '2025-08-19 12:00:00'),
(7, NULL, 'dave@gmail.com', 'dave', 9, '2025-08-23 13:41:49', 37200.00, 'BK68A9C51D35623', 'confirmed', '12345678999', '[\"J4\",\"J5\"]', 15000.00, 200.00, 22000.00, '{\"1\":2,\"5\":2}', 1, 13, 'Superman (2025)', '2025-08-24 15:00:00'),
(8, NULL, 'victor@gmail.com', 'Victor Smith ', 10, '2025-08-23 19:26:00', 34200.00, 'BK68AA15C812EAC', 'confirmed', '09123456789', '[\"F6\",\"F7\"]', 14000.00, 200.00, 20000.00, '{\"1\":2,\"6\":2}', 4, 3, 'Nobody 2', '2025-08-30 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `cinemas`
--

CREATE TABLE `cinemas` (
  `cinema_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `amenities` text DEFAULT NULL COMMENT 'JSON array of amenities',
  `image` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cinemas`
--

INSERT INTO `cinemas` (`cinema_id`, `name`, `location`, `address`, `contact_phone`, `contact_email`, `amenities`, `image`, `latitude`, `longitude`, `created_at`) VALUES
(1, 'Anvora Cinemas, ICM', 'Ikeja', '123 Main St, Downtown', '555-0101', 'downtown@anvoracinemas.com', '[\"Food Court\",\"Wheelchair Access\"]', '10d3fdd044d54be6bb55213df28afb30.jpg', 0.00000000, 0.00000000, '2025-04-18 12:49:33'),
(2, 'Anvora Westside', 'Westside', '456 Oak Ave, Westside', '555-0102', 'westside@anvoracinemas.com', '[\"VIP Lounge\",\"Bar\"]', 'IMG-20221130-WA0000.jpg', 0.00000000, 0.00000000, '2025-04-18 12:49:33'),
(3, 'Anvora Cinemas Leasure Mall', 'Surulere', 'Surulere, Lagos', '555-0103', 'eastmall@anvoracinemas.com', '[\"Arcade\",\"Baby Changing\"]', 'IMG-20221130-WA0000.jpg', 0.00000000, 0.00000000, '2025-04-18 12:49:33'),
(4, 'Anvora Cinemas Jara Mall', 'Ikeja', 'Ikeja, Lagos', '555-0104', 'southside@anvoracinemas.com', '[\"Food Court\",\"Wheelchair Access\",\"VIP Lounge\",\"Bar\"]', 'Dude sitting on Flat-roof.jpg', 0.00000700, 0.00000700, '2025-05-05 07:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `food_categories`
--

CREATE TABLE `food_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_categories`
--

INSERT INTO `food_categories` (`category_id`, `name`, `slug`, `created_at`) VALUES
(1, 'Combos', 'combos', '2025-06-04 01:44:35'),
(2, 'Snacks', 'snacks', '2025-06-04 01:44:45'),
(3, 'Meals', 'meals', '2025-06-04 01:44:57'),
(4, 'Desserts', 'desserts', '2025-06-04 01:45:12'),
(5, 'Drinks', 'drinks', '2025-06-04 01:45:19');

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`item_id`, `category_id`, `name`, `description`, `price`, `image`, `is_active`, `created_at`) VALUES
(1, 4, 'Ice Cream Sundae', 'Vanilla ice cream with chocolate syrup, sprinkles, and a cherry.', 5500.00, '683fa61e6198f.jpg', 1, '2025-06-04 01:49:18'),
(2, 5, 'Fountain Drink', '32oz fountain drink with free refills (Coke, Diet Coke, Sprite, etc.)', 1400.00, '683fa679b3b51.png', 1, '2025-06-04 01:50:49'),
(3, 3, 'Cinema Hot Dog', 'Juicy all-beef hot dog with your choice of toppings', 5000.00, '683fa6d9d0f41.jpg', 1, '2025-06-04 01:52:25'),
(4, 2, 'Regular Popcorn', 'Freshly popped, lightly salted popcorn in a regular size', 4000.00, '683fa744b26e9.jpg', 1, '2025-06-04 01:54:12'),
(5, 2, 'Large Popcorn', 'Our signature popcorn in a large bucket with free refill', 5500.00, '683fa78521150.webp', 1, '2025-06-04 01:55:17'),
(6, 2, 'Loaded Nachos', 'Crispy tortilla chips topped with cheese, jalapeños, and salsa', 4500.00, '683fa804b21be.webp', 1, '2025-06-04 01:57:24'),
(7, 2, 'Candy Box', 'Assorted movie theater candies (M&Ms, Skittles, Sour Patch, etc.)', 3500.00, '683fa864b76a2.jpg', 1, '2025-06-04 01:59:00'),
(8, 1, 'Classic Combo', 'Regular popcorn + fountain drink + candy of your choice. ', 10000.00, '683fa9196e932.jpg', 1, '2025-06-04 02:02:01'),
(9, 1, 'Ultimate Combo', 'Large popcorn + 2 fountain drinks + 2 candies', 10000.00, '683faa0f751d0.jpeg', 1, '2025-06-04 02:06:07'),
(10, 1, 'Family Combo', '2 large popcorns + 2 fountain drinks + candy', 10000.00, '683faa793d701.jpg', 1, '2025-06-04 02:07:53'),
(11, 1, 'Sweet Lovers Combo', 'Regular popcorn + ice cream', 10000.00, '683fabecc6bbd.png', 1, '2025-06-04 02:14:04'),
(12, 1, 'Date Night Combo', '1 large popcorn + drinks + candy', 10000.00, '683fac7fd07e4.jpeg', 1, '2025-06-04 02:16:31');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `release_date` date NOT NULL,
  `age_rating` varchar(10) NOT NULL,
  `director` varchar(100) DEFAULT NULL,
  `cast` text DEFAULT NULL,
  `poster_img` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `language` varchar(50) DEFAULT 'English',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rating` varchar(10) DEFAULT 'NR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `description`, `duration`, `release_date`, `age_rating`, `director`, `cast`, `poster_img`, `trailer_url`, `genre`, `language`, `is_featured`, `created_at`, `updated_at`, `rating`) VALUES
(1, 'The Last Adventure', 'An epic journey through uncharted territories', 142, '2023-11-15', 'PG-13', 'Jane Smith', 'John Doe, Sarah Lee, Mike Chen', 'uploads/posters/thelastadventure.jpg', 'https://youtu.be/sample1', 'Adventure', 'English', 1, '2025-04-18 12:49:33', '2025-05-23 04:38:33', 'NR'),
(2, 'Midnight Detective', 'A noir thriller about a detective solving his own murder', 118, '2023-11-10', 'R', 'Alan Park', 'Robert Downey, Emma Stone, Chris Evans', 'uploads/posters/midnightdetective.jpg', 'https://youtu.be/sample2', 'Thriller', 'English', 1, '2025-04-18 12:49:33', '2025-05-23 04:39:11', 'NR'),
(3, 'Cosmic Journey', 'Intergalactic adventure through wormholes and time', 156, '1993-11-20', 'PG', 'Lisa Wong', 'Tom Hanks, Zendaya, Idris Elba', 'uploads/posters/cosmicjourney.jpg', 'https://youtu.be/sample3', 'Sci-Fi', 'English', 0, '2025-04-18 12:49:33', '2025-05-23 04:39:28', 'NR'),
(5, 'The Heist', 'A group of thieves plan the ultimate bank robbery', 128, '2024-07-19', 'R', 'Christopher Nolan', 'Leonardo DiCaprio, Brad Pitt, Margot Robbie', 'uploads/posters/The_Heist_2024_film.jpg', 'https://youtu.be/sample5', 'Action', 'English', 1, '2025-04-18 12:49:33', '2025-05-23 04:39:41', 'NR'),
(6, ' Sonic the Hedgehog 3', 'Sonic, Knuckles, and Tails reunite against a powerful new adversary, Shadow, a mysterious villain with powers unlike anything they have faced before.', 110, '2024-02-04', 'G', 'Jeff Fowler', 'Jim Carrey, Ben Schwartz, Keanu Reeves', 'uploads/posters/sonic_the_hedgehog_three.jpg', 'https://youtu.be/klJoTmOhivk', 'Action', 'English', 1, '2025-04-18 22:44:35', '2025-05-23 04:39:58', 'NR'),
(7, 'Sinners', 'Sinners is a 2025 American musical supernatural action horror film produced, written, and directed by Ryan Coogler. Set in 1932 in the Mississippi Delta, the film stars Michael B. Jordan in dual roles as criminal twin brothers who return to their hometown to start again where they are confronted by a supernatural evil. ', 120, '2025-05-30', 'PG-13', 'Ryan Clooger', 'Michael .B. Jordan', 'uploads/posters/sinners.jpg', 'https://youtu.be/bKGxHflevuk?si=WlM2TG1UQabvbXzU', 'Horror', 'English', 0, '2025-05-24 17:47:13', '2025-05-30 01:49:51', 'NR'),
(8, 'Mission Impossible - The final reckoning', 'Mission: Impossible – The Final Reckoning is a 2025 American action spy film directed by Christopher McQuarrie from a screenplay he co-wrote with Erik Jendresen', 120, '2025-08-18', 'PG', 'Tom Cruise', 'Tom Cruise, Esai Morles, Shea Whigham, Ving Rhames, etc', 'uploads/posters/missionimpossible.jpeg', 'https://youtu.be/fsQgc9pCyDU', 'Action', 'English', 0, '2025-08-17 23:30:52', '2025-08-17 23:36:58', 'NR'),
(9, 'Superman (2025)', 'Superman (2025) is a new superhero film directed by James Gunn, marking the beginning of the DC Universe Chapter One: Gods and Monsters, featuring David Corenswet as Superman.', 135, '2025-08-22', 'G', 'James Gunn', 'David Corenswet, Rachel Brosnahan, Nicholas Hoult, etc', 'uploads/posters/superman.png', 'https://youtu.be/XlFuYGfFabk', 'Action', 'English', 0, '2025-08-20 14:36:00', '2025-08-23 13:25:24', 'NR'),
(10, 'Nobody 2', 'Nobody 2 is a 2025 American action thriller film directed by Timo Tjahjanto from a screenplay by Derek Kolstad and Aaron Rabin. It is a sequel to Nobody (2021).', 135, '2025-08-30', 'G', 'Timo Tjahjanto', 'Bob Odenkirk, Christopher Lloyd, Connie Nielsen, etc', 'uploads/posters/nobody2.jpeg', 'https://youtu.be/mGkLRm7aGb8', 'Action', 'English', 0, '2025-08-23 18:40:02', '2025-08-23 18:40:02', 'NR');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','paypal','cash') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed','free_item') NOT NULL,
  `discount_value` decimal(6,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `applicable_movies` text DEFAULT NULL COMMENT 'JSON array of movie IDs',
  `applicable_cinemas` text DEFAULT NULL COMMENT 'JSON array of cinema IDs',
  `promo_code` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showtimes`
--

CREATE TABLE `showtimes` (
  `showtime_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `auditorium_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `price` decimal(6,2) NOT NULL,
  `format` enum('2D','3D','4D') DEFAULT '2D',
  `is_special_event` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `showtimes`
--

INSERT INTO `showtimes` (`showtime_id`, `movie_id`, `auditorium_id`, `start_time`, `end_time`, `price`, `format`, `is_special_event`) VALUES
(3, 7, 13, '2025-08-22 10:00:00', '2025-08-22 12:00:00', 7000.00, '3D', 0),
(5, 6, 5, '2025-07-29 10:00:00', '2025-07-29 11:50:00', 7500.00, '3D', 0),
(6, 5, 3, '2025-07-29 12:00:00', '2025-07-29 14:08:00', 7500.00, '3D', 0),
(7, 2, 4, '2025-07-29 12:00:00', '2025-07-29 13:58:00', 8000.00, '4D', 0),
(8, 8, 13, '2025-08-24 12:00:00', '2025-08-24 14:00:00', 7500.00, '3D', 0),
(9, 9, 13, '2025-08-24 15:00:00', '2025-08-24 17:15:00', 7500.00, '3D', 0),
(10, 10, 3, '2025-08-30 10:00:00', '2025-08-30 12:15:00', 7000.00, '3D', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `loyalty_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `auditoriums`
--
ALTER TABLE `auditoriums`
  ADD PRIMARY KEY (`auditorium_id`),
  ADD KEY `cinema_id` (`cinema_id`);

--
-- Indexes for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD PRIMARY KEY (`booking_id`,`seat_number`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `showtime_id` (`showtime_id`);

--
-- Indexes for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD PRIMARY KEY (`cinema_id`);

--
-- Indexes for table `food_categories`
--
ALTER TABLE `food_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD PRIMARY KEY (`showtime_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `auditorium_id` (`auditorium_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auditoriums`
--
ALTER TABLE `auditoriums`
  MODIFY `auditorium_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cinemas`
--
ALTER TABLE `cinemas`
  MODIFY `cinema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `food_categories`
--
ALTER TABLE `food_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showtimes`
--
ALTER TABLE `showtimes`
  MODIFY `showtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditoriums`
--
ALTER TABLE `auditoriums`
  ADD CONSTRAINT `auditoriums_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`) ON DELETE CASCADE;

--
-- Constraints for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD CONSTRAINT `booked_seats_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`showtime_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `food_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `food_categories` (`category_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD CONSTRAINT `showtimes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showtimes_ibfk_2` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`auditorium_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
