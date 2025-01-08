<?php
session_start();
require 'function.php'; // Koneksi ke database

// Pastikan nomor_risalah tersedia di URL
if (isset($_GET['nomor_risalah'])) {
    $nomorRisalah = $_GET['nomor_risalah'];

    // Query untuk mendapatkan data berdasarkan nomor risalah
    $query = "SELECT * FROM risalah WHERE nomor_risalah = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nomorRisalah); // "s" untuk string
    $stmt->execute();
    $result = $stmt->get_result();

    // Pastikan data ditemukan
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Dokumen tidak ditemukan.";
        header('Location: kelola.php');
        exit;
    }
} else {
    $_SESSION['message'] = "Nomor Risalah tidak ditemukan.";
    header('Location: kelola.php');
    exit;
}

// Proses pengeditan dokumen
if (isset($_POST['update'])) {
    $nomorRisalah = $_POST['nomor_risalah'];
    $judulDokumen = $_POST['judul_dokumen'];
    $tanggalRapat = $_POST['tanggal_rapat'];
    $penanggungJawab = $_POST['penanggung_jawab'];
    $status = $_POST['status'];

    // Update data dokumen di database
    $updateQuery = "UPDATE risalah SET 
                    nomor_risalah = ?, 
                    judul_dokumen = ?, 
                    tanggal_rapat = ?, 
                    penanggung_jawab = ?, 
                    status = ? 
                    WHERE nomor_risalah = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssss", $nomorRisalah, $judulDokumen, $tanggalRapat, $penanggungJawab, $status, $nomorRisalah);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Dokumen berhasil diperbarui.";
        header('Location: kelola.php');
        exit;
    } else {
        $_SESSION['message'] = "Gagal memperbarui dokumen.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Risalah</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="sb-nav-fixed">
    <!-- Topbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary">
        <a class="navbar-brand ps-3" href="index.php">
            <img src="images/logodprd.png" alt="Logo" width="30" height="30" class="d-inline-block align-top"> Arsip
        </a>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="settings.php">Pengaturan Akun</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Sidenav -->
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
                        <a class="nav-link" href="uploadsurat.php">
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
                    <h1 class="mt-4">Edit Risalah</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="kelola.php">Kelola Risalah</a></li>
                        <li class="breadcrumb-item active">Edit Risalah</li>
                    </ol>

                    <form method="post">
                        <div class="form-group">
                            <label for="nomor_risalah">Nomor Risalah</label>
                            <input type="text" class="form-control" id="nomor_risalah" name="nomor_risalah" value="<?php echo htmlspecialchars($row['nomor_risalah']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="judul_dokumen">Judul Dokumen</label>
                            <input type="text" class="form-control" id="judul_dokumen" name="judul_dokumen" value="<?php echo htmlspecialchars($row['judul_dokumen']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="tanggal_rapat">Tanggal Rapat</label>
                            <input type="date" class="form-control" id="tanggal_rapat" name="tanggal_rapat" value="<?php echo htmlspecialchars($row['tanggal_rapat']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="penanggung_jawab">Penanggung Jawab</label>
                            <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" value="<?php echo htmlspecialchars($row['penanggung_jawab']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($row['status']); ?>" required />
                        </div>
                        <button type="submit" name="update" class="btn btn-primary">Perbarui</button>
                        <a href="kelola.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
