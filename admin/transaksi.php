<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = sqlsrv_query($koneksi, "
    SELECT
        peminjaman.*,
        member.nama_member,
        mobil.nama_mobil,
        mobil.nomor_polisi,

        pegawai_awal.nama_pegawai AS pegawai_cek_awal,
        pegawai_akhir.nama_pegawai AS pegawai_cek_akhir

    FROM peminjaman

    JOIN member
        ON peminjaman.id_member = member.id_member

    JOIN mobil
        ON peminjaman.id_mobil = mobil.id_mobil

    LEFT JOIN kondisi_mobil kondisi_awal
        ON peminjaman.id_peminjaman = kondisi_awal.id_peminjaman
        AND kondisi_awal.jenis_kondisi = 'Awal'

    LEFT JOIN pegawai pegawai_awal
        ON kondisi_awal.id_pegawai = pegawai_awal.id_pegawai

    LEFT JOIN kondisi_mobil kondisi_akhir
        ON peminjaman.id_peminjaman = kondisi_akhir.id_peminjaman
        AND kondisi_akhir.jenis_kondisi = 'Akhir'

    LEFT JOIN pegawai pegawai_akhir
        ON kondisi_akhir.id_pegawai = pegawai_akhir.id_pegawai

    ORDER BY peminjaman.id_peminjaman DESC
");

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Peminjaman</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .status {
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }

        .pending {
            background: #fef3c7;
            color: #d97706;
        }

        .success {
            background: #dcfce7;
            color: #16a34a;
        }

        .process {
            background: #dbeafe;
            color: #2563eb;
        }

        .action {
            display: flex;
            gap: 10px;
        }

        .btn-detail {
            background: #08142f;
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
        }

        .badge-soft {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .text-muted {
            color: #9ca3af;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Admin Panel</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="mobil.php">Data Mobil</a>
            <a href="pegawai.php">Data Pegawai</a>
            <a href="customer.php">Data Customer</a>
            <a href="transaksi.php" class="active">Data Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <div class="page-title">
                <h1>Data Peminjaman</h1>
                <p>Kelola seluruh transaksi rental mobil.</p>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Mobil</th>
                    <th>Nomor Polisi</th>
                    <th>Tanggal Pinjam</th>
                    <th>Rencana Kembali</th>
                    <th>Total Sewa</th>
                    <th>Cek Awal</th>
                    <th>Cek Akhir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <strong><?= $row['nama_member']; ?></strong>
                        </td>

                        <td><?= $row['nama_mobil']; ?></td>
                        <td><?= $row['nomor_polisi']; ?></td>

                        <td>
                            <?= $row['tanggal_pinjam'] ? $row['tanggal_pinjam']->format('Y-m-d') : '-'; ?>
                        </td>

                        <td>
                            <?= $row['rencana_kembali'] ? $row['rencana_kembali']->format('Y-m-d') : '-'; ?>
                        </td>

                        <td>
                            Rp <?= number_format($row['total_sewa'], 0, ',', '.'); ?>
                        </td>

                        <td>
                            <?php if ($row['pegawai_cek_awal']) { ?>
                                <span class="badge badge-soft"><?= $row['pegawai_cek_awal']; ?></span>
                            <?php } else { ?>
                                <span class="text-muted">Belum dicek</span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if ($row['pegawai_cek_akhir']) { ?>
                                <span class="badge badge-soft"><?= $row['pegawai_cek_akhir']; ?></span>
                            <?php } else { ?>
                                <span class="text-muted">Belum dicek</span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php
                            $status = $row['status_transaksi'];

                            if ($status == 'Menunggu Cek Awal') {
                                echo "<span class='status pending'>Menunggu Cek Awal</span>";
                            } else if ($status == 'Sedang Dipinjam') {
                                echo "<span class='status process'>Sedang Dipinjam</span>";
                            } else if ($status == 'Menunggu Cek Akhir') {
                                echo "<span class='status pending'>Menunggu Cek Akhir</span>";
                            } else if ($status == 'Menunggu Pelunasan') {
                                echo "<span class='status pending'>Menunggu Pelunasan</span>";
                            } else {
                                echo "<span class='status success'>Selesai</span>";
                            }
                            ?>
                        </td>

                        <td>
                            <div class="action">
                                <a href="detail_transaksi.php?id=<?= $row['id_peminjaman']; ?>" class="btn-detail">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>

</body>

</html>