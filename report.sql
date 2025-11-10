-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 04:03 PM
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
  `class_name` varchar(255) DEFAULT NULL,
  `prefix` varchar(255) DEFAULT NULL,
  `LEVEL` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `prefix`, `LEVEL`) VALUES
(30, 'Primary One', 'P.1', 'Lower Primary'),
(42, 'Primary Two', 'P.2', 'Lower Primary'),
(43, 'Primary Three', 'P.3', 'Lower Primary'),
(44, 'Primary Four', 'P.4', 'Lower Primary'),
(45, 'Primary Five', 'P.5', 'Upper Primary'),
(46, 'Primary Six', 'P.6', 'Upper Primary'),
(47, 'Primary Seven', 'P.7', 'Upper Primary');

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
(99, 30, 15),
(100, 30, 14),
(101, 30, 20),
(102, 30, 21),
(103, 30, 16),
(104, 30, 13),
(105, 42, 16),
(106, 42, 20),
(107, 42, 21),
(108, 42, 22),
(109, 42, 19),
(110, 42, 15),
(111, 42, 14),
(112, 43, 19),
(113, 43, 16),
(114, 43, 22),
(115, 43, 21),
(116, 43, 20),
(117, 47, 16),
(118, 47, 17),
(119, 47, 18),
(120, 47, 19),
(121, 46, 16),
(122, 46, 17),
(123, 46, 18),
(124, 46, 19),
(125, 46, 22),
(126, 30, 17),
(127, 42, 13),
(128, 44, 22),
(129, 44, 17),
(130, 44, 18);

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
(42, 'b.o.t', 8, 2025, 30),
(43, 'm.o.t', 8, 2025, 30),
(46, 'b.o.t', 12, 2025, 42),
(47, 'b.o.t', 12, 2025, 43),
(48, 'b.o.t', 12, 2025, 44),
(49, 'b.o.t', 12, 2025, 45),
(50, 'b.o.t', 12, 2025, 46),
(51, 'b.o.t', 12, 2025, 47);

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
(1370, 320, 42, 13, 67),
(1371, 320, 42, 15, 29),
(1372, 320, 42, 14, 50),
(1376, 320, 43, 13, 90),
(1377, 320, 43, 15, 90),
(1378, 320, 43, 14, 90),
(1388, 320, 43, 20, 45),
(1389, 320, 43, 21, 65),
(1390, 320, 43, 16, 65);

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
-- Table structure for table `school_profile`
--

CREATE TABLE `school_profile` (
  `profile_id` int(11) NOT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_1` varchar(255) DEFAULT NULL,
  `phone_2` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `motto` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_profile`
--

INSERT INTO `school_profile` (`profile_id`, `school_name`, `address`, `phone_1`, `phone_2`, `email`, `motto`, `profile_image`) VALUES
(4, 'st elisha', 'bukomansimbi', '0744683027/0700510546', 45, 'katojkalemba@gmail.com', 'slow but sure', 'img/stdent_image/school_logo_1757408816.png');

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
(25, 30, 'main'),
(29, 42, 'MAIN'),
(30, 43, 'MAIN'),
(31, 44, 'MAIN'),
(32, 45, 'MAIN'),
(33, 46, 'MAIN'),
(34, 47, 'MAIN');

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
  `status` enum('day','boarding') DEFAULT NULL,
  `level` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `gender`, `dob`, `LIN`, `class_id`, `stream_id`, `image`, `status`, `level`) VALUES
(320, 'NAGAYI', 'JOSEPHINE', 'FEMALE', '2025-10-17', '9900119653', 30, 25, '', 'day', 'active');

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
(13, 'LUGANDA'),
(14, 'WRITING'),
(15, 'READING'),
(16, 'MATHEMATICS'),
(17, 'SOCIAL STUDIES'),
(18, 'SCIENCE'),
(19, 'ENGLISH'),
(20, 'LITERACY I'),
(21, 'LITERACY II'),
(22, 'COMPUTER APPLICATION');

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
(16, 15, 30, 25),
(18, 16, 44, 31),
(19, 17, 44, 31);

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
  `initials` varchar(255) DEFAULT NULL,
  `term_id` int(11) NOT NULL,
  `academic_year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignments`
--

INSERT INTO `teacher_subject_assignments` (`id`, `teacher_id`, `class_id`, `stream_id`, `subject_id`, `initials`, `term_id`, `academic_year`) VALUES
(28, 15, 30, 25, 13, 'NO', 8, 2025),
(35, 4, 30, 25, 15, NULL, 8, 2025);

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
(8, 'Term 1'),
(11, 'Term 2'),
(12, 'Term 3');

