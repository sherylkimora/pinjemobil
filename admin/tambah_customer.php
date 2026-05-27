<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama = $_POST['nama_member'];
    $email = $_POST['email'];
    $telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];

    $query = "INSERT INTO member
              (nama_member, ktp, sim, alamat, nomor_telepon, email, tanggal_registrasi)
              VALUES
              (?, '', '', ?, ?, ?, CAST(GETDATE() AS DATE))";

    $params = [$nama, $alamat, $telepon, $email];

    $result = sqlsrv_query($koneksi, $query, $params);

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: customer.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Customer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="form-container">

        <h1>Tambah Customer</h1>

        <form method="POST">

            <input type="text" name="nama_member" placeholder="Nama Customer" required>

            <input type="email" name="email" placeholder="Email" required>

            <input type="text" name="nomor_telepon" placeholder="Nomor Telepon" required>

            <input type="text" name="alamat" placeholder="Alamat" required>

            <button type="submit" name="submit">
                Simpan
            </button>

        </form>

    </div>

</body>

</html>