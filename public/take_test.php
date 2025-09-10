<?php
session_start();

// --- Core Includes & Access Control ---
require_once __DIR__ . '/../config/config.php';
$pdo = require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$user_test_id = $_GET['id'] ?? null;

if (!$user_test_id || !filter_var($user_test_id, FILTER_VALIDATE_INT)) die("Invalid test ID.");

// --- Fetch Test Data and Verify Access ---
try {
    $stmt = $pdo->prepare("SELECT ut.*, t.test_name, t.duration FROM user_tests ut JOIN tests t ON ut.test_id = t.id WHERE ut.id = ? AND ut.user_id = ?");
    $stmt->execute([$user_test_id, $user_id]);
    $user_test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_test) die("Access Denied: This test is not assigned to you.");
    if ($user_test['status'] === 'completed') die("This test has already been completed.");

    // --- Start Test if Pending ---
    if ($user_test['status'] === 'pending') {
        $start_time = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE user_tests SET status = 'in_progress', start_time = ? WHERE id = ?");
        $stmt->execute([$start_time, $user_test_id]);
        $user_test['start_time'] = $start_time;
    }

    // --- Fetch and Prepare Questions ---
    if (!isset($_SESSION['test_questions'][$user_test_id])) {
        $stmt = $pdo->prepare("SELECT q.* FROM questions q JOIN test_questions tq ON q.id = tq.question_id WHERE tq.test_id = ?");
        $stmt->execute([$user_test['test_id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        shuffle($questions);
        $_SESSION['test_questions'][$user_test_id] = array_values($questions);
        $_SESSION['user_answers'][$user_test_id] = [];
    }
    $questions = $_SESSION['test_questions'][$user_test_id];
    $total_questions = count($questions);
    $current_question_index = isset($_GET['q']) ? (int)$_GET['q'] : 0;

} catch (PDOException $e) { die("Database error: " . $e->getMessage()); }

// --- Handle Answer Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_answer = $_POST['answer'] ?? null;
    $question_id = $_POST['question_id'] ?? null;

    if ($question_id && $submitted_answer !== null) {
        $question_data = $questions[$current_question_index];
        $is_correct = (trim($submitted_answer) === trim($question_data['correct_answer']));

        $sql = "INSERT INTO user_answers (user_test_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE selected_answer = VALUES(selected_answer), is_correct = VALUES(is_correct)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_test_id, $question_id, $submitted_answer, $is_correct]);
        $_SESSION['user_answers'][$user_test_id][$question_id] = $submitted_answer;
    }

    // --- Handle Navigation ---
    if (isset($_POST['next'])) {
        $next_q = $current_question_index + 1;
        if ($next_q < $total_questions) {
            header("Location: take_test.php?id=$user_test_id&q=$next_q");
            exit();
        }
    }
    if (isset($_POST['prev'])) {
        $prev_q = $current_question_index - 1;
        if ($prev_q >= 0) {
            header("Location: take_test.php?id=$user_test_id&q=$prev_q");
            exit();
        }
    }
    // If 'next' was clicked on the last question, it falls through to the finish logic.

    // --- Handle Finish Test ---
    if (isset($_POST['finish']) || isset($_POST['next']) && $current_question_index >= $total_questions - 1) {
        // 1. Calculate score
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_answers WHERE user_test_id = ? AND is_correct = 1");
        $stmt->execute([$user_test_id]);
        $correct_answers = $stmt->fetchColumn();

        $score = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;

        // 2. Update the user_tests table
        $end_time = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE user_tests SET score = ?, status = 'completed', end_time = ? WHERE id = ?");
        $stmt->execute([$score, $end_time, $user_test_id]);

        // 3. Notify admins
        $student_name = $_SESSION['user_name'] ?? 'A student';
        notify_admins($pdo, "Test Completion: '$student_name' has completed the test '{$user_test['test_name']}' with a score of " . number_format($score, 2) . "%.");

        // 4. Clean up session
        unset($_SESSION['test_questions'][$user_test_id]);
        unset($_SESSION['user_answers'][$user_test_id]);

        // 5. Redirect to results page
        header("Location: results.php?id=$user_test_id");
        exit();
    }
}

$current_question = $questions[$current_question_index] ?? null;
if (!$current_question) die("Invalid question number.");
$user_answers = $_SESSION['user_answers'][$user_test_id] ?? [];

// --- Calculate Time Remaining ---
$start_timestamp = strtotime($user_test['start_time']);
$duration_seconds = $user_test['duration'] * 60;
$end_timestamp = $start_timestamp + $duration_seconds;
$time_remaining = $end_timestamp - time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taking Test: <?php echo htmlspecialchars($user_test['test_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f8f9fa; } .test-header { background-color: #fff; padding: 1rem; border-bottom: 1px solid #dee2e6; margin-bottom: 2rem; } .timer { font-size: 1.5rem; font-weight: bold; color: #dc3545; } .question-card { background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); } .question-text { font-size: 1.2rem; margin-bottom: 1.5rem; } .answer-option { margin-bottom: 1rem; } </style>
</head>
<body>
<div class="test-header"><div class="container d-flex justify-content-between align-items-center"><h4 class="mb-0"><?php echo htmlspecialchars($user_test['test_name']); ?></h4><div class="timer" id="timer">--:--</div></div></div>
<div class="container"><div class="row justify-content-center"><div class="col-lg-10">
<div class="question-card">
    <h5>Question <?php echo $current_question_index + 1; ?> of <?php echo $total_questions; ?></h5><hr>
    <div class="question-text"><?php echo $current_question['question_text']; ?></div>
    <form action="take_test.php?id=<?php echo $user_test_id; ?>&q=<?php echo $current_question_index; ?>" method="POST">
        <input type="hidden" name="question_id" value="<?php echo $current_question['id']; ?>">
        <?php $options = json_decode($current_question['options'], true); shuffle($options); ?>
        <?php foreach ($options as $key => $option): ?>
        <div class="form-check answer-option">
            <input class="form-check-input" type="radio" name="answer" id="option_<?php echo $key; ?>" value="<?php echo htmlspecialchars($option); ?>" <?php echo (isset($user_answers[$current_question['id']]) && $user_answers[$current_question['id']] == $option) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="option_<?php echo $key; ?>"><?php echo $option; ?></label>
        </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between">
            <button type="submit" name="prev" class="btn btn-secondary" <?php echo ($current_question_index == 0) ? 'disabled' : ''; ?>>Previous</button>
            <?php if ($current_question_index < $total_questions - 1): ?>
                <button type="submit" name="next" class="btn btn-primary">Next Question</button>
            <?php else: ?>
                <button type="submit" name="finish" class="btn btn-success">Finish Test</button>
            <?php endif; ?>
        </div>
    </form>
</div>
</div></div></div>
<script>
    const timerElement = document.getElementById('timer');
    let timeLeft = <?php echo $time_remaining; ?>;
    const countdown = setInterval(function() {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerElement.textContent = "Time's Up!";
            document.querySelector('form').submit(); // Auto-submit
        } else {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            timeLeft--;
        }
    }, 1000);
</script>
</body>
</html>
