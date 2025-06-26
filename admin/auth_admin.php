<?php
session_start();
require_once("config.php");

if(!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// Ambil data admin terbaru dari database
$stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin']['id']]);
$_SESSION['admin'] = $stmt->fetch(PDO::FETCH_ASSOC);
?>