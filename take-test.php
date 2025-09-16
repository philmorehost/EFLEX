<?php
// Check if the config file exists. If not, redirect to the installer.
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: install/');
    exit;
}

$pageTitle = "Take Test";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lib/htmlpurifier/library/HTMLPurifier.standalone.php';

// --- HTML Purifier Setup ---
$purifier_config = HTMLPurifier_Config::createDefault();
$purifier_config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href],ul,ol,li,br,sup,sub,span[style],div[style]');
$purifier_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,text-decoration,color,background-color,text-align,margin,padding,list-style-type');
$purifier = new HTMLPurifier($purifier_config);

// --- Auth Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$test_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$test_id) { header("Location: index.php?error=invalidtest"); exit; }

// --- Fetch User Info (for profile picture) ---
$user_stmt = $conn->prepare("SELECT profile_picture_path FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$profile_pic = $user['profile_picture_path'] ?? 'assets/img/default_avatar.svg';
if (empty($user['profile_picture_path']) || !file_exists(__DIR__ . '/' . $user['profile_picture_path'])) {
    $profile_pic = 'assets/img/default_avatar.svg';
}

// --- Fetch Test Info ---
$test_stmt = $conn->prepare("SELECT * FROM tests WHERE test_id = ?");
$test_stmt->bind_param("i", $test_id);
$test_stmt->execute();
$test = $test_stmt->get_result()->fetch_assoc();
$test_stmt->close();
if (!$test) { header("Location: index.php?error=testnotfound"); exit; }

// --- Check Test Availability ---
$now = time();
if (!empty($test['available_from']) && $now < strtotime($test['available_from'])) {
    header("Location: index.php?error=testnotyetavailable");
    exit;
}
if (!empty($test['available_to']) && $now > strtotime($test['available_to'])) {
    header("Location: index.php?error=testexpired");
    exit;
}

// --- Find or Create Test Attempt ---
$attempt_stmt = $conn->prepare("SELECT * FROM test_attempts WHERE test_id = ? AND user_id = ? AND status = 'in_progress'");
$attempt_stmt->bind_param("ii", $test_id, $user_id);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();
$attempt_stmt->close();

if (!$attempt) {
    $start_time = date('Y-m-d H:i:s');
    $insert_stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_id, start_time) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iis", $test_id, $user_id, $start_time);
    $insert_stmt->execute();
    $attempt_id = $insert_stmt->insert_id;
    $insert_stmt->close();
    $attempt = ['attempt_id' => $attempt_id, 'start_time' => $start_time];
} else {
    $attempt_id = $attempt['attempt_id'];
}

// --- Calculate remaining time on the server side ---
$seconds_remaining = -1; // Default to no time limit
if (!empty($test['time_limit_minutes'])) {
    $start_time_unix = strtotime($attempt['start_time']);
    $end_time_unix = $start_time_unix + ($test['time_limit_minutes'] * 60);
    $now_unix = time();
    $seconds_remaining = $end_time_unix - $now_unix;
    if ($seconds_remaining < 0) $seconds_remaining = 0;
}


// --- Final Submission Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_test'])) {
    // Grading logic from previous implementation
    $total_questions = 0; $correct_answers = 0;
    $correct_answers_sql = "SELECT q.question_id, o.option_id FROM questions q JOIN options o ON q.question_id = o.question_id JOIN test_questions tq ON q.question_id = tq.question_id WHERE tq.test_id = ? AND o.is_correct = 1 AND q.question_type IN ('multiple_choice', 'true_false')";
    $ca_stmt = $conn->prepare($correct_answers_sql);
    $ca_stmt->bind_param("i", $test_id);
    $ca_stmt->execute();
    $correct_answers_map = array_column($ca_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'option_id', 'question_id');
    $ca_stmt->close();

    $student_answers_sql = "SELECT question_id, selected_option_id FROM student_answers WHERE attempt_id = ?";
    $sa_stmt = $conn->prepare($student_answers_sql);
    $sa_stmt->bind_param("i", $attempt_id);
    $sa_stmt->execute();
    $student_answers = array_column($sa_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'selected_option_id', 'question_id');
    $sa_stmt->close();

    foreach ($student_answers as $question_id => $answer) {
        if (isset($correct_answers_map[$question_id])) {
            $total_questions++;
            if ($correct_answers_map[$question_id] == $answer) {
                $correct_answers++;
            }
        }
    }
    $score = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;

    $end_time = date('Y-m-d H:i:s');
    $update_attempt_stmt = $conn->prepare("UPDATE test_attempts SET status = 'completed', end_time = ?, score = ? WHERE attempt_id = ?");
    $update_attempt_stmt->bind_param("sdi", $end_time, $score, $attempt_id);
    $update_attempt_stmt->execute();
    $update_attempt_stmt->close();

    header("Location: index.php?success=test_submitted");
    exit;
}

