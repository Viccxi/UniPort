<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['user']['id']) || !isset($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$post_id = (int)$_POST['post_id'];

// Cek apakah user sudah like post ini
$stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->execute([$user_id, $post_id]);
$liked = $stmt->fetch();

if ($liked) {
    $stmt = $db->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    $status = 'unliked';
} else {
    $stmt = $db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $post_id]);
    $status = 'liked';
}

$stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$like_count = $stmt->fetchColumn();

echo json_encode([
    'status' => $status,
    'like_count' => $like_count
]);