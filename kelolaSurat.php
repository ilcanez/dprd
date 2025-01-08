<?php
session_start();
require 'function.php'; // Koneksi ke database

// Ambil bulan dan tahun dari form atau gunakan bulan dan tahun saat ini sebagai default
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Query untuk dokumen berdasarkan bulan dan tahun yang dipilih
if (isset($_GET['month']) && isset($_GET['year'])) {
    $query = "SELECT * FROM tb_suratmasuk WHERE MONTH(tgl_surat) = ? AND YEAR(tgl_surat) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $selectedMonth, $selectedYear);
} else {
    $query = "SELECT * FROM tb_suratmasuk";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$data = $stmt->get_result();

// Handle file download
if (isset($_GET['download_id'])) {
    $downloadId = $_GET['download_id'];
    $fileQuery = "SELECT file, nama_file FROM tb_suratmasuk WHERE id_suratmasuk = ?";
    $fileStmt = $conn->prepare($fileQuery);
    $fileStmt->bind_param("i", $downloadId);
    $fileStmt->execute();
    $fileResult = $fileStmt->get_result();
    
    if ($row = $fileResult->fetch_assoc()) {
        $file_path = 'uploads/' . $row['file'];
        if (file_exists($file_path)) {
            // Pastikan kita mengirim header yang benar untuk pengunduhan file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "File tidak ditemukan.";
        }
    }
}

// Handle file view
if (isset($_GET['view_id'])) {
    $viewId = $_GET['view_id'];
    $fileQuery = "SELECT file FROM tb_suratmasuk WHERE id_suratmasuk = ?";
    $fileStmt = $conn->prepare($fileQuery);
    $fileStmt->bind_param("i", $viewId);
    $fileStmt->execute();
    $fileResult = $fileStmt->get_result();
    
    if ($row = $fileResult->fetch_assoc()) {
        $file_path = 'uploads/' . $row['file'];
        
        // Periksa apakah file ada dan ekstensi file adalah PDF
        if (file_exists($file_path)) {
            $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            if ($file_extension === 'pdf') {
                // Kirimkan header untuk menampilkan PDF di browser
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
            } else {
                echo "File ini bukan PDF.";
            }
        } else {
            echo "File tidak ditemukan.";
        }
    }
}

// Hapus surat masuk jika ada request untuk menghapus
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    
    // Get file name before deleting record
    $fileQuery = "SELECT file FROM tb_suratmasuk WHERE id_suratmasuk = ?";
    $fileStmt = $conn->prepare($fileQuery);
    $fileStmt->bind_param("i", $deleteId);
    $fileStmt->execute();
    $fileResult = $fileStmt->get_result();
    $fileRow = $fileResult->fetch_assoc();
    
    $deleteQuery = "DELETE FROM tb_suratmasuk WHERE id_suratmasuk = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $deleteId);
    if ($deleteStmt->execute()) {
        // Delete physical file
        if ($fileRow && file_exists('uploads/' . $fileRow['file'])) {
            unlink('uploads/' . $fileRow['file']);
        }
        echo "<script>alert('Surat masuk berhasil dihapus!'); window.location.href = 'kelolaSurat.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus surat masuk.'); window.location.href = 'kelolaSurat.php';</script>";
    }
    $deleteStmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Surat Masuk</title>
    <link href="css/styles.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .action-buttons .btn {
            margin-right: 5px;
        }
        .file-actions {
            display: flex;
            gap: 5px;
        }
        .file-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
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
        </nav>s
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
                    <h1 class="mt-4">Kelola Surat Masuk</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Surat Masuk</li>
                    </ol>
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="month" class="form-label">Bulan</label>
                                <select name="month" id="month" class="form-select">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo $selectedMonth == sprintf("%02d", $m) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select">
                                    <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $selectedYear == $y ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal Surat</th>
                                <th>Perihal</th>
                                <th>File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($data->num_rows > 0): ?>
                                <?php $i = 1; while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo $row['tgl_surat']; ?></td>
                                        <td><?php echo $row['perihal']; ?></td>
                                        <td>
                                            <div class="file-actions">
                                                <a href="?view_id=<?php echo $row['id_suratmasuk']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                                <a href="?download_id=<?php echo $row['id_suratmasuk']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i> Unduh
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="editsurat.php?id=<?php echo $row['id_suratmasuk']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?delete_id=<?php echo $row['id_suratmasuk']; ?>" 
                                                   onclick="return confirm('Yakin ingin menghapus?');" 
                                                   class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
