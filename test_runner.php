<?php
// test_runner.php
define('TEST_MODE', true);

// Create a temporary file for the upload
$tmp_name = tempnam(sys_get_temp_dir(), 'test_upload');
copy('test.txt', $tmp_name);

// Simulate the $_FILES superglobal
$_FILES['fileToUpload'] = [
    'name' => 'test.txt',
    'type' => 'text/plain',
    'size' => filesize('test.txt'),
    'tmp_name' => $tmp_name,
    'error' => 0
];

// Simulate the $_POST superglobal with some permissions selected
$_POST['permissions'] = ['print', 'copy', 'modify'];

// Set the request method
$_SERVER['REQUEST_METHOD'] = 'POST';

// Include the convert.php script to process the simulated request
include 'convert.php';

// Clean up the temporary file
unlink($tmp_name);
