<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit;
}

$id = $_GET['id'];

$sql = "SELECT * FROM member WHERE id_member = ?";
$params = array($id);

$query = sqlsrv_query($koneksi, $sql, $params);

if ($query === false) {
	die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

if (!$data) {
	die("Customer tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>

<head>

	<title>Detail Customer</title>

	<style>
		.card {
			width: 1100px;
			margin: 30px auto;
			padding: 30px;
			background: white;
		}

		img {
			width: 300px;
			margin-top: 10px;
		}

		.dokumen{
    		display:flex;
    		gap:40px;
    		margin-top:20px;
    		align-items:flex-start;
		}

		.box-dokumen{
    		display:flex;
    		flex-direction:column;
		}

		.box-dokumen img{
		    width:500px;
		    border-radius:8px;
		}

		.dokumen{
		    display:flex;
		    flex-wrap:wrap;
		    gap:40px;
		}

		.btn-kembali{
            background:#2563eb;
            color:white;
            padding:6px 12px;
            border-radius:6px;
            text-decoration:none;
		}
	</style>

</head>

<body>

	<div class="card">

		<h2>Detail Customer</h2>

		<p>Nama : <?= $data['nama_member']; ?></p>

		<p>Email : <?= $data['email']; ?></p>

		<p>No HP : <?= $data['nomor_telepon']; ?></p>

		<p>Alamat : <?= $data['alamat']; ?></p>

		<hr>

	<div class="dokumen">

    <div class="box-dokumen">

        <h3>KTP</h3>

        <img src="../<?= $data['ktp']; ?>">

    </div>

    <div class="box-dokumen">

        <h3>SIM</h3>

        <img src="../<?= $data['sim']; ?>">

    </div>

</div>

		<br><br>

		<a href="customer.php" class="btn-kembali">
			Kembali
		</a>

	</div>

</body>

</html>