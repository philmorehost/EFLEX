<?php
// This is a simple API endpoint to save a student's answer via AJAX.
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';

// --- Auth Check ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$attempt_id = $input['attempt_id'] ?? null;
$question_id = $input['question_id'] ?? null;
$answer = $input['answer'] ?? null;

if (!$attempt_id || !$question_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    exit;
}

// Verify that the attempt belongs to the current user to prevent cheating
$stmt = $conn->prepare("SELECT user_id FROM test_attempts WHERE attempt_id = ?");
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid attempt.']);
    exit;
}
$attempt_owner = $result->fetch_assoc()['user_id'];
if ($attempt_owner != $_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Authorization error.']);
    exit;
}
$stmt->close();


// --- Save the answer ---
$selected_option_id = is_numeric($answer) ? $answer : null;
$answer_text = !is_numeric($answer) ? $answer : null;

// Use INSERT ... ON DUPLICATE KEY UPDATE to save the answer.
// This requires a UNIQUE key on (attempt_id, question_id) in the student_answers table.
$upsert_sql = "INSERT INTO student_answers (attempt_id, question_id, selected_option_id, answer_text)
               VALUES (?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE selected_option_id = VALUES(selected_option_id), answer_text = VALUES(answer_text)";
$ans_stmt = $conn->prepare($upsert_sql);
$ans_stmt->bind_param("iiis", $attempt_id, $question_id, $selected_option_id, $answer_text);

if ($ans_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Answer saved.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save answer.']);
}

$ans_stmt->close();
?>
