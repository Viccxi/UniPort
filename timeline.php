<?php 
require_once("auth.php");
require_once("config.php"); // Assuming you have a database connection file

// Handle post creation
if(isset($_POST['create_post'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user']['id'];
    try {
        $stmt = $db->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $content]);
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle post deletion
if(isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    $user_id = $_SESSION['user']['id'];
    try {
        // Verify the post belongs to the user before deleting
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

// Get all posts
try {
    $stmt = $db->prepare("
        SELECT posts.*, users.name, users.photo 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Judul Pesbuk - Social Media Platform</title>

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
            position: sticky;
            top: 20px;
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users me-2"></i>Judul Pesbuk
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
                        <img class="img-fluid rounded-circle mb-3 border border-3 border-primary" 
                             width="160" 
                             src="img/<?php echo $_SESSION['user']['photo'] ?>" 
                             alt="<?php echo $_SESSION['user']['name'] ?>">
                        
                        <h3 class="mb-1"><?php echo $_SESSION["user"]["name"] ?></h3>
                        <p class="text-muted mb-3"><?php echo $_SESSION["user"]["email"] ?></p>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h5 class="mb-0">128</h5>
                                <small class="text-muted">Posts</small>
                            </div>
                            <div>
                                <h5 class="mb-0">1.2K</h5>
                                <small class="text-muted">Followers</small>
                            </div>
                            <div>
                                <h5 class="mb-0">350</h5>
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
                        <form action="" method="post">
                            <div class="d-flex mb-3">
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo $_SESSION['user']['photo'] ?>" 
                                     width="40" 
                                     alt="<?php echo $_SESSION['user']['name'] ?>">
                                <input type="text" 
                                       class="form-control rounded-pill" 
                                       placeholder="What's on your mind, <?php echo explode(' ', $_SESSION['user']['name'])[0]; ?>?"
                                       name="content"
                                       required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-2">
                                        <i class="fas fa-image text-success"></i> Photo
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-2">
                                        <i class="fas fa-video text-danger"></i> Video
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-smile text-warning"></i> Feeling
                                    </button>
                                </div>
                                <button type="submit" name="create_post" class="btn btn-primary px-4">
                                    Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Posts Feed -->
                <?php foreach($posts as $post): ?>
                <div class="card post-card mb-4">
                    <div class="card-body">
                        <!-- Post Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo $post['photo'] ?>" 
                                     width="40" 
                                     alt="<?php echo $post['name'] ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo $post['name'] ?></h6>
                                    <small class="text-muted"><?php echo date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></small>
                                </div>
                            </div>
                            
                            <!-- Dropdown Menu for post actions (only show if post belongs to current user) -->
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
                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])) ?></p>
                        
                        <!-- Post Actions -->
                        <div class="d-flex justify-content-between border-top border-bottom py-2 mb-3">
                            <button class="btn btn-sm btn-outline-secondary like-btn">
                                <i class="fas fa-thumbs-up me-1"></i> Like (24)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary comment-btn">
                                <i class="fas fa-comment me-1"></i> Comment (5)
                            </button>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-share me-1"></i> Share
                            </button>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <div class="d-flex mb-2">
                                <img class="rounded-circle me-2" 
                                     src="img/<?php echo $_SESSION['user']['photo'] ?>" 
                                     width="32" 
                                     alt="<?php echo $_SESSION['user']['name'] ?>">
                                <div class="flex-grow-1">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           placeholder="Write a comment...">
                                </div>
                            </div>
                            
                            <!-- Sample Comments -->
                            <div class="d-flex mb-2">
                                <img class="rounded-circle me-2" 
                                     src="https://randomuser.me/api/portraits/men/1.jpg" 
                                     width="32" 
                                     alt="John Doe">
                                <div class="bg-light p-2 rounded flex-grow-1">
                                    <strong>John Doe</strong>
                                    <p class="mb-0">Nice post! Keep it up.</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
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
    </script>
</body>
</html>