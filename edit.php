<?php
session_start();
require 'function.php'; // Koneksi ke database

// Pastikan id dokumen tersedia di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data dokumen berdasarkan ID
    $query = "SELECT * FROM risalah WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['message'] = "Dokumen tidak ditemukan.";
        header('Location: kelola.php');
        exit;
    }
} else {
    $_SESSION['message'] = "ID tidak ditemukan.";
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
                    nomor_risalah = '$nomorRisalah', 
                    judul_dokumen = '$judulDokumen', 
                    tanggal_rapat = '$tanggalRapat', 
                    penanggung_jawab = '$penanggungJawab', 
                    status = '$status' 
                    WHERE id = $id";
    
    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['message'] = "Dokumen berhasil diperbarui.";
        header('Location: kelola.php');
        exit;
    } else {
        $_SESSION['message'] = "Gagal memperbarui dokumen.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Edit Dokumen</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .btn-sm {
            padding: 5px 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
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
                        <a class="nav-link" href="upload.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-upload"></i></div>
                            Unggah Dokumen
                        </a>
                        <a class="nav-link" href="kelola.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Kelola Risalah
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Edit Dokumen</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="kelola.php">Kelola Risalah</a></li>
                        <li class="breadcrumb-item active">Edit Dokumen</li>
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
                            <i class="fas fa-edit"></i> Edit Risalah
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="form-group">
                                    <label for="nomor_risalah">Nomor Risalah</label>
                                    <input type="text" class="form-control" id="nomor_risalah" name="nomor_risalah" value="<?php echo $row['nomor_risalah']; ?>" required />
                                </div>
                                <div class="form-group">
                                    <label for="judul_dokumen">Judul Dokumen</label>
                                    <input type="text" class="form-control" id="judul_dokumen" name="judul_dokumen" value="<?php echo $row['judul_dokumen']; ?>" required />
                                </div>
                                <div class="form-group">
                                    <label for="tanggal_rapat">Tanggal Rapat</label>
                                    <input type="date" class="form-control" id="tanggal_rapat" name="tanggal_rapat" value="<?php echo $row['tanggal_rapat']; ?>" required />
                                </div>
                                <div class="form-group">
                                    <label for="penanggung_jawab">Penanggung Jawab</label>
                                    <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab" value="<?php echo $row['penanggung_jawab']; ?>" required />
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <input type="text" class="form-control" id="status" name="status" value="<?php echo $row['status']; ?>" required />
                                </div>
                                <button type="submit" name="update" class="btn btn-primary">Perbarui Dokumen</button>
                                <a href="kelola.php" class="btn btn-secondary">Kembali</a>
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
