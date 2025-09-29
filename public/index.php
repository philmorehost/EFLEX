<?php
/**
 * public/index.php - Front Controller
 *
 * This file is the single entry point for all requests into the application.
 * It initializes the core application logic and starts the router.
 */

// Load the bootstrap file to initialize the application
require_once '../config/bootstrap.php';

// Initialize the Router
$router = new Core\Router();