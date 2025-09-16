<?php
$pageTitle = "Import Students";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

$errors = [];
$success_message = '';

// --- Form Submission and CSV Processing Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"];

    // Check for errors and file type
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file. Please try again.";
    } else {
        $file_type = mime_content_type($file["tmp_name"]);
        if ($file_type !== 'text/csv' && $file_type !== 'text/plain') {
            $errors[] = "Invalid file type. Please upload a valid CSV file.";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $handle = fopen($file["tmp_name"], "r");
            $header = fgetcsv($handle); // Skip header row
            $row_count = 1;
            $imported_count = 0;

            // Prepare statement for insertion
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role_id, status) VALUES (?, ?, ?, ?, 4, 'active')");

            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_count++;

                // Map data to variables
                $first_name = trim($data[0]);
                $last_name = trim($data[1]);
                $email = trim($data[2]);

                // --- Validation ---
                if (empty($first_name) || empty($last_name) || empty($email)) {
                    throw new Exception("Row $row_count: Missing required data. Each row must have a first name, last name, and email.");
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                     throw new Exception("Row $row_count: Invalid email format for '$email'.");
                }
                // Check if email already exists
                $check_email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $check_email_stmt->bind_param("s", $email);
                $check_email_stmt->execute();
                if ($check_email_stmt->get_result()->num_rows > 0) {
                    throw new Exception("Row $row_count: Email '$email' already exists in the system.");
                }
                $check_email_stmt->close();


                // --- Generate and hash password ---
                $password = bin2hex(random_bytes(8)); // Generate a random 16-char password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // --- Insert User ---
                $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
                $stmt->execute();

                $imported_count++;
            }
            $stmt->close();
            fclose($handle);

            $conn->commit();
            $success_message = "Import successful! $imported_count students have been added. Their passwords have been randomly generated. Please advise them to use the 'Forgot Password' feature to set their own password.";

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred during import: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Import Students from CSV</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Import Failed!</strong>
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">CSV Format Instructions</h5>
                        </div>
                        <div class="card-body">
                            <p>Please ensure your CSV file has a header row with the following columns in this exact order:</p>
                            <p><code>first_name, last_name, email</code></p>
                            <p>Passwords will be automatically and randomly generated for each student upon import. Students should be instructed to use the "Forgot Password" link on the login page to set their own password for the first time.</p>
                            <a href="../assets/csv/sample-student-import.csv" class="btn btn-sm btn-outline-secondary" download>
                                <i class="bi bi-download me-2"></i>Download Sample CSV
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Upload CSV File</h5>
                        </div>
                        <div class="card-body">
                            <form action="import-students.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Select CSV File</label>
                                    <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Upload and Import Students</button>
                                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
