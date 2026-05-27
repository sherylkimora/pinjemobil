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

$query = "SELECT peminjaman.*, mobil.id_mobil, mobil.nama_mobil, mobil.nomor_polisi, pengembalian.*
          FROM peminjaman
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
          LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
          WHERE peminjaman.id_peminjaman = ?
          AND peminjaman.id_member = ?";

$result = sqlsrv_query($koneksi, $query, [$id_peminjaman, $id_member]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

if (isset($_POST['bayar'])) {
    $update_pengembalian = sqlsrv_query(
        $koneksi,
        "UPDATE pengembalian
         SET status_pembayaran = 'Lunas',
             tanggal_pelunasan = CAST(GETDATE() AS DATE)
         WHERE id_peminjaman = ?",
        [$id_peminjaman]
    );

    if ($update_pengembalian === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $update_peminjaman = sqlsrv_query(
        $koneksi,
        "UPDATE peminjaman
         SET status_transaksi = 'Selesai'
         WHERE id_peminjaman = ?",
        [$id_peminjaman]
    );

    if ($update_peminjaman === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $update_mobil = sqlsrv_query(
        $koneksi,
        "UPDATE mobil
         SET status_mobil = 'Tersedia'
         WHERE id_mobil = ?",
        [$data['id_mobil']]
    );

    if ($update_mobil === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: riwayat.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pelunasan - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <h1>Pelunasan Pembayaran</h1>
                <p>Bayar sisa pembayaran rental mobil.</p>
            </div>
            <a href="riwayat.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card">
            <h2><?= $data['nama_mobil']; ?></h2>
            <p class="car-location"><?= $data['nomor_polisi']; ?></p>

            <div class="summary-box">
                <p>Total Sewa</p>
                <h3>Rp <?= number_format($data['total_sewa'], 0, ',', '.'); ?></h3>

                <p>DP yang Sudah Dibayar</p>
                <h3>Rp <?= number_format($data['pembayaran_dp'], 0, ',', '.'); ?></h3>

                <p>Total Denda</p>
                <h3>Rp <?= number_format($data['total_denda'], 0, ',', '.'); ?></h3>

                <p>Total Bayar Akhir</p>
                <h3>Rp <?= number_format($data['total_bayar_sewa'], 0, ',', '.'); ?></h3>

                <p>Sisa Bayar</p>
                <h3>Rp <?= number_format($data['sisa_bayar'], 0, ',', '.'); ?></h3>
            </div>

            <form method="POST">
                <button type="submit" name="bayar" class="btn">
                    Bayar Sisa Pembayaran
                </button>
            </form>
        </div>
    </div>

</body>

</html>