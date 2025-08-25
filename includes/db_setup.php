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

    "banners" => "CREATE TABLE `banners` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `image_url` varchar(255) NOT NULL,
        `link_url` varchar(255) DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "hero_slides" => "CREATE TABLE `hero_slides` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `type` enum('image','video') NOT NULL DEFAULT 'image',
        `content_url` varchar(255) NOT NULL,
        `title` varchar(255) DEFAULT NULL,
        `description` text,
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `sort_order` int(11) NOT NULL DEFAULT '0',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "product_images" => "CREATE TABLE `product_images` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `image_url` varchar(255) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "roles" => "CREATE TABLE `roles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `role_name` varchar(255) NOT NULL UNIQUE,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "permissions" => "CREATE TABLE `permissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `permission_name` varchar(255) NOT NULL UNIQUE,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "role_permissions" => "CREATE TABLE `role_permissions` (
        `role_id` int(11) NOT NULL,
        `permission_id` int(11) NOT NULL,
        PRIMARY KEY (`role_id`, `permission_id`),
        KEY `role_id` (`role_id`),
        KEY `permission_id` (`permission_id`),
        CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
        CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
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

    // Check for image column in categories table
    $result_cat_img = $mysqli->query("SHOW COLUMNS FROM `categories` LIKE 'image'");
    if($result_cat_img->num_rows == 0){
        $mysqli->query("ALTER TABLE `categories` ADD `image` VARCHAR(255) DEFAULT NULL AFTER `name`");
    }

    // Check for transaction_reference column in orders table
    $result_tr = $mysqli->query("SHOW COLUMNS FROM `orders` LIKE 'transaction_reference'");
    if($result_tr->num_rows == 0){
        $mysqli->query("ALTER TABLE `orders` ADD `transaction_reference` VARCHAR(255) DEFAULT NULL AFTER `payment_proof`");
    }

    // RBAC Migrations
    // Add role_id to users table
    $result_role_id = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'role_id'");
    if($result_role_id->num_rows == 0){
        $mysqli->query("ALTER TABLE `users` ADD `role_id` INT(11) NULL AFTER `email`");
        // Could add a foreign key constraint here, but might be complex with default roles.
    }

    // Drop old role column if role_id exists
    $result_old_role = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'role'");
    if($result_old_role->num_rows > 0 && $result_role_id->num_rows > 0){
        $mysqli->query("ALTER TABLE `users` DROP COLUMN `role`");
    }

    // Check for overlay_color column in hero_slides table
    $result_oc = $mysqli->query("SHOW COLUMNS FROM `hero_slides` LIKE 'overlay_color'");
    if($result_oc->num_rows == 0){
        $mysqli->query("ALTER TABLE `hero_slides` ADD `overlay_color` VARCHAR(10) DEFAULT '#000000' AFTER `description`");
    }

    // Check for overlay_opacity column in hero_slides table
    $result_oo = $mysqli->query("SHOW COLUMNS FROM `hero_slides` LIKE 'overlay_opacity'");
    if($result_oo->num_rows == 0){
        $mysqli->query("ALTER TABLE `hero_slides` ADD `overlay_opacity` DECIMAL(2,1) DEFAULT 0.5 AFTER `overlay_color`");
    }

    // Check for onesignal_player_id column in users table
    $result_osid = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'onesignal_player_id'");
    if($result_osid->num_rows == 0){
        $mysqli->query("ALTER TABLE `users` ADD `onesignal_player_id` VARCHAR(255) NULL DEFAULT NULL AFTER `role_id`");
    }

    // --- Seed Roles and Permissions ---
    $super_admin_role_id = 0;
    $role_result = $mysqli->query("SELECT id FROM roles WHERE role_name = 'Super Admin'");
    if($role_result->num_rows == 0){
        // Create Super Admin Role if it doesn't exist
        $mysqli->query("INSERT INTO roles (role_name) VALUES ('Super Admin')");
        $super_admin_role_id = $mysqli->insert_id;

        // Define and create all permissions
        $permissions = [
            'manage_products', 'manage_categories', 'manage_orders',
            'manage_users', 'manage_site_settings', 'manage_banners',
            'manage_hero_slider', 'manage_roles'
        ];
        $stmt_perm = $mysqli->prepare("INSERT INTO permissions (permission_name) VALUES (?)");
        $stmt_rp = $mysqli->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach($permissions as $p_name){
            // Create permission if it doesn't exist
            $perm_check = $mysqli->query("SELECT id FROM permissions WHERE permission_name = '$p_name'");
            if($perm_check->num_rows == 0){
                $stmt_perm->bind_param("s", $p_name);
                $stmt_perm->execute();
                $permission_id = $mysqli->insert_id;

                // Assign new permission to the Super Admin role
                $stmt_rp->bind_param("ii", $super_admin_role_id, $permission_id);
                $stmt_rp->execute();
            }
        }
        $stmt_perm->close();
        $stmt_rp->close();
    } else {
        $super_admin_role_id = $role_result->fetch_assoc()['id'];
    }

    // --- Create/Update Default Admin User ---
    if($super_admin_role_id > 0){
        $admin_user_result = $mysqli->query("SELECT id, role_id FROM users WHERE username = 'admin'");
        if($admin_user_result->num_rows == 0){
            // Admin user does not exist, create it
            $username = 'admin';
            $email = 'admin@example.com';
            $password = 'password';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_user = "INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)";
            if($stmt_user = $mysqli->prepare($sql_user)){
                $stmt_user->bind_param("sssi", $username, $email, $hashed_password, $super_admin_role_id);
                if($stmt_user->execute()){
                    $_SESSION['admin_created'] = true;
                }
                $stmt_user->close();
            }
        } else {
            // Admin user exists, check if role_id is NULL and update if necessary
            $admin_user = $admin_user_result->fetch_assoc();
            if(is_null($admin_user['role_id'])){
                $admin_user_id = $admin_user['id'];
                $mysqli->query("UPDATE users SET role_id = $super_admin_role_id WHERE id = $admin_user_id");
            }
        }
    }
}
?>
