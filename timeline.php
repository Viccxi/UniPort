<?php 
require_once("auth.php");
require_once("config.php");

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
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content, photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$comment_post_id, $comment_user_id, $comment_content, $comment_photo]);
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pesbuk - Social Media Platform</title>

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
        .like-btn:hover {
            color: #dc3545 !important;
        }
        .comment-btn:hover {
            color: #0d6efd !important;
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
        #imgZoomModal .modal-content {
            background: rgba(0,0,0,0.7);
            box-shadow: none;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users me-2"></i>Pesbuk
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell me-1"></i> Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-envelope me-1"></i> Messages</a>
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
                        $photo_path = "img/" . $_SESSION['user']['photo'];
                        if(!file_exists($photo_path) || empty($_SESSION['user']['photo'])) {
                            $photo_path = "img/default.png";
                        }
                        ?>
                        <img class="img-fluid rounded-circle mb-3 border border-3 border-primary"
                             width="160"
                             src="<?php echo $photo_path ?>"
                             alt="<?php echo htmlspecialchars($_SESSION['user']['name']) ?>">
                        <h3 class="mb-1"><?php echo $_SESSION["user"]["name"] ?></h3>
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
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Friends</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <div class="col-4">
                                <img src="https://randomuser.me/api/portraits/<?php echo $i%2==0?'women':'men'; ?>/<?php echo $i; ?>.jpg" 
                                     class="img-fluid rounded-circle border" 
                                     alt="Friend <?php echo $i; ?>">
                            </div>
                            <?php endfor; ?>
                        </div>
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
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo $_SESSION['user']['photo'] ?>" 
                                     width="40" 
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
                                $user_photo = !empty($post['user_photo']) ? "img/" . $post['user_photo'] : "img/default.png";
                                if(!file_exists($user_photo)) $user_photo = "img/default.png";
                                ?>
                                <img class="rounded-circle me-2" 
                                     src="<?php echo $user_photo ?>" 
                                     width="40" 
                                     alt="<?php echo htmlspecialchars($post['name']) ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo $post['name'] ?></h6>
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
                                echo '<img src="' . $photo_path . '" class="img-fluid rounded mb-2" style="max-width:300px;">';
                            }
                        }
                        ?>
                        <!-- Post Actions -->
                        <div class="d-flex justify-content-between border-top border-bottom py-2 mb-3">
                            <button class="btn btn-sm btn-outline-secondary like-btn">
                                <i class="fas fa-thumbs-up me-1"></i> Like (<?php echo $post_like_counts[$post['id']] ?? 0; ?>)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary comment-btn">
                                <i class="fas fa-comment me-1"></i> Comment (<?php echo $post_comment_counts[$post['id']] ?? 0; ?>)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-share me-1"></i> Share
                            </button>
                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <!-- Form tambah komentar -->
                            <form action="" method="post" enctype="multipart/form-data" class="d-flex mb-2 align-items-center comment-form">
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo $_SESSION['user']['photo'] ?>" 
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
                            <div class="d-flex mb-2">
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo !empty($comment['photo_user']) ? $comment['photo_user'] : 'default.png'; ?>" 
                                     width="32" 
                                     alt="<?php echo $comment['name'] ?>">
                                <div class="bg-light p-2 rounded flex-grow-1">
                                    <strong><?php echo $comment['name']; ?></strong>
                                    <p class="mb-1">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </p>
                                    <?php if(!empty($comment['photo'])): ?>
                                    <?php
                                    $comment_photo_path = "img/comments/" . $comment['photo'];
                                    if (file_exists($comment_photo_path)) {
                                        echo '<img src="' . $comment_photo_path . '" class="img-thumbnail shadow comment-img" style="max-width:120px;max-height:120px;cursor:pointer;">';
                                        echo '<br>';
                                    }
                                    ?>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-1"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></small>
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
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="post_id" id="editPostId">
                        <textarea class="form-control" name="content" id="editPostContent" rows="5"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_post" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Image Zoom Modal -->
    <div class="modal fade" id="imgZoomModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body p-0 text-center">
            <img id="zoomedImg" src="" style="max-width:90vw;max-height:80vh;box-shadow:0 0 20px #000;border-radius:12px;">
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit post modal
        document.addEventListener('DOMContentLoaded', function() {
            var editPostModal = document.getElementById('editPostModal');
            editPostModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var postId = button.getAttribute('data-post-id');
                var content = button.getAttribute('data-content');
                document.getElementById('editPostId').value = postId;
                document.getElementById('editPostContent').value = content;
            });
            // Like button functionality
            document.querySelectorAll('.like-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if(this.classList.contains('btn-outline-secondary')) {
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-danger');
                        this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Liked (25)';
                    } else {
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-secondary');
                        this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Like (24)';
                    }
                });
            });
        });

        // Preview foto post
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const [file] = this.files;
            if(file) {
                const preview = document.getElementById('photoPreview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });

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
    </script>
</body>
</html>