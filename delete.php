<?php
session_start();
require 'function.php';

if (!isset($_GET['id'])) {
    header('Location: kelola.php');
    exit;
}

$id = intval($_GET['id']);

// Ambil informasi file dari database
$query = "SELECT * FROM risalah WHERE id = $id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $filePath = 'uploads/' . $row['file_name']; // Sesuaikan dengan kolom yang menyimpan nama file

    // Hapus data dari database
    $deleteQuery = "DELETE FROM risalah WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        // Hapus file fisik jika ada
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $_SESSION['message'] = 'Data berhasil dihapus.';
    } else {
        $_SESSION['message'] = 'Gagal menghapus data.';
    }
} else {
    $_SESSION['message'] = 'Data tidak ditemukan.';
}

header('Location: kelola.php');
exit;
?>
