<?php
// includes/functions.php

function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_random_token() {
    return bin2hex(random_bytes(32));
}

function get_user_project_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

function get_user_completed_task_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

function get_user_pending_task_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

function get_user_recent_activity($conn, $user_id, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT 'task' as type, title, created_at
        FROM tasks 
        WHERE user_id = ?
        UNION ALL
        SELECT 'project' as type, name as title, created_at
        FROM projects 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("iii", $user_id, $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $activities = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted_activities = array();
    foreach ($activities as $activity) {
        $formatted_activities[] = array(
            'date' => date('M j, Y', strtotime($activity['created_at'])),
            'description' => ucfirst($activity['type']) . ': ' . $activity['title']
        );
    }

    return $formatted_activities;
}

function attempt_login($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ? AND verified = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password . PASSWORD_PEPPER, $user['password'])) {
            return $user;
        }
    }

    return false;
}
?>