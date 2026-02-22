-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 05:30 PM
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
-- Database: `doctor_appointment_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `notes`, `status`, `created_at`) VALUES
(1, 4, 1, '2026-02-26', '09:06:00', NULL, 'completed', '2026-02-22 08:06:57'),
(2, 4, 3, '2026-02-25', '12:30:00', 'i want to see doc', 'pending', '2026-02-22 15:09:37'),
(3, 4, 1, '2026-02-27', '15:00:00', 'feef', 'pending', '2026-02-22 15:17:00'),
(4, 4, 4, '2026-02-26', '14:00:00', '', 'pending', '2026-02-22 15:32:56');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization`) VALUES
(1, 2, 'cardiologist'),
(3, 5, 'Gynacologist'),
(4, 6, 'Dentist');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','doctor','patient') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@clinic.com', '$2y$10$iFEmIi4GE.lG3U0eB3bo4ewtjyraP9WThMZ9BErHMy5lcEnUWZ8AG', 'admin', '2026-02-22 07:35:32'),
(2, 'Tobias Ishiwu', 'tobyishiwu@gmail.com', '$2y$10$z5LHQKpDuIM44.qmL27kru4/VRZk9ilpWUwsoIn2wNhuNLt3JoMLy', 'doctor', '2026-02-22 07:54:15'),
(4, 'TOBIAS CHIDIEBERE ISHIWU', 'tobysishiwu@gmail.com', '$2y$10$XVdpztnDh4E6RZarYX41b.RNh8zZX/gf/OazY977E0f7F3YrvTd.a', 'patient', '2026-02-22 07:59:11'),
(5, 'Dr. Abigail Ishiwu', 'abby@gmail.com', '$2y$10$xlr4FJG0E7ETdxTXfp2pIesFg8eR.ujhlZjnrFDFpdBY9Zy1L444.', 'doctor', '2026-02-22 08:25:04'),
(6, 'DR TOBIAS CHIDIEBERE', 'toby@gmail.com', '$2y$10$r2G9KJx1njKRoen89vKLKeEccpkKVUGiDLqY/.9ipTH8rU1YtAsoy', 'doctor', '2026-02-22 08:25:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
