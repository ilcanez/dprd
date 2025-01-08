<?php
session_start();
require 'function.php';
echo "Session status: " . (isset($_SESSION['logged_in']) ? "Logged in" : "Not logged in") . "<br>"; // Debugging

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection
$connection = mysqli_connect("localhost", "root", "", "arsip_risalah"); // Ganti dengan detail koneksi database Anda

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Menentukan rentang tahun untuk grafik (5 tahun sebelumnya dan 5 tahun mendatang dari tahun 2025)
$start_year = 2020; // 5 tahun ke belakang
$end_year = 2030;   // 5 tahun ke depan

// Query untuk mengambil data statistik (dokumen per bulan)
$query = "
    SELECT YEAR(tanggal_rapat) AS year, MONTH(tanggal_rapat) AS month, COUNT(*) AS document_count
    FROM risalah
    WHERE YEAR(tanggal_rapat) BETWEEN $start_year AND $end_year
    GROUP BY YEAR(tanggal_rapat), MONTH(tanggal_rapat)
    ORDER BY year ASC, month ASC
";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Menyiapkan data untuk Chart.js
$months = [];
$document_counts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $months[] = date("F Y", mktime(0, 0, 0, $row['month'], 1, $row['year']));
    $document_counts[] = $row['document_count'];
}

// Query untuk mengambil semua "Risalah" dokumen untuk tabel
$risalahQuery = "SELECT * FROM risalah ORDER BY tanggal_rapat DESC";
$risalahResult = mysqli_query($connection, $risalahQuery);

if (!$risalahResult) {
    die("Query failed: " . mysqli_error($connection));
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Sistem Informasi Pengarsipan" />
        <meta name="author" content="Your Name" />
        <title>Dashboard - Sistem Informasi Pengarsipan</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Ringkasan</li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Total Risalah</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">Kelola Surat</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="nav-link" href="kelolaSurat.php?status=menunggu">  Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">Transkript Audio</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="nav-link" href="kelola.php?status=menunggu">  Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body">Dokumen keluar</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-area me-1"></i>
                                        Statistik Dokumen per Bulan
                                    </div>
                                    <div class="card-body">
                                        <canvas id="areaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Distribusi Dokumen berdasarkan Kategori
                                    </div>
                                    <div class="card-body">
                                        <canvas id="barChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                 Dokumen baru diunggah  
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nomor Risalah</th>
                                            <th>Judul Dokumen</th>
                                            <th>Tanggal Rapat</th>
                                            <th>Penanggung Jawab</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Check if $risalahResult contains any rows before looping through
                                        if (mysqli_num_rows($risalahResult) > 0) {
                                            while ($row = mysqli_fetch_assoc($risalahResult)) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['nomor_risalah']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['judul_dokumen']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['tanggal_rapat']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['penanggung_jawab']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                                echo "<td><a href='#' class='btn btn-primary btn-sm'>Detail</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            var ctx1 = document.getElementById('areaChart').getContext('2d');
            var ctx2 = document.getElementById('barChart').getContext('2d');
            var areaChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: <?php echo json_encode($document_counts); ?>,
                        fill: false,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: 0.1
                    }]
                }
            });

            var barChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Kategori 1', 'Kategori 2', 'Kategori 3'], // Gantilah dengan kategori yang sesuai
                    datasets: [{
                        label: 'Distribusi Dokumen',
                        data: [12, 19, 3], // Gantilah dengan data yang sesuai
                        backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                        borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </body>
</html>
