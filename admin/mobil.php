<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT mobil.*, cabang.nama_cabang 
          FROM mobil
          LEFT JOIN cabang ON mobil.id_cabang = cabang.id_cabang
          ORDER BY mobil.id_mobil DESC";

$data = sqlsrv_query($koneksi, $query);

if ($data === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Mobil - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Admin Panel</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="mobil.php" class="active">Data Mobil</a>
            <a href="pegawai.php">Data Pegawai</a>
            <a href="customer.php">Data Customer</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Data Mobil</h1>
                <p>Kelola mobil yang tersedia di Pinjem Mobil.</p>
            </div>

            <a href="tambah_mobil.php" class="btn">+ Tambah Mobil</a>
        </div>

        <div class="card">
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nomor Polisi</th>
                    <th>Nama Mobil</th>
                    <th>Kapasitas</th>
                    <th>Status</th>
                    <th>Harga Sewa</th>
                    <th>Cabang</th>
                </tr>

                <?php
                $no = 1;
                while ($mobil = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <?php if (!empty($mobil['foto_mobil'])) { ?>
                                <img src="../<?= $mobil['foto_mobil']; ?>" class="car-img">
                            <?php } else { ?>
                                <div class="car-img"></div>
                            <?php } ?>
                        </td>

                        <td><?= $mobil['nomor_polisi']; ?></td>
                        <td><strong><?= $mobil['nama_mobil']; ?></strong></td>
                        <td><?= $mobil['kapasitas']; ?> orang</td>

                        <td>
                            <?php if ($mobil['status_mobil'] == 'Tersedia') { ?>
                                <span class="badge badge-available">Tersedia</span>
                            <?php } else if ($mobil['status_mobil'] == 'Dipinjam') { ?>
                                    <span class="badge badge-borrowed">Dipinjam</span>
                            <?php } else { ?>
                                    <span class="badge badge-maintenance">Maintenance</span>
                            <?php } ?>
                        </td>

                        <td>Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?></td>
                        <td><?= $mobil['nama_cabang']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>

</html>