-- Base schema for the VTU Website

-- Users table to store user information, credentials, and balance
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `wallet_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `bvn` varchar(11) DEFAULT NULL,
  `nin` varchar(11) DEFAULT NULL,
  `status` enum('active','suspended','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table to log all financial activities
CREATE TABLE `transactions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `service` varchar(50) NOT NULL COMMENT 'e.g., deposit, airtime, data, transfer',
  `transaction_ref` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','successful','failed','reversed') NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'Store extra details like API responses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_ref` (`transaction_ref`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table for storing API keys and other site-wide configuration
CREATE TABLE `settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'e.g., beewave_access_key',
  `value` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-populating settings with placeholders for payment gateways
INSERT INTO `settings` (`name`) VALUES
('payment_beewave_access_key'),
('payment_beewave_secret_key'),
('payment_beewave_encrypt_key'),
('payment_paystack_public_key'),
('payment_paystack_secret_key'),
('payment_flutterwave_public_key'),
('payment_flutterwave_secret_key'),
('vtu_datagifting_api_key');

-- Virtual accounts for user deposits
CREATE TABLE `virtual_accounts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `tracking_ref` varchar(100) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_code` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_ref` (`tracking_ref`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `virtual_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Savings table to hold user savings
CREATE TABLE `savings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loans table for loan applications
CREATE TABLE `loans` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `amount_requested` decimal(15,2) NOT NULL,
  `amount_repaid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','active','repaid','denied') NOT NULL DEFAULT 'pending',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 10.00 COMMENT 'Percentage interest',
  `tenure_days` int(11) NOT NULL DEFAULT 30,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loan repayments table to log individual payments
CREATE TABLE `loan_repayments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `loan_repayments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_repayments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Landing page content table for editable homepage
CREATE TABLE `landing_page_content` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_key` varchar(100) NOT NULL,
  `content_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-populating landing page content with default values
INSERT INTO `landing_page_content` (`content_key`, `content_value`) VALUES
('hero_title', 'The Better, Smarter, and Faster Way To Pay Bills'),
('hero_subtitle', 'Join millions of people who use our platform to pay bills, buy airtime, data, and manage their finances.'),
('services_title', 'Our Awesome Services'),
('services_subtitle', 'We provide you with the best and most affordable services.'),
('why_us_title', 'Why Choose Us?'),
('why_us_item1_title', 'We Are Fast'),
('why_us_item1_text', 'Our services are delivered instantly. No waiting time.'),
('why_us_item2_title', 'We Are Reliable'),
('why_us_item2_text', 'You can count on us for 24/7 service availability.'),
('why_us_item3_title', 'We Are Secure'),
('why_us_item3_text', 'Your transactions and data are always safe with us.');