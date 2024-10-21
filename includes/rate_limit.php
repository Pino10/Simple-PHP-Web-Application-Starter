<?php
// includes/rate_limit.php

function check_rate_limit($ip, $action, $max_attempts = 5, $time_period = 300) {
    $file = sys_get_temp_dir() . "/rate_limit_{$action}_{$ip}.php";
    
    if (file_exists($file)) {
        $data = include $file;
        if (time() - $data['timestamp'] > $time_period) {
            $data = ['count' => 1, 'timestamp' => time()];
        } else {
            $data['count']++;
        }
    } else {
        $data = ['count' => 1, 'timestamp' => time()];
    }
    
    file_put_contents($file, '<?php return ' . var_export($data, true) . ';');
    
    return $data['count'] <= $max_attempts;
}

function increment_login_attempts($ip) {
    check_rate_limit($ip, 'login');
}

function reset_login_attempts($ip) {
    $file = sys_get_temp_dir() . "/rate_limit_login_{$ip}.php";
    if (file_exists($file)) {
        unlink($file);
    }
}
?>