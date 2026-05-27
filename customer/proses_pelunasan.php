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

$query = "SELECT peminjaman.*, mobil.id_mobil
          FROM peminjaman
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
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

header("Location: successPay.php?id=" . $id_peminjaman . "&jenis=pelunasan");
exit;
?>