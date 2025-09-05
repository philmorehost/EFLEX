<?php
// autoloader.php
spl_autoload_register(function ($class) {
    // PSR-4 mapping for Fawno\FPDF
    $prefix = 'Fawno\\FPDF\\';
    $base_dir = __DIR__ . '/FPDF-master/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // PSR-4 mapping for FPDF\Scripts
    $prefix = 'FPDF\\Scripts\\';
    $base_dir = __DIR__ . '/FPDF-master/scripts/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Classmap for FPDF
require_once __DIR__ . '/FPDF-master/fpdf/fpdf.php';
