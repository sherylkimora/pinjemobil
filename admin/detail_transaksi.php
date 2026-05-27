<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID transaksi tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

$sql = "SELECT
            peminjaman.*,
            member.nama_member,
            mobil.nama_mobil,
            mobil.nomor_polisi,
            pengembalian.total_denda,
            pengembalian.total_bayar_sewa,
            pengembalian.sisa_bayar,
            pengembalian.status_pembayaran
        FROM peminjaman
        JOIN member ON peminjaman.id_member = member.id_member
        JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
        LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
        WHERE peminjaman.id_peminjaman = ?";

$query_kondisi = sqlsrv_query(
    $koneksi,
    "SELECT 
        kondisi_mobil.*,
        pegawai.nama_pegawai
     FROM kondisi_mobil
     LEFT JOIN pegawai ON kondisi_mobil.id_pegawai = pegawai.id_pegawai
     WHERE kondisi_mobil.id_peminjaman = ?
     ORDER BY 
        CASE 
            WHEN kondisi_mobil.jenis_kondisi = 'Awal' THEN 1
            WHEN kondisi_mobil.jenis_kondisi = 'Akhir' THEN 2
            ELSE 3
        END",
    [$id]
);

$history_query = sqlsrv_query($koneksi, "
    SELECT kondisi_mobil.*, pegawai.nama_pegawai
    FROM kondisi_mobil
    LEFT JOIN pegawai ON kondisi_mobil.id_pegawai = pegawai.id_pegawai
    WHERE kondisi_mobil.id_peminjaman = ?
    ORDER BY kondisi_mobil.tanggal_upload ASC
", [$id]);

$history_list = [];
if ($history_query !== false) {
    while ($row = sqlsrv_fetch_array($history_query, SQLSRV_FETCH_ASSOC)) {
        $history_list[] = $row;
    }
}

if ($query_kondisi === false) {
    die(print_r(sqlsrv_errors(), true));
}

$query = sqlsrv_query($koneksi, $sql, [$id]);

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .detail-box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-top: 30px;
        }

        .item {
            margin-bottom: 20px;
        }

        .label {
            color: #6b7280;
            margin-bottom: 5px;
        }

        .value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        body {
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .main {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
        }

        .section-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 28px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .value {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .history-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin: 0;
            padding: 0;
        }

        .history-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
        }

        .history-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .history-heading {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .history-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-awal {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-akhir {
            background: #dcfce7;
            color: #15803d;
        }

        .history-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 24px;
        }

        .history-photo-wrap {
            margin-top: 18px;
        }

        .history-photo-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .history-photo {
            width: 220px;
            max-width: 100%;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .btn-back {
            display: inline-block;
            background: #08142f;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        .empty-text {
            color: #6b7280;
            font-size: 15px;
        }

        @media (max-width: 768px) {

            .info-grid,
            .history-grid {
                grid-template-columns: 1fr;
            }

            .main {
                padding: 0 16px 30px;
            }

            .section-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="main">

        <h1 class="page-title">Detail Transaksi</h1>

        <div class="section-card">
            <h2 class="section-title">Informasi Transaksi</h2>

            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Customer</div>
                    <div class="value"><?= $data['nama_member']; ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Mobil</div>
                    <div class="value"><?= $data['nama_mobil']; ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Nomor Polisi</div>
                    <div class="value"><?= $data['nomor_polisi']; ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Tanggal Pinjam</div>
                    <div class="value">
                        <?= is_object($data['tanggal_pinjam']) ? $data['tanggal_pinjam']->format('Y-m-d') : $data['tanggal_pinjam']; ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="label">Rencana Kembali</div>
                    <div class="value">
                        <?= is_object($data['rencana_kembali']) ? $data['rencana_kembali']->format('Y-m-d') : $data['rencana_kembali']; ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="label">Lama Sewa</div>
                    <div class="value"><?= $data['lama_sewa']; ?> hari</div>
                </div>

                <div class="info-item">
                    <div class="label">Total Sewa</div>
                    <div class="value">Rp <?= number_format($data['total_sewa'], 0, ',', '.'); ?></div>
                </div>

                <div class="info-item">
                    <div class="label">DP</div>
                    <div class="value">Rp <?= number_format($data['pembayaran_dp'], 0, ',', '.'); ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Total Denda</div>
                    <div class="value">Rp <?= number_format($data['total_denda'], 0, ',', '.'); ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Sisa Bayar</div>
                    <div class="value">Rp <?= number_format($data['sisa_bayar'], 0, ',', '.'); ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Status Transaksi</div>
                    <div class="value"><?= $data['status_transaksi']; ?></div>
                </div>

                <div class="info-item">
                    <div class="label">Status Pembayaran</div>
                    <div class="value"><?= $data['status_pembayaran']; ?></div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <h2 class="section-title">History Pengecekan Kondisi</h2>

            <?php if (!empty($history_list)) { ?>
                <div class="history-list">

                    <?php foreach ($history_list as $history) { ?>
                        <div class="history-card">

                            <div class="history-top">
                                <h3 class="history-heading">
                                    Pengecekan Kondisi <?= $history['jenis_kondisi']; ?>
                                </h3>

                                <span
                                    class="history-badge <?= $history['jenis_kondisi'] == 'Awal' ? 'badge-awal' : 'badge-akhir'; ?>">
                                    <?= $history['jenis_kondisi']; ?>
                                </span>
                            </div>

                            <div class="history-grid">
                                <div class="info-item">
                                    <div class="label">Dicek Oleh</div>
                                    <div class="value"><?= $history['nama_pegawai']; ?></div>
                                </div>

                                <div class="info-item">
                                    <div class="label">Tanggal Upload</div>
                                    <div class="value">
                                        <?= is_object($history['tanggal_upload']) ? $history['tanggal_upload']->format('Y-m-d') : $history['tanggal_upload']; ?>
                                    </div>
                                </div>

                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <div class="label">Keterangan</div>
                                    <div class="value"><?= $history['keterangan_kondisi']; ?></div>
                                </div>
                            </div>

                            <?php if (!empty($history['foto_kondisi'])) { ?>
                                <div class="history-photo-wrap">
                                    <div class="history-photo-label">Foto Kondisi</div>
                                    <img src="../<?= $history['foto_kondisi']; ?>" class="history-photo">
                                </div>
                            <?php } ?>

                        </div>
                    <?php } ?>

                </div>
            <?php } else { ?>
                <p class="empty-text">Belum ada history pengecekan kondisi.</p>
            <?php } ?>

            <a href="transaksi.php" class="btn-back">Kembali</a>
        </div>

    </div>

</body>

</html>