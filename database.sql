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
  `exam_body` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
-- Clear existing categories and insert new ones
DELETE FROM `question_categories`;
INSERT INTO `question_categories` (`category_name`, `exam_body`, `department`, `description`) VALUES
-- WAEC Categories
('English Language', 'WAEC', 'Core', 'WAEC Core English Language'),
('General Mathematics', 'WAEC', 'Core', 'WAEC Core General Mathematics'),
('Biology', 'WAEC', 'Science', 'WAEC Science Department Biology'),
('Chemistry', 'WAEC', 'Science', 'WAEC Science Department Chemistry'),
('Physics', 'WAEC', 'Science', 'WAEC Science Department Physics'),
('Literature-in-English', 'WAEC', 'Arts/Humanities', 'WAEC Arts/Humanities Department Literature'),
('Government', 'WAEC', 'Arts/Humanities', 'WAEC Arts/Humanities Department Government'),
('Economics', 'WAEC', 'Arts/Humanities', 'WAEC Arts/Humanities Department Economics'),
('Financial Accounting', 'WAEC', 'Commercial/Business', 'WAEC Commercial/Business Department Accounting'),
('Commerce', 'WAEC', 'Commercial/Business', 'WAEC Commercial/Business Department Commerce'),
('Technical Drawing', 'WAEC', 'Technical', 'WAEC Technical Department Drawing'),
-- NECO Categories
('English Language', 'NECO', 'Core', 'NECO Core English Language'),
('General Mathematics', 'NECO', 'Core', 'NECO Core General Mathematics'),
('Biology', 'NECO', 'Science', 'NECO Science Department Biology'),
('Chemistry', 'NECO', 'Science', 'NECO Science Department Chemistry'),
('Physics', 'NECO', 'Science', 'NECO Science Department Physics'),
('Literature-in-English', 'NECO', 'Arts/Humanities', 'NECO Arts/Humanities Department Literature'),
('Government', 'NECO', 'Arts/Humanities', 'NECO Arts/Humanities Department Government'),
('Economics', 'NECO', 'Arts/Humanities', 'NECO Arts/Humanities Department Economics'),
-- JAMB Categories
('English Language', 'JAMB', 'Core', 'JAMB Compulsory English Language'),
('Mathematics', 'JAMB', 'Science', 'JAMB Science Department Mathematics'),
('Physics', 'JAMB', 'Science', 'JAMB Science Department Physics'),
('Chemistry', 'JAMB', 'Science', 'JAMB Science Department Chemistry'),
('Biology', 'JAMB', 'Science', 'JAMB Science Department Biology'),
('Economics', 'JAMB', 'Administration and Management', 'JAMB Administration & Management Economics'),
('Literature in English', 'JAMB', 'Arts and Humanities', 'JAMB Arts & Humanities Literature'),
('Government', 'JAMB', 'Arts and Humanities', 'JAMB Arts & Humanities Government');

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

-- Seed questions for JAMB Mathematics
SET @jamb_math_cat_id = (SELECT category_id FROM question_categories WHERE category_name = 'Mathematics' AND exam_body = 'JAMB');
INSERT INTO `questions` (`category_id`, `question_type`, `question_text`, `created_by`) VALUES
(@jamb_math_cat_id, 'multiple_choice', 'If x varies directly as y and x = 5 when y = 20, find the value of x when y = 36.', 1),
(@jamb_math_cat_id, 'multiple_choice', 'Simplify (1/2 + 1/3) / (1/4 - 1/6).', 1),
(@jamb_math_cat_id, 'multiple_choice', 'A car travels at an average speed of 60 km/h. How long does it take to cover a distance of 240 km?', 1);
SET @q1 = LAST_INSERT_ID();
SET @q2 = @q1 + 1;
SET @q3 = @q2 + 1;
INSERT INTO `options` (`question_id`, `option_text`, `is_correct`) VALUES
(@q1, '9', 1), (@q1, '12', 0), (@q1, '15', 0), (@q1, '18', 0),
(@q2, '10', 1), (@q2, '12', 0), (@q2, '5', 0), (@q2, '8', 0),
(@q3, '4 hours', 1), (@q3, '3 hours', 0), (@q3, '5 hours', 0), (@q3, '6 hours', 0);

-- Seed questions for WAEC English
SET @waec_english_cat_id = (SELECT category_id FROM question_categories WHERE category_name = 'English Language' AND exam_body = 'WAEC');
INSERT INTO `questions` (`category_id`, `question_type`, `question_text`, `created_by`) VALUES
(@waec_english_cat_id, 'multiple_choice', 'Choose the word that is nearest in meaning to the underlined word: The man was very `obdurate` in his opinion.', 1),
(@waec_english_cat_id, 'multiple_choice', 'From the words lettered A to D, choose the word that best completes the following sentence: The police are looking for the ____ who broke into the house.', 1);
SET @q4 = LAST_INSERT_ID();
SET @q5 = @q4 + 1;
INSERT INTO `options` (`question_id`, `option_text`, `is_correct`) VALUES
(@q4, 'stubborn', 1), (@q4, 'flexible', 0), (@q4, 'weak', 0), (@q4, 'kind', 0),
(@q5, 'culprit', 1), (@q5, 'victim', 0), (@q5, 'witness', 0), (@q5, 'judge', 0);

-- Seed questions for NECO Biology
SET @neco_bio_cat_id = (SELECT category_id FROM question_categories WHERE category_name = 'Biology' AND exam_body = 'NECO');
INSERT INTO `questions` (`category_id`, `question_type`, `question_text`, `created_by`) VALUES
(@neco_bio_cat_id, 'multiple_choice', 'Which of the following is a characteristic of living things?', 1),
(@neco_bio_cat_id, 'multiple_choice', 'The powerhouse of the cell is the ____.', 1);
SET @q6 = LAST_INSERT_ID();
SET @q7 = @q6 + 1;
INSERT INTO `options` (`question_id`, `option_text`, `is_correct`) VALUES
(@q6, 'Growth', 1), (@q6, 'Hardness', 0), (@q6, 'Color', 0), (@q6, 'Shape', 0),
(@q7, 'Mitochondrion', 1), (@q7, 'Nucleus', 0), (@q7, 'Ribosome', 0), (@q7, 'Chloroplast', 0);

-- Seed questions for JAMB Economics
SET @jamb_econ_cat_id = (SELECT category_id FROM question_categories WHERE category_name = 'Economics' AND exam_body = 'JAMB');
INSERT INTO `questions` (`category_id`, `question_type`, `question_text`, `created_by`) VALUES
(@jamb_econ_cat_id, 'multiple_choice', 'The law of demand states that ____.', 1),
(@jamb_econ_cat_id, 'multiple_choice', 'A major feature of a capitalist economy is ____.', 1);
SET @q8 = LAST_INSERT_ID();
SET @q9 = @q8 + 1;
INSERT INTO `options` (`question_id`, `option_text`, `is_correct`) VALUES
(@q8, 'the higher the price, the lower the quantity demanded', 1), (@q8, 'the higher the price, the higher the quantity demanded', 0), (@q8, 'price and quantity demanded are directly related', 0), (@q8, 'price has no effect on quantity demanded', 0),
(@q9, 'private ownership of means of production', 1), (@q9, 'government control of the economy', 0), (@q9, 'equal distribution of wealth', 0), (@q9, 'absence of a price system', 0);
