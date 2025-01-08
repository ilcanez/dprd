<?php
session_start();
require 'function.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "arsip_risalah");

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query: Total Arsip Berdasarkan Jenis
$queryTotal = "
    SELECT 'Risalah' AS jenis, COUNT(*) AS total FROM risalah
    UNION ALL
    SELECT 'Surat Masuk', COUNT(*) FROM tb_suratmasuk
    UNION ALL
    SELECT 'Surat Keluar', COUNT(*) FROM tb_suratkeluar;
";

$resultTotal = $mysqli->query($queryTotal);
$dataTotal = [];
if ($resultTotal) {
    while ($row = $resultTotal->fetch_assoc()) {
        $dataTotal[] = $row;
    }
} else {
    die("Error pada query Total: " . $mysqli->error);
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Arsip</title>
    <link href="css/styles.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="sb-nav-fixed">
    <!-- Header -->
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
        <!-- Sidebar -->
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
                    <h1 class="mt-4">Statistik Arsip</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Statistik Arsip</li>
                    </ol>

                    <div class="row">
                        <!-- Diagram 2 (Bar) -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar"></i> Diagram Bar Total Arsip
                                </div>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Diagram 3 (Area) -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-area"></i> Statistik Area Total Arsip
                                </div>
                                <div class="card-body">
                                    <canvas id="areaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Diagram 1 (Pie) -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie"></i> Diagram Pie
                                </div>
                                <div class="card-body">
                                    <canvas id="pieChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Diagram 4 (Doughnut) -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-doughnut"></i> Doughnut Chart
                                </div>
                                <div class="card-body">
                                    <canvas id="doughnutChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Data Total Arsip dari database
        const jenisArsip = <?php echo json_encode(array_column($dataTotal, 'jenis')); ?>;
        const totalArsip = <?php echo json_encode(array_column($dataTotal, 'total')); ?>;

        // Diagram Bar Total Arsip
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: jenisArsip,
                datasets: [{
                    label: 'Jumlah Arsip',
                    data: totalArsip,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107']
                }]
            }
        });

        // Diagram Area Total Arsip
        const areaCtx = document.getElementById('areaChart').getContext('2d');
        new Chart(areaCtx, {
            type: 'line',
            data: {
                labels: jenisArsip,
                datasets: [{
                    label: 'Jumlah Arsip',
                    data: totalArsip,
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderColor: '#007bff',
                    fill: true
                }]
            }
        });

        // Diagram Pie untuk Total Arsip
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: jenisArsip,
                datasets: [{
                    data: totalArsip,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107']
                }]
            }
        });

        // Doughnut Chart untuk total arsip
        const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: jenisArsip,
                datasets: [{
                    data: totalArsip,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107']
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
