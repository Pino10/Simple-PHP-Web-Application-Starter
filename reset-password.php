<?php
require_once 'config.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    if (isset($_POST['email'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND verified = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $update_stmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token_hash, $expires, $user['id']);
            
            if ($update_stmt->execute()) {
                $reset_link = APP_URL . "/reset-password.php?token=" . $token;
                $to = $email;
                $subject = "Password Reset Request - " . APP_NAME;
                $message = "Hello,\n\nYou have requested to reset your password. Click the link below to reset your password:\n\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.";
                $headers = "From: noreply@" . $_SERVER['HTTP_HOST'];

                if (mail($to, $subject, $message, $headers)) {
                    $success = "A password reset link has been sent to your email.";
                } else {
                    $error = "Failed to send password reset email. Please try again later.";
                }
            } else {
                $error = "An error occurred. Please try again later.";
            }
            $update_stmt->close();
        } else {
            $success = "If the email exists in our system, a password reset link will be sent.";
        }
        $stmt->close();
    } elseif (isset($_POST['new_password']) && isset($_POST['token'])) {
        $new_password = $_POST['new_password'];
        $token = $_POST['token'];
        $token_hash = hash('sha256', $token);

        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token_hash = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password . PASSWORD_PEPPER, PASSWORD_DEFAULT);

            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires = NULL WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user['id']);
            
            if ($update_stmt->execute()) {
                $success = "Your password has been reset successfully. You can now login with your new password.";
            } else {
                $error = "An error occurred while resetting your password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error = "Invalid or expired reset token.";
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
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <?php
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";

        if (!isset($_GET['token'])) {
        ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Request Password Reset</button>
        </form>
        <?php
        } elseif (isset($_GET['token'])) {
        ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
        <?php
        }
        ?>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>