<?php
session_start();
require_once __DIR__ . '/../templates/header.php'; // The header now handles session checks and role fetching.

$pdo = require __DIR__ . '/../config/database.php';

// The user's role is now available in the session from the header
$user_role = $_SESSION['user_role'] ?? '';

if ($user_role === 'User') {
    // --- Student Dashboard View ---
    try {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT ut.id as user_test_id, t.test_name, t.duration, ut.status, es.scheduled_time
                FROM user_tests ut
                JOIN tests t ON ut.test_id = t.id
                LEFT JOIN exam_schedules es ON ut.test_id = es.test_id
                WHERE ut.user_id = ?
                GROUP BY ut.id
                ORDER BY ut.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $assigned_tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: Could not fetch assigned tests. " . $e->getMessage());
    }

    // Include a specific view for the student dashboard
    require_once __DIR__ . '/../templates/dashboard_student.php';

} else {
    // --- Admin/Staff Dashboard View ---
    // For admins, staff, etc., show the administrative dashboard cards.
    require_once __DIR__ . '/../templates/dashboard_cards.php';
}

require_once __DIR__ . '/../templates/footer.php';
?>
