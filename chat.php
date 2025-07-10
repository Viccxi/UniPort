<?php
require_once 'auth.php';
require_once 'config.php';

// Debug (matikan di production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$my_id = $_SESSION['user']['id'];

// Validasi parameter user_id
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("User ID tidak valid.");
}

$friend_id = (int) $_GET['user_id'];

// Cek apakah saling follow
$stmt = $db->prepare("
    SELECT COUNT(*) FROM followers f1
    JOIN followers f2 ON f1.follower_id = f2.user_id
    WHERE f1.user_id = :me AND f1.follower_id = :them
      AND f2.follower_id = :me AND f2.user_id = :them
");
$stmt->execute(['me' => $my_id, 'them' => $friend_id]);
$is_friend = $stmt->fetchColumn();

if (!$is_friend) {
    die("Kamu belum saling follow dengan user ini.");
}

// Ambil data user teman
$stmtUser = $db->prepare("SELECT name, last_active FROM users WHERE id = ?");
$stmtUser->execute([$friend_id]);
$friend = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$friend) {
    die("User tidak ditemukan.");
}

// Hitung status online
$last_active_raw = $friend['last_active']; // format asli dari DB
$last_active = strtotime($last_active_raw);
$is_online = (time() - $last_active <= 30);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan <?= htmlspecialchars($friend['name']) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background: #f1f1f1;
        }

        .chat-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 100%;
        }

        .chat-header {
            padding: 15px;
            background: #007BFF;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .status {
            padding: 0 15px 10px;
            font-size: 12px;
            color: #eee;
            background: #007BFF;
        }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #fff;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 10px;
            max-width: 75%;
            word-wrap: break-word;
            clear: both;
            font-size: 14px;
        }

        .from-me {
            background-color: #DCF8C6;
            float: right;
            text-align: right;
        }

        .from-them {
            background-color: #F1F0F0;
            float: left;
            text-align: left;
        }

        .send-form {
            display: flex;
            gap: 10px;
            padding: 10px 15px;
            background: #f9f9f9;
            border-top: 1px solid #ccc;
        }

        .send-form textarea {
            flex: 1;
            resize: none;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            height: 50px;
        }

        .send-form button {
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .clearfix {
            clear: both;
        }

        @media screen and (max-width: 600px) {
            .chat-header {
                font-size: 16px;
            }
            .send-form {
                flex-direction: column;
            }
            .send-form button {
                width: 100%;
            }
        }

        .back-button {
            text-decoration: none;
            color: white;
            font-size: 20px;
            margin-right: 10px;
        }
        .chat-header {
            display: flex;
            align-items: center;
        }

    </style>

</head>
<body>
<div class="chat-wrapper">
    <div class="chat-header">
    <a href="messages.php" class="back-button">←</a>
    <span>Chat dengan <?= htmlspecialchars($friend['name']) ?></span>
</div>

    <div class="status">
        <?= $is_online ? '🟢 Online' : '⏱ Terakhir online ' . date('H:i d M Y', $last_active) ?>
    </div>

    <div id="chat-messages">
        <p>Memuat pesan...</p>
    </div>

    <!-- Sticky di bawah -->
    <form class="send-form" method="POST" action="send_message.php">
        <input type="hidden" name="receiver_id" value="<?= $friend_id ?>">
        <textarea name="content" required placeholder="Tulis pesan..."></textarea>
        <button type="submit">Kirim</button>
    </form>
</div>

<script>
function fetchMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_messages.php?user_id=<?= $friend_id ?>", true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("chat-messages").innerHTML = xhr.responseText;
            document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
        }
    };
    xhr.send();
}

fetchMessages();
setInterval(fetchMessages, 3000);
setInterval(() => {
    fetch("onlineupdate.php");
}, 5000);
</script>
</body>

</html>
