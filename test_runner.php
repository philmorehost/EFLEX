<?php
// test_runner.php
define('TEST_MODE', true);

// We need to simulate the structure of the $_FILES array for multiple uploads.
$test_files = ['test1.txt', 'test2.txt'];
$_FILES['fileToUpload'] = [
    'name' => [],
    'type' => [],
    'tmp_name' => [],
    'error' => [],
    'size' => []
];

foreach ($test_files as $filename) {
    $tmp_path = tempnam(sys_get_temp_dir(), 'test_upload_');
    copy($filename, $tmp_path);

    $_FILES['fileToUpload']['name'][] = $filename;
    $_FILES['fileToUpload']['type'][] = 'text/plain';
    $_FILES['fileToUpload']['tmp_name'][] = $tmp_path;
    $_FILES['fileToUpload']['error'][] = 0;
    $_FILES['fileToUpload']['size'][] = filesize($filename);
}

// Simulate the $_POST superglobal with some permissions selected
$_POST['permissions'] = ['print', 'copy'];

// Set the request method
$_SERVER['REQUEST_METHOD'] = 'POST';

// Include the convert.php script to process the simulated request
// The output of this will be the zip file content.
include 'convert.php';

// Clean up the temporary files
foreach ($_FILES['fileToUpload']['tmp_name'] as $tmp_name) {
    if (file_exists($tmp_name)) {
        unlink($tmp_name);
    }
}
