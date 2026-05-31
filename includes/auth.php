<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /rongsokhub/index.php');
        exit();
    }
}

function redirectIfNotRole($role) {
    redirectIfNotLoggedIn();
    if (!isRole($role)) {
        header('Location: /rongsokhub/index.php');
        exit();
    }
}

// Alias untuk kompatibilitas
function require_role($role) {
    redirectIfNotRole($role);
}
?>