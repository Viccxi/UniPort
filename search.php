<?php
require_once("auth.php");
require_once("config.php");

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = $posts = $portfolios = [];

if ($query !== '') {
    // Search users (username, name, bio)
    $stmt = $db->prepare("SELECT id, name, username, photo, bio FROM users WHERE 
        username LIKE ? OR name LIKE ? OR bio LIKE ?");
    $like = "%$query%";
    $stmt->execute([$like, $like, $like]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search posts (content)
    $stmt = $db->prepare("SELECT posts.*, users.name, users.photo AS user_photo FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.content LIKE ? ORDER BY posts.created_at DESC");
    $stmt->execute([$like]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search portfolios (title, description)
    $stmt = $db->prepare("SELECT portfolios.*, users.name, users.photo AS user_photo FROM portfolios 
        JOIN users ON portfolios.user_id = users.id 
        WHERE portfolios.title LIKE ? OR portfolios.description LIKE ? ORDER BY portfolios.created_at DESC");
    $stmt->execute([$like, $like]);
    $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <div class="mb-3">
        <a href="timeline.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <form class="mb-4" method="get">
        <div class="input-group">
            <input type="text" class="form-control" name="q" placeholder="Cari user, post, portofolio..." value="<?php echo htmlspecialchars($query); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
        </div>
    </form>

    <?php if ($query !== ''): ?>
        <h5 class="mb-3">Hasil untuk "<span class="text-primary"><?php echo htmlspecialchars($query); ?></span>"</h5>

        <!-- User Results -->
        <h6>User</h6>
        <?php if ($users): ?>
            <ul class="list-group mb-4">
                <?php foreach ($users as $user): ?>
                    <li class="list-group-item d-flex align-items-center">
                        <?php
                        $photo = (!empty($user['photo']) && $user['photo'] !== 'default.svg' && file_exists("img/uploads/" . $user['photo']))
                            ? "img/uploads/" . $user['photo']
                            : "img/default.svg";
                        ?>
                        <img src="<?php echo $photo; ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit:cover;">
                        <div>
                            <a href="profilevisit.php?id=<?php echo $user['id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($user['name']); ?></a>
                            <div class="text-muted small">@<?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="small"><?php echo htmlspecialchars($user['bio']); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-muted mb-4">Tidak ada user ditemukan.</div>
        <?php endif; ?>

        <!-- Post Results -->
        <h6>Post</h6>
        <?php if ($posts): ?>
            <div class="row mb-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <?php
                                    $photo = (!empty($post['user_photo']) && $post['user_photo'] !== 'default.svg' && file_exists("img/uploads/" . $post['user_photo']))
                                        ? "img/uploads/" . $post['user_photo']
                                        : "img/default.svg";
                                    ?>
                                    <img src="<?php echo $photo; ?>" class="rounded-circle me-2" width="36" height="36" style="object-fit:cover;">
                                    <a href="profilevisit.php?id=<?php echo $post['user_id']; ?>" class="fw-semibold text-decoration-none"><?php echo htmlspecialchars($post['name']); ?></a>
                                    <span class="ms-2 text-muted small"><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div class="mb-2"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                                <?php if (!empty($post['photo']) && $post['photo'] !== 'default.svg' && file_exists("img/posts/" . $post['photo'])): ?>
                                    <img src="img/posts/<?php echo $post['photo']; ?>" class="img-fluid rounded mb-2" style="max-width:100%;max-height:350px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-muted mb-4">Tidak ada post ditemukan.</div>
        <?php endif; ?>

        <!-- Portfolio Results -->
        <h6>Portofolio</h6>
        <?php if ($portfolios): ?>
            <div class="row mb-4">
                <?php foreach ($portfolios as $port): ?>
                    <div class="col-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <?php
                                    $photo = (!empty($port['user_photo']) && $port['user_photo'] !== 'default.svg' && file_exists("img/uploads/" . $port['user_photo']))
                                        ? "img/uploads/" . $port['user_photo']
                                        : "img/default.svg";
                                    ?>
                                    <img src="<?php echo $photo; ?>" class="rounded-circle me-2" width="36" height="36" style="object-fit:cover;">
                                    <a href="profilevisit.php?id=<?php echo $port['user_id']; ?>" class="fw-semibold text-decoration-none"><?php echo htmlspecialchars($port['name']); ?></a>
                                </div>
                                <div class="fw-bold mb-1"><?php echo htmlspecialchars($port['title']); ?></div>
                                <div class="mb-2"><?php echo nl2br(htmlspecialchars($port['description'])); ?></div>
                                <?php if (!empty($port['photo']) && file_exists("img/portfolios/" . $port['photo'])): ?>
                                    <img src="img/portfolios/<?php echo $port['photo']; ?>" class="img-fluid rounded mb-2" style="max-width:100%;max-height:350px;">
                                <?php endif; ?>
                                <a href="profilevisit.php?id=<?php echo $port['user_id']; ?>#portfolio" 
                                   class="btn btn-outline-primary btn-sm mt-2 portfolio-detail-btn" 
                                   data-portid="<?php echo $port['id']; ?>">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-muted mb-4">Tidak ada portofolio ditemukan.</div>
        <?php endif; ?>

    <?php endif; ?>
</div>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
document.querySelectorAll('.portfolio-detail-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        localStorage.setItem('highlightPortfolioId', this.getAttribute('data-portid'));
    });
});
</script>
</body>
</html>