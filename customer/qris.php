<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$jenis = $_GET['jenis'] ?? 'dp';
$id_member = $_SESSION['id_member'];

if (!$id) {
    echo "ID peminjaman tidak ditemukan.";
    exit;
}

$sql = "SELECT 
            peminjaman.*,
            mobil.nama_mobil,
            mobil.nomor_polisi,
            pengembalian.id_pengembalian,
            pengembalian.keterlambatan,
            pengembalian.total_denda,
            pengembalian.sisa_bayar
        FROM peminjaman
        JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
        LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
        WHERE peminjaman.id_peminjaman = ?
        AND peminjaman.id_member = ?";

$query = sqlsrv_query($koneksi, $sql, [$id, $id_member]);

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data pembayaran tidak ditemukan.";
    exit;
}

$detail_denda = [];

if ($jenis == 'pelunasan' && !empty($data['id_pengembalian'])) {
    $query_detail = sqlsrv_query(
        $koneksi,
        "SELECT nama_denda, nominal_denda 
         FROM detail_denda 
         WHERE id_pengembalian = ?",
        [$data['id_pengembalian']]
    );

    if ($query_detail === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row_denda = sqlsrv_fetch_array($query_detail, SQLSRV_FETCH_ASSOC)) {
        $detail_denda[] = $row_denda;
    }
}

$total_denda = $data['total_denda'] ?? 0;
$sisa_bayar = $data['sisa_bayar'] ?? 0;

$keterlambatan = $data['keterlambatan'] ?? 0;
$denda_per_hari = 50000;
$denda_keterlambatan = $keterlambatan * $denda_per_hari;

if ($jenis == 'dp') {
    $judul = "Pembayaran DP";
    $label_nominal = "Nominal DP (50%)";
    $nominal = $data['pembayaran_dp'];
    $link_proses = "proses_bayar_dp.php?id=" . $id;
} else {
    $judul = "Pelunasan Pembayaran";
    $label_nominal = "Total Pelunasan";
    $nominal = $sisa_bayar;
    $link_proses = "proses_pelunasan.php?id=" . $id;
}

$total_denda_kondisi = 0;

if (!empty($detail_denda)) {
    foreach ($detail_denda as $denda) {
        $total_denda_kondisi += $denda['nominal_denda'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $judul; ?> - Pinjem Mobil</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 420px;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.1);
        }

        .topbar {
            background: #08142f;
            color: white;
            padding: 22px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }

        .content {
            padding: 25px;
        }

        .qr-card {
            background: #f9fafb;
            border-radius: 25px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
        }

        .qr-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #111827;
        }

        .qr-desc {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .qr-image {
            width: 240px;
            background: white;
            padding: 15px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .info-box {
            background: #eef4ff;
            padding: 15px;
            border-radius: 16px;
            color: #1e3a8a;
            font-size: 14px;
            margin-top: 25px;
            line-height: 1.5;
        }

        .detail-card {
            background: #ffffff;
            border: 1px solid #eee;
            border-radius: 22px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .detail-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #111827;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            color: #4b5563;
        }

        .row span:last-child {
            font-weight: bold;
            color: #111827;
            text-align: right;
        }

        .dp {
            color: #1677ff !important;
            font-size: 20px;
        }

        .button {
            display: block;
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 16px;
            background: #08142f;
            color: white;
            font-size: 17px;
            text-decoration: none;
            text-align: center;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .button:hover {
            background: #162447;
        }

        .back {
            display: block;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            background: #f3f4f6;
            text-align: center;
            text-decoration: none;
            color: #111827;
            font-size: 16px;
        }

        .detail-subbox {
            margin-top: -6px;
            margin-bottom: 20px;
            padding-left: 18px;
            border-left: 3px solid #e5e7eb;
        }

        .detail-subrow {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .detail-subrow span:last-child {
            font-weight: 600;
            color: #6b7280;
            text-align: right;
            min-width: 120px;
        }

        .detail-subsub {
            padding-left: 14px;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .detail-subsub .detail-subrow {
            font-size: 12px;
            color: #9ca3af;
        }

        .total-denda-row {
            margin-bottom: 10px;
        }

        .total-denda-row span:last-child {
            color: red;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="topbar">
            <?= $judul; ?>
        </div>

        <div class="content">

            <div class="qr-card">

                <div class="qr-title">
                    Scan QRIS
                </div>

                <div class="qr-desc">
                    Silakan scan QR code berikut untuk melakukan pembayaran rental mobil.
                </div>

                <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=PINJEMMOBIL-<?= $id; ?>-<?= $jenis; ?>"
                    class="qr-image">

                <div class="info-box">
                    Pastikan nominal pembayaran sesuai dengan tagihan sebelum melakukan transaksi.
                </div>

            </div>

            <div class="detail-card">

                <div class="detail-title">
                    Detail Pembayaran
                </div>

                <div class="row">
                    <span>Mobil</span>
                    <span><?= $data['nama_mobil']; ?></span>
                </div>

                <div class="row">
                    <span>Nomor Polisi</span>
                    <span><?= $data['nomor_polisi']; ?></span>
                </div>

                <div class="row">
                    <span>Total Sewa</span>
                    <span>Rp <?= number_format($data['total_sewa'], 0, ',', '.'); ?></span>
                </div>

                <div class="row">
                    <span>DP 50%</span>
                    <span>Rp <?= number_format($data['pembayaran_dp'], 0, ',', '.'); ?></span>
                </div>

                <?php if ($jenis == 'pelunasan') { ?>

                    <div class="row total-denda-row">
                        <span><strong>Total Denda</strong></span>
                        <span style="color:red;">
                            Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="detail-subbox">
                        <div class="detail-subrow">
                            <span>
                                Keterlambatan:
                                <?= $keterlambatan; ?> hari x
                                Rp <?= number_format($denda_per_hari, 0, ',', '.'); ?>
                            </span>
                            <span>
                                Rp <?= number_format($denda_keterlambatan, 0, ',', '.'); ?>
                            </span>
                        </div>

                        <div class="detail-subrow">
                            <span>Denda Kondisi</span>
                            <span>
                                Rp <?= number_format($total_denda_kondisi, 0, ',', '.'); ?>
                            </span>
                        </div>

                        <?php if (!empty($detail_denda)) { ?>
                            <div class="detail-subsub">
                                <?php foreach ($detail_denda as $denda) { ?>
                                    <div class="detail-subrow">
                                        <span>- <?= $denda['nama_denda']; ?></span>
                                        <span>
                                            Rp <?= number_format($denda['nominal_denda'], 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>

                <?php } ?>

                <div class="row">
                    <span><?= $label_nominal; ?></span>
                    <span class="dp">Rp <?= number_format($nominal, 0, ',', '.'); ?></span>
                </div>

                <div class="row">
                    <span>Metode Pembayaran</span>
                    <span>QRIS</span>
                </div>

            </div>

            <a href="<?= $link_proses; ?>" class="button">
                Saya Sudah Bayar
            </a>

            <a href="riwayat.php" class="back">
                Kembali
            </a>

        </div>

    </div>

</body>

</html>