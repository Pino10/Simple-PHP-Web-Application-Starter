<?php
require_once 'config.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf_functions.php';

// Rest of your signup code...

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $hashed_password = password_hash($password . PASSWORD_PEPPER, PASSWORD_DEFAULT);
            $verification_code = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, verification_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $hashed_password, $verification_code);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Send verification email
                $to = $email;
                $subject = "Verify Your Account - " . APP_NAME;
                $message = "Hello $fullname,\n\nPlease click the following link to verify your account:\n\n";
                $message .= APP_URL . "/verify.php?code=$verification_code&email=" . urlencode($email);
                $headers = "From: noreply@" . $_SERVER['HTTP_HOST'];

                if (mail($to, $subject, $message, $headers)) {
                    $success = "Registration successful. Please check your email to verify your account.";
                } else {
                    $error = "Registration successful, but failed to send verification email. Please contact support.";
                }
            } else {
                $error = "Error occurred. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Sign Up for <?php echo APP_NAME; ?></h1>
        <?php
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";
        ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>