<?php
require_once 'config.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf_functions.php';
require_once __DIR__ . '/includes/rate_limit.php';

// Rest of your login code...

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    if (!check_rate_limit($_SERVER['REMOTE_ADDR'], 'login', 5, 300)) {
        die("Too many login attempts. Please try again later.");
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $user = attempt_login($conn, $email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
            increment_login_attempts($_SERVER['REMOTE_ADDR']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Login to <?php echo APP_NAME; ?></h1>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        <p><a href="reset-password.php">Forgot password?</a></p>
    </div>
</body>
</html>