<?php 
require_once("auth.php");
require_once("config.php");

// Set default photo for users who have no photo
try {
    $db->exec("UPDATE users SET photo = 'default.svg' WHERE photo IS NULL OR photo = ''");
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle post creation
if(isset($_POST['create_post'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];
    $photo_filename = null;

    // Handle upload foto
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "img/posts/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $original_name = str_replace(' ', '_', basename($_FILES["photo"]["name"]));
        $photo_filename = time() . '_' . $original_name;
        $target_file = $target_dir . $photo_filename;
        if(!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            die("Upload gagal ke $target_file. Cek permission folder dan nama file.");
        }
    }

    try {
        $stmt = $db->prepare("INSERT INTO posts (user_id, content, photo, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $content, $photo_filename]);
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle post deletion
if(isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    $user_id = $_SESSION['user']['id'];
    try {
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle post update
if(isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];
    try {
        $stmt = $db->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $post_id, $user_id]);
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle comment creation
if(isset($_POST['create_comment'])) {
    $comment_content = $_POST['comment_content'];
    $comment_post_id = $_POST['comment_post_id'];
    $comment_user_id = $_SESSION['user']['id'];
    $comment_photo = null;

    if(isset($_FILES['comment_photo']) && $_FILES['comment_photo']['error'] == 0) {
        $target_dir = "img/comments/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $original_name = str_replace(' ', '_', basename($_FILES["comment_photo"]["name"]));
        $comment_photo = time() . '_' . $original_name;
        $target_file = $target_dir . $comment_photo;
        if(!move_uploaded_file($_FILES["comment_photo"]["tmp_name"], $target_file)) {
            $comment_photo = null;
        }
    }

    try {
        // Simpan komentar
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content, photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$comment_post_id, $comment_user_id, $comment_content, $comment_photo]);
        $comment_id = $db->lastInsertId();

        // Ambil pemilik post
        $stmt = $db->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$comment_post_id]);
        $post_owner_id = $stmt->fetchColumn();

        // Cek apakah komentator adalah follower dari pemilik post
        $stmt = $db->prepare("SELECT COUNT(*) FROM followers WHERE user_id = ? AND follower_id = ? AND status = 'accepted'");
        $stmt->execute([$post_owner_id, $comment_user_id]);
        $is_follower = $stmt->fetchColumn();

if ($post_owner_id != $comment_user_id) {
    // Simpan notifikasi
    $stmt = $db->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, comment_id, type, is_read, created_at) VALUES (?, ?, ?, ?, 'comment', 0, NOW())");
    $stmt->execute([$post_owner_id, $comment_user_id, $comment_post_id, $comment_id]);
}


        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}


// Handle update comment
if(isset($_POST['update_comment'])) {
    $comment_id = $_POST['edit_comment_id'];
    $content = $_POST['edit_comment_content'];
    $user_id = $_SESSION['user']['id'];
    try {
        $stmt = $db->prepare("UPDATE comments SET content = ?, edited_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $comment_id, $user_id]);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle delete comment
if(isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $user_id = $_SESSION['user']['id'];
    try {
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle like post
if(isset($_POST['like_post'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user']['id'];

    try {
        // Cek apakah sudah like
        $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $hasLiked = $stmt->fetchColumn();

        if (!$hasLiked) {
            // Simpan like
            $stmt = $db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $post_id]);

            // Ambil pemilik post
            $stmt = $db->prepare("SELECT user_id FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post_owner_id = $stmt->fetchColumn();

            // Jangan kirim notifikasi ke diri sendiri
            if ($post_owner_id != $user_id) {
                // Simpan notifikasi
                $stmt = $db->prepare("INSERT INTO notifications (user_id, from_user_id, post_id, comment_id, type, is_read, created_at)
                                      VALUES (?, ?, ?, NULL, 'like', 0, NOW())");
                $stmt->execute([$post_owner_id, $user_id, $post_id]);
            }
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;

    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}


// Get all posts
try {
    $stmt = $db->prepare("
        SELECT posts.*, users.name, users.photo AS user_photo
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$post_comment_counts = [];
$post_like_counts = [];

foreach ($posts as $p) {
    // Hitung komentar
    $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $stmt->execute([$p['id']]);
    $post_comment_counts[$p['id']] = $stmt->fetchColumn();

    // Hitung like (misal tabel likes: id, post_id, user_id)
    $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$p['id']]);
    $post_like_counts[$p['id']] = $stmt->fetchColumn();
}

$user_like_status = [];
if (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
    $stmt = $db->prepare("SELECT post_id FROM likes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $liked_post_id) {
        $user_like_status[$liked_post_id] = true;
    }
}

// Ambil teman (saling follow/accepted)
$stmt = $db->prepare("
    SELECT u.id, u.name, u.photo
    FROM users u
    JOIN followers f1 ON f1.follower_id = u.id AND f1.user_id = ? AND f1.status = 'accepted'
    JOIN followers f2 ON f2.user_id = u.id AND f2.follower_id = ? AND f2.status = 'accepted'
    WHERE u.id != ?
    GROUP BY u.id
    LIMIT 5
");
$stmt->execute([$_SESSION['user']['id'], $_SESSION['user']['id'], $_SESSION['user']['id']]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UniPort</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: #4267B2 !important;
        }
        .profile-card {
            position: static;
        }
        .post-card {
            transition: transform 0.2s;
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .like-btn, .comment-btn {
            cursor: pointer;
        }
        .like-btn.btn-outline-secondary:hover,
        .like-btn.btn-outline-secondary:focus {
            color: #0d6efd !important;
            background: #e7f1ff !important;
            border-color: #0d6efd !important;
        }
        .like-btn.btn-blue,
        .like-btn.btn-blue:focus,
        .like-btn.btn-blue:active {
            background: #e7f1ff !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
        .rounded-circle {
            object-fit: cover;
            aspect-ratio: 1 / 1;
        }
        .comments-section img.rounded-circle {
            width: 32px;
            height: 32px;
            object-fit: cover;
            aspect-ratio: 1/1;
        }
        input[type="file"]::-webkit-file-upload-button {
            visibility: hidden;
        }
        input[type="file"]::file-selector-button {
            visibility: hidden;
        }
        input[type="file"] {
            color: transparent;
        }
        .upload-label input[type="file"] {
            display: none;
        }
        .comment-photo-preview img {
            animation: popIn 0.5s;
        }
        #imgZoomModal .modal-dialog {
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0 auto;
        }

        #imgZoomModal .modal-content {
            background: rgba(0,0,0,0.7);
            box-shadow: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #imgZoomModal .modal-body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        #zoomedImg {
            display: block;
            margin: 0 auto;
            max-width: 90vw;
            max-height: 80vh;
            box-shadow: 0 0 20px #000;
            border-radius: 12px;
        }
        .post-image-wrapper {
            background: #f5f5f5;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .post-img-overlay {
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .d-flex.align-items-start > a,
        .d-flex.align-items-start > .flex-grow-1 {
            align-self: flex-start;
        }
        .comment-author {
            font-weight: bold;
            margin-bottom: 0;
            font-size: 1em;
            line-height: 1.1;
        }
        .comment-content {
            margin-top: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            line-height: 1.3;
        }
        .bg-light.p-2.rounded.position-relative.mt-1 {
            margin-top: 2px !important;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }
        .like-btn.btn-blue,
        .like-btn.btn-blue:focus,
        .like-btn.btn-blue:active {
            background: #e7f1ff !important;
            color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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

    <div class="container mb-5">
        <div class="row">
            <!-- Left Sidebar - Profile Card -->
            <div class="col-lg-4">
                <div class="card profile-card mb-4">
                    <div class="card-body text-center">
                        <?php
                        $photo_path = (!empty($_SESSION['user']['photo']) && $_SESSION['user']['photo'] !== 'default.svg' && file_exists("img/uploads/" . $_SESSION['user']['photo']))
                            ? "img/uploads/" . $_SESSION['user']['photo']
                            : "img/default.svg";
                        ?>
                        <a href="profilevisit.php?id=<?php echo $_SESSION['user']['id']; ?>" class="text-decoration-none text-dark">
                            <img class="img-fluid rounded-circle mb-3 border border-3 border-primary"
                                 width="160"
                                 src="<?php echo $photo_path ?>"
                                 alt="<?php echo htmlspecialchars($_SESSION['user']['name']) ?>">
                            <h3 class="mb-1"><?php echo $_SESSION["user"]["name"] ?></h3>
                        </a>
                        <p class="text-muted mb-3"><?php echo $_SESSION["user"]["email"] ?></p>
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h5 class="mb-0">0</h5>
                                <small class="text-muted">Posts</small>
                            </div>
                            <div>
                                <h5 class="mb-0">0</h5>
                                <small class="text-muted">Followers</small>
                            </div>
                            <div>
                                <h5 class="mb-0">0</h5>
                                <small class="text-muted">Following</small>
                            </div>
                        </div>
                        <a href="profile.php" class="btn btn-primary btn-sm w-100 mb-2">
                            <i class="fas fa-user-edit me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>
                <!-- Friends List -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Friends</h5>
                        <?php if(count($friends) == 5): ?>
                            <a href="friends.php" class="btn btn-link btn-sm">See More</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($friends)): ?>
                            <div class="text-center text-muted py-3">Belum memiliki teman</div>
                        <?php else: ?>
                        <div class="row g-2">
                            <?php foreach(array_slice($friends, 0, 4) as $f): ?>
                            <div class="col-3 text-center">
                                <?php
                                $friend_photo = (!empty($f['photo']) && $f['photo'] !== 'default.svg' && file_exists("img/uploads/".$f['photo']))
                                    ? "img/uploads/".$f['photo']
                                    : "img/default.svg";
                                ?>
                                <a href="profilevisit.php?id=<?php echo $f['id']; ?>" class="text-decoration-none">
                                    <img src="<?php echo $friend_photo; ?>" class="img-fluid rounded-circle border mb-1" style="width:48px;height:48px;object-fit:cover;">
                                    <div style="font-size:0.85em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($f['name']); ?></div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Create Post -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="d-flex mb-3">
                                <?php
                                $mini_photo_path = (!empty($_SESSION['user']['photo']) && $_SESSION['user']['photo'] !== 'default.svg' && file_exists("img/uploads/" . $_SESSION['user']['photo']))
                                    ? "img/uploads/" . $_SESSION['user']['photo']
                                    : "img/default.svg";
                                ?>
                                <img class="rounded-circle me-2" 
                                    src="<?php echo $mini_photo_path ?>" 
                                    width="32" 
                                    alt="<?php echo $_SESSION['user']['name'] ?>">
                                <input type="text" 
                                       class="form-control rounded-pill" 
                                       id="postContent"
                                       placeholder="What's on your mind, <?php echo explode(' ', $_SESSION['user']['name'])[0]; ?>?"
                                       name="content"
                                       required>                            </div>
                            <div class="mb-2">
                                <input type="file" name="photo" accept="image/*" id="photoInput" class="form-control form-control-sm">
                                <div style="width:100%;text-align:center;">
                                    <img id="photoPreview" src="#" alt="Preview" style="display:none;max-width:100%;max-height:350px;margin-top:10px;border-radius:10px;">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="create_post" class="btn btn-primary px-4">
                                    Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Posts Feed -->
                <?php foreach($posts as $post): ?>
                    <?php
                    $stmt = $db->prepare("SELECT comments.*, users.name, users.photo AS photo_user FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY comments.created_at ASC");
                    $stmt->execute([$post['id']]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                <div class="card post-card mb-4">
                    <div class="card-body">
                        <!-- Post Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <?php
                                $user_photo = (!empty($post['user_photo']) && file_exists("img/uploads/" . $post['user_photo'])) ? "img/uploads/" . $post['user_photo'] : "img/default.svg";
                                if(!file_exists($user_photo)) $user_photo = "img/default.svg";
                                ?>
                                <a href="profilevisit.php?id=<?php echo $post['user_id']; ?>">
                                    <img class="rounded-circle me-2"
                                         src="<?php echo $user_photo ?>"
                                         width="40"
                                         alt="<?php echo htmlspecialchars($post['name']) ?>">
                                </a>
                                <div>
                                    <a href="profilevisit.php?id=<?php echo $post['user_id']; ?>" class="text-decoration-none text-dark">
                                        <h6 class="mb-0"><?php echo $post['name'] ?></h6>
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
                                            <span><i class="fas fa-thumbs-up me-1"></i> <span class="like-count">' . ($post_like_counts[$post['id']] ?? 0) . '</span></span>
                                            <span><i class="fas fa-comment me-1"></i> <span class="comment-count">' . ($post_comment_counts[$post['id']] ?? 0) . '</span></span>
                                        </div>
                                    </div>
                                </div>
                                ';
                            }
                        }
                        ?>
                        <!-- Post Actions -->
                        <div class="d-flex border-top border-bottom py-2 mb-3 justify-content-center gap-2">
                            <button class="btn btn-sm like-btn <?php echo !empty($user_like_status[$post['id']]) ? 'btn-blue' : 'btn-outline-secondary'; ?>" data-post-id="<?php echo $post['id']; ?>">
                                <i class="fas fa-thumbs-up me-1"></i> <?php echo !empty($user_like_status[$post['id']]) ? 'Liked' : 'Like'; ?> (<?php echo $post_like_counts[$post['id']] ?? 0; ?>)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary comment-btn">
                                <i class="fas fa-comment me-1"></i> Comment (<?php echo $post_comment_counts[$post['id']] ?? 0; ?>)
                            </button>

                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <!-- Form tambah komentar -->
                            <form action="" method="post" enctype="multipart/form-data" class="d-flex mb-2 align-items-center comment-form">
                                <?php
                                $comment_form_photo = (!empty($_SESSION['user']['photo']) && $_SESSION['user']['photo'] !== 'default.svg' && file_exists("img/uploads/" . $_SESSION['user']['photo']))
                                    ? "img/uploads/" . $_SESSION['user']['photo']
                                    : "img/default.svg";
                                ?>
                                <img class="rounded-circle me-2" 
                                     src="<?php echo $comment_form_photo ?>" 
                                     width="32" 
                                     alt="<?php echo $_SESSION['user']['name'] ?>">
                                <div class="flex-grow-1 me-2">
                                    <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control form-control-sm comment-content-input" 
                                               name="comment_content"
                                               placeholder="Write a comment..." required>
                                        </button>
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
                                                    echo '<img src="' . $comment_photo_path . '" class="img-thumbnail shadow comment-img" style="max-width:120px;max-height:120px;cursor:pointer;"><br>';
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
        </div>
    </div>
    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="post_id" id="editPostId">
                        <div class="mb-3">
                            <label for="editPostContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editPostContent" name="content" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="photoInput" class="form-label">Upload Photo</label>
                            <input type="file" name="photo" accept="image/*" id="photoInput" class="form-control">
                            <div style="width:100%;text-align:center;">
                                <img id="photoPreview" src="#" alt="Preview" style="display:none;max-width:100%;max-height:350px;margin-top:10px;border-radius:10px;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_post" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Komentar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="edit_comment_id" id="editCommentId">
                <textarea class="form-control" name="edit_comment_content" id="editCommentContent" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle edit post modal
        var editPostModal = document.getElementById('editPostModal');
        if (editPostModal) {
            editPostModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var postId = button.getAttribute('data-post-id');
                var content = button.getAttribute('data-content');
                document.getElementById('editPostId').value = postId;
                document.getElementById('editPostContent').value = content;
            });
        }

        // Like button functionality
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
                    console.log(data);
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

        // Preview foto post
        var photoInput = document.getElementById('photoInput');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const [file] = this.files;
                if(file) {
                    const preview = document.getElementById('photoPreview');
                    preview.src = URL.createObjectURL(file);
                    preview.style.display = 'block';
                }
            });
        }

        // Animasi upload gambar komentar
        document.querySelectorAll('.comment-photo-input').forEach(input => {
            input.addEventListener('change', function() {
                const previewDiv = this.closest('.comment-form').querySelector('.comment-photo-preview');
                const img = previewDiv.querySelector('img');
                if(this.files && this.files[0]) {
                    img.src = URL.createObjectURL(this.files[0]);
                    previewDiv.style.display = 'block';
                    img.style.animation = 'popIn 0.5s';
                } else {
                    previewDiv.style.display = 'none';
                }
            });
        });

        // Image zoom functionality
        document.querySelectorAll('.comment-img').forEach(img => {
            img.addEventListener('click', function() {
                document.getElementById('zoomedImg').src = this.src;
                var modal = new bootstrap.Modal(document.getElementById('imgZoomModal'));
                modal.show();
            });
        });

        // Image zoom functionality for post images
        document.querySelectorAll('.post-img-zoom').forEach(img => {
            img.addEventListener('click', function() {
                // Ambil data
                const src = this.src;
                const postCard = this.closest('.post-card');
                const likeCount = postCard.querySelector('.like-count')?.textContent || '0';
                const commentCount = postCard.querySelector('.comment-count')?.textContent || '0';

                // Set gambar dan overlay info di modal
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

                // Tampilkan modal
                var modal = new bootstrap.Modal(document.getElementById('imgZoomModal'));
                modal.show();
            });
        });

        // Edit Comment Modal
        document.querySelectorAll('.edit-comment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editCommentId').value = this.getAttribute('data-comment-id');
                document.getElementById('editCommentContent').value = this.getAttribute('data-comment-content');
                var modal = new bootstrap.Modal(document.getElementById('editCommentModal'));
                modal.show();
            });
        });

        document.querySelectorAll('.edit-inline-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-comment-id');
                document.getElementById('comment-content-' + id).style.display = 'none';
                document.getElementById('edit-form-' + id).classList.remove('d-none');
                document.getElementById('edit-form-' + id).querySelector('input[name="edit_comment_content"]').focus();
            });
        });

        document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-comment-id');
                document.getElementById('edit-form-' + id).classList.add('d-none');
                document.getElementById('comment-content-' + id).style.display = '';
            });
        });
    });
    </script>
</body>
</html>