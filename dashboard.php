<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$role = get_user_role();
if ($role === 'Administrator') {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
} elseif ($role === 'Staff') {
    header('Location: ' . APP_URL . '/staff/dashboard.php');
    exit;
} else {
    header('Location: ' . APP_URL . '/customer/dashboard.php');
    exit;
}
