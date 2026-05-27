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

$query = "UPDATE peminjaman
          SET status_transaksi = 'Menunggu Cek Akhir'
          WHERE id_peminjaman = ?
          AND id_member = ?";

$result = sqlsrv_query($koneksi, $query, [$id_peminjaman, $id_member]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

header("Location: riwayat.php");
exit;
?>