<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    session_destroy();
    header("Location: login_admin.php");
    exit;
} else {
    session_destroy(); 
    header("Location: login.php");
    exit;
}
?>

