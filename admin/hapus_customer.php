<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID customer tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

$query = "DELETE FROM member WHERE id_member = ?";
$result = sqlsrv_query($koneksi, $query, [$id]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

header("Location: customer.php");
exit;
?>