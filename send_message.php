<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$sender_id = $_SESSION['user']['id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$receiver_id || $content === '') {
    die("Data tidak lengkap.");
}

$stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->execute([$sender_id, $receiver_id, $content]);

header("Location: chat.php?user_id=" . $receiver_id);
exit;
