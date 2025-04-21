<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect_if_not_logged_in() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function restrict_to_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../index.php'); 
        exit;
    }
}
?>
