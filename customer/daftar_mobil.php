<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

$id_member = $_SESSION['id_member'];

$get_member = sqlsrv_query(
    $koneksi,
    "SELECT id_cabang FROM member WHERE id_member = ?",
    [$id_member]
);

if ($get_member === false) {
    die(print_r(sqlsrv_errors(), true));
}

$member = sqlsrv_fetch_array($get_member, SQLSRV_FETCH_ASSOC);
$id_cabang_member = $member['id_cabang'];

$query = "SELECT mobil.*, cabang.nama_cabang
          FROM mobil
          LEFT JOIN cabang ON mobil.id_cabang = cabang.id_cabang
          WHERE mobil.status_mobil = 'Tersedia'
          AND mobil.id_cabang = ?
          ORDER BY mobil.id_mobil DESC";

$data = sqlsrv_query($koneksi, $query, [$id_cabang_member]);

if ($data === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Mobil - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="daftar_mobil.php" class="active">Daftar Mobil</a>
            <a href="riwayat.php">Riwayat Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Daftar Mobil</h1>
                <p>Pilih mobil tersedia untuk dipinjam.</p>
            </div>
        </div>

        <div class="car-grid">
            <?php while ($mobil = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="car-card">
                    <?php if (!empty($mobil['foto_mobil'])) { ?>
                        <img src="../<?= $mobil['foto_mobil']; ?>" class="car-card-img">
                    <?php } else { ?>
                        <div class="car-card-img empty-img"></div>
                    <?php } ?>

                    <div class="car-card-body">
                        <div class="car-card-top">
                            <h3><?= $mobil['nama_mobil']; ?></h3>
                            <span class="badge badge-available">Tersedia</span>
                        </div>

                        <p class="car-location"><?= $mobil['nama_cabang']; ?></p>

                        <div class="car-info">
                            <span><?= $mobil['kapasitas']; ?> orang</span>
                            <span><?= $mobil['nomor_polisi']; ?></span>
                        </div>

                        <div class="car-price">
                            Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?>
                            <small>/ hari</small>
                        </div>

                        <a href="pinjam_mobil.php?id=<?= $mobil['id_mobil']; ?>" class="btn btn-full">
                            Pinjam Mobil
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

</body>

</html>