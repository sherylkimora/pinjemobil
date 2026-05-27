<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pegawai') {
    header("Location: ../login.php");
    exit;
}

$result_cek_awal = sqlsrv_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status_transaksi = 'Menunggu Cek Awal'"
);

$row_cek_awal = sqlsrv_fetch_array($result_cek_awal, SQLSRV_FETCH_ASSOC);
$cek_awal = $row_cek_awal['total'];

$result_cek_akhir = sqlsrv_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status_transaksi = 'Menunggu Cek Akhir'"
);

$row_cek_akhir = sqlsrv_fetch_array($result_cek_akhir, SQLSRV_FETCH_ASSOC);
$cek_akhir = $row_cek_akhir['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Pegawai - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Pegawai</span>
        </div>

        <div class="menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="cek_awal.php">Cek Kondisi Awal</a>
            <a href="cek_akhir.php">Cek Kondisi Akhir</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Dashboard Pegawai</h1>
                <p>Halo, <?= $_SESSION['nama']; ?>. Kelola pengecekan kondisi mobil di sini.</p>
            </div>
            <a href="../logout.php" class="btn btn-secondary">Logout</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Menunggu Cek Awal</h3>
                <p><?= $cek_awal; ?></p>
            </div>

            <div class="stat-card">
                <h3>Menunggu Cek Akhir</h3>
                <p><?= $cek_akhir; ?></p>
            </div>

            <!-- <div class="stat-card">
                <h3>Role</h3>
                <p>Pegawai</p>
            </div> -->
<!-- 
            <div class="stat-card">
                <h3>Status</h3>
                <p>Aktif</p>
            </div> -->
        </div>
    </div>

</body>

</html>