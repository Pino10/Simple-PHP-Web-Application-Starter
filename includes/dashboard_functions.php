<?php
// includes/dashboard_functions.php

function get_user_projects($conn, $user_id, $limit = 5) {
    $stmt = $conn->prepare("SELECT id, name, status FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $projects;
}

function get_user_tasks($conn, $user_id, $limit = 5) {
    $stmt = $conn->prepare("SELECT id, title, status FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $tasks;
}

function get_user_activity_summary($conn, $user_id) {
    $summary = [
        'total_projects' => get_user_project_count($conn, $user_id),
        'total_tasks' => get_user_completed_task_count($conn, $user_id) + get_user_pending_task_count($conn, $user_id),
        'completed_tasks' => get_user_completed_task_count($conn, $user_id),
        'pending_tasks' => get_user_pending_task_count($conn, $user_id)
    ];
    return $summary;
}

function get_user_notifications($conn, $user_id, $limit = 5) {
    $stmt = $conn->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $notifications;
}
?>