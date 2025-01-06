<?php
session_start(); // Memulai sesi

// Menghapus semua variabel sesi
session_unset();

// Menghancurkan sesi
session_destroy();

// Menghapus cookie sesi jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/'); // Menghapus cookie sesi
}

// Arahkan pengguna ke halaman login setelah logout
header('Location: login.php');
exit;
?>
