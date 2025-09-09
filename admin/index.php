<?php
// Initialize the session
session_start();

// Include database connection file
require_once "../includes/db_connect.php";

// If user is already logged in as admin, redirect to admin dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'){
    header("location: dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password, role, role_id FROM users WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if($stmt->execute()){
                $stmt->store_result();

                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $username, $hashed_password, $role, $role_id);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, now verify the role
                            if(in_array($role, ['admin', 'staff'])){
                                // Role is admin or staff, start a new session
                                session_start();

                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;
                                if ($role === 'staff') {
                                    $_SESSION["role_id"] = $role_id;
                                }

                                header("location: dashboard.php");
                            } else {
                                $login_err = "Access Denied. You do not have permission to access this area.";
                            }
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom_style.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .login-form { width: 100%; max-width: 330px; padding: 15px; margin: auto; }
    </style>
</head>
<body>

<main class="login-form text-center">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h1 class="h3 mb-3 fw-normal">Admin Panel Login</h1>

        <?php if(!empty($login_err)){ echo '<div class="alert alert-danger">' . $login_err . '</div>'; } ?>

        <div class="form-floating mb-3">
            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="floatingUsername" placeholder="Username" value="<?php echo $username; ?>" required>
            <label for="floatingUsername">Username</label>
            <span class="invalid-feedback"><?php echo $username_err; ?></span>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="floatingPassword" placeholder="Password" required>
            <label for="floatingPassword">Password</label>
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
        <p class="mt-5 mb-3 text-muted">&copy; 2025 Eflex E-commerce</p>
    </form>
</main>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
