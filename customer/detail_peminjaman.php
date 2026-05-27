<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID peminjaman tidak ditemukan.";
    exit;
}

$id_peminjaman = $_GET['id'];
$id_member = $_SESSION['id_member'];

$sql = "SELECT 
            peminjaman.*,
            mobil.nama_mobil,
            mobil.nomor_polisi,
            mobil.foto_mobil,
            pengembalian.id_pengembalian,
            pengembalian.keterlambatan,
            pengembalian.total_denda,
            pengembalian.total_bayar_sewa,
            pengembalian.sisa_bayar,
            pengembalian.status_pembayaran,
            pengembalian.tanggal_kembali
        FROM peminjaman
        LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
        LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
        WHERE peminjaman.id_peminjaman = ?
        AND peminjaman.id_member = ?";

$query = sqlsrv_query($koneksi, $sql, [$id_peminjaman, $id_member]);

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data peminjaman tidak ditemukan.";
    exit;
}

$detail_denda = [];

if (!empty($data['id_pengembalian'])) {
    $query_denda = sqlsrv_query(
        $koneksi,
        "SELECT nama_denda, nominal_denda
         FROM detail_denda
         WHERE id_pengembalian = ?",
        [$data['id_pengembalian']]
    );

    if ($query_denda === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row_denda = sqlsrv_fetch_array($query_denda, SQLSRV_FETCH_ASSOC)) {
        $detail_denda[] = $row_denda;
    }
}

$total_denda = $data['total_denda'] ?? 0;
$sisa_bayar = $data['sisa_bayar'] ?? 0;
$keterlambatan = $data['keterlambatan'] ?? 0;
$denda_per_hari = 50000;
$denda_keterlambatan = $keterlambatan * $denda_per_hari;

$total_denda_kondisi = 0;
foreach ($detail_denda as $denda) {
    $total_denda_kondisi += $denda['nominal_denda'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Peminjaman - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 24px;
        }

        .detail-img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: 18px;
            background: #f3f4f6;
            margin-bottom: 18px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 14px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row span:first-child {
            color: #6b7280;
        }

        .detail-row span:last-child {
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .detail-total span:last-child {
            color: #2563eb;
            font-size: 20px;
        }

        .fine-detail {
            margin-top: 10px;
            margin-bottom: 10px;
            padding-left: 18px;
            border-left: 3px solid #e5e7eb;
        }

        .fine-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .fine-row span:last-child {
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">Pinjem Mobil</div>

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
                <h1>Detail Peminjaman</h1>
                <p>Lihat rincian transaksi peminjaman mobil kamu.</p>
            </div>

            <a href="riwayat.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="detail-layout">
            <div class="card">
                <?php if (!empty($data['foto_mobil'])) { ?>
                    <img src="../<?= $data['foto_mobil']; ?>" class="detail-img">
                <?php } ?>

                <h2><?= $data['nama_mobil']; ?></h2>
                <p class="car-location"><?= $data['nomor_polisi']; ?></p>

                <br>

                <span class="badge badge-available">
                    <?= $data['status_transaksi']; ?>
                </span>
            </div>

            <div class="card">
                <h2>Rincian Pembayaran</h2>
                <br>

                <div class="detail-row">
                    <span>Tanggal Pinjam</span>
                    <span><?= $data['tanggal_pinjam']->format('Y-m-d'); ?></span>
                </div>

                <div class="detail-row">
                    <span>Rencana Kembali</span>
                    <span><?= $data['rencana_kembali']->format('Y-m-d'); ?></span>
                </div>

                <?php if (!empty($data['tanggal_kembali'])) { ?>
                    <div class="detail-row">
                        <span>Tanggal Kembali Aktual</span>
                        <span><?= $data['tanggal_kembali']->format('Y-m-d'); ?></span>
                    </div>
                <?php } ?>

                <div class="detail-row">
                    <span>Lama Sewa</span>
                    <span><?= $data['lama_sewa']; ?> hari</span>
                </div>

                <div class="detail-row">
                    <span>Total Sewa</span>
                    <span>Rp <?= number_format($data['total_sewa'], 0, ',', '.'); ?></span>
                </div>

                <div class="detail-row">
                    <span>DP Dibayar</span>
                    <span>Rp <?= number_format($data['pembayaran_dp'], 0, ',', '.'); ?></span>
                </div>

                <?php if (!empty($data['id_pengembalian'])) { ?>
                    <div class="detail-row">
                        <span>Total Denda</span>
                        <span style="color:red;">Rp <?= number_format($total_denda, 0, ',', '.'); ?></span>
                    </div>

                    <div class="fine-detail">
                        <div class="fine-row">
                            <span>Keterlambatan: <?= $keterlambatan; ?> hari x Rp
                                <?= number_format($denda_per_hari, 0, ',', '.'); ?></span>
                            <span>Rp <?= number_format($denda_keterlambatan, 0, ',', '.'); ?></span>
                        </div>

                        <div class="fine-row">
                            <span>Denda Kondisi</span>
                            <span>Rp <?= number_format($total_denda_kondisi, 0, ',', '.'); ?></span>
                        </div>

                        <?php foreach ($detail_denda as $denda) { ?>
                            <div class="fine-row">
                                <span>- <?= $denda['nama_denda']; ?></span>
                                <span>Rp <?= number_format($denda['nominal_denda'], 0, ',', '.'); ?></span>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="detail-row detail-total">
                        <span>Total Pelunasan</span>
                        <span>Rp <?= number_format($sisa_bayar, 0, ',', '.'); ?></span>
                    </div>

                    <div class="detail-row">
                        <span>Status Pembayaran</span>
                        <span><?= $data['status_pembayaran'] ?? '-'; ?></span>
                    </div>
                <?php } else { ?>
                    <div class="detail-row detail-total">
                        <span>Sisa Setelah DP</span>
                        <span>Rp <?= number_format($data['total_sewa'] - $data['pembayaran_dp'], 0, ',', '.'); ?></span>
                    </div>
                <?php } ?>

                <br>

                <?php if ($data['status_transaksi'] == 'Sedang Dipinjam') { ?>
                    <a href="ajukan_pengembalian.php?id=<?= $data['id_peminjaman']; ?>" class="btn">
                        Ajukan Pengembalian
                    </a>

                <?php } else if ($data['status_transaksi'] == 'Menunggu Pelunasan') { ?>
                        <a href="qris.php?id=<?= $data['id_peminjaman']; ?>&jenis=pelunasan" class="btn">
                            Bayar Sisa
                        </a>
                <?php } ?>
            </div>
        </div>
    </div>

</body>

</html>