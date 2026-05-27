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

$query = "SELECT * FROM peminjaman
          WHERE id_peminjaman = ?
          AND id_member = ?";

$result = sqlsrv_query($koneksi, $query, [$id_peminjaman, $id_member]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

/*
    Pembayaran DP tidak mengubah status menjadi Selesai.
    Setelah DP dibayar, status tetap Menunggu Cek Awal,
    karena pegawai masih harus upload kondisi awal mobil.
*/

header("Location: successPay.php?id=" . $id_peminjaman . "&jenis=dp");
exit;
?>