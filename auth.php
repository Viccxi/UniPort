<?php
session_start();
require_once("config.php");

if(!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Pastikan data user lengkap dengan mengambil dari database
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
?>