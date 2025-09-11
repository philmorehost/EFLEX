<?php
$pageTitle = "Take Test";
require_once __DIR__ . '/includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role_id'] != 4) { // User role
    header("Location: index.php?error=permissiondenied");
    exit;
}

$user_id = $_SESSION['user_id'];
$test_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$test_id) {
    header("Location: index.php?error=invalidtest");
    exit;
}

// Fetch test info
$test = $conn->query("SELECT * FROM tests WHERE test_id = $test_id")->fetch_assoc();
if (!$test) {
    header("Location: index.php?error=testnotfound");
    exit;
}

// --- Find or Create Test Attempt ---
$attempt_stmt = $conn->prepare("SELECT * FROM test_attempts WHERE test_id = ? AND user_id = ? AND status = 'in_progress'");
$attempt_stmt->bind_param("ii", $test_id, $user_id);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();
$attempt_stmt->close();

if (!$attempt) {
    // No 'in_progress' attempt found, create a new one
    $start_time = date('Y-m-d H:i:s');
    $insert_stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_id, start_time) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iis", $test_id, $user_id, $start_time);
    $insert_stmt->execute();
    $attempt_id = $insert_stmt->insert_id;
    $insert_stmt->close();
    // Re-fetch the attempt to have a consistent object with start_time
    $attempt = $conn->query("SELECT * FROM test_attempts WHERE attempt_id = $attempt_id")->fetch_assoc();
} else {
    $attempt_id = $attempt['attempt_id'];
}

// --- Fetch all questions for the test ---
$questions_sql = "SELECT q.question_id, q.question_text, q.question_type
                  FROM questions q
                  JOIN test_questions tq ON q.question_id = tq.question_id
                  WHERE tq.test_id = $test_id
                  ORDER BY tq.question_order ASC";
$questions = $conn->query($questions_sql)->fetch_all(MYSQLI_ASSOC);