// --- Fetch all questions and student's previous answers for this attempt ---
$questions_sql = "SELECT q.question_id, q.question_text, q.question_type, sa.selected_option_id, sa.answer_text
                  FROM questions q
                  JOIN test_questions tq ON q.question_id = tq.question_id
                  LEFT JOIN student_answers sa ON q.question_id = sa.question_id AND sa.attempt_id = ?
                  WHERE tq.test_id = ?
                  ORDER BY RAND()";
$q_stmt = $conn->prepare($questions_sql);
$q_stmt->bind_param("ii", $attempt_id, $test_id);
$q_stmt->execute();
$questions = $q_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$q_stmt->close();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <div class="test-info">
            <h3><?php echo htmlspecialchars($test['title']); ?></h3>
        </div>
        <div class="d-flex align-items-center">
            <div class="h3 me-4" id="timer">--:--</div>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
        </div>
    </div>
    <hr>

    <div id="question-display-area">
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-container" id="question-<?php echo $index; ?>" style="display: none;">
                <div class="card">
                    <div class="card-header">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></div>
                    <div class="card-body">
                        <div class="card-text fs-5"><?php echo $purifier->purify($q['question_text']); ?></div>
                        <hr>
                        <div class="options-area" data-question-id="<?php echo $q['question_id']; ?>">
                            <?php if ($q['question_type'] === 'multiple_choice' || $q['question_type'] === 'true_false'): ?>
                                <?php
                                $opt_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
                                $opt_stmt->bind_param("i", $q['question_id']);
                                $opt_stmt->execute();
                                $options = $opt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                $opt_stmt->close();
                                shuffle($options); // Randomize the order of options
                                foreach ($options as $option):
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="answer_<?php echo $q['question_id']; ?>"
                                               value="<?php echo $option['option_id']; ?>"
                                               <?php echo ($q['selected_option_id'] == $option['option_id']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label"><?php echo $purifier->purify($option['option_text']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif ($q['question_type'] === 'short_answer'): ?>
                                <textarea class="form-control" name="answer_<?php echo $q['question_id']; ?>" rows="3"><?php echo htmlspecialchars($q['answer_text'] ?? ''); ?></textarea>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between my-4">
        <button id="prev-btn" class="btn btn-secondary">Previous</button>
        <button id="skip-btn" class="btn btn-warning">Skip Question</button>
        <button id="next-btn" class="btn btn-primary">Next</button>
    </div>

    <div class="card">
        <div class="card-header">Question Map</div>
        <div class="card-body" id="question-map">
            <?php foreach ($questions as $index => $q): ?>
                <button class="btn btn-outline-secondary me-1 mb-1 question-map-btn" data-q-index="<?php echo $index; ?>"><?php echo $index + 1; ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="text-center my-4">
        <button id="finish-btn" class="btn btn-lg btn-success">Finish & Submit Test</button>
        <form id="finish-form" action="take-test.php?id=<?php echo $test_id; ?>" method="POST" style="display:none;"><input type="hidden" name="finish_test" value="1"></form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // State Management
    let currentQuestionIndex = 0;
    const questions = <?php echo json_encode($questions); ?>;
    const totalQuestions = questions.length;
    const attemptId = <?php echo $attempt_id; ?>;
    let answerStates = {}; // 0: unanswered, 1: answered, 2: skipped

    // UI Elements
    const questionContainers = document.querySelectorAll('.question-container');
    const questionMapBtns = document.querySelectorAll('.question-map-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const skipBtn = document.getElementById('skip-btn');
    const finishBtn = document.getElementById('finish-btn');
    const optionsArea = document.getElementById('question-display-area');

    function showQuestion(index) {
        if (index < 0 || index >= totalQuestions) return;

        questionContainers.forEach(q => q.style.display = 'none');
        document.getElementById(`question-${index}`).style.display = 'block';
        currentQuestionIndex = index;
        updateNavButtons();
        updateQuestionMap();
    }

    function updateNavButtons() {
        prevBtn.disabled = currentQuestionIndex === 0;
        nextBtn.disabled = currentQuestionIndex === totalQuestions - 1;
    }

    function updateQuestionMap() {
        questionMapBtns.forEach((btn, index) => {
            btn.classList.remove('btn-primary', 'btn-success', 'btn-warning', 'btn-outline-secondary');
            let stateClass = 'btn-outline-secondary'; // Default: unanswered
            if (answerStates[index] === 1) stateClass = 'btn-success'; // Answered
            if (answerStates[index] === 2) stateClass = 'btn-warning'; // Skipped
            if (index === currentQuestionIndex) stateClass = 'btn-primary'; // Current
            btn.classList.add(stateClass);
        });
    }

    function saveAnswer(questionId, answer) {
        fetch('api/save-answer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                attempt_id: attemptId,
                question_id: questionId,
                answer: answer
            })
        }).then(response => response.json()).then(data => {
            if(data.status === 'success') console.log(`Answer for Q${questionId} saved.`);
        });
    }

    // Event Listeners
    nextBtn.addEventListener('click', () => showQuestion(currentQuestionIndex + 1));
    prevBtn.addEventListener('click', () => showQuestion(currentQuestionIndex - 1));
    skipBtn.addEventListener('click', () => {
        answerStates[currentQuestionIndex] = 2; // Mark as skipped
        showQuestion(currentQuestionIndex + 1);
    });

    questionMapBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const index = parseInt(btn.dataset.qIndex, 10);
            showQuestion(index);
        });
    });

    optionsArea.addEventListener('change', function(e) {
        if (e.target.name.startsWith('answer_')) {
            const questionId = e.target.name.split('_')[1];
            answerStates[currentQuestionIndex] = 1; // Mark as answered
            updateQuestionMap();
            saveAnswer(questionId, e.target.value);
        }
    });

    finishBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to finish and submit your test?')) {
            document.getElementById('finish-form').submit();
        }
    });

    // --- Timer Logic ---
    const timerDisplay = document.getElementById('timer');
    let remainingSeconds = <?php echo $seconds_remaining; ?>;

    if (remainingSeconds >= 0) {
        const timerInterval = setInterval(() => {
            if (remainingSeconds <= 0) {
                clearInterval(timerInterval);
                timerDisplay.innerHTML = "Time's Up!";
                alert("Time is up! Your test will be submitted automatically.");
                document.getElementById('finish-form').submit();
                return;
            }

            remainingSeconds--;

            const hours = Math.floor(remainingSeconds / 3600);
            const minutes = Math.floor((remainingSeconds % 3600) / 60);
            const seconds = remainingSeconds % 60;

            let timerText = ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
            if (hours > 0) {
                timerText = ('0' + hours).slice(-2) + ":" + timerText;
            }
            timerDisplay.innerHTML = timerText;
        }, 1000);
    } else {
        timerDisplay.innerHTML = "No time limit";
    }

    // --- Initial State Setup ---
    questions.forEach((q, index) => {
        if (q.selected_option_id || q.answer_text) {
            answerStates[index] = 1; // Pre-answered
        }
    });
    showQuestion(0); // Show first question
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
