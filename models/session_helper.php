<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_display_name() {
    if (is_logged_in()) {
        if (!empty($_SESSION['first_name'])) {
            return $_SESSION['first_name'];
        }
        return $_SESSION['username'];
    }
    return "Guest";
}

function get_user_full_name() {
    if (is_logged_in()) {
        $full_name = $_SESSION['first_name'];
        if (!empty($_SESSION['last_name'])) {
            $full_name .= " " . $_SESSION['last_name'];
        }
        return $full_name;
    }
    return "Guest";
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function get_user_role() {
    if (is_logged_in() && isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return 'user';
}

function is_admin() {
    return get_user_role() === 'admin';
}

function is_moderator() {
    return get_user_role() === 'moderator';
}

?>
