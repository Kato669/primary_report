-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 08:11 PM
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
-- Database: `report`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`) VALUES
(30, 'p1'),
(31, 'p2'),
(32, 'p3'),
(33, 'p4'),
(34, 'p5'),
(35, 'p6'),
(36, 'p7');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`) VALUES
(56, 36, 2),
(57, 36, 3),
(58, 36, 4),
(59, 36, 5),
(60, 36, 8),
(61, 36, 7);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `exam_name` varchar(255) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `academic_year` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `exam_name`, `term_id`, `academic_year`, `class_id`) VALUES
(19, 'mock', 8, 2025, 36),
(20, 'b.o.t', 8, 2025, 36),
(21, 'm.o.t', 8, 2025, 36);

-- --------------------------------------------------------

--
-- Table structure for table `grading_scale`
--

CREATE TABLE `grading_scale` (
  `grade_id` int(11) NOT NULL,
  `grade_name` varchar(20) DEFAULT NULL,
  `min_score` int(11) DEFAULT NULL,
  `max_score` int(11) DEFAULT NULL,
  `comment` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grading_scale`
--

INSERT INTO `grading_scale` (`grade_id`, `grade_name`, `min_score`, `max_score`, `comment`) VALUES
(1, 'D1', 95, 100, 'Excellent'),
(2, 'D2', 85, 94, 'Amazing'),
(3, 'C3', 80, 84, 'Great Work'),
(4, 'C4', 70, 79, 'Nice Work'),
(5, 'C5', 60, 69, 'Thanks'),
(6, 'C6', 55, 59, 'Fair'),
(7, 'P7', 50, 54, 'Improve'),
(8, 'P8', 40, 49, 'Poor'),
(9, 'F9', 0, 39, 'Work Hard');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`mark_id`, `student_id`, `exam_id`, `subject_id`, `score`) VALUES
(146, 24, 19, 7, 100),
(147, 24, 19, 4, 100),
(148, 24, 19, 3, 100),
(149, 24, 19, 2, 100),
(150, 24, 19, 5, 100),
(151, 24, 19, 8, 100),
(152, 24, 20, 7, 50),
(153, 24, 20, 4, 50),
(154, 24, 20, 3, 50),
(155, 24, 20, 2, 50),
(156, 24, 20, 5, 50),
(157, 24, 20, 8, 50),
(158, 24, 21, 7, 50),
(159, 24, 21, 4, 50),
(160, 24, 21, 3, 50),
(161, 24, 21, 2, 50),
(162, 24, 21, 5, 50),
(163, 24, 21, 8, 50),
(164, 25, 20, 7, 87),
(165, 25, 20, 4, 78),
(166, 25, 20, 3, 67),
(167, 25, 20, 2, 97),
(168, 25, 20, 5, 56),
(169, 25, 20, 8, 32),
(170, 25, 21, 7, 78),
(171, 25, 21, 4, 89),
(172, 25, 21, 3, 76),
(173, 25, 21, 2, 56),
(174, 25, 21, 5, 89),
(175, 25, 21, 8, 67),
(176, 25, 19, 7, 88),
(177, 25, 19, 4, 55),
(178, 25, 19, 3, 67),
(179, 25, 19, 2, 54),
(180, 25, 19, 5, 67),
(181, 25, 19, 8, 87);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `class_position` int(11) DEFAULT NULL,
  `stream_position` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `average` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

CREATE TABLE `streams` (
  `id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `stream_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `streams`
--

INSERT INTO `streams` (`id`, `class_id`, `stream_name`) VALUES
(14, 30, 'active'),
(15, 30, 'brilliant'),
(16, 31, 'active'),
(17, 31, 'brilliant'),
(18, 36, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `LIN` varchar(255) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('day','boarding') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `gender`, `dob`, `LIN`, `class_id`, `stream_id`, `image`, `status`) VALUES
(24, 'nalubega', 'angel', 'female', '2019-03-04', '535TETEJD', 36, 18, 'student_1757148677.jpg', 'day'),
(25, 'waswa', 'ssenkima abdul', 'male', '2010-12-12', '233eerr', 36, 18, '', 'day');

-- --------------------------------------------------------

--
-- Table structure for table `student_comments`
--

CREATE TABLE `student_comments` (
  `comment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_teacher_comment` text DEFAULT NULL,
  `head_teacher_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_comments`
--

INSERT INTO `student_comments` (`comment_id`, `student_id`, `exam_id`, `class_teacher_comment`, `head_teacher_comment`, `created_at`, `updated_at`) VALUES
(10, 24, 19, NULL, 'Great', '2025-09-06 12:36:36', '2025-09-06 13:10:54'),
(11, 24, 20, NULL, 'Great', '2025-09-06 12:36:37', '2025-09-06 13:10:54'),
(12, 24, 21, NULL, 'Great', '2025-09-06 12:36:37', '2025-09-06 13:10:54'),
(13, 25, 19, NULL, 'much effort needed', '2025-09-06 13:10:54', '2025-09-06 13:10:54'),
(14, 25, 20, NULL, 'much effort needed', '2025-09-06 13:10:54', '2025-09-06 13:10:54'),
(15, 25, 21, NULL, 'much effort needed', '2025-09-06 13:10:54', '2025-09-06 13:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`) VALUES
(2, 'science'),
(3, 'mathematics'),
(4, 'english'),
(5, 'social studies'),
(6, 'religious education'),
(7, 'computer application'),
(8, 'spiritual formations'),
(9, 'literacy 1'),
(10, 'literacy 2'),
(11, 'luganda');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `user_id`, `class_id`, `stream_id`) VALUES
(10, 15, 36, 18);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_assignments`
--

CREATE TABLE `teacher_subject_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `initials` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignments`
--

INSERT INTO `teacher_subject_assignments` (`id`, `teacher_id`, `class_id`, `stream_id`, `subject_id`, `initials`) VALUES
(13, 15, 36, 18, 7, 'NO'),
(14, 15, 36, 18, 4, 'NO'),
(15, 4, 36, 18, 3, 'KJK'),
(16, 4, 36, 18, 2, 'KJK'),
(17, 4, 36, 18, 5, 'KJK'),
(18, 15, 36, 18, 8, 'NO');

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `term_id` int(11) NOT NULL,
  `term_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`term_id`, `term_name`) VALUES
