<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pegawai') {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT peminjaman.*, member.nama_member, mobil.nama_mobil, mobil.nomor_polisi, mobil.foto_mobil
          FROM peminjaman
          LEFT JOIN member ON peminjaman.id_member = member.id_member
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
          WHERE peminjaman.status_transaksi = 'Menunggu Cek Awal'
          ORDER BY peminjaman.id_peminjaman DESC";

$data = sqlsrv_query($koneksi, $query);

if ($data === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Cek Kondisi Awal - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Pegawai</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="cek_awal.php" class="active">Cek Kondisi Awal</a>
            <a href="cek_akhir.php">Cek Kondisi Akhir</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Cek Kondisi Awal</h1>
                <p>Upload foto dan catatan kondisi mobil sebelum mobil digunakan customer.</p>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Mobil</th>
                    <th>Customer</th>
                    <th>Tanggal Pinjam</th>
                    <th>Rencana Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <strong><?= $row['nama_mobil']; ?></strong><br>
                            <small><?= $row['nomor_polisi']; ?></small>
                        </td>
                        <td><?= $row['nama_member']; ?></td>
                        <td><?= $row['tanggal_pinjam'] ? $row['tanggal_pinjam']->format('Y-m-d') : '-'; ?></td>
                        <td><?= $row['rencana_kembali'] ? $row['rencana_kembali']->format('Y-m-d') : '-'; ?></td>
                        <td><?= $row['status_transaksi']; ?></td>
                        <td>
                            <a href="upload_kondisi_awal.php?id=<?= $row['id_peminjaman']; ?>" class="btn">
                                Upload Kondisi Awal
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>

</html>