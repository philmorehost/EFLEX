<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $test_id = $_POST['test_id'] ?? null;

    $validation_errors = [];
    if (empty($user_id)) $validation_errors[] = 'You must select a user.';
    if (empty($test_id)) $validation_errors[] = 'You must select a test.';

    // Check if this test is already assigned to this user
    if (empty($validation_errors)) {
        $stmt = $pdo->prepare("SELECT id FROM user_tests WHERE user_id = ? AND test_id = ?");
        $stmt->execute([$user_id, $test_id]);
        if ($stmt->fetch()) {
            $validation_errors[] = 'This test has already been assigned to this user.';
        }
    }

    if (empty($validation_errors)) {
        $sql = "INSERT INTO user_tests (user_id, test_id, status) VALUES (?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$user_id, $test_id])) {
            $_SESSION['success'] = 'Test assigned successfully!';
        } else {
            $validation_errors[] = 'Failed to assign the test.';
        }
    }

    if (!empty($validation_errors)) {
        $_SESSION['errors'] = $validation_errors;
    }

    header("Location: assign_test.php");
    exit();
}

// --- Fetch data for dropdowns ---
try {
    // Fetch users (specifically those with the 'User' role)
    $users = $pdo->query("SELECT u.id, u.name, u.email FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name = 'User' ORDER BY u.name ASC")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch all available tests
    $tests = $pdo->query("SELECT id, test_name FROM tests ORDER BY test_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Assign Test to User</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Assignment Form</h6></div>
        <div class="card-body">
            <form action="assign_test.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Choose a user...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="test_id" class="form-label">Select Test</label>
                        <select class="form-select" name="test_id" required>
                            <option value="">Choose a test...</option>
                            <?php foreach ($tests as $test): ?>
                                <option value="<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['test_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Assign Test</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
