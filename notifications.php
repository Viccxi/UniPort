<?php
require_once 'auth.php';
require_once 'config.php';

$my_id = $_SESSION['user']['id'];

$stmt = $db->prepare("
    SELECT n.*, 
           u.full_name AS from_name,
           u.photo AS from_photo,
           c.content AS comment_content,
           c.created_at AS comment_time,
           c.user_id AS commenter_id,
           n.type
    FROM notifications n
    LEFT JOIN users u ON n.from_user_id = u.id
    LEFT JOIN comments c ON n.comment_id = c.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$my_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notif-avatar {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 12px;
        }
        .notif-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 16px;
            border-bottom: 1px solid #eaeaea;
            transition: background-color 0.2s;
        }
        .notif-item:hover {
            background-color: #f8f9fa;
        }
        .notif-content {
            flex: 1;
        }
        .notif-time {
            font-size: 0.8rem;
            color: #888;
            margin-top: 4px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <h3 class="mb-4">🔔 Notifikasi</h3>

    <div class="bg-white rounded shadow-sm">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                    $photo = (!empty($notif['from_photo']) && file_exists("img/uploads/" . $notif['from_photo']))
                        ? "img/uploads/" . $notif['from_photo']
                        : "img/default.svg";

                    $name = htmlspecialchars($notif['from_name']);
                    $time = $notif['comment_time'] ?? $notif['created_at'];
                    $formatted_time = date('d M Y, H:i', strtotime($time));

                    $message = '';
                    if ($notif['type'] === 'comment') {
                        $content_preview = htmlspecialchars(mb_strimwidth($notif['comment_content'], 0, 80, '...'));
                        $message = "$name mengomentari postingan Anda: <br><em>\"$content_preview\"</em>";
                    } elseif ($notif['type'] === 'like') {
                        $message = "$name menyukai postingan Anda.";
                    } else {
                        $message = "$name melakukan sesuatu pada postingan Anda.";
                    }
                ?>
                <div class="notif-item">
                    <a href="profilevisit.php?id=<?= $notif['from_user_id'] ?>">
                        <img src="<?= $photo ?>" alt="Profil" class="notif-avatar">
                    </a>
                    <div class="notif-content">
                        <div class="text-dark">
                            <?= $notif['type'] === 'comment' ? (
                                '<a href="profilevisit.php?id=' . $notif['from_user_id'] . '" class="fw-semibold text-dark text-decoration-none">'
                                . $name . '</a> mengomentari postingan Anda: <br><a href="timeline.php#post-' . $notif['post_id'] . '" class="text-muted text-decoration-none"><em>"' . $content_preview . '"</em></a>'
                            ) : (
                                '<a href="profilevisit.php?id=' . $notif['from_user_id'] . '" class="fw-semibold text-dark text-decoration-none">'
                                . $name . '</a> menyukai postingan Anda.'
                            ) ?>
                        </div>
                        <div class="notif-time"><?= $formatted_time ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-4 text-center text-muted">Belum ada notifikasi</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