-- --------------------------------------------------------

--
-- Table structure for table `term_info`
--

CREATE TABLE `term_info` (
  `info_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `academic_year` int(11) DEFAULT NULL,
  `term_end` date DEFAULT NULL,
  `next_start` date DEFAULT NULL,
  `fees_day` decimal(10,2) DEFAULT NULL,
  `fees_boarding` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(15, 'namugga oliver', 'noliver', '$2y$10$0LvZg1271EfQXGWtUuEuOeD0x5RMNAb7zsnqaT9N0jJYvX6KBcgiu', 'class_teacher', 0),
(16, 'kassa john', 'kjohn', '$2y$10$Faor2YZwe/titYkwbs0Ev.gylsez1DW7J4P/iynghWRC4vlNYqcOy', 'class_teacher', 0),
(17, 'mirugwe alex', 'malex', '$2y$10$Kg783wNDAZHEnnchKcDLj.LJMDlfsQ.wt6h9vuJnvOAk.PaCsSIse', 'class_teacher', 0),
(18, 'ndawula henry', 'nhenry', '$2y$10$X1O9ifDCU3LaW7qVOHSfSe9a4OYBPqR9kKbI/ywvsN7VSOMQOL9Se', 'teacher', 0),
(19, 'john doe', 'johndoe', '$2y$10$fXg12ZQcqPv8SYCok1iMae008q1jyQw9ile6A3XS5p5bkhxe4jNwq', 'teacher', 0),
(20, 'Donna Roach', 'rogoqaxe', '$2y$10$FcNIvlFyuqdl8OSyaZf1tOP3DhY52Yn3S2XB9PltykXHHRigar312', 'admin', 0),
(21, 'Cheyenne Knapp', 'hucanylix', '$2y$10$RjQr1bdDjERwYU2CyEXqreh6DeUu0YdotL6AElOFnDoqsi/Wu/2hO', 'class_teacher', 0),
(22, 'Adam Gamble', 'dyxowal', '$2y$10$mlhTq4W78aoWh2Nn.V21ee9funIxDieZcy0Otc.z1TH/5WbFSXUpy', 'admin', 0),
(23, 'Joelle Myers', 'gybakyc', '$2y$10$ZRuQfTwIiUTWJnfUP0SZeOzIrsFdCHjh6i6VCRCUNXClTOmXk6SRi', 'admin', 0),
(24, 'Norman Horne', 'xekejovil', '$2y$10$F7FWs1rPC/p4hT/SocHHju5LPHsm5QwxvKOYuzCOFJAwW7gvYYfJq', 'class_teacher', 0),
(25, 'Xandra Glenn', 'wegeqezog', '$2y$10$oE8h1UrMZj6eE7TCzqJ.k.otVtq6XpJVPSISz6WXl1Y6wTWlKKFfi', 'teacher', 0),
(26, 'Byron Dawson', 'sikix', '$2y$10$HKkt8Uf5q9pl2OqO3laIz.t6ibUdjAjwR3p7xdNAio4X6ofCGEK8C', 'admin', 0),
(27, 'matovu vincent', 'vmatovu', '$2y$10$8dlCXn7vUS2aVILaIvl2qelX7z8FuH56NcErcDe/Qa7l6zuv2y5aO', 'admin', 0),
(28, 'NAGAYI JOSEPHINE', 'njosephine', '$2y$10$AqNd4CViNAIFV8iXdfIkHOvm4d3HNL1pHUE38b1MMs5YEEMqynukW', 'teacher', 0);

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
-- Indexes for table `school_profile`
--
ALTER TABLE `school_profile`
  ADD PRIMARY KEY (`profile_id`);

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
-- Indexes for table `term_info`
--
ALTER TABLE `term_info`
  ADD PRIMARY KEY (`info_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `term_id` (`term_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `grading_scale`
--
ALTER TABLE `grading_scale`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1391;

--
-- AUTO_INCREMENT for table `school_profile`
--
ALTER TABLE `school_profile`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `streams`
--
ALTER TABLE `streams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;

--
-- AUTO_INCREMENT for table `student_comments`
--
ALTER TABLE `student_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `term_info`
--
ALTER TABLE `term_info`
  MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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

--
-- Constraints for table `term_info`
--
ALTER TABLE `term_info`
  ADD CONSTRAINT `term_info_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `term_info_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
