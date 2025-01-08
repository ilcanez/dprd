<?php
session_start();
require 'function.php'; // Koneksi ke database

// Ambil bulan dan tahun dari form atau gunakan bulan dan tahun saat ini sebagai default
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Query untuk dokumen berdasarkan bulan dan tahun yang dipilih, atau jika tidak ada bulan dan tahun, tampilkan semua dokumen
if (isset($_GET['month']) && isset($_GET['year'])) {
    $query = "SELECT * FROM risalah WHERE MONTH(tanggal_rapat) = ? AND YEAR(tanggal_rapat) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $selectedMonth, $selectedYear);
} else {
    $query = "SELECT * FROM risalah";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$data = $stmt->get_result();

// Hapus risalah jika ada request untuk menghapus
if (isset($_GET['delete_nomor_risalah'])) {
    $deleteNomorRisalah = $_GET['delete_nomor_risalah'];
    $deleteQuery = "DELETE FROM risalah WHERE nomor_risalah = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $deleteNomorRisalah);
    if ($deleteStmt->execute()) {
        echo "<script>alert('Risalah berhasil dihapus!'); window.location.href = 'kelola.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus risalah.'); window.location.href = 'kelola.php';</script>";
    }
    $deleteStmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Risalah</title>
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
                    <h1 class="mt-4">Kelola Risalah</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Risalah</li>
                    </ol>

                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="month" class="form-label">Bulan</label>
                                <select name="month" id="month" class="form-select">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo $selectedMonth == sprintf("%02d", $m) ? 'selected' : ''; ?>>
                                            <?php echo date("F", mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select">
                                    <?php for ($y = date("Y") - 10; $y <= date("Y"); $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $selectedYear == $y ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 align-self-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tombol untuk menampilkan semua dokumen -->
                    <a href="kelola.php" class="btn btn-secondary mb-4">Tampilkan Semua Dokumen</a>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
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
                            <?php if ($data && $data->num_rows > 0): ?>
                                <?php while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nomor_risalah']); ?></td>
                                        <td><?php echo htmlspecialchars($row['judul_dokumen']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tanggal_rapat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['penanggung_jawab']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td>
    <div class="btn-group" role="group" aria-label="File">
        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="btn btn-primary btn-sm me-2" target="_blank">
            <i class="fas fa-eye"></i> Lihat
        </a>
        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="btn btn-success btn-sm" download>
            <i class="fas fa-download"></i> Unduh
        </a>
    </div>
</td>
<td>
    <div class="btn-group" role="group" aria-label="Aksi">
        <a href="edit.php?nomor_risalah=<?php echo htmlspecialchars($row['nomor_risalah']); ?>" class="btn btn-warning btn-sm me-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="kelola.php?delete_nomor_risalah=<?php echo htmlspecialchars($row['nomor_risalah']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus risalah ini?')">
            <i class="fas fa-trash"></i> Hapus
        </a>
    </div>
</td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data untuk bulan yang dipilih.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
