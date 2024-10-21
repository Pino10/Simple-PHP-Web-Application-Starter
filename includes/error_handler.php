<?php
// includes/error_handler.php

function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = date("Y-m-d H:i:s") . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, __DIR__ . "/../logs/error.log");

    if (ini_get("display_errors")) {
        printf("<pre>%s</pre>\n", htmlspecialchars($error_message));
    } else {
        echo "An error occurred. Please try again later or contact support.";
    }
}

set_error_handler("custom_error_handler");

function log_activity($user_id, $action) {
    $log_message = date("Y-m-d H:i:s") . " - User ID: $user_id - Action: $action\n";
    error_log($log_message, 3, __DIR__ . "/../logs/activity.log");
}
?>