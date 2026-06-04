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

$sql = "SELECT member.*, cabang.nama_cabang, cabang.alamat AS alamat_cabang
        FROM member
        LEFT JOIN cabang ON member.id_cabang = cabang.id_cabang
        WHERE member.id_member = ?";

$query = sqlsrv_query($koneksi, $sql, [$id]);

if ($query === false) {
	die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

if (!$data) {
	echo "Customer tidak ditemukan.";
	exit;
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Detail Customer - Pinjem Mobil</title>
	<link rel="stylesheet" href="../assets/css/style.css">

	<style>
		.detail-layout {
			display: grid;
			grid-template-columns: 1fr;
			gap: 24px;
		}

		.customer-header {
			display: flex;
			justify-content: space-between;
			gap: 20px;
			align-items: flex-start;
		}

		.customer-name h2 {
			font-size: 30px;
			margin-bottom: 6px;
			color: #111827;
		}

		.customer-name p {
			color: #6b7280;
			line-height: 1.6;
		}

		.badge-branch {
			background: #eef2ff;
			color: #3730a3;
			padding: 10px 14px;
			border-radius: 999px;
			font-size: 13px;
			font-weight: 700;
			white-space: nowrap;
		}

		.info-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 18px;
			margin-top: 24px;
		}

		.info-item {
			background: #f9fafb;
			border: 1px solid #eef0f4;
			border-radius: 16px;
			padding: 18px;
		}

		.info-item span {
			display: block;
			color: #6b7280;
			font-size: 13px;
			margin-bottom: 8px;
		}

		.info-item strong {
			display: block;
			color: #111827;
			font-size: 16px;
			line-height: 1.5;
		}

		.document-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 22px;
		}

		.document-card {
			background: #ffffff;
			border: 1px solid #eef0f4;
			border-radius: 20px;
			padding: 20px;
		}

		.document-card h3 {
			margin-bottom: 14px;
			color: #111827;
		}

		.document-img-wrap {
			background: #f9fafb;
			border-radius: 18px;
			padding: 14px;
			border: 1px solid #eef0f4;
		}

		.document-img {
			width: 100%;
			max-height: 280px;
			object-fit: contain;
			display: block;
			border-radius: 14px;
		}

		.empty-document {
			height: 220px;
			border-radius: 14px;
			background: #f3f4f6;
			color: #6b7280;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 700;
		}

		.detail-actions {
			margin-top: 24px;
			display: flex;
			gap: 12px;
		}

		@media (max-width: 900px) {
			.customer-header {
				flex-direction: column;
			}

			.info-grid,
			.document-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>

<body>

	<div class="sidebar">
		<div class="logo">
			Pinjem Mobil
			<span>Hi, <?= $_SESSION['nama']; ?>!</span>
		</div>

		<div class="menu">
			<a href="dashboard.php">Dashboard</a>
			<a href="mobil.php">Data Mobil</a>
			<a href="pegawai.php">Data Pegawai</a>
			<a href="customer.php" class="active">Data Customer</a>
			<a href="transaksi.php">Data Peminjaman</a>
			<a href="../logout.php">Logout</a>
		</div>
	</div>

	<div class="main">

		<div class="topbar">
			<div class="page-title">
				<h1>Detail Customer</h1>
				<p>Informasi lengkap customer dan dokumen identitas.</p>
			</div>

			<a href="customer.php" class="btn btn-secondary">Kembali</a>
		</div>

		<div class="detail-layout">

			<div class="card">
				<div class="customer-header">
					<div class="customer-name">
						<h2><?= $data['nama_member']; ?></h2>
						<p>
							Customer terdaftar di sistem Pinjem Mobil.
							Data ini digunakan untuk proses peminjaman dan verifikasi dokumen.
						</p>
					</div>

					<span class="badge-branch">
						<?= $data['nama_cabang'] ?? 'Cabang belum dipilih'; ?>
					</span>
				</div>

				<div class="info-grid">
					<div class="info-item">
						<span>Email</span>
						<strong><?= $data['email']; ?></strong>
					</div>

					<div class="info-item">
						<span>Nomor Telepon</span>
						<strong><?= $data['nomor_telepon']; ?></strong>
					</div>

					<div class="info-item">
						<span>Alamat Customer</span>
						<strong><?= $data['alamat']; ?></strong>
					</div>

					<div class="info-item">
						<span>Cabang Customer</span>
						<strong>
							<?= $data['nama_cabang'] ?? '-'; ?><br>
							<?= $data['alamat_cabang'] ?? ''; ?>
						</strong>
					</div>

					<div class="info-item">
						<span>Tanggal Registrasi</span>
						<strong>
							<?php
							if ($data['tanggal_registrasi'] instanceof DateTime) {
								echo $data['tanggal_registrasi']->format('Y-m-d');
							} else {
								echo $data['tanggal_registrasi'];
							}
							?>
						</strong>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="customer-header">
					<div class="customer-name">
						<h2>Dokumen Customer</h2>
						<p>KTP dan SIM dummy untuk kebutuhan demo sistem.</p>
					</div>
				</div>

				<div class="document-grid">
					<div class="document-card">
						<h3>KTP</h3>

						<div class="document-img-wrap">
							<?php if (!empty($data['ktp'])) { ?>
								<img src="../<?= $data['ktp']; ?>" class="document-img">
							<?php } else { ?>
								<div class="empty-document">KTP belum tersedia</div>
							<?php } ?>
						</div>
					</div>

					<div class="document-card">
						<h3>SIM</h3>

						<div class="document-img-wrap">
							<?php if (!empty($data['sim'])) { ?>
								<img src="../<?= $data['sim']; ?>" class="document-img">
							<?php } else { ?>
								<div class="empty-document">SIM belum tersedia</div>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="detail-actions">
					<a href="customer.php" class="btn btn-secondary">Kembali</a>
				</div>
			</div>

		</div>

	</div>

</body>

</html>