(8, 'term 1'),
(11, 'term 2'),
(12, 'term 3');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(150) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `is_deleted`) VALUES
(4, 'kato james kalemba', 'katojkalemba', '$2y$10$nwdGfpcMH27e56BTZd1m/e9T8XAkOiq7kAaPnybjJFvLQDE6mICQ2', 'admin', 0),
(15, 'namugga oliver', 'noliver', '$2y$10$0LvZg1271EfQXGWtUuEuOeD0x5RMNAb7zsnqaT9N0jJYvX6KBcgiu', 'class_teacher', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `fk_exams_class` (`class_id`);

--
-- Indexes for table `grading_scale`
--
ALTER TABLE `grading_scale`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `stream_id` (`stream_id`);

--
-- Indexes for table `student_comments`
--
ALTER TABLE `student_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD UNIQUE KEY `unique_student_exam` (`student_id`,`exam_id`),
  ADD KEY `fk_comment_exam` (`exam_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `stream_id` (`stream_id`);

--
-- Indexes for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `stream_id` (`stream_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`term_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `grading_scale`
--
ALTER TABLE `grading_scale`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `streams`
--
ALTER TABLE `streams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `student_comments`
--
ALTER TABLE `student_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`),
  ADD CONSTRAINT `fk_exams_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`),
  ADD CONSTRAINT `marks_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `positions_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`);

--
-- Constraints for table `streams`
--
ALTER TABLE `streams`
  ADD CONSTRAINT `streams_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`id`);

--
-- Constraints for table `student_comments`
--
ALTER TABLE `student_comments`
  ADD CONSTRAINT `fk_comment_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`id`);

--
-- Constraints for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_3` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`id`),
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_4` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
