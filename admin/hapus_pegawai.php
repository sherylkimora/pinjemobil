<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID pegawai tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

$query = "UPDATE pegawai
          SET status_pegawai = 'Nonaktif'
          WHERE id_pegawai = ?";

$result = sqlsrv_query($koneksi, $query, [$id]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

header("Location: pegawai.php");
exit;
?>