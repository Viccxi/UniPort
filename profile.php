<?php 
require_once("auth.php");
require_once("config.php"); // Pastikan koneksi database

// Ambil ID user profil yang sedang dibuka
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user']['id'];

// Proses upload foto profil
if(isset($_POST['update_photo'])) {
    $target_dir = "img/";
    $filename = time() . '_' . basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $filename;

    if(move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $stmt = $db->prepare("UPDATE users SET photo = ? WHERE id = ?");
        $stmt->execute([$filename, $_SESSION['user']['id']]);
        $_SESSION['user']['photo'] = $filename;
        header("Location: ".$_SERVER['REQUEST_URI']); exit;
    } else {
        echo "<div class='alert alert-danger'>Upload failed.</div>";
    }
}

// Proses follow
if(isset($_POST['follow'])) {
    $stmt = $db->prepare("INSERT INTO followers (user_id, follower_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$profile_user_id, $_SESSION['user']['id']]);
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

// Proses unfollow
if(isset($_POST['unfollow'])) {
    $stmt = $db->prepare("DELETE FROM followers WHERE user_id = ? AND follower_id = ?");
    $stmt->execute([$profile_user_id, $_SESSION['user']['id']]);
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

// Proses accept/reject permintaan teman
if(isset($_POST['accept'])) {
    $stmt = $db->prepare("UPDATE followers SET status='accepted' WHERE user_id=? AND follower_id=?");
    $stmt->execute([$_SESSION['user']['id'], $_POST['follower_id']]);
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}
if(isset($_POST['reject'])) {
    $stmt = $db->prepare("DELETE FROM followers WHERE user_id=? AND follower_id=?");
    $stmt->execute([$_SESSION['user']['id'], $_POST['follower_id']]);
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

// Proses kirim pesan
if(isset($_POST['send_message'])) {
    $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user']['id'], $_POST['receiver_id'], $_POST['message']]);
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

// Ambil data user profil
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$profile_user = $stmt->fetch();

// Cek status follow
$stmt = $db->prepare("SELECT * FROM followers WHERE user_id = ? AND follower_id = ?");
$stmt->execute([$profile_user_id, $_SESSION['user']['id']]);
$follow = $stmt->fetch();

// Ambil jumlah followers & following
$stmt = $db->prepare("SELECT COUNT(*) FROM followers WHERE user_id=? AND status='accepted'");
$stmt->execute([$profile_user_id]);
$followers_count = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM followers WHERE follower_id=? AND status='accepted'");
$stmt->execute([$profile_user_id]);
$following_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <?php if(isset($success)) echo "<p class='text-success'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

        <div class="card mb-4">
            <div class="card-body text-center">
                <?php
                $photo_file = !empty($_SESSION['user']['photo']) ? "img/" . $_SESSION['user']['photo'] : "img/default.png";
                if(!file_exists($photo_file)) $photo_file = "img/default.png";
                ?>
                <img src="<?php echo $photo_file ?>" width="120" class="rounded-circle mb-2"><br>
                <h3 class="mb-1"><?php echo htmlspecialchars($profile_user['name']); ?></h3>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($profile_user['email']); ?></p>
                <div class="d-flex justify-content-center mb-3">
                    <div class="mx-3">
                        <h5 class="mb-0"><?php echo $followers_count; ?></h5>
                        <small class="text-muted">Followers</small>
                    </div>
                    <div class="mx-3">
                        <h5 class="mb-0"><?php echo $following_count; ?></h5>
                        <small class="text-muted">Following</small>
                    </div>
                </div>
                <?php if($profile_user_id != $_SESSION['user']['id']): ?>
                    <?php if(!$follow): ?>
                        <form method="post" style="display:inline">
                            <button name="follow" class="btn btn-primary btn-sm">Follow</button>
                        </form>
                    <?php elseif($follow['status'] == 'pending'): ?>
                        <button class="btn btn-warning btn-sm" disabled>Pending</button>
                    <?php elseif($follow['status'] == 'accepted'): ?>
                        <form method="post" style="display:inline">
                            <button name="unfollow" class="btn btn-danger btn-sm">Unfollow</button>
                        </form>
                        <!-- Form kirim pesan -->
                        <form method="post" class="mt-2">
                            <input type="hidden" name="receiver_id" value="<?php echo $profile_user_id; ?>">
                            <textarea name="message" required class="form-control mb-2" placeholder="Tulis pesan..."></textarea>
                            <button name="send_message" class="btn btn-primary btn-sm">Kirim</button>
                        </form>
                        <!-- Tampilkan pesan -->
                        <div class="mt-3 text-start">
                            <h6>Pesan:</h6>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
                            $stmt->execute([$_SESSION['user']['id'], $profile_user_id, $profile_user_id, $_SESSION['user']['id']]);
                            while($msg = $stmt->fetch()):
                                echo "<div><b>".($msg['sender_id']==$_SESSION['user']['id']?"Saya":"Dia").":</b> ".htmlspecialchars($msg['message'])."</div>";
                            endwhile;
                            ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Form upload foto profil untuk diri sendiri -->
                    <form method="post" enctype="multipart/form-data" id="photoForm" class="mt-2">
                        <input type="file" name="photo" accept="image/*" required>
                        <button type="submit" name="update_photo" class="btn btn-secondary btn-sm">Upload Photo</button>
                    </form>
                    <!-- Tampilkan permintaan teman -->
                    <div class="mt-4 text-start">
                        <h6>Permintaan Teman:</h6>
                        <?php
                        $stmt = $db->prepare("SELECT followers.*, users.name FROM followers JOIN users ON followers.follower_id = users.id WHERE followers.user_id = ? AND followers.status = 'pending'");
                        $stmt->execute([$_SESSION['user']['id']]);
                        while($req = $stmt->fetch()):
                        ?>
                        <div>
                            <?php echo htmlspecialchars($req['name']); ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="follower_id" value="<?php echo $req['follower_id']; ?>">
                                <button name="accept" class="btn btn-success btn-sm">Accept</button>
                                <button name="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>