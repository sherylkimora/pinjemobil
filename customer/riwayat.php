<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

$id_member = $_SESSION['id_member'];

$query = "SELECT 
            peminjaman.*, 
            mobil.nama_mobil, 
            mobil.nomor_polisi, 
            mobil.foto_mobil,
            pengembalian.total_denda,
            pengembalian.sisa_bayar,
            pengembalian.status_pembayaran
          FROM peminjaman
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
          LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
          WHERE peminjaman.id_member = ?
          ORDER BY peminjaman.id_peminjaman DESC";

$data = sqlsrv_query($koneksi, $query, [$id_member]);

if ($data === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_query($koneksi, $query, [$id_member]);

if ($data === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Peminjaman - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .action {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .status-note {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            white-space: nowrap;
        }

        .table small {
            color: #6b7280;
            font-size: 12px;
        }

        .btn.btn-secondary {
            background: #f3f4f6;
            color: #111827;
        }

        .badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="daftar_mobil.php">Daftar Mobil</a>
            <a href="riwayat.php" class="active">Riwayat Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Riwayat Peminjaman</h1>
                <p>Lihat status peminjaman mobil kamu.</p>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Mobil</th>
                    <th>Nomor Polisi</th>
                    <th>Tanggal</th>
                    <th>Lama Sewa</th>
                    <th>Total Sewa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td><strong><?= $row['nama_mobil']; ?></strong></td>

                        <td><?= $row['nomor_polisi']; ?></td>

                        <td>
                            <?= $row['tanggal_pinjam']->format('Y-m-d'); ?>
                            <br>
                            <small>s/d <?= $row['rencana_kembali']->format('Y-m-d'); ?></small>
                        </td>

                        <td><?= $row['lama_sewa']; ?> hari</td>

                        <td>
                            Rp <?= number_format($row['total_sewa'], 0, ',', '.'); ?>
                        </td>

                        <td>
                            <?php if ($row['status_transaksi'] == 'Menunggu Cek Awal') { ?>
                                <span class="badge badge-maintenance">Menunggu Cek Awal</span>

                            <?php } else if ($row['status_transaksi'] == 'Sedang Dipinjam') { ?>
                                    <span class="badge badge-borrowed">Sedang Dipinjam</span>

                            <?php } else if ($row['status_transaksi'] == 'Menunggu Cek Akhir') { ?>
                                        <span class="badge badge-maintenance">Menunggu Cek Akhir</span>

                            <?php } else if ($row['status_transaksi'] == 'Menunggu Pelunasan') { ?>
                                            <span class="badge badge-maintenance">Menunggu Pelunasan</span>

                            <?php } else if ($row['status_transaksi'] == 'Selesai') { ?>
                                                <span class="badge badge-available">Selesai</span>

                            <?php } else { ?>
                                <?= $row['status_transaksi']; ?>
                            <?php } ?>
                        </td>

                        <td>
                            <div class="action">
                                <a href="detail_peminjaman.php?id=<?= $row['id_peminjaman']; ?>" class="btn btn-secondary">
                                    Detail
                                </a>

                                <?php if ($row['status_transaksi'] == 'Sedang Dipinjam') { ?>

                                    <a href="ajukan_pengembalian.php?id=<?= $row['id_peminjaman']; ?>" class="btn">
                                        Ajukan Pengembalian
                                    </a>

                                <?php } else if ($row['status_transaksi'] == 'Menunggu Pelunasan') { ?>

                                        <a href="qris.php?id=<?= $row['id_peminjaman']; ?>&jenis=pelunasan" class="btn">
                                            Bayar Sisa
                                        </a>

                                <?php } else if ($row['status_transaksi'] == 'Menunggu Cek Awal') { ?>

                                            <span class="status-note">Menunggu pegawai</span>

                                <?php } else if ($row['status_transaksi'] == 'Menunggu Cek Akhir') { ?>

                                                <span class="status-note">Sedang dicek</span>

                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>

</html>