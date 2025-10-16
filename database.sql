-- CBT Platform Database Schema
-- Version 1.5

-- (Omitting previous tables for brevity)
-- ...

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
INSERT INTO `roles` (`role_id`, `role_name`) VALUES (2,'Admin'),(4,'User'),(3,'Staff'),(1,'Super Admin');

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended','pending') NOT NULL DEFAULT 'pending',
  `profile_picture_path` varchar(255) DEFAULT NULL,
  `address` text,
  `phone` varchar(50) DEFAULT NULL,
  `sex` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
INSERT INTO `users` (`user_id`, `role_id`, `first_name`, `last_name`, `email`, `password_hash`, `status`, `created_at`, `updated_at`) VALUES (1,1,'Super','Admin','superadmin@cbt.com','$2y$10$2.A/9.j0d.fE4wO5.C7q.uY3gC7L0q6B/2eK5B/gC3vH6zJ9tO3i','active','2025-09-11 07:13:32','2025-09-11 07:13:32');

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` int(11) NOT NULL,
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `token` (`token`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `permission_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
INSERT INTO `permission_categories` (`category_id`, `category_name`) VALUES (1,'User Management'),(2,'Role Management'),(3,'Test Management'),(4,'System Settings');

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) NOT NULL,
  `description` text,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_name` (`permission_name`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
INSERT INTO `permissions` (`permission_id`, `permission_name`, `description`, `category_id`) VALUES (1,'view_users','Can view the list of users.',1),(2,'create_users','Can add new users.',1),(3,'edit_users','Can edit existing users.',1),(4,'delete_users','Can delete users.',1),(5,'view_roles','Can view the list of roles and their permissions.',2),(6,'create_roles','Can create new roles.',2),(7,'edit_roles','Can edit existing roles and assign permissions.',2),(8,'delete_roles','Can delete roles.',2),(9,'view_tests','Can view tests.',3),(10,'create_tests','Can create new tests and questions.',3),(11,'edit_tests','Can edit existing tests.',3),(12,'delete_tests','Can delete tests.',3),(13,'view_settings','Can view system settings.',4),(14,'edit_settings','Can change system settings.',4);

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(2,1),(2,2),(2,3),(2,4),(2,9),(2,10),(2,11),(2,12),(3,9),(3,10),(3,11);

CREATE TABLE `question_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
INSERT INTO `question_categories` (`category_id`, `category_name`, `description`) VALUES (1,'General Knowledge','A variety of general topics.'),(2,'Mathematics','Questions related to mathematical concepts.'),(3,'History','Questions about historical events and figures.');

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') NOT NULL,
  `question_text` text NOT NULL,
  `model_answer` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tests` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `time_limit_minutes` int(11) DEFAULT NULL,
  `available_from` datetime DEFAULT NULL,
  `available_to` datetime DEFAULT NULL,
  `passing_score` int(11) NOT NULL DEFAULT '70',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`test_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `test_questions` (
  `test_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_order` int(11) NOT NULL,
  PRIMARY KEY (`test_id`,`question_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `test_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  PRIMARY KEY (`attempt_id`),
  KEY `test_id` (`test_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `student_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text,
  `score` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `attempt_question_unique` (`attempt_id`,`question_id`),
  KEY `question_id` (`question_id`),
  KEY `selected_option_id` (`selected_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'CBT Platform'),
('smtp_host', 'smtp.example.com'),
('smtp_port', '587'),
('smtp_user', 'user@example.com'),
('smtp_pass', ''),
('smtp_secure', 'tls');

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `permissions` ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `permission_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `question_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `options` ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tests` ADD CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `test_questions`
  ADD CONSTRAINT `test_questions_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `test_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `test_attempts`
  ADD CONSTRAINT `test_attempts_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `test_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `test_attempts` (`attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `options` (`option_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add new question categories
INSERT INTO `question_categories` (`category_name`, `description`) VALUES
('JAMB Mathematics', 'JAMB Mathematics Past Questions'),
('WAEC Mathematics', 'WAEC Mathematics Past Questions'),
('NECO Mathematics', 'NECO Mathematics Past Questions'),
('JAMB English', 'JAMB English Language Past Questions'),
('WAEC English', 'WAEC English Language Past Questions'),
('NECO English', 'NECO English Language Past Questions'),
('JAMB Biology', 'JAMB Biology Past Questions'),
('WAEC Biology', 'WAEC Biology Past Questions'),
('NECO Biology', 'NECO Biology Past Questions');

-- Seed questions for JAMB Mathematics
-- (Assuming category_id for 'JAMB Mathematics' is 4)
INSERT INTO `questions` (`category_id`, `question_type`, `question_text`, `created_by`) VALUES
(4, 'multiple_choice', 'If x varies directly as y and x = 5 when y = 20, find the value of x when y = 36.', 1),
(4, 'multiple_choice', 'Simplify (1/2 + 1/3) / (1/4 - 1/6).', 1),
(4, 'multiple_choice', 'A car travels at an average speed of 60 km/h. How long does it take to cover a distance of 240 km?', 1),
(4, 'multiple_choice', 'Find the simple interest on N5000 for 3 years at a rate of 5% per annum.', 1),
(4, 'multiple_choice', 'The angles of a triangle are in the ratio 2:3:4. Find the size of the smallest angle.', 1);

-- Get the last inserted question IDs
SET @q1 = LAST_INSERT_ID();
SET @q2 = @q1 + 1;
SET @q3 = @q2 + 1;
SET @q4 = @q3 + 1;
SET @q5 = @q4 + 1;

-- Add options for the questions
INSERT INTO `options` (`question_id`, `option_text`, `is_correct`) VALUES
(@q1, '9', 1), (@q1, '12', 0), (@q1, '15', 0), (@q1, '18', 0),
(@q2, '10', 1), (@q2, '12', 0), (@q2, '5', 0), (@q2, '8', 0),
(@q3, '4 hours', 1), (@q3, '3 hours', 0), (@q3, '5 hours', 0), (@q3, '6 hours', 0),
(@q4, 'N750', 1), (@q4, 'N500', 0), (@q4, 'N1000', 0), (@q4, 'N250', 0),
(@q5, '40 degrees', 1), (@q5, '60 degrees', 0), (@q5, '80 degrees', 0), (@q5, '20 degrees', 0);

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_content`
--

CREATE TABLE `landing_page_content` (
  `section_name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `landing_page_content`
--

INSERT INTO `landing_page_content` (`section_name`, `content`) VALUES
('hero_title', 'Ace Your WAEC, NECO & JAMB Exams with Confidence'),
('hero_subtitle', 'Your ultimate CBT practice platform for guaranteed success. Prepare with thousands of past questions and detailed solutions.'),
('testimonial_1_name', 'Adekunle Adebayo'),
('testimonial_1_school', 'University of Lagos'),
('testimonial_1_image', 'assets/img/student1.jpg'),
('testimonial_1_quote', 'This platform was a game-changer for my JAMB preparation. The mock tests were incredibly similar to the real exam.'),
('testimonial_2_name', 'Chiamaka Nwosu'),
('testimonial_2_school', 'University of Nigeria, Nsukka'),
('testimonial_2_image', 'assets/img/student2.jpg'),
('testimonial_2_quote', 'I passed my WAEC exams with flying colors, all thanks to the detailed resources and practice questions available here.'),
('testimonial_3_name', 'Fatima Bello'),
('testimonial_3_school', 'Ahmadu Bello University'),
('testimonial_3_image', 'assets/img/student3.jpg'),
('testimonial_3_quote', 'The NECO past questions were so helpful. I felt confident and prepared on the exam day. I highly recommend this to every student.');
