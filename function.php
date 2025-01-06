<?php
$conn = mysqli_connect("localhost", "root", "", "arsip_risalah");

// Periksa koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
