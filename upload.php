<?php
session_start();
require 'function.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomorRisalah = $_POST['nomor_risalah'];
    $judulDokumen = $_POST['judul_dokumen'];
    $tanggalRapat = $_POST['tanggal_rapat'];
    $penanggungJawab = $_POST['penanggung_jawab'];
    $status = $_POST['status'];

    // Proses file upload
    $targetDir = "uploads/";
    $fileName = basename($_FILES["document"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFilePath)) {
        // Simpan data ke database
        $sql = "INSERT INTO risalah (nomor_risalah, judul_dokumen, tanggal_rapat, penanggung_jawab, status, file_path)
                VALUES ('$nomorRisalah', '$judulDokumen', '$tanggalRapat', '$penanggungJawab', '$status', '$targetFilePath')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Dokumen berhasil diunggah!";
        } else {
            $_SESSION['message'] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['message'] = "Gagal mengunggah file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Unggah Dokumen</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary">
        <a class="navbar-brand ps-3" href="dashboard.php">Admin Panel</a>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        </a>
                        <a class="nav-link" href="kelola.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Kelola risalah
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Unggah Dokumen</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Unggah Dokumen</li>
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
                            <i class="fas fa-upload"></i> Form Unggah Dokumen
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="nomorRisalah" class="form-label">Nomor Risalah</label>
                                    <input type="text" class="form-control" id="nomorRisalah" name="nomor_risalah" required>
                                </div>
                                <div class="mb-3">
                                    <label for="judulDokumen" class="form-label">Judul Dokumen</label>
                                    <input type="text" class="form-control" id="judulDokumen" name="judul_dokumen" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggalRapat" class="form-label">Tanggal Rapat</label>
                                    <input type="date" class="form-control" id="tanggalRapat" name="tanggal_rapat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="penanggungJawab" class="form-label">Penanggung Jawab</label>
                                    <input type="text" class="form-control" id="penanggungJawab" name="penanggung_jawab" required>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Diverifikasi">Diverifikasi</option>
                                        <option value="Menunggu">Menunggu</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="documentFile" class="form-label">File Dokumen</label>
                                    <input type="file" class="form-control" id="documentFile" name="document" accept=".pdf,.doc,.docx" required>
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
