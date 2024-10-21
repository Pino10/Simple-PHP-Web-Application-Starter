<?php
require_once 'config.php';

$error = '';
$success = '';

if (isset($_GET['code']) && isset($_GET['email'])) {
    $verification_code = $_GET['code'];
    $email = $_GET['email'];

    $stmt = $conn->prepare("SELECT id, verified FROM users WHERE email = ? AND verification_code = ?");
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['verified'] == 0) {
            $update_stmt = $conn->prepare("UPDATE users SET verified = 1, verification_code = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            
            if ($update_stmt->execute()) {
                $success = "Your email has been verified successfully. You can now login.";
            } else {
                $error = "Error verifying email. Please try again or contact support.";
            }
            $update_stmt->close();
        } else {
            $success = "Your email has already been verified. You can login.";
        }
    } else {
        $error = "Invalid verification code or email.";
    }
    $stmt->close();
} else {
    $error = "Invalid verification link.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Email Verification</h1>
        <?php
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";
        ?>
        <p><a href="login.php">Go to Login</a></p>
    </div>
</body>
</html>