-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2024 at 06:04 PM
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
-- Database: `findhire`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('Rejected','Accepted','Pending','Withdrawn') NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `applicant_id`, `resume_path`, `cover_letter`, `status`, `applied_at`) VALUES
(9, 15, 7, 'uploads/1734021877_Yanna_Shin_Resume.pdf', NULL, 'Accepted', '2024-12-12 16:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `requirements`, `created_by`, `created_at`, `expiration_date`, `location`, `salary`) VALUES
(13, 'Front End Developer', 'We are looking for a talented Front-End Developer to join our growing team. You will be responsible for designing, building, and optimizing user-facing components of our web applications. This includes working closely with UX/UI designers to deliver seamless and engaging user experiences.', '- Proficiency in HTML, CSS, JavaScript, and React.js\r\n- Experience with responsive design and cross-browser compatibility\r\n- Familiarity with RESTful APIs and JSON\r\n- At least 2 years of relevant work experience\r\n- Excellent problem-solving skills and attention to detail', 6, '2024-12-12 16:37:30', '2024-12-31', 'Makati City Metro Manila', '60000'),
(14, 'Administrator', 'Join our team as a Network Administrator! You will manage and maintain the company’s IT network infrastructure, ensuring system reliability, security, and scalability. This role also includes troubleshooting network issues and implementing new technology solutions.', '- Bachelor’s degree in Computer Science, IT, or related field\r\n- Solid knowledge of networking protocols (TCP/IP, DNS, VPN, etc.)\r\n- Hands-on experience with Cisco hardware and configurations\r\n- Strong analytical and problem-solving skills\r\n- Relevant certifications (e.g., CCNA, CompTIA Network+) are a plus', 6, '2024-12-12 16:39:09', '2025-01-01', 'Cebu City', '50000'),
(15, 'Software QA Tester', 'We are seeking a detail-oriented Software QA Tester to ensure the quality and functionality of our software applications. You will be responsible for creating test plans, conducting manual and automated testing, and collaborating with developers to resolve bugs.', 'Experience in software testing (manual and automated)\r\nFamiliarity with testing tools like Selenium, JIRA, and Postman\r\nKnowledge of SDLC and Agile methodologies\r\nStrong communication skills and teamwork abilities\r\nBachelor’s degree in IT or a related field', 6, '2024-12-12 16:40:28', '2024-12-26', 'Quezon City', '40000');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `voice_message` varchar(255) DEFAULT NULL,
  `seen_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `file_path`, `attachment`, `voice_message`, `seen_at`) VALUES
(79, 7, 6, 'I\'ll follow up my updated resume', '2024-12-12 16:57:16', NULL, 'uploads/1734022636_Yanna_Shin_Resume.pdf', NULL, NULL),
(80, 6, 7, 'For what Job?', '2024-12-12 16:57:50', NULL, NULL, NULL, NULL),
(81, 7, 6, 'Software QA Tester', '2024-12-12 16:58:10', NULL, NULL, NULL, NULL),
(82, 6, 7, 'Got it!', '2024-12-12 16:58:30', NULL, NULL, NULL, NULL),
(83, 7, 6, 'Thank you!! 💖💖', '2024-12-12 16:59:00', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('HR','Applicant') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `work_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `profile_image`, `work_details`, `created_at`) VALUES
(6, 'hrvycstlo', 'John Harvy', 'Castillo', 'johnharvycastillo@gmail.com', '0949 916 5930', '$2y$10$x2N/sa9kepnoRJOWWpupy.DvZZXLJBY0hOV4r9crXQ4dsPYIuKfb6', 'HR', 'uploads/0e1009dd-613f-4377-bd46-dda07daf7ece.jpg', NULL, '2024-12-12 16:34:19'),
(7, 'ynshn', 'Yanna', 'Shin', 'ynshn@gmail.com', '09499165930', '$2y$10$rvdKkkPiONWWuWE0sxU9MebXIAvLvB7xxYUFL5lO0W2X0ObIi2igS', 'Applicant', 'uploads/RobloxScreenShot20241128_191945715.png', NULL, '2024-12-12 16:42:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `applicant_id` (`applicant_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
