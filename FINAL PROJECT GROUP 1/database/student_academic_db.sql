-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2026 at 04:21 PM
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
-- Database: `student_academic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `remarks` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `student_id`, `record_date`, `status`, `remarks`) VALUES
(2, 21, '2026-07-03', 'Late', ''),
(3, 21, '2026-07-04', 'Absent', 'No Excuse Letter');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `assignment_score` int(11) NOT NULL,
  `quiz_score` int(11) NOT NULL,
  `exam_score` int(11) NOT NULL,
  `final_grade` int(11) NOT NULL,
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject`, `assignment_score`, `quiz_score`, `exam_score`, `final_grade`, `encoded_by`, `encoded_at`, `subject_id`) VALUES
(2, 21, 'Computer Programming 1', 10, 10, 50, 26, 33, '2026-07-03 08:25:11', 1),
(3, 21, 'Physics', 50, 50, 90, 66, 33, '2026-07-03 09:53:44', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 2, 'Report generated', 'Student Ana Santos has attendance 92%, predicted grade 107%, and final grade 90%.', 'sent', '2026-07-02 17:05:41'),
(22, 2, 'Grade encoded', 'You stored a new grade for Ian Torres', 'sent', '2026-07-03 07:20:19'),
(24, 2, 'Report generated', 'Student Ian Torres has attendance 78%, predicted grade 49%, and final grade 28%.', 'sent', '2026-07-03 07:20:45'),
(26, 32, 'Account approved', 'Hello leonardo macalinao iii,\n\nYour account has been approved by an administrator and is now active.\n\nThank you,\nAI-powered student academic performance predictor Team', 'sent', '2026-07-03 07:53:41'),
(27, 2, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 70%, and final grade 0%.', 'sent', '2026-07-03 08:20:28'),
(28, 32, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 70%, and final grade 0%.', 'sent', '2026-07-03 08:20:31'),
(29, 33, 'Account approved', 'Hello Lorelie M. Macalinao,\n\nYour account has been approved by an administrator and is now active.\n\nThank you,\nAI-powered student academic performance predictor Team', 'sent', '2026-07-03 08:23:17'),
(30, 33, 'Grade encoded', 'You stored a new grade for leonardo macalinao iii', 'sent', '2026-07-03 08:25:17'),
(31, 32, 'New grade posted', 'A new grade has been recorded for you.', 'sent', '2026-07-03 08:25:20'),
(32, 33, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', 'sent', '2026-07-03 08:26:12'),
(33, 32, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', 'sent', '2026-07-03 08:26:16'),
(34, 33, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', 'sent', '2026-07-03 08:26:23'),
(35, 32, 'Report generated', 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', 'sent', '2026-07-03 08:26:26'),
(36, 33, 'Grade encoded', 'You stored a new grade for leonardo macalinao iii', 'sent', '2026-07-03 09:53:48'),
(37, 32, 'New grade posted', 'A new grade has been recorded for you.', 'sent', '2026-07-03 09:53:51'),
(38, 33, 'Report generated', 'Student leonardo macalinao iii has attendance 100%, predicted grade 50%, and final grade 66%.', 'sent', '2026-07-03 09:54:48'),
(39, 32, 'Report generated', 'Student leonardo macalinao iii has attendance 100%, predicted grade 50%, and final grade 66%.', 'sent', '2026-07-03 09:54:51');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `summary` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `student_id`, `generated_by`, `summary`, `created_at`) VALUES
(3, 21, 2, 'Student leonardo macalinao iii has attendance 80%, predicted grade 70%, and final grade 0%.', '2026-07-03 08:20:23'),
(4, 21, 33, 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', '2026-07-03 08:26:08'),
(5, 21, 33, 'Student leonardo macalinao iii has attendance 80%, predicted grade 49%, and final grade 26%.', '2026-07-03 08:26:19'),
(6, 21, 33, 'Student leonardo macalinao iii has attendance 100%, predicted grade 50%, and final grade 66%.', '2026-07-03 09:54:45');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `program` varchar(100) DEFAULT 'General Studies',
  `phone` varchar(20) DEFAULT '',
  `guardian_name` varchar(100) DEFAULT '',
  `attendance` int(11) DEFAULT 0,
  `study_hours` int(11) DEFAULT 0,
  `assignments` int(11) DEFAULT 0,
  `quiz_score` int(11) DEFAULT 0,
  `predicted_grade` int(11) DEFAULT 0,
  `final_grade` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `program`, `phone`, `guardian_name`, `attendance`, `study_hours`, `assignments`, `quiz_score`, `predicted_grade`, `final_grade`, `status`, `created_at`) VALUES
(1, 3, 'STU000', 'Computer Science', '09170000002', 'Student Guardian', 2, 4, 3, 6, 13, 90, '0', '2026-07-02 15:42:00'),
(21, 32, 'STU032', 'Information Technology', '09770337918', 'Leonardo G. Macalinao Jr.', 100, 10, 50, 50, 50, 66, '0', '2026-07-03 07:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `name`, `created_at`) VALUES
(1, NULL, 'Computer Programming 1', '2026-07-03 07:20:15'),
(2, 'MATH101', 'Mathematics', '2026-07-03 07:46:49'),
(3, 'PHYS101', 'Physics', '2026-07-03 07:46:49'),
(4, 'CS101', 'Computer Science', '2026-07-03 07:46:49'),
(5, 'ENG101', 'English Composition', '2026-07-03 07:46:49'),
(6, 'STAT101', 'Statistics', '2026-07-03 07:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT 'General',
  `phone` varchar(20) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `department`, `phone`) VALUES
(1, 2, 'Mathematics', '09170000001'),
(6, 29, 'Physics', '09170001001'),
(7, 30, 'Mathematics', '09170001002'),
(8, 33, 'General', '000');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_email_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(10) DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `full_name`, `email`, `created_at`, `is_email_verified`, `verification_code`, `verification_expires_at`) VALUES
(1, 'admin', '$2y$10$blVm5lLPuKIunN35cRxfSegMoy4jVj0XOJBlsSB/momTbO3KgBZfi', 'admin', 'Admin User', 'admin@school.edu', '2026-07-02 15:42:00', 1, NULL, NULL),
(2, 'teacher', '$2y$10$ZMF62HmCUW7ETKnrdO4J3OOMx1WTN1DlHKDL9gzT5VqJzvmWbjHxq', 'teacher', 'Teacher User', 'teacher@school.edu', '2026-07-02 15:42:00', 1, NULL, NULL),
(3, 'student', '$2y$10$f1ug/LSR3eJScmHL8nnhUeno1mxNlGSPyEIJHu4c3B90upcoHsfe.', 'student', 'Student User', 'student@school.edu', '2026-07-02 15:42:00', 1, NULL, NULL),
(29, 'mentor', '$2y$10$EEgGUq36Vq0FKDRyIaKV5On2t.S8RfcZ8axjbCvBpqQYWD9tmDiy6', 'teacher', 'Mentor Teacher', 'mentor.teacher@school.edu', '2026-07-03 07:48:35', 1, NULL, NULL),
(30, 'advisor', '$2y$10$0OZ4PORrX.pgPSJ8MYMEh.Vt//3B9WRAZGiyiSgH232M5ccd3oaqm', 'teacher', 'Advisor Teacher', 'advisor.teacher@school.edu', '2026-07-03 07:48:35', 1, NULL, NULL),
(31, 'olivia.james', '$2y$10$vW1FbZCW7BwDq3Uz5DMQXuDm6SfBvIX0lwZKVnR0jU3x8E/zUl0qy', '', 'Olivia James', 'olivia.james@student.edu', '2026-07-03 07:48:36', 1, NULL, NULL),
(32, 'lmacalinao', '$2y$10$cNASgopyVfOUBuS4QnVxluoJiu8Vc.rAbC3tP/0MmmTiPzTLvpINS', 'student', 'leonardo macalinao iii', 'leonardolllmacalinaoisap@gmail.com', '2026-07-03 07:53:22', 1, NULL, NULL),
(33, 'lomacalinao', '$2y$10$PQAsl5H8jl5t/rhbpy9wGOtwvqmcg3dRGhe2.XMTusX1EP8rV0Kf.', 'teacher', 'Lorelie M. Macalinao', 'twaittwix28@gmail.com', '2026-07-03 08:22:55', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `encoded_by` (`encoded_by`),
  ADD KEY `fk_grades_subject` (`subject_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`encoded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
