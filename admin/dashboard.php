<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$result_mobil = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total FROM mobil");
$row_mobil = sqlsrv_fetch_array($result_mobil, SQLSRV_FETCH_ASSOC);
$jumlah_mobil = $row_mobil['total'];

$result_customer = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total FROM member");
$row_customer = sqlsrv_fetch_array($result_customer, SQLSRV_FETCH_ASSOC);
$jumlah_customer = $row_customer['total'];

$result_pegawai = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total FROM pegawai");
$row_pegawai = sqlsrv_fetch_array($result_pegawai, SQLSRV_FETCH_ASSOC);
$jumlah_pegawai = $row_pegawai['total'];

$result_peminjaman = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman");
$row_peminjaman = sqlsrv_fetch_array($result_peminjaman, SQLSRV_FETCH_ASSOC);
$jumlah_peminjaman = $row_peminjaman['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Admin - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Admin Panel</span>
        </div>

        <div class="menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="mobil.php">Data Mobil</a>
            <a href="pegawai.php">Data Pegawai</a>
            <a href="customer.php">Data Customer</a>
            <a href="transaksi.php">Data Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Dashboard Admin</h1>
                <p>Halo, <?= $_SESSION['nama']; ?>. Kelola data Pinjem Mobil dari sini.</p>
            </div>
            <a href="../logout.php" class="btn btn-secondary">Logout</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Mobil</h3>
                <p><?= $jumlah_mobil; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Customer</h3>
                <p><?= $jumlah_customer; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Pegawai</h3>
                <p><?= $jumlah_pegawai; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Peminjaman</h3>
                <p><?= $jumlah_peminjaman; ?></p>
            </div>
        </div>
    </div>

</body>

</html>