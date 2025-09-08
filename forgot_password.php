<?php
// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize the session
session_start();

// Include database connection and helper files
require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// --- Note: The following lines assume PHPMailer is installed via Composer ---
// You may need to adjust the path based on your installation.
require_once 'vendor/autoload.php';

$email = "";
$message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $message = '<div class="alert alert-danger">Please enter your email address.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Invalid email format.</div>';
    } else {
        // Check if email exists in the users table
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Email exists, generate a token
                $token = bin2hex(random_bytes(50));
                $expires_at = time() + 3600; // 1 hour expiry

                // Store the token in the password_resets table (token should be hashed)
                $hashed_token = password_hash($token, PASSWORD_DEFAULT);

                $sql_insert = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
                if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                    $stmt_insert->bind_param("ssi", $email, $hashed_token, $expires_at);
                    $stmt_insert->execute();
                    $stmt_insert->close();

                    // --- Send the email ---
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;

                    $mail = new PHPMailer(true);
                    try {
                        // Server settings from the database
                        $mail->isSMTP();
                        $mail->Host       = get_app_setting('smtp_host');
                        $mail->SMTPAuth   = true;
                        $mail->Username   = get_app_setting('smtp_user');
                        $mail->Password   = get_app_setting('smtp_pass');
                        $mail->SMTPSecure = get_app_setting('smtp_encryption', PHPMailer::ENCRYPTION_SMTPS);
                        $mail->Port       = get_app_setting('smtp_port', 465);

                        // Recipients
                        $mail->setFrom(get_app_setting('from_email'), get_app_setting('from_name', 'Eflex'));
                        $mail->addAddress($email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body    = 'Hi,<br><br>You requested a password reset. Please click the link below to reset your password. This link is valid for 1 hour.<br><br><a href="' . $reset_link . '">' . $reset_link . '</a><br><br>If you did not request this, please ignore this email.<br><br>Thanks,<br>The Eflex Team';
                        $mail->AltBody = 'To reset your password, please visit the following URL: ' . $reset_link;

                        $mail->send();
                        $message = '<div class="alert alert-success">If an account with that email exists, a password reset link has been sent. Please check your inbox.</div>';
                    } catch (Exception $e) {
                        // In a real app, you would log this error.
                        $message = '<div class="alert alert-danger">Message could not be sent. Please try again later.</div>';
                    }
                }
            } else {
                // To prevent user enumeration, show the same success message even if the email doesn't exist.
                $message = '<div class="alert alert-success">If an account with that email exists, a password reset link has been sent. Please check your inbox.</div>';
            }
            $stmt->close();
        }
    }
}

// Include the header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Forgot Password</h2>
        <p>Please enter your email address to receive a password reset link.</p>

        <?php if (!empty($message)) echo $message; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Send Reset Link">
            </div>
            <p class="mt-3"><a href="login.php">Back to Login</a></p>
        </form>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
