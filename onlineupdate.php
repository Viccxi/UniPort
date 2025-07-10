<?php
require_once 'auth.php';
require_once 'config.php';
file_put_contents("log/log_online.txt/", date("Y-m-d H:i:s") . " - " . $_SESSION['user']['id'] . "\n", FILE_APPEND);

$stmt = $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);

