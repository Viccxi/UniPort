<?php
require_once("auth.php");
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $link = $_POST['link'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user']['id'];

    // Upload gambar jika ada
    $image_filename = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "img/portfolios/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_filename = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    // Simpan ke database
    $stmt = $db->prepare("INSERT INTO portfolios (user_id, title, description, category, link, file, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $category, $link, $image_filename, $status]);

    header("Location: profilevisit.php?id=$user_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Portofolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-4">Tambah Portofolio</h3>
    <form method="post" enctype="multipart/form-data" class="shadow p-4 rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Judul Proyek</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control" placeholder="Ceritakan tentang proyek ini..." required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="category" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Desain">Desain</option>
                <option value="Musik">Musik</option>
                <option value="Pemrograman">Pemrograman</option>
                <option value="Multimedia">Multimedia</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Link (jika ada)</label>
            <input type="url" name="link" class="form-control" placeholder="https://contoh.com/proyek">
        </div>
        <div class="mb-3">
            <label class="form-label">Upload Gambar</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="published" selected>Publik</option>
                <option value="draft">Draft (Belum ditampilkan)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Portofolio</button>
        <a href="profilevisit.php?id=<?= $_SESSION['user']['id'] ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
