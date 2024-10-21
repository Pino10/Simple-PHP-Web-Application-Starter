<?php
require_once 'config.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/csrf_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
$stmt = $conn->prepare("SELECT fullname, email, phone, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);

    if (empty($fullname)) {
        $error = "Full name cannot be empty.";
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, bio = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $fullname, $phone, $bio, $user_id);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully.";
            $user['fullname'] = $fullname;
            $user['phone'] = $phone;
            $user['bio'] = $bio;
        } else {
            $error = "Error updating profile. Please try again.";
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Your Profile</h1>
        <?php
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";
        ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <button type="submit">Update Profile</button>
        </form>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
        <p><a href="change-password.php">Change Password</a></p>
    </div>
</body>
</html>