// --- Handle Answer Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_answers = $_POST['answers'] ?? [];

    // Save answers
    foreach ($submitted_answers as $question_id => $answer) {
        $selected_option_id = is_numeric($answer) ? $answer : null;
        $answer_text = !is_numeric($answer) ? $answer : null;

        // Use INSERT ... ON DUPLICATE KEY UPDATE for efficiency
        $upsert_sql = "INSERT INTO student_answers (attempt_id, question_id, selected_option_id, answer_text)
                       VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE selected_option_id = VALUES(selected_option_id), answer_text = VALUES(answer_text)";
        $ans_stmt = $conn->prepare($upsert_sql);
        $upsert_sql = "INSERT INTO student_answers (attempt_id, question_id, selected_option_id, answer_text)
                       VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE selected_option_id = VALUES(selected_option_id), answer_text = VALUES(answer_text)";
        $ans_stmt = $conn->prepare($upsert_sql);
        $ans_stmt->bind_param("iiis", $attempt_id, $question_id, $selected_option_id, $answer_text);
        $ans_stmt->execute();
        $ans_stmt->close();
    }

    // If "Finish Test" was clicked, perform grading
    if (isset($_POST['finish_test'])) {
        $total_questions = 0;
        $correct_answers = 0;

        // Get all correct answers for this test in one query
        $correct_answers_sql = "SELECT q.question_id, o.option_id
                                FROM questions q
                                JOIN options o ON q.question_id = o.question_id
                                JOIN test_questions tq ON q.question_id = tq.question_id
                                WHERE tq.test_id = ? AND o.is_correct = 1 AND q.question_type IN ('multiple_choice', 'true_false')";
        $ca_stmt = $conn->prepare($correct_answers_sql);
        $ca_stmt->bind_param("i", $test_id);
        $ca_stmt->execute();
        $correct_answers_map = array_column($ca_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'option_id', 'question_id');
        $ca_stmt->close();

        // Grade the submitted answers
        foreach ($submitted_answers as $question_id => $answer) {
            if (isset($correct_answers_map[$question_id])) { // Is gradable
                $total_questions++;
                if ($correct_answers_map[$question_id] == $answer) {
                    $correct_answers++;
                }
            }
        }

        $score = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;

        // Mark attempt as completed and save score
        $end_time = date('Y-m-d H:i:s');
        $update_attempt_stmt = $conn->prepare("UPDATE test_attempts SET status = 'completed', end_time = ?, score = ? WHERE attempt_id = ?");
        $update_attempt_stmt->bind_param("sdi", $end_time, $score, $attempt_id);
        $update_attempt_stmt->execute();
        $update_attempt_stmt->close();

        header("Location: index.php?success=test_submitted");
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h1><?php echo htmlspecialchars($test['title']); ?></h1>
        <div class="h3" id="timer">--:--</div>
    </div>
    <hr>

    <form id="test-form" action="take-test.php?id=<?php echo $test_id; ?>" method="POST">
        <?php foreach ($questions as $index => $q): ?>
            <div class="card mb-4">
                <div class="card-header">
                    Question <?php echo $index + 1; ?>
                </div>
                <div class="card-body">
                    <p class="card-text fs-5"><?php echo nl2br(htmlspecialchars($q['question_text'])); ?></p>
                    <hr>
                    <?php if ($q['question_type'] === 'multiple_choice'): ?>
                        <?php
                        $options_sql = "SELECT * FROM options WHERE question_id = " . $q['question_id'] . " ORDER BY option_id";
                        $options = $conn->query($options_sql)->fetch_all(MYSQLI_ASSOC);
                        foreach ($options as $option):
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answers[<?php echo $q['question_id']; ?>]" id="opt_<?php echo $option['option_id']; ?>" value="<?php echo $option['option_id']; ?>">
                                <label class="form-check-label" for="opt_<?php echo $option['option_id']; ?>">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($q['question_type'] === 'true_false'): ?>
                        <?php
                        $options_sql = "SELECT * FROM options WHERE question_id = " . $q['question_id'] . " ORDER BY option_id";
                        $options = $conn->query($options_sql)->fetch_all(MYSQLI_ASSOC);
                        foreach ($options as $option):
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answers[<?php echo $q['question_id']; ?>]" id="opt_<?php echo $option['option_id']; ?>" value="<?php echo $option['option_id']; ?>">
                                <label class="form-check-label" for="opt_<?php echo $option['option_id']; ?>">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($q['question_type'] === 'short_answer'): ?>
                        <textarea class="form-control" name="answers[<?php echo $q['question_id']; ?>]" rows="3"></textarea>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-grid gap-2">
             <button type="submit" name="finish_test" class="btn btn-lg btn-success" onclick="return confirm('Are you sure you want to finish and submit your test?');">Finish & Submit Test</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerDisplay = document.getElementById('timer');
    const testForm = document.getElementById('test-form');

    // Ensure test and attempt objects are available
    <?php if (isset($test) && isset($attempt)): ?>
        const startTime = new Date('<?php echo $attempt["start_time"]; ?>').getTime();
        const timeLimitMinutes = parseInt('<?php echo $test["time_limit_minutes"]; ?>', 10);

        if (!isNaN(timeLimitMinutes) && timeLimitMinutes > 0) {
            const endTime = startTime + timeLimitMinutes * 60 * 1000;

            const timerInterval = setInterval(function() {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    clearInterval(timerInterval);
                    timerDisplay.innerHTML = "Time's Up!";
                    alert("Time is up! Your test will be submitted automatically.");

                    const autoSubmitInput = document.createElement('input');
                    autoSubmitInput.type = 'hidden';
                    autoSubmitInput.name = 'finish_test';
                    autoSubmitInput.value = 'auto_submitted';
                    testForm.appendChild(autoSubmitInput);

                    testForm.submit();
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let timerText = ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
                if (hours > 0) {
                    timerText = ('0' + hours).slice(-2) + ":" + timerText;
                }
                timerDisplay.innerHTML = timerText;

            }, 1000);
        } else {
            timerDisplay.innerHTML = "No time limit";
        }
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
