<?php
session_start();
require 'function.php'; // Koneksi ke database

// Proses penghapusan file dan data dari database
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Ambil informasi file dari database
    $query = "SELECT file_path FROM risalah WHERE id = $deleteId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $filePath = $row['file_path'];

        // Hapus file dari sistem
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Hapus data dari database
                $deleteQuery = "DELETE FROM risalah WHERE id = $deleteId";
                if (mysqli_query($conn, $deleteQuery)) {
                    $_SESSION['message'] = "File dan data berhasil dihapus.";
                } else {
                    $_SESSION['message'] = "File terhapus, tetapi gagal menghapus data dari database.";
                }
            } else {
                $_SESSION['message'] = "Gagal menghapus file. Periksa izin direktori.";
            }
        } else {
            $_SESSION['message'] = "File tidak ditemukan di sistem.";
        }
    } else {
        $_SESSION['message'] = "Data tidak ditemukan di database.";
    }

    header('Location: kelola.php');
    exit;
}

// Ambil bulan dan tahun saat ini
$currentMonth = date('m');
$currentYear = date('Y');

// Query untuk dokumen bulan ini
$query = "SELECT * FROM risalah WHERE MONTH(tanggal_rapat) = '$currentMonth' AND YEAR(tanggal_rapat) = '$currentYear'";

// Ambil data dari database
$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Risalah</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .table-actions {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 5px 10px;
        }
        .table td {
            vertical-align: middle;
        }
        .file-download {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #007bff;
            text-decoration: none;
        }
        .file-download:hover {
            text-decoration: underline;
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
                    <h1 class="mt-4">Kelola Risalah</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Risalah</li>
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
                            <i class="fas fa-table"></i> Daftar Risalah
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nomor Risalah</th>
                                        <th>Judul Dokumen</th>
                                        <th>Tanggal Rapat</th>
                                        <th>Penanggung Jawab</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data && mysqli_num_rows($data) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($data)): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['nomor_risalah']; ?></td>
                                            <td><?php echo $row['judul_dokumen']; ?></td>
                                            <td><?php echo $row['tanggal_rapat']; ?></td>
                                            <td><?php echo $row['penanggung_jawab']; ?></td>
                                            <td><?php echo $row['status']; ?></td>
                                            <td>
                                                <a href="<?php echo $row['file_path']; ?>" class="file-download" target="_blank">
                                                    <i class="fas fa-download"></i> Unduh
                                                </a>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="kelola.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus risalah ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data untuk bulan ini.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
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
