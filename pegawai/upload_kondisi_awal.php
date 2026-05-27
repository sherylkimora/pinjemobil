<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pegawai') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID peminjaman tidak ditemukan.";
    exit;
}

$id_peminjaman = $_GET['id'];

$query = "SELECT peminjaman.*, member.nama_member, mobil.nama_mobil, mobil.nomor_polisi, mobil.foto_mobil
          FROM peminjaman
          LEFT JOIN member ON peminjaman.id_member = member.id_member
          LEFT JOIN mobil ON peminjaman.id_mobil = mobil.id_mobil
          WHERE peminjaman.id_peminjaman = ?";

$result = sqlsrv_query($koneksi, $query, [$id_peminjaman]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data peminjaman tidak ditemukan.";
    exit;
}

if (isset($_POST['simpan'])) {
    $id_pegawai = $_SESSION['id_pegawai'];
    $keterangan = $_POST['keterangan_kondisi'];
    $foto_kondisi = "";

    if ($_FILES['foto_kondisi']['name'] != "") {
        $nama_file = time() . "_" . $_FILES['foto_kondisi']['name'];
        $tmp = $_FILES['foto_kondisi']['tmp_name'];
        $tujuan = "../uploads/kondisi/" . $nama_file;

        move_uploaded_file($tmp, $tujuan);

        $foto_kondisi = "uploads/kondisi/" . $nama_file;
    }

    $insert = "INSERT INTO kondisi_mobil
               (id_peminjaman, id_pegawai, jenis_kondisi, foto_kondisi, keterangan_kondisi, tanggal_upload)
               VALUES
               (?, ?, 'Awal', ?, ?, CAST(GETDATE() AS DATE))";

    $params_insert = [$id_peminjaman, $id_pegawai, $foto_kondisi, $keterangan];

    $result_insert = sqlsrv_query($koneksi, $insert, $params_insert);

    if ($result_insert === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $update = "UPDATE peminjaman 
               SET status_transaksi = 'Sedang Dipinjam'
               WHERE id_peminjaman = ?";

    $result_update = sqlsrv_query($koneksi, $update, [$id_peminjaman]);

    if ($result_update === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: cek_awal.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Kondisi Awal - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Pegawai</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="cek_awal.php" class="active">Cek Kondisi Awal</a>
            <a href="cek_akhir.php">Cek Kondisi Akhir</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Upload Kondisi Awal</h1>
                <p>Catat kondisi mobil sebelum customer membawa mobil.</p>
            </div>
            <a href="cek_awal.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="booking-layout">
            <div class="card">
                <?php if (!empty($data['foto_mobil'])) { ?>
                    <img src="../<?= $data['foto_mobil']; ?>" class="booking-img">
                <?php } ?>

                <h2><?= $data['nama_mobil']; ?></h2>
                <p class="car-location"><?= $data['nomor_polisi']; ?></p>

                <div class="car-info">
                    <span>Customer: <?= $data['nama_member']; ?></span>
                    <span>Pinjam:
                        <?= $data['tanggal_pinjam'] ? $data['tanggal_pinjam']->format('Y-m-d') : '-'; ?></span>
                    <span>Kembali:
                        <?= $data['rencana_kembali'] ? $data['rencana_kembali']->format('Y-m-d') : '-'; ?></span>
                </div>
            </div>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Foto Kondisi Awal</label>
                        <input type="file" name="foto_kondisi" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Kondisi Awal</label>
                        <textarea name="keterangan_kondisi" class="form-control" rows="6"
                            placeholder="Contoh: kondisi body baik, terdapat baret kecil di pintu kanan"
                            required></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-full">
                        Simpan Kondisi Awal
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>