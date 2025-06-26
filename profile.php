<?php 
require_once("auth.php");
require_once("config.php"); // Pastikan koneksi database

// Ambil ID user profil yang sedang dibuka
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user']['id'];

// Proses upload foto profil
if(isset($_POST['update_photo'])) {
    $target_dir = "img/uploads/";
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
    <!-- Tombol kembali di kanan atas -->
    <nav class="navbar navbar-light bg-light mb-4">
        <div class="container-fluid justify-content-end">
            <a href="timeline.php" class="btn btn-outline-primary">Kembali</a>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if(isset($success)) echo "<p class='text-success'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

        <div class="card mb-4">
            <div class="card-body text-center">
                <?php
                if (!empty($profile_user['photo']) && $profile_user['photo'] !== 'default.svg' && file_exists("img/uploads/" . $profile_user['photo'])) {
                    $photo_file = "img/uploads/" . $profile_user['photo'];
                } else {
                    $photo_file = "img/default.svg";
                }
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
                    <?php if($profile_user_id == $_SESSION['user']['id']): ?>
    <form method="post" class="mt-3 text-start" style="max-width:400px;margin:auto;">
        <div class="mb-2">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="2"><?php echo htmlspecialchars($profile_user['bio'] ?? ''); ?></textarea>
        </div>
        <div class="mb-2">
            <label class="form-label">Tanggal Lahir</label>
            <div class="row g-1">
                <div class="col">
                    <select name="birth_day" class="form-select">
                        <option value="">Hari</option>
                        <?php for($d=1;$d<=31;$d++): ?>
                            <option value="<?php echo $d; ?>" <?php if(isset($profile_user['birthdate']) && date('j',strtotime($profile_user['birthdate']))==$d) echo 'selected'; ?>><?php echo $d; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col">
                    <select name="birth_month" class="form-select">
                        <option value="">Bulan</option>
                        <?php for($m=1;$m<=12;$m++): ?>
                            <option value="<?php echo $m; ?>" <?php if(isset($profile_user['birthdate']) && date('n',strtotime($profile_user['birthdate']))==$m) echo 'selected'; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col">
                    <select name="birth_year" class="form-select">
                        <option value="">Tahun</option>
                        <?php for($y=date('Y');$y>=1950;$y--): ?>
                            <option value="<?php echo $y; ?>" <?php if(isset($profile_user['birthdate']) && date('Y',strtotime($profile_user['birthdate']))==$y) echo 'selected'; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Bekerja di</label>
            <input type="text" name="work" class="form-control" value="<?php echo htmlspecialchars($profile_user['work'] ?? ''); ?>">
        </div>
        <div class="mb-2">
            <label class="form-label">Alamat</label>
            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($profile_user['address'] ?? ''); ?>">
        </div>
        <button type="submit" name="update_profile" class="btn btn-success btn-sm">Simpan Profil</button>
    </form>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
if (isset($_POST['update_profile'])) {
    $bio = $_POST['bio'] ?? '';
    $work = $_POST['work'] ?? '';
    $address = $_POST['address'] ?? '';

    // Gabungkan tanggal lahir
    $birth_day = $_POST['birth_day'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_year = $_POST['birth_year'] ?? '';

    $birthdate = null;
    if ($birth_day && $birth_month && $birth_year) {
        $birthdate = sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day);
    }

    $stmt = $db->prepare("UPDATE users SET bio = ?, birthdate = ?, work = ?, address = ? WHERE id = ?");
    $stmt->execute([$bio, $birthdate, $work, $address, $_SESSION['user']['id']]);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>