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
