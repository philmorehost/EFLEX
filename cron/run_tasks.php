#!/usr/bin/env php
<?php
// cron/run_tasks.php

// This script is meant to be run from the command line via a cron job.
// Example cron job:
// * * * * * /usr/bin/php /path/to/your/project/cron/run_tasks.php >> /path/to/your/project/logs/cron.log 2>&1

echo "Cron task started at " . date('Y-m-d H:i:s') . "\n";

// We need to define ROOT_PATH manually because we are not in the public directory.
define('ROOT_PATH', dirname(__DIR__));

// Include database configuration
$pdo = require ROOT_PATH . '/config/database.php';

if (!$pdo) {
    echo "Failed to connect to the database.\n";
    exit(1);
}

try {
    // Find scheduled exams that are due and still pending
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id, test_id FROM exam_schedules WHERE scheduled_time <= ? AND status = 'pending'");
    $stmt->execute([$now]);
    $due_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($due_schedules)) {
        echo "No pending schedules are due.\n";
        exit(0);
    }

    echo "Found " . count($due_schedules) . " due schedule(s) to process.\n";

    // Update the status of each due schedule to 'executed'
    $update_stmt = $pdo->prepare("UPDATE exam_schedules SET status = 'executed' WHERE id = ?");

    foreach ($due_schedules as $schedule) {
        if ($update_stmt->execute([$schedule['id']])) {
            echo "  - Marked schedule ID " . $schedule['id'] . " (Test ID: " . $schedule['test_id'] . ") as executed.\n";
            // In a real application, you might trigger emails or other notifications here.
        } else {
            echo "  - Failed to update schedule ID " . $schedule['id'] . ".\n";
        }
    }

    echo "Cron task finished successfully.\n";

} catch (PDOException $e) {
    echo "Database error during cron task: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
?>
