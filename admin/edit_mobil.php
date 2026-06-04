<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID mobil tidak ditemukan.";
    exit;
}

$id_mobil = $_GET['id'];

$query = "SELECT * FROM mobil WHERE id_mobil = ?";
$result = sqlsrv_query($koneksi, $query, [$id_mobil]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data mobil tidak ditemukan.";
    exit;
}

$cabang_query = sqlsrv_query(
    $koneksi,
    "SELECT * FROM cabang ORDER BY nama_cabang"
);

if (isset($_POST['submit'])) {

    $nomor_polisi = $_POST['nomor_polisi'];
    $nama_mobil = $_POST['nama_mobil'];
    $kapasitas = $_POST['kapasitas'];
    $harga_sewa = $_POST['harga_sewa'];
    $status_mobil = $_POST['status_mobil'];
    $id_cabang = $_POST['id_cabang'];

    $foto_mobil = $data['foto_mobil'];

    if (
        isset($_FILES['foto_mobil']) &&
        $_FILES['foto_mobil']['error'] == 0
    ) {

        $folder_upload = "../uploads/mobil/";

        if (!file_exists($folder_upload)) {
            mkdir($folder_upload, 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['foto_mobil']['name']);

        move_uploaded_file(
            $_FILES['foto_mobil']['tmp_name'],
            $folder_upload . $nama_file
        );

        $foto_mobil = "uploads/mobil/" . $nama_file;
    }

    $update = "
        UPDATE mobil
SET
    status_mobil = ?,
    harga_sewa = ?,
    foto_mobil = ?,
    id_cabang = ?
WHERE id_mobil = ?
    ";

    $params = [
        $status_mobil,
        $harga_sewa,
        $foto_mobil,
        $id_cabang,
        $id_mobil
    ];

    $update_result = sqlsrv_query(
        $koneksi,
        $update,
        $params
    );

    if ($update_result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: mobil.php");
    exit;
}
?>

<!DOCTYPE html>

<html>

<head>
    <title>Edit Mobil - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Hi, <?= $_SESSION['nama']; ?>!</span>
        </div>


        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="mobil.php" class="active">Data Mobil</a>
            <a href="pegawai.php">Data Pegawai</a>
            <a href="customer.php">Data Customer</a>
            <a href="transaksi.php">Data Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>


    </div>

    <div class="main">


        <div class="topbar">
            <div class="page-title">
                <h1>Edit Mobil</h1>
                <p>Perbarui informasi mobil rental.</p>
            </div>

            <a href="mobil.php" class="btn btn-secondary">
                Kembali
            </a>
        </div>

        <div class="card">

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Nomor Polisi</label>
                    <input type="text" class="form-control" value="<?= $data['nomor_polisi']; ?>" disabled>
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Nama Mobil</label>
                        <input type="text" class="form-control" value="<?= $data['nama_mobil']; ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Kapasitas</label>
                        <input type="number" class="form-control" value="<?= $data['kapasitas']; ?>" disabled>
                    </div>

                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Harga Sewa</label>
                        <input type="number" name="harga_sewa" class="form-control" value="<?= $data['harga_sewa']; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Status Mobil</label>

                        <select name="status_mobil" class="form-control">

                            <option value="Tersedia" <?= $data['status_mobil'] == 'Tersedia' ? 'selected' : ''; ?>>
                                Tersedia
                            </option>

                            <option value="Dipinjam" <?= $data['status_mobil'] == 'Dipinjam' ? 'selected' : ''; ?>>
                                Dipinjam
                            </option>

                            <option value="Maintenance" <?= $data['status_mobil'] == 'Maintenance' ? 'selected' : ''; ?>>
                                Maintenance
                            </option>

                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label>Cabang</label>

                    <select name="id_cabang" class="form-control">

                        <?php while ($cabang = sqlsrv_fetch_array($cabang_query, SQLSRV_FETCH_ASSOC)) { ?>

                            <option value="<?= $cabang['id_cabang']; ?>" <?= $cabang['id_cabang'] == $data['id_cabang'] ? 'selected' : ''; ?>>

                                <?= $cabang['nama_cabang']; ?>

                            </option>

                        <?php } ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Foto Mobil Saat Ini</label>

                    <br><br>

                    <?php if (!empty($data['foto_mobil'])) { ?>
                        <img src="../<?= $data['foto_mobil']; ?>" style="
                        width:220px;
                        border-radius:12px;
                        border:1px solid #ddd;
                        padding:10px;
                        background:#fff;
                    ">
                    <?php } ?>

                    <br><br>

                    <input type="file" name="foto_mobil" class="form-control">
                </div>

                <div class="actions">

                    <button type="submit" name="submit" class="btn">
                        Simpan Perubahan
                    </button>

                    <a href="mobil.php" class="btn btn-secondary">
                        Batal
                    </a>

                </div>

            </form>

        </div>


    </div>

</body>

</html>