<?php
require_once 'config.php';

require_once __DIR__ . '/includes/dashboard_functions.php';
require_once __DIR__ . '/includes/csrf_functions.php';

// Rest of your dashboard code...

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user statistics (example queries - adjust based on your application's needs)
$total_projects = get_user_project_count($conn, $user_id);
$completed_tasks = get_user_completed_task_count($conn, $user_id);
$pending_tasks = get_user_pending_task_count($conn, $user_id);

// Fetch recent activity
$recent_activity = get_user_recent_activity($conn, $user_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
            <nav>
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <section class="stats">
                <h2>Your Statistics</h2>
                <div class="stat-item">
                    <h3>Total Projects</h3>
                    <p><?php echo $total_projects; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Completed Tasks</h3>
                    <p><?php echo $completed_tasks; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Pending Tasks</h3>
                    <p><?php echo $pending_tasks; ?></p>
                </div>
            </section>
            <section class="recent-activity">
                <h2>Recent Activity</h2>
                <ul>
                    <?php foreach ($recent_activity as $activity): ?>
                    <li>
                        <span class="activity-date"><?php echo htmlspecialchars($activity['date']); ?></span>
                        <span class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>