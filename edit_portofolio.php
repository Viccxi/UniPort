<?php
require_once("auth.php");
require_once("config.php");

$portfolio_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data portofolio
$stmt = $db->prepare("SELECT * FROM portfolios WHERE id = ?");
$stmt->execute([$portfolio_id]);
$portfolio = $stmt->fetch();

// Cek apakah data ada dan milik user
if (!$portfolio || $portfolio['user_id'] != $_SESSION['user']['id']) {
    die("Portofolio tidak ditemukan atau akses ditolak.");
}

// Proses update
if (isset($_POST['update_portofolio'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $link = $_POST['link'];

    $image_name = $portfolio['image']; // pakai gambar lama jika tidak diubah

    // Cek apakah upload gambar baru
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/portfolios/";
        $new_image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $new_image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Hapus gambar lama jika ada
            if (!empty($portfolio['image']) && file_exists($target_dir . $portfolio['image'])) {
                unlink($target_dir . $portfolio['image']);
            }
            $image_name = $new_image_name;
        }
    }

    // Update data
    $stmt = $db->prepare("UPDATE portfolios SET title=?, category=?, description=?, link=?, image=? WHERE id=?");
    $stmt->execute([$title, $category, $description, $link, $image_name, $portfolio_id]);

    header("Location: profilevisit.php?id=" . $_SESSION['user']['id']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Portofolio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <a href="profilevisit.php?id=<?php echo $_SESSION['user']['id'] ?>" class="btn btn-secondary btn-sm mb-3">← Kembali</a>
    <h3>Edit Portofolio</h3>
    <form method="post" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Judul Proyek</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($portfolio['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($portfolio['category']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($portfolio['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Link</label>
            <input type="url" name="link" class="form-control" value="<?php echo htmlspecialchars($portfolio['link']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Gambar (opsional)</label><br>
            <?php if (!empty($portfolio['image']) && file_exists('uploads/portfolios/' . $portfolio['image'])): ?>
                <img src="uploads/portfolios/<?php echo $portfolio['image']; ?>" width="150" class="mb-2 rounded"><br>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*" class="form-control">
        </div>
        <button type="submit" name="update_portofolio" class="btn btn-success">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>