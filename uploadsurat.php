<?php
session_start();
require 'function.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noSurat = $_POST['no_surat'];
    $tglSurat = $_POST['tgl_surat'];
    $tglDiterima = $_POST['tgl_diterima'];
    $perihal = $_POST['perihal'];
    $asalSurat = $_POST['asal_surat'];
    $keterangan = $_POST['keterangan'];

    // Periksa apakah nomor surat sudah ada
    $checkQuery = "SELECT * FROM tb_suratmasuk WHERE no_surat = '$noSurat'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Nomor Surat sudah ada. Gunakan nomor lain.";
    } else {
        // Proses file upload
        $targetDir = "uploads/";
        $fileName = basename($_FILES["file_surat"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["file_surat"]["tmp_name"], $targetFilePath)) {
            // Simpan data ke database
            $sql = "INSERT INTO tb_suratmasuk (no_surat, tgl_surat, tgl_diterima, perihal, asal_surat, keterangan, file_surat)
                    VALUES ('$noSurat', '$tglSurat', '$tglDiterima', '$perihal', '$asalSurat', '$keterangan', '$targetFilePath')";

            if (mysqli_query($conn, $sql)) {
                $_SESSION['message'] = "Surat berhasil diunggah!";
            } else {
                $_SESSION['message'] = "Terjadi kesalahan: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['message'] = "Gagal mengunggah file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Unggah Surat</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary">
            <a class="navbar-brand ps-3" href="index.php">
                <img src="images/logodprd.png" alt="Logo" width="30" height="30" class="d-inline-block align-top"> Arsip
            </a>
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Cari risalah..." aria-label="Search" aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-light" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="settings.php">Pengaturan Akun</a></li>
                        <li><a class="dropdown-item" href="activity.php">Log Aktivitas</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                <div class="nav">
                            <div class="sb-sidenav-menu-heading">Utama</div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="upload.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-upload"></i></div>
                                Unggah Dokumen Risalah
                            </a>
                            <a class="nav-link" href="upload.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-upload"></i></div>
                                Unggah Surat
                            </a>
                            <a class="nav-link" href="kelola.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                                Kelola Risalah
                            </a>
                            <a class="nav-link" href="kelolaSurat.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                                Kelola Surat
                            </a>
                            <a class="nav-link" href="statistik.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                                Statistik Arsip
                            </a>
                            <a class="nav-link" href="settings.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                Pengaturan Akun
                            </a>
                        </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Masuk sebagai:</div>
                    Admin
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Unggah Surat</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Unggah Surat</li>
                    </ol>

                    <!-- Pesan Notifikasi -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info">
                            <?php 
                            echo $_SESSION['message']; 
                            unset($_SESSION['message']); 
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-upload"></i> Form Unggah Surat
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="noSurat" class="form-label">Nomor Surat</label>
                                    <input type="text" class="form-control" id="noSurat" name="no_surat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tglSurat" class="form-label">Tanggal Surat</label>
                                    <input type="date" class="form-control" id="tglSurat" name="tgl_surat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tglDiterima" class="form-label">Tanggal Diterima</label>
                                    <input type="date" class="form-control" id="tglDiterima" name="tgl_diterima" required>
                                </div>
                                <div class="mb-3">
                                    <label for="perihal" class="form-label">Perihal</label>
                                    <input type="text" class="form-control" id="perihal" name="perihal" required>
                                </div>
                                <div class="mb-3">
                                    <label for="asalSurat" class="form-label">Asal Surat</label>
                                    <input type="text" class="form-control" id="asalSurat" name="asal_surat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fileSurat" class="form-label">File Surat</label>
                                    <input type="file" class="form-control" id="fileSurat" name="file_surat" accept=".pdf,.doc,.docx" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Unggah</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">&copy; 2024 Your Company</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
