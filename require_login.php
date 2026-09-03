<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('SITE_URL')) {
    // include config to get SITE_URL and DB if not already loaded
    include __DIR__ . '/config.php';
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'part_log.php');
    exit();
}

?>
