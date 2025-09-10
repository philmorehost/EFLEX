<?php
session_start();

// --- Core Includes & Access Control ---
require_once __DIR__ . '/../config/config.php';
$pdo = require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$user_test_id = $_GET['id'] ?? null;

if (!$user_test_id || !filter_var($user_test_id, FILTER_VALIDATE_INT)) {
    die("Invalid test ID.");
}

// --- Fetch Test Data and Verify Access ---
try {
    $stmt = $pdo->prepare("SELECT ut.*, t.test_name, t.duration FROM user_tests ut JOIN tests t ON ut.test_id = t.id WHERE ut.id = ?");
    $stmt->execute([$user_test_id]);
    $user_test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_test || $user_test['user_id'] != $user_id) {
        die("Access Denied: This test is not assigned to you.");
    }
    if ($user_test['status'] === 'completed') {
        die("This test has already been completed.");
    }

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
        shuffle($questions); // Randomize question order
        $_SESSION['test_questions'][$user_test_id] = $questions;
    }
    $questions = $_SESSION['test_questions'][$user_test_id];
    $total_questions = count($questions);
    $current_question_index = $_GET['q'] ?? 0;
    $current_question = $questions[$current_question_index] ?? null;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

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
    <style>
        body { background-color: #f8f9fa; }
        .test-header { background-color: #fff; padding: 1rem; border-bottom: 1px solid #dee2e6; margin-bottom: 2rem; }
        .timer { font-size: 1.5rem; font-weight: bold; color: #dc3545; }
        .question-card { background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
        .question-text { font-size: 1.2rem; margin-bottom: 1.5rem; }
        .answer-option { margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="test-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><?php echo htmlspecialchars($user_test['test_name']); ?></h4>
        <div class="timer" id="timer">--:--</div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="question-card">
                <h5>Question <?php echo $current_question_index + 1; ?> of <?php echo $total_questions; ?></h5>
                <hr>
                <div class="question-text">
                    <?php echo $current_question['question_text']; // CKEditor content, trust for now ?>
                </div>

                <form action="submit_answer.php" method="POST">
                    <?php
                        $options = json_decode($current_question['options'], true);
                        shuffle($options); // Randomize answer order
                    ?>
                    <?php foreach ($options as $key => $option): ?>
                    <div class="form-check answer-option">
                        <input class="form-check-input" type="radio" name="answer" id="option_<?php echo $key; ?>" value="<?php echo htmlspecialchars($option); ?>">
                        <label class="form-check-label" for="option_<?php echo $key; ?>">
                            <?php echo $option; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" disabled>Previous</button>
                        <button type="submit" class="btn btn-primary">Next Question</button>
                        <button type="button" class="btn btn-success">Finish Test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const timerElement = document.getElementById('timer');
    let timeLeft = <?php echo $time_remaining; ?>;

    const countdown = setInterval(function() {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerElement.textContent = "Time's Up!";
            // Auto-submit form here
            // window.location.href = 'submit_test.php?id=...';
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
