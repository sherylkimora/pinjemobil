<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

$id_member = $_SESSION['id_member'];

/* Ambil data cabang customer */
$get_cabang = sqlsrv_query(
    $koneksi,
    "SELECT 
        member.id_cabang, 
        cabang.nama_cabang, 
        cabang.alamat
     FROM member
     LEFT JOIN cabang ON member.id_cabang = cabang.id_cabang
     WHERE member.id_member = ?",
    [$id_member]
);

if ($get_cabang === false) {
    die(print_r(sqlsrv_errors(), true));
}

$cabang_customer = sqlsrv_fetch_array($get_cabang, SQLSRV_FETCH_ASSOC);

$id_cabang_customer = $cabang_customer['id_cabang'] ?? null;
$nama_cabang = $cabang_customer['nama_cabang'] ?? 'Belum memilih cabang';
$alamat_cabang = $cabang_customer['alamat'] ?? '-';

/* Hitung mobil tersedia sesuai cabang customer */
if ($id_cabang_customer) {
    $result_mobil = sqlsrv_query(
        $koneksi,
        "SELECT COUNT(*) AS total 
         FROM mobil 
         WHERE status_mobil = 'Tersedia'
         AND id_cabang = ?",
        [$id_cabang_customer]
    );
} else {
    $result_mobil = sqlsrv_query(
        $koneksi,
        "SELECT COUNT(*) AS total 
         FROM mobil 
         WHERE status_mobil = 'Tersedia'"
    );
}

if ($result_mobil === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row_mobil = sqlsrv_fetch_array($result_mobil, SQLSRV_FETCH_ASSOC);
$mobil_tersedia = $row_mobil['total'];

/* Hitung riwayat peminjaman customer */
$result_riwayat = sqlsrv_query(
    $koneksi,
    "SELECT COUNT(*) AS total 
     FROM peminjaman 
     WHERE id_member = ?",
    [$id_member]
);

if ($result_riwayat === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row_riwayat = sqlsrv_fetch_array($result_riwayat, SQLSRV_FETCH_ASSOC);
$riwayat = $row_riwayat['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Customer - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .branch-card small {
            display: block;
            margin-top: 8px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .stats-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        @media (max-width: 900px) {
            .stats-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
        </div>

        <div class="menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="daftar_mobil.php">Daftar Mobil</a>
            <a href="riwayat.php">Riwayat Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="hero">
            <div class="hero-content">
                <h1>Find your car!</h1>
                <p>
                    Pilih mobil yang tersedia di cabang kamu, tentukan tanggal peminjaman,
                    lalu ajukan sewa mobil dengan mudah.
                </p>
                <a href="daftar_mobil.php" class="btn hero-btn">Lihat Mobil</a>
            </div>
        </div>

        <div class="topbar">
            <div class="page-title">
                <h1>Dashboard Customer</h1>
                <p>Halo, <?= $_SESSION['nama']; ?>. Selamat datang di Pinjem Mobil.</p>
            </div>
            <a href="../logout.php" class="btn btn-secondary">Logout</a>
        </div>

        <div class="stats stats-3">
            <div class="stat-card">
                <h3>Mobil Tersedia</h3>
                <p><?= $mobil_tersedia; ?></p>
            </div>

            <div class="stat-card">
                <h3>Riwayat Peminjaman</h3>
                <p><?= $riwayat; ?></p>
            </div>

            <div class="stat-card branch-card">
                <h3>Cabang Saya</h3>
                <p><?= $nama_cabang; ?></p>
                <small><?= $alamat_cabang; ?></small>
            </div>
        </div>
    </div>

</body>

</html>