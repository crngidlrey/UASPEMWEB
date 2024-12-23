<?php
session_start();

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect berdasarkan status login
if (!isLoggedIn()) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
} else {
    // Jika sudah login, redirect ke CMS
    header("Location: cms.php");
    exit();
}
?>