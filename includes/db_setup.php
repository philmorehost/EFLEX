<?php
// This file contains the SQL statements to create the database tables.
// It will be included and executed by db_connect.php to ensure the schema exists.

function setup_database_tables($mysqli) {
    $table_creation_queries = [
        "users" => "CREATE TABLE `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `password` varchar(255) NOT NULL,
          `email` varchar(100) NOT NULL,
          `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "categories" => "CREATE TABLE `categories` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "products" => "CREATE TABLE `products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `category_id` int(11) DEFAULT NULL,
          `name` varchar(255) NOT NULL,
          `description` text NOT NULL,
          `price` decimal(10,2) NOT NULL,
          `image` varchar(255) DEFAULT 'default.jpg',
      `is_featured` tinyint(1) NOT NULL DEFAULT '0',
      `is_top_seller` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `category_id` (`category_id`),
          CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "orders" => "CREATE TABLE `orders` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `total_amount` decimal(10,2) NOT NULL,
      `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
      `payment_method` varchar(50) DEFAULT NULL,
      `payment_proof` varchar(255) DEFAULT NULL,
          `status` varchar(50) NOT NULL DEFAULT 'Pending',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "order_items" => "CREATE TABLE `order_items` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `quantity` int(11) NOT NULL,
          `price` decimal(10,2) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          KEY `product_id` (`product_id`),
          CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
          CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "settings" => "CREATE TABLE `settings` (
      `setting_key` varchar(255) NOT NULL,
      `setting_value` text,
      PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "user_subscriptions" => "CREATE TABLE `user_subscriptions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `order_id` int(11) NOT NULL,
        `status` enum('active','expired') NOT NULL DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `expires_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `product_id` (`product_id`),
        KEY `order_id` (`order_id`),
        CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
        CONSTRAINT `user_subscriptions_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "product_local_files" => "CREATE TABLE `product_local_files` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `filename` varchar(255) NOT NULL,
        `original_filename` varchar(255) NOT NULL,
        `filepath` varchar(255) NOT NULL,
        `mimetype` varchar(100) NOT NULL,
        `filesize` int(11) NOT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `product_local_files_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    // The foreign key constraints require the tables to be created in a specific order.
    // We can ensure this by the order in the array above: users, categories, products, orders, order_items.
    foreach($table_creation_queries as $table_name => $query){
        // Check if table exists
        $result = $mysqli->query("SHOW TABLES LIKE '".$table_name."'");
        if($result->num_rows == 0){
            // Table does not exist, create it
            if(!$mysqli->query($query)){
                 // Handle error - for now, we'll just die.
                 die("Table creation failed for '$table_name': (" . $mysqli->errno . ") " . $mysqli->error);
            }
        }
    }

    // --- Schema Migration Checks ---
    // Check for is_featured column in products table
    $result_featured = $mysqli->query("SHOW COLUMNS FROM `products` LIKE 'is_featured'");
    if($result_featured->num_rows == 0){
        $mysqli->query("ALTER TABLE `products` ADD `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `image`");
    }

    // Check for is_top_seller column in products table
    $result_topseller = $mysqli->query("SHOW COLUMNS FROM `products` LIKE 'is_top_seller'");
    if($result_topseller->num_rows == 0){
        $mysqli->query("ALTER TABLE `products` ADD `is_top_seller` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`");
    }

    // Check for stripe_payment_intent_id column in orders table
    $result_stripe = $mysqli->query("SHOW COLUMNS FROM `orders` LIKE 'stripe_payment_intent_id'");
    if($result_stripe->num_rows == 0){
        $mysqli->query("ALTER TABLE `orders` ADD `stripe_payment_intent_id` VARCHAR(255) DEFAULT NULL AFTER `total_amount`");
    }

    // Check for payment_method column in orders table
    $result_pm = $mysqli->query("SHOW COLUMNS FROM `orders` LIKE 'payment_method'");
    if($result_pm->num_rows == 0){
        $mysqli->query("ALTER TABLE `orders` ADD `payment_method` VARCHAR(50) DEFAULT NULL AFTER `stripe_payment_intent_id`");
    }

    // Check for payment_proof column in orders table
    $result_pp = $mysqli->query("SHOW COLUMNS FROM `orders` LIKE 'payment_proof'");
    if($result_pp->num_rows == 0){
        $mysqli->query("ALTER TABLE `orders` ADD `payment_proof` VARCHAR(255) DEFAULT NULL AFTER `payment_method`");
    }

    // Check for subscription_status column in users table
    $result_ss = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'subscription_status'");
    if($result_ss->num_rows == 0){
        $mysqli->query("ALTER TABLE `users` ADD `subscription_status` ENUM('active','inactive') NOT NULL DEFAULT 'inactive' AFTER `role`");
    }

    // Check for subscription_expiry column in users table
    $result_se = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'subscription_expiry'");
    if($result_se->num_rows == 0){
        $mysqli->query("ALTER TABLE `users` ADD `subscription_expiry` DATE DEFAULT NULL AFTER `subscription_status`");
    }

    // Check for status column in users table
    $result_status = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'status'");
    if($result_status->num_rows == 0){
        $mysqli->query("ALTER TABLE `users` ADD `status` ENUM('active','suspended') NOT NULL DEFAULT 'active' AFTER `role`");
    }

    // Migration: Check for google_drive_folder_id column in products table and remove it if it exists
    if ($mysqli->query("SHOW COLUMNS FROM `products` LIKE 'google_drive_folder_id'")->num_rows > 0) {
        $mysqli->query("ALTER TABLE `products` DROP COLUMN `google_drive_folder_id`");
    }

    // Migration: Drop the old product_google_drive_files table if it exists
    if ($mysqli->query("SHOW TABLES LIKE 'product_google_drive_files'")->num_rows > 0) {
        $mysqli->query("DROP TABLE `product_google_drive_files`");
    }

    // Check for duration_days column in products table
    $result_dd = $mysqli->query("SHOW COLUMNS FROM `products` LIKE 'duration_days'");
    if($result_dd->num_rows == 0){
        $mysqli->query("ALTER TABLE `products` ADD `duration_days` INT(11) DEFAULT 365 AFTER `price`");
    }

    // Since settings are key-value, we don't need to alter the table.
    // We just need to ensure the keys are handled in the admin panel.
    // I will add the UI for these in site_settings.php next.

    // Check if an admin user exists, if not, create a default one.
    $result = $mysqli->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    if($result->num_rows == 0){
        $username = 'admin';
        $email = 'admin@example.com';
        $password = 'password'; // NOTE: User should change this immediately.
        $role = 'admin';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            if($stmt->execute()){
                // Set a session variable to indicate success
                $_SESSION['admin_created'] = true;
            }
            $stmt->close();
        }
    }
}
?>
