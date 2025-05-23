<?php

session_start();
unset($_SESSION['user']); // Hapus hanya session user
header("Location: index.php");
exit;