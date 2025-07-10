<?php
require_once("auth.php");
require_once("config.php");

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($profile_id <= 0) die("User tidak ditemukan.");

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User tidak ditemukan.");

$is_own_profile = $profile_id == $_SESSION['user']['id'];

// Cek apakah saya follow dia
$stmt = $db->prepare("SELECT * FROM followers WHERE user_id = ? AND follower_id = ? AND status='accepted'");
$stmt->execute([$profile_id, $_SESSION['user']['id']]);
$is_following = $stmt->fetch();

// Cek apakah dia follow saya
$stmt = $db->prepare("SELECT * FROM followers WHERE user_id = ? AND follower_id = ? AND status='accepted'");
$stmt->execute([$_SESSION['user']['id'], $profile_id]);
$is_followed_by = $stmt->fetch();

// Status teman jika saling follow
$is_friend = $is_following && $is_followed_by;

$stmt = $db->prepare("SELECT COUNT(*) FROM followers WHERE user_id=? AND status='accepted'");
$stmt->execute([$profile_id]);
$followers_count = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM followers WHERE follower_id=? AND status='accepted'");
$stmt->execute([$profile_id]);
$following_count = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['follow'])) {
    $stmt = $db->prepare("INSERT IGNORE INTO followers (user_id, follower_id, status) VALUES (?, ?, 'accepted')");
    $stmt->execute([$profile_id, $_SESSION['user']['id']]);
    header("Location: profilevisit.php?id=$profile_id");
    exit;
}
if(isset($_POST['unfollow'])) {
    $stmt = $db->prepare("DELETE FROM followers WHERE user_id = ? AND follower_id = ?");
    $stmt->execute([$profile_id, $_SESSION['user']['id']]);
    header("Location: profilevisit.php?id=$profile_id");
    exit;
}

// Query portfolios SEKALI SAJA sebelum HTML
$stmt = $db->prepare("SELECT * FROM portfolios WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_id]);
$portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil teman (saling follow/accepted)
$stmt = $db->prepare("SELECT u.id, u.name, u.photo FROM users u
    INNER JOIN followers f1 ON f1.user_id = u.id AND f1.follower_id = ?
    INNER JOIN followers f2 ON f2.user_id = ? AND f2.follower_id = u.id
    WHERE f1.status='accepted' AND f2.status='accepted' AND u.id != ?");
$stmt->execute([$profile_id, $profile_id, $profile_id]);
$friends_preview = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_friends = count($friends_preview);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kunjungan Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .profile-header {
            margin-top: 32px;
            margin-bottom: 24px;
        }
        .profile-avatar {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            background: #f5f5f5;
        }
        .profile-action {
            margin-top: 18px;
        }
        @media (max-width: 576px) {
            .profile-header {
                margin-top: 16px;
                margin-bottom: 16px;
            }
            .profile-avatar {
                width: 90px;
                height: 90px;
            }
        }
        .like-btn.btn-blue,
        .like-btn.btn-blue:focus,
        .like-btn.btn-blue:active {
            background: #e7f1ff !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
        .like-btn.btn-outline-secondary:hover,
        .like-btn.btn-outline-secondary:focus {
            color: #0d6efd !important;
            background: #e7f1ff !important;
            border-color: #0d6efd !important;
        }
        .avatar-stack {
            display: flex;
            position: relative;
            height: 32px;
            min-width: 60px;
        }
        .avatar-stack .avatar-img {
            position: absolute;
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 3px rgba(0,0,0,0.2);
            transition: z-index 0.2s;
        }
        .avatar-stack .avatar-img:hover {
            z-index: 10;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="assets/logouniport.png" alt="Logo Uniport" style="height:38px; margin-right:10px;">
            <span style="font-weight:700; font-size:1.8rem; color:#70757B;">UniPort</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="mx-auto" style="width:320px;max-width:100%;" action="search.php" method="get" role="search">
                <div class="input-group">
                    <input class="form-control form-control-sm text-center" type="search" name="q" placeholder="Search" aria-label="Search" style="min-width:0;">
                    <button class="btn btn-outline-primary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF'])=='timeline.php') ? 'active text-danger fw-bold' : ''; ?>" href="timeline.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php"><i class="fas fa-bell me-1"></i> Notifications</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="messages.php"><i class="fas fa-envelope me-1"></i> Messages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Profile Header -->
    <div class="profile-header text-center">
        <?php
        $photo_file = !empty($user['photo']) ? "img/" . $user['photo'] : "uploads/default.svg";
        if(!file_exists($photo_file)) $photo_file = "uploads/default.svg";
        if (!empty($user['photo']) && $user['photo'] !== 'default.svg' && file_exists("img/uploads/" . $user['photo'])) {
            $photo_file = "img/uploads/" . $user['photo'];
        } else {
            $photo_file = "img/default.svg";
        }
        ?>
        <img src="<?php echo $photo_file ?>" class="profile-avatar mb-2" alt="Foto Profil">
        <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
        <div class="d-flex justify-content-center gap-4 mb-2">
            <div>
                <span class="fw-bold"><?php echo $followers_count; ?></span><br>
                <small class="text-muted">Followers</small>
            </div>
            <div>
                <span class="fw-bold"><?php echo $following_count; ?></span><br>
                <small class="text-muted">Following</small>
            </div>
        </div>
        <div class="profile-action">
            <?php if(!$is_own_profile): ?>
                <?php if(!$is_following && !$is_followed_by): ?>
                    <!-- Belum saling follow -->
                    <form method="post" style="display:inline">
                        <button name="follow" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Follow
                        </button>
                    </form>
                <?php elseif(!$is_following && $is_followed_by): ?>
                    <!-- User lain sudah follow kita, kita belum follow balik -->
                    <form method="post" style="display:inline">
                        <button name="follow" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Follow Back
                        </button>
                    </form>
                <?php elseif($is_following && !$is_followed_by): ?>
                    <!-- Kita sudah follow dia, dia belum follow kita -->
                    <form method="post" style="display:inline">
                        <button name="unfollow" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-check me-1"></i> Followed
                        </button>
                    </form>
                <?php elseif($is_friend): ?>
                    <!-- Saling follow (Teman) -->
                    <div class="dropdown d-inline">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-friends me-1"></i> Teman
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form method="post" style="display:inline">
                                    <button name="unfollow" class="dropdown-item text-danger" type="submit">
                                        <i class="fas fa-user-minus me-1"></i> Unfollow
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Stack avatar + deskripsi teman -->
        <?php if ($total_friends > 0): ?>
        <div class="d-flex flex-column align-items-center mt-3">
            <div class="avatar-stack position-relative mb-2" style="width: 80px;">
                <?php foreach(array_slice($friends_preview, 0, 3) as $index => $friend): ?>
                    <?php
                        $friend_photo = (!empty($friend['photo']) && $friend['photo'] !== 'default.svg' && file_exists("img/uploads/" . $friend['photo']))
                            ? "img/uploads/" . $friend['photo']
                            : "img/default.svg";
                    ?>
                    <a href="profilevisit.php?id=<?php echo $friend['id']; ?>">
                        <img src="<?php echo $friend_photo; ?>" 
                             class="rounded-circle avatar-img"
                             style="left: <?php echo $index * 20; ?>px;"
                             alt="<?php echo htmlspecialchars($friend['name']); ?>">
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="text-muted small text-center">
                Berteman dengan 
                <strong>
                    <?php
                        $names = array_column($friends_preview, 'name');
                        $ids = array_column($friends_preview, 'id');
                        $display = [];
                        for ($i = 0; $i < min(2, count($names)); $i++) {
                            $display[] = '<a href="profilevisit.php?id=' . $ids[$i] . '" class="text-decoration-none">' . htmlspecialchars($names[$i]) . '</a>';
                        }
                        echo implode(', ', $display);
                        if ($total_friends > 3) {
                            $others = $total_friends - 3;
                            echo ', dan <a href="friends.php?id=' . $profile_id . '" class="text-decoration-none">' . $others . ' lainnya</a>';
                        } elseif (isset($names[2])) {
                            echo ', dan <a href="profilevisit.php?id=' . $ids[2] . '" class="text-decoration-none">' . htmlspecialchars($names[2]) . '</a>';
                        }
                    ?>
                </strong>
            </div>
        </div>
        <?php else: ?>
            <div class="text-muted small mt-3 text-center">Belum memiliki teman</div>
        <?php endif; ?>
    </div>
    <!-- Friends Preview -->
    

    <!-- Tabs -->
    <ul class="nav nav-tabs justify-content-center mb-3" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="beranda-tab" data-bs-toggle="tab" data-bs-target="#beranda" type="button" role="tab">Beranda Profil</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="portfolio-tab" data-bs-toggle="tab" data-bs-target="#portfolio" type="button" role="tab">Portofolio</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tentang-tab" data-bs-toggle="tab" data-bs-target="#tentang" type="button" role="tab">Tentang</button>
        </li>
    </ul>
    <div class="tab-content" id="profileTabContent">
        <!-- Beranda Profil -->
        <div class="tab-pane fade show active" id="beranda" role="tabpanel">
            <?php foreach($posts as $post): ?>
                <?php
                // Ambil komentar untuk post ini
                $stmt = $db->prepare("SELECT comments.*, users.name, users.photo AS photo_user FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY comments.created_at ASC");
                $stmt->execute([$post['id']]);
                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Hitung like & komentar
                $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
                $stmt->execute([$post['id']]);
                $comment_count = $stmt->fetchColumn();

                $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
                $stmt->execute([$post['id']]);
                $like_count = $stmt->fetchColumn();

                // Cek apakah user sudah like
                $user_liked = false;
                if (isset($_SESSION['user']['id'])) {
                    $stmt = $db->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?");
                    $stmt->execute([$post['id'], $_SESSION['user']['id']]);
                    $user_liked = $stmt->fetchColumn() ? true : false;
                }
                ?>
                <div class="card post-card mb-4">
                    <div class="card-body">
                        <!-- Post Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <?php
                                $user_photo = (!empty($post['user_photo']) && $post['user_photo'] !== 'default.svg' && file_exists("img/uploads/" . $post['user_photo']))
                                    ? "img/uploads/" . $post['user_photo']
                                    : "img/default.svg";
                                ?>
                                <a href="profilevisit.php?id=<?php echo $post['user_id']; ?>">
                                    <img class="rounded-circle me-2"
                                         src="<?php echo $user_photo ?>"
                                         width="40"
                                         height="40"
                                         style="object-fit:cover;aspect-ratio:1/1;"
                                         alt="<?php echo htmlspecialchars($post['name']) ?>">
                                </a>
                                <div>
                                    <a href="profilevisit.php?id=<?php echo $post['user_id']; ?>" class="text-decoration-none text-dark">
                                        <h6 class="mb-0"><?php echo $user['name'] ?></h6>
                                    </a>
                                    <small class="text-muted"><?php echo date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></small>
                                </div>
                            </div>
                            <?php if($post['user_id'] == $_SESSION['user']['id']): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPostModal"
                                                data-post-id="<?php echo $post['id'] ?>"
                                                data-content="<?php echo htmlspecialchars($post['content']) ?>">
                                            <i class="fas fa-edit me-2"></i>Edit
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" 
                                           href="?delete_post=<?php echo $post['id'] ?>"
                                           onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash me-2"></i>Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Post Content -->
                        <p class="mb-3">
                            <?php echo nl2br(htmlspecialchars($post['content'])) ?>
                        </p>
                        <?php
                        if (!empty($post['photo']) && $post['photo'] !== 'default.svg') {
                            $photo_path = "img/posts/" . $post['photo'];
                            if (file_exists($photo_path)) {
                                echo '
                                <div class="d-flex justify-content-center mb-2">
                                    <div class="post-image-wrapper position-relative" style="max-width:350px;max-height:450px;overflow:hidden;">
                                        <img src="' . $photo_path . '" 
                                             class="img-fluid rounded post-img-zoom" 
                                             style="object-fit:contain; width:100%; height:auto; max-height:450px; cursor:pointer; background:#f5f5f5;" 
                                             alt="Post Image"
                                             data-post-id="' . $post['id'] . '">
                                        <div class="post-img-overlay d-flex justify-content-between align-items-center px-3 py-1"
                                             style="position:absolute;bottom:0;left:0;width:100%;background:rgba(0,0,0,0.45);color:#fff;border-radius:0 0 12px 12px;">
                                            <span><i class="fas fa-thumbs-up me-1"></i> <span class="like-count">' . $like_count . '</span></span>
                                            <span><i class="fas fa-comment me-1"></i> <span class="comment-count">' . $comment_count . '</span></span>
                                        </div>
                                    </div>
                                </div>
                                ';
                            }
                        }
                        ?>
                        <!-- Post Actions -->
                        <div class="d-flex justify-content-between border-top border-bottom py-2 mb-3">
                            <button class="btn btn-sm like-btn <?php echo $user_liked ? 'btn-blue' : 'btn-outline-secondary'; ?>" data-post-id="<?php echo $post['id']; ?>">
                                <i class="fas fa-thumbs-up me-1"></i> <?php echo $user_liked ? 'Liked' : 'Like'; ?> (<?php echo $like_count; ?>)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary comment-btn">
                                <i class="fas fa-comment me-1"></i> Comment (<?php echo $comment_count; ?>)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-share me-1"></i> Share
                            </button>
                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <!-- Form tambah komentar -->
                            <form action="" method="post" enctype="multipart/form-data" class="d-flex mb-2 align-items-center comment-form">
                                <?php
                                $mini_photo_path = (!empty($_SESSION['user']['photo']) && $_SESSION['user']['photo'] !== 'default.svg' && file_exists("img/uploads/" . $_SESSION['user']['photo']))
                                    ? "img/uploads/" . $_SESSION['user']['photo']
                                    : "img/default.svg";
                                ?>
                                <img class="rounded-circle me-2"
                                     src="<?php echo $mini_photo_path ?>"
                                     width="32"
                                     height="32"
                                     style="object-fit:cover;aspect-ratio:1/1;"
                                     alt="<?php echo $_SESSION['user']['name'] ?>">
                                <div class="flex-grow-1 me-2">
                                    <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control form-control-sm comment-content-input" 
                                               name="comment_content"
                                               placeholder="Write a comment..." required>
                                        <label class="btn btn-light btn-sm mb-0 upload-label" style="position:relative;overflow:hidden;">
                                            <i class="fa fa-image"></i>
                                            <input type="file" name="comment_photo" accept="image/*" class="d-none comment-photo-input">
                                        </label>
                                    </div>
                                    <div class="comment-photo-preview mt-2" style="display:none;">
                                        <img src="#" class="img-thumbnail shadow-lg" style="max-width:120px;max-height:120px;animation:popIn .5s;">
                                    </div>
                                </div>
                                <button type="submit" name="create_comment" class="btn btn-primary btn-sm ms-2">Post</button>
                            </form>
                            <!-- Daftar komentar -->
                            <?php foreach($comments as $comment): ?>
                                <?php
                                $comment_user_photo = (!empty($comment['photo_user']) && $comment['photo_user'] !== 'default.svg' && file_exists("img/uploads/" . $comment['photo_user']))
                                    ? "img/uploads/" . $comment['photo_user']
                                    : "img/default.svg";
                                ?>
                                <div class="d-flex mb-2 align-items-start">
                                    <a href="profilevisit.php?id=<?php echo $comment['user_id']; ?>">
                                        <img class="rounded-circle me-2"
                                             src="<?php echo $comment_user_photo ?>"
                                             width="32"
                                             height="32"
                                             style="object-fit:cover;aspect-ratio:1/1;"
                                             alt="<?php echo $comment['name'] ?>">
                                    </a>
                                    <div class="flex-grow-1">
                                        <div class="bg-light p-2 rounded position-relative mt-1">
                                            <span class="comment-author">
                                                <a href="profilevisit.php?id=<?php echo $comment['user_id']; ?>" class="text-decoration-none text-dark">
                                                    <?php echo $comment['name']; ?>
                                                </a>
                                            </span>
                                            <div class="comment-content" id="comment-content-<?php echo $comment['id']; ?>">
                                                <span><?php echo nl2br(htmlspecialchars($comment['content'])); ?></span>
                                            </div>
                                            <!-- Form edit inline, awalnya hidden -->
                                            <form method="post" class="d-none mt-2" id="edit-form-<?php echo $comment['id']; ?>">
                                                <input type="hidden" name="edit_comment_id" value="<?php echo $comment['id']; ?>">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="edit_comment_content" class="form-control" value="<?php echo htmlspecialchars($comment['content']); ?>" required>
                                                    <button type="submit" name="update_comment" class="btn btn-primary btn-sm">Simpan</button>
                                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn" data-comment-id="<?php echo $comment['id']; ?>">Batal</button>
                                                </div>
                                            </form>
                                            <?php if(!empty($comment['photo'])): ?>
                                                <?php
                                                $comment_photo_path = "img/comments/" . $comment['photo'];
                                                if (file_exists($comment_photo_path)) {
                                                    echo '<img src="' . $comment_photo_path . '" 
                                                         class="img-thumbnail shadow comment-img mt-2" 
                                                         style="max-width:120px;max-height:120px;cursor:pointer;">';
                                                }
                                                ?>
                                            <?php endif; ?>
                                            <small class="text-muted d-block mt-1">
                                                <?php 
                                                    echo date('F j, Y \a\t H:i', strtotime($comment['created_at']));
                                                    if (!empty($comment['edited_at'])) {
                                                        echo ' <span class="text-primary">(edited at ' . date('H:i', strtotime($comment['edited_at'])) . ')</span>';
                                                    }
                                                ?>
                                            </small>
                                            <?php if($comment['user_id'] == $_SESSION['user']['id']): ?>
                                                <div class="dropdown position-absolute top-0 end-0">
                                                    <button class="btn btn-link btn-sm text-muted" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item edit-inline-btn" 
                                                                    data-comment-id="<?php echo $comment['id']; ?>">
                                                                <i class="fas fa-edit me-2"></i>Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" 
                                                               href="?delete_comment=<?php echo $comment['id']; ?>"
                                                               onclick="return confirm('Hapus komentar ini?')">
                                                                <i class="fas fa-trash me-2"></i>Hapus
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Portofolio -->
        <div class="tab-pane fade" id="portfolio" role="tabpanel">
            <h5 class="mt-4 mb-2">Portofolio</h5>
            <?php if ($_SESSION['user']['id'] == $profile_id): ?>
                <div class="d-flex mb-3 align-items-center gap-2">
                    <a href="tambah_portofolio.php?id=<?php echo $profile_id ?>" class="btn btn-primary btn-sm">Tambah Portofolio</a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Kelola
                        </button>
                        <ul class="dropdown-menu">
                            <?php
                            foreach ($portfolios as $port) {
                                echo '<li class="px-2 py-1">
                                    <span class="fw-semibold">'.htmlspecialchars($port['title']).'</span><br>
                                    <a href="edit_portofolio.php?id='.$port['id'].'" class="btn btn-link btn-sm text-primary px-0">Edit</a>
                                    <a href="profilevisit.php?id='.$profile_id.'&delete_portofolio='.$port['id'].'" class="btn btn-link btn-sm text-danger px-0" onclick="return confirm(\'Hapus portofolio ini?\')">Hapus</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
            <?php foreach ($portfolios as $port): ?>
                <div class="col-md-6" id="portfolio-port<?php echo $port['id']; ?>">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($port['title']); ?></h5>
                            <p class="text-muted mb-2">Kategori: <?php echo htmlspecialchars($port['category']); ?></p>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#portfolioModal<?php echo $port['id']; ?>">Lihat Detail</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Detail Portofolio -->
                <div class="modal fade" id="portfolioModal<?php echo $port['id']; ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><?php echo htmlspecialchars($port['title']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <?php if (!empty($port['file']) && file_exists("img/portfolios/" . $port['file'])): ?>
                          <img src="img/portfolios/<?php echo $port['file']; ?>" class="img-fluid rounded mb-3">
                        <?php endif; ?>
                        <p><strong>Kategori:</strong> <?php echo htmlspecialchars($port['category']); ?></p>
                        <p><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($port['description'])); ?></p>
                        <?php if (!empty($port['link'])): ?>
                          <p><strong>Link:</strong> <a href="<?php echo htmlspecialchars($port['link']); ?>" target="_blank"><?php echo htmlspecialchars($port['link']); ?></a></p>
                        <?php endif; ?>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <!-- Tentang -->
        <div class="tab-pane fade" id="tentang" role="tabpanel">
            <ul class="list-group list-group-flush text-start mx-auto" style="max-width:400px;">
                <li class="list-group-item"><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio'] ?? '-'); ?></li>
                <li class="list-group-item"><strong>Tanggal Lahir:</strong>
                    <?php
                    if(!empty($user['birthdate']) && $user['birthdate']!='0000-00-00') {
                        echo date('d F Y', strtotime($user['birthdate']));
                    } else {
                        echo '-';
                    }
                    ?>
                </li>
                <li class="list-group-item"><strong>Bekerja di:</strong> <?php echo htmlspecialchars($user['work'] ?? '-'); ?></li>
                <li class="list-group-item"><strong>Alamat:</strong> <?php echo htmlspecialchars($user['address'] ?? '-'); ?></li>
            </ul>
        </div>
    </div>

    <!-- Stack avatar + deskripsi teman -->
</div>
<!-- Modal Zoom Gambar -->
<div class="modal fade" id="imgZoomModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 text-center">
        <img id="zoomedImg" src="" style="max-width:90vw;max-height:80vh;box-shadow:0 0 20px #000;border-radius:12px;">
        <!-- Overlay info like & komen akan ditambahkan lewat JS -->
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome untuk ikon -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function() {
        const btn = this;
        const postId = btn.getAttribute('data-post-id');
        btn.disabled = true;

        fetch('like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'post_id=' + encodeURIComponent(postId)
        })
        .then(response => response.json())
        .then(data => {
            if (data && typeof data.like_count !== 'undefined') {
                if (data.status === 'liked') {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-blue');
                    btn.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Liked (' + data.like_count + ')';
                } else {
                    btn.classList.remove('btn-blue');
                    btn.classList.add('btn-outline-secondary');
                    btn.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Like (' + data.like_count + ')';
                }
            }
        })
        .finally(() => {
            btn.disabled = false;
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Zoom gambar komentar
    document.querySelectorAll('.comment-img').forEach(img => {
        img.addEventListener('click', function() {
            document.getElementById('zoomedImg').src = this.src;
            // Hapus overlay info jika ada
            let overlay = document.getElementById('zoomedImgOverlay');
            if (overlay) overlay.remove();
            var modal = new bootstrap.Modal(document.getElementById('imgZoomModal'));
            modal.show();
        });
    });

    // Zoom gambar post
    document.querySelectorAll('.post-img-zoom').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.src;
            const postCard = this.closest('.post-card');
            const likeCount = postCard.querySelector('.like-count')?.textContent || '0';
            const commentCount = postCard.querySelector('.comment-count')?.textContent || '0';

            const zoomedImg = document.getElementById('zoomedImg');
            zoomedImg.src = src;

            // Tambahkan info like & komen di bawah gambar pada modal
            let overlay = document.getElementById('zoomedImgOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'zoomedImgOverlay';
                overlay.style = 'margin-top:12px;display:flex;justify-content:center;gap:32px;font-size:1.2rem;';
                zoomedImg.parentNode.appendChild(overlay);
            }
            overlay.innerHTML = `
                <span><i class="fas fa-thumbs-up me-1"></i> <span class="like-count">${likeCount}</span></span>
                <span><i class="fas fa-comment me-1"></i> <span class="comment-count">${commentCount}</span></span>
            `;

            var modal = new bootstrap.Modal(document.getElementById('imgZoomModal'));
            modal.show();
        });
    });

    // Highlight dan scroll ke portofolio jika ada ID di localStorage
    const highlightId = localStorage.getItem('highlightPortfolioId');
    if (highlightId) {
        // Aktifkan tab portofolio
        var tab = document.querySelector('button[data-bs-target="#portfolio"]');
        if (tab) tab.click();

        // Setelah tab aktif, scroll ke portofolio
        setTimeout(function() {
            const el = document.getElementById('portfolio-port' + highlightId);
            if (el) {
                el.classList.add('border', 'border-primary', 'shadow');
                el.scrollIntoView({behavior: 'smooth', block: 'center'});
                setTimeout(() => el.classList.remove('border', 'border-primary', 'shadow'), 2000);
            }
            localStorage.removeItem('highlightPortfolioId');
        }, 400);
    }
});
</script>
</body>
</html><?php
if (isset($_GET['delete_portofolio']) && $_SESSION['user']['id'] == $profile_id) {
    $del_id = intval($_GET['delete_portofolio']);
    // Hapus file gambar jika ada
    $stmt = $db->prepare("SELECT file FROM portfolios WHERE id=? AND user_id=?");
    $stmt->execute([$del_id, $profile_id]);
    $pf = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pf && !empty($pf['file']) && file_exists("img/portfolios/".$pf['file'])) {
        unlink("img/portfolios/".$pf['file']);
    }
    // Hapus data di database
    $stmt = $db->prepare("DELETE FROM portfolios WHERE id=? AND user_id=?");
    $stmt->execute([$del_id, $profile_id]);
    header("Location: profilevisit.php?id=$profile_id");
    exit;
}