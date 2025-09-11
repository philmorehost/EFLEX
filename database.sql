-- CBT Platform Database Schema
-- Version 1.0

-- Set SQL mode and timezone
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `roles`
--
CREATE TABLE `roles` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--
INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Super Admin'),
(2, 'Admin'),
(3, 'Staff'),
(4, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--
-- Default Super Admin user
-- Password: superadmin
INSERT INTO `users` (`role_id`, `first_name`, `last_name`, `email`, `password_hash`, `status`) VALUES
(1, 'Super', 'Admin', 'superadmin@cbt.com', '$2y$10$2.A/9.j0d.fE4wO5.C7q.uY3gC7L0q6B/2eK5B/gC3vH6zJ9tO3i', 'active');


--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
