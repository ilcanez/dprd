<?php
session_start();
require 'function.php';

// Get document details by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Pastikan semua kolom yang diperlukan diambil
    $query = "SELECT id_suratmasuk, perihal, tgl_surat, file, nama_file FROM tb_suratmasuk WHERE id_suratmasuk = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $surat = $result->fetch_assoc();
    
    if (!$surat) {
        echo "<script>alert('Dokumen tidak ditemukan.'); window.location.href = 'kelolaSurat.php';</script>";
        exit;
    }
} else {
    header("Location: kelolaSurat.php");
    exit;
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
        if (file_exists($file_path)) {
            $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            if ($file_extension === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
                readfile($file_path);
                exit;
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $perihal = $_POST['perihal'];
    $tgl_surat = $_POST['tgl_surat'];
    $updateFields = [];
    $queryParams = [];
    $types = "";

    // Basic fields update
    $updateFields[] = "perihal = ?";
    $updateFields[] = "tgl_surat = ?";
    $queryParams[] = $perihal;
    $queryParams[] = $tgl_surat;
    $types .= "ss"; // string, string

    // Handle file upload if new file is selected
    if (isset($_FILES['new_file']) && $_FILES['new_file']['size'] > 0) {
        $file = $_FILES['new_file'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = array('pdf', 'doc', 'docx');
        
        if (in_array($fileExt, $allowedExt)) {
            if ($fileError === 0) {
                // Generate unique filename
                $fileNameNew = uniqid('', true) . "." . $fileExt;
                $fileDestination = 'uploads/' . $fileNameNew;
                
                if (move_uploaded_file($fileTmp, $fileDestination)) {
                    // Delete old file if exists
                    if ($surat['file'] && file_exists('uploads/' . $surat['file'])) {
                        unlink('uploads/' . $surat['file']);
                    }
                    
                    // Add file update to query
                    $updateFields[] = "file = ?";
                    $updateFields[] = "nama_file = ?";
                    $queryParams[] = $fileNameNew;
                    $queryParams[] = $fileName;
                    $types .= "ss"; // string, string
                }
            }
        }
    }

    // Prepare and execute update query
    if (!empty($updateFields)) {
        $queryParams[] = $id;
        $types .= "i"; // integer for id
        
        $query = "UPDATE tb_suratmasuk SET " . implode(", ", $updateFields) . " WHERE id_suratmasuk = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$queryParams);
        
        if ($stmt->execute()) {
            echo "<script>alert('Dokumen berhasil diperbarui!'); window.location.href = 'kelolaSurat.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui dokumen.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Surat Masuk</title>
    <link href="css/styles.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="sb-nav-fixed">
    <!-- Header -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary">
        <a class="navbar-brand ps-3" href="dashboard.php">Admin Panel</a>
    </nav>

    <div id="layoutSidenav">
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Edit Surat Masuk</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="kelolaSurat.php">Kelola Surat Masuk</a></li>
                        <li class="breadcrumb-item active">Edit Surat</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="perihal" class="form-label">Perihal</label>
                                    <input type="text" class="form-control" id="perihal" name="perihal" 
                                           value="<?php echo htmlspecialchars($surat['perihal']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tgl_surat" class="form-label">Tanggal Surat</label>
                                    <input type="date" class="form-control" id="tgl_surat" name="tgl_surat" 
                                           value="<?php echo $surat['tgl_surat']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">File Saat Ini</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted">
                                            <?php 
                                            if (isset($surat['nama_file']) && $surat['nama_file'] != '') {
                                                echo htmlspecialchars($surat['nama_file']);
                                            } else {
                                                echo "(Tidak ada file)";
                                            }
                                            ?>
                                        </span>
                                        <?php if (isset($surat['file']) && $surat['file'] != ''): ?>
                                            <a href="?view_id=<?php echo $id; ?>" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_file" class="form-label">Upload File Baru (Opsional)</label>
                                    <input type="file" class="form-control" id="new_file" name="new_file" 
                                           accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Format yang diizinkan: PDF, DOC, DOCX</small>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="kelolaSurat.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>