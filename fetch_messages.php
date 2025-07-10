<?php
require_once 'auth.php';
require_once 'config.php';

$my_id = $_SESSION['user']['id'];
$friend_id = $_GET['user_id'] ?? 0;

$stmt = $db->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = :me AND receiver_id = :them)
       OR (sender_id = :them AND receiver_id = :me)
    ORDER BY created_at ASC
");
$stmt->execute(['me' => $my_id, 'them' => $friend_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output HTML pesan
foreach ($messages as $msg) {
    $class = $msg['sender_id'] == $my_id ? 'from-me' : 'from-them';
    echo '<div class="message ' . $class . '">';
    echo nl2br(htmlspecialchars($msg['message']));
    echo '<div style="font-size: 10px; color: gray;">' . date('H:i d M Y', strtotime($msg['created_at'])) . '</div>';
    echo '</div><div class="clearfix"></div>';
}
