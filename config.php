<?php
try {
    // Gantilah 'root' dan '' sesuai dengan kredensial yang benar jika diperlukan
    $pdo = new PDO('mysql:host=localhost;dbname=arsip_risalah', 'root', '');
    // Menetapkan mode error PDO untuk menampilkan pengecualian jika terjadi kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Cek apakah koneksi berhasil
if (!$pdo) {
    die("Koneksi database gagal.");
}
?>
