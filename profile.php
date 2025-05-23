<?php 
require_once("auth.php");
require_once("config.php"); // Pastikan koneksi database

if(isset($_POST['update_photo'])) {
    $target_dir = "img/uploads/";
    $filename = basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $filename;

    // Cek dan buat folder jika belum ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if(move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        try {
            $stmt = $db->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute([$target_file, $_SESSION['user']['id']]);
            // Update session agar foto baru langsung tampil
            $_SESSION['user']['photo'] = $target_file;
            $success = "Photo updated successfully!";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Failed to upload photo.";
    }
}

// Ambil data user terbaru
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <!-- Include CSS -->
</head>
<body>
    <!-- Form upload foto profil -->
    <?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" enctype="multipart/form-data" id="photoForm">
        <img src="<?php echo $user['photo']; ?>" width="120" class="rounded-circle mb-2"><br>
        <input type="file" name="photo" accept="image/*" required>
        <button type="submit" name="update_photo">Upload Photo</button>
    </form>
    <!-- Form edit profile lainnya -->

    <script>
    // Validasi client-side untuk upload foto profil
    document.getElementById("photoForm").addEventListener("submit", function(e) {
        const fileInput = document.querySelector("[name='photo']");
        if (!fileInput.value) {
            e.preventDefault();
            alert("Please select a photo to upload!");
        }
    });
    </script>
</body>
</html>