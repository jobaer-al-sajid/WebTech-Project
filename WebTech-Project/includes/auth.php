<?php
require_once __DIR__ . '/config.php';

// Redirect a logged-in user to their own dashboard
function redirect_to_dashboard() {
    if (!isset($_SESSION['role'])) return;
    switch ($_SESSION['role']) {
        case 'admin':    header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit;
        case 'employee': header('Location: ' . BASE_URL . '/employee/dashboard.php'); exit;
        case 'customer': header('Location: ' . BASE_URL . '/customer/dashboard.php'); exit;
    }
}

// Call this at the top of any protected page. Pass the role that is allowed.
function require_role($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function current_user() {
    return [
        'id'    => $_SESSION['user_id']   ?? null,
        'name'  => $_SESSION['user_name'] ?? null,
        'role'  => $_SESSION['role']      ?? null,
        'email' => $_SESSION['email']     ?? null,
    ];
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
