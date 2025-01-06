<?php
session_start(); // Pastikan session dimulai sebelum pemeriksaan

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Jika belum login, arahkan ke halaman login
    header('location:login.php');
    exit;
}
?>
