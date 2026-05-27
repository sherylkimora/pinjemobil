<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$jenis = $_GET['jenis'] ?? 'dp';

if (!$id) {
    echo "ID transaksi tidak ditemukan.";
    exit;
}

$query = "SELECT peminjaman.*, mobil.nama_mobil, mobil.nomor_polisi
          FROM peminjaman
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
          WHERE peminjaman.id_peminjaman = ?";

$result = sqlsrv_query($koneksi, $query, [$id]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit;
}

if ($jenis == 'dp') {
    $jenis_label = "DP";
    $pesan = "Pembayaran DP berhasil. Peminjaman kamu sedang menunggu pengecekan kondisi awal oleh pegawai.";
} else {
    $jenis_label = "PELUNASAN";
    $pesan = "Pelunasan berhasil dilakukan. Transaksi rental kamu sudah selesai.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Success</title>

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
            font-size: 26px;
            font-weight: bold;
        }

        .content {
            padding: 35px 30px;
            text-align: center;
        }

        .check {
            width: 140px;
            height: 140px;
            background: #22c55e;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 70px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
        }

        .title {
            font-size: 36px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 15px;
        }

        .desc {
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 35px;
            font-size: 16px;
        }

        .card {
            background: #f9fafb;
            border-radius: 22px;
            padding: 22px;
            text-align: left;
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 18px;
            color: #111827;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            color: #4b5563;
        }

        .row span:last-child {
            font-weight: bold;
            color: #111827;
            text-align: right;
        }

        .status {
            color: #22c55e !important;
        }

        .button {
            display: block;
            width: 100%;
            padding: 18px;
            border-radius: 16px;
            background: #08142f;
            color: white;
            text-decoration: none;
            text-align: center;
            font-size: 17px;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .button:hover {
            background: #162447;
        }

        .secondary {
            display: block;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            background: #f3f4f6;
            text-decoration: none;
            text-align: center;
            color: #111827;
            font-size: 16px;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="topbar">
            Pembayaran Berhasil
        </div>

        <div class="content">

            <div class="check">
                ✓
            </div>

            <div class="title">
                Payment Success!
            </div>

            <div class="desc">
                <?= $pesan; ?>
            </div>

            <div class="card">

                <div class="card-title">
                    Ringkasan Pembayaran
                </div>

                <div class="row">
                    <span>Status</span>
                    <span class="status">Berhasil</span>
                </div>

                <div class="row">
                    <span>Metode</span>
                    <span>QRIS</span>
                </div>

                <div class="row">
                    <span>Jenis Pembayaran</span>
                    <span><?= $jenis_label; ?></span>
                </div>

                <div class="row">
                    <span>Mobil</span>
                    <span><?= $data['nama_mobil']; ?></span>
                </div>

                <div class="row">
                    <span>ID Transaksi</span>
                    <span>#TRX<?= str_pad($data['id_peminjaman'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>

            </div>

            <a href="riwayat.php" class="button">
                Lihat Riwayat
            </a>

            <a href="dashboard.php" class="secondary">
                Kembali ke Dashboard
            </a>

        </div>

    </div>

</body>

</html>