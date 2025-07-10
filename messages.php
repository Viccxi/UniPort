<?php
require_once 'auth.php';
require_once 'config.php';

$my_id = $_SESSION['user']['id'];

// Mark messages as read when user opens chat (you'll need to implement this in chat.php)
// This is just for demonstration - actual implementation should be in chat.php
if (isset($_GET['mark_as_read']) && isset($_GET['sender_id'])) {
    $sender_id = $_GET['sender_id'];
    $update = $db->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
    $update->execute([$my_id, $sender_id]);
}

// Get mutual friends with last message and unread count
$query = "
    SELECT u.id, u.full_name, u.username, u.photo,
           (SELECT message FROM messages 
            WHERE (sender_id = u.id AND receiver_id = :me) 
               OR (sender_id = :me AND receiver_id = u.id)
            ORDER BY created_at DESC LIMIT 1) AS last_message,
           (SELECT created_at FROM messages 
            WHERE (sender_id = u.id AND receiver_id = :me) 
               OR (sender_id = :me AND receiver_id = u.id)
            ORDER BY created_at DESC LIMIT 1) AS last_time,
           (SELECT COUNT(*) FROM messages 
            WHERE sender_id = u.id AND receiver_id = :me AND is_read = 0) AS unread_count
    FROM users u
    WHERE u.id IN (
        SELECT f1.follower_id
        FROM followers f1
        JOIN followers f2 ON f1.follower_id = f2.user_id
        WHERE f1.user_id = :me AND f2.follower_id = :me
    )
    ORDER BY last_time DESC
";
$stmt = $db->prepare($query);
$stmt->execute(['me' => $my_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesan Langsung</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-bg: #ffffff;
      --secondary-bg: #f5f5f5;
      --accent-color: #0088cc;
      --accent-light: #e6f3ff;
      --text-primary: #333333;
      --text-secondary: #707579;
      --unread-badge: #3d8af7;
      --card-shadow: 0 1px 1px rgba(0,0,0,0.08);
      --card-hover: 0 2px 4px rgba(0,0,0,0.1);
    }

    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background-color: var(--secondary-bg);
      color: var(--text-primary);
      padding: 0;
    }

    .container {
      max-width: 420px;
      margin: 0 auto;
      background-color: var(--primary-bg);
      height: 100vh;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .header {
      padding: 16px;
      background-color: var(--primary-bg);
      border-bottom: 1px solid #eaeaea;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .header h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 500;
    }

    .chat-list {
      padding: 8px 0;
    }

    .chat-card {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      position: relative;
      transition: background-color 0.2s;
      cursor: pointer;
    }

    .chat-card:hover {
      background-color: var(--accent-light);
    }

    .chat-card.active {
      background-color: var(--accent-light);
    }

    .chat-card .avatar-container {
      position: relative;
      margin-right: 12px;
    }

    .chat-card .avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .profile-link {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 5;
    }

    .chat-content {
      flex: 1;
      min-width: 0;
      padding-right: 8px;
    }

    .chat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 4px;
    }

    .chat-name {
      font-weight: 500;
      font-size: 16px;
      color: var(--text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .chat-time {
      font-size: 12px;
      color: var(--text-secondary);
      flex-shrink: 0;
      margin-left: 8px;
    }

    .chat-preview {
      display: flex;
      align-items: center;
    }

    .chat-message {
      font-size: 14px;
      color: var(--text-secondary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 220px;
    }

    .unread-badge {
      background-color: var(--unread-badge);
      color: white;
      font-size: 12px;
      font-weight: 500;
      border-radius: 16px;
      min-width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 6px;
      margin-left: auto;
    }

    .action-menu {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background-color: var(--primary-bg);
      border-radius: 4px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
      padding: 8px 0;
      display: none;
      z-index: 20;
    }

    .action-menu.show {
      display: block;
    }

    .action-item {
      padding: 8px 16px;
      font-size: 14px;
      cursor: pointer;
    }

    .action-item:hover {
      background-color: var(--secondary-bg);
    }

    .action-item.delete {
      color: #ff3b30;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-secondary);
    }

    .back-btn {
  font-size: 20px;
  color: var(--accent-color);
  text-decoration: none;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.back-btn:hover {
  background-color: var(--accent-light);
}

  </style>
</head>
<body>

<div class="container">
  <div class="header" style="display: flex; align-items: center; gap: 12px;">
  <a href="timeline.php" class="back-btn">←</a>
  <h2>Pesan Langsung</h2>
</div>


  <div class="chat-list">
    <?php if (count($friends) > 0): ?>
      <?php foreach ($friends as $friend):
        $img = (!empty($friend['photo']) && file_exists("img/uploads/" . $friend['photo']))
            ? "img/uploads/" . $friend['photo']
            : "img/default.svg";
        $last = $friend['last_message'] ?? 'Belum ada pesan';
        $time = $friend['last_time'] ? date('H:i', strtotime($friend['last_time'])) : '';
        $unread = $friend['unread_count'] ?? 0;
      ?>
      <div class="chat-card" onclick="window.location.href='chat.php?user_id=<?= $friend['id'] ?>&mark_as_read=1'">
        <div class="avatar-container">
          <img src="<?= htmlspecialchars($img) ?>" alt="Foto Profil" class="avatar">
          <a href="profilevisit.php?id=<?= $friend['id'] ?>" class="profile-link"></a>
        </div>
        
        <div class="chat-content">
          <div class="chat-header">
            <div class="chat-name"><?= htmlspecialchars($friend['full_name']) ?></div>
            <div class="chat-time"><?= $time ?></div>
          </div>
          
          <div class="chat-preview">
            <div class="chat-message">
              <?= htmlspecialchars($last) ?>
            </div>
            <?php if ($unread > 0): ?>
              <div class="unread-badge"><?= $unread ?></div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="action-menu" id="menu-<?= $friend['id'] ?>">
          <div class="action-item" onclick="archiveChat(<?= $friend['id'] ?>)">Arsipkan</div>
          <div class="action-item delete" onclick="deleteChat(<?= $friend['id'] ?>)">Hapus</div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <p>Belum ada percakapan</p>
        <p>Temukan teman untuk mulai mengobrol</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Function to show context menu
  function showMenu(event, userId) {
    event.stopPropagation();
    event.preventDefault();
    document.querySelectorAll('.action-menu').forEach(menu => {
      menu.classList.remove('show');
    });
    document.getElementById(`menu-${userId}`).classList.add('show');
    return false;
  }

  // Close menus when clicking anywhere
  document.addEventListener('click', function() {
    document.querySelectorAll('.action-menu').forEach(menu => {
      menu.classList.remove('show');
    });
  });

  function archiveChat(userId) {
    // Implement archive functionality
    console.log(`Archiving chat with user ${userId}`);
    // AJAX call to archive chat
    alert(`Percakapan dengan user ${userId} telah diarsipkan`);
    document.querySelectorAll('.action-menu').forEach(menu => {
      menu.classList.remove('show');
    });
  }

  function deleteChat(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus percakapan ini?')) {
      // Implement delete functionality
      console.log(`Deleting chat with user ${userId}`);
      // AJAX call to delete chat
      alert(`Percakapan dengan user ${userId} telah dihapus`);
      document.querySelectorAll('.action-menu').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  }

  // Prevent menu from closing when clicking inside it
  document.querySelectorAll('.action-menu').forEach(menu => {
    menu.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  });

  // Right click handler for context menu
  document.querySelectorAll('.chat-card').forEach(card => {
    card.addEventListener('contextmenu', function(e) {
      e.preventDefault();
      const userId = this.querySelector('.action-menu').id.replace('menu-', '');
      showMenu(e, userId);
      return false;
    });
  });
</script>

</body>
</html>