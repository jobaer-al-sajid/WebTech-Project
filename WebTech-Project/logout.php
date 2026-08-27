<?php

require_once __DIR__ . '/includes/config.php';

// Clear session data
$_SESSION = [];

// Destroy session
session_destroy();

// Go directly to Dental Palace login page
header('Location: /dental_palace/login.php');
exit;