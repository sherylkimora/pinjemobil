<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$cabang = sqlsrv_query($koneksi, "SELECT * FROM cabang");

if ($cabang === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['simpan'])) {
    $id_cabang = $_POST['id_cabang'];
    $nomor_polisi = $_POST['nomor_polisi'];
    $nama_mobil = $_POST['nama_mobil'];
    $kapasitas = $_POST['kapasitas'];
    $status_mobil = $_POST['status_mobil'];
    $harga_sewa = $_POST['harga_sewa'];

    $foto_mobil = "";

    if ($_FILES['foto_mobil']['name'] != "") {
        $nama_file = time() . "_" . $_FILES['foto_mobil']['name'];
        $lokasi_tmp = $_FILES['foto_mobil']['tmp_name'];
        $folder_tujuan = "../uploads/mobil/" . $nama_file;

        move_uploaded_file($lokasi_tmp, $folder_tujuan);

        $foto_mobil = "uploads/mobil/" . $nama_file;
    }

    $query = "INSERT INTO mobil 
              (id_cabang, nomor_polisi, nama_mobil, kapasitas, status_mobil, harga_sewa, foto_mobil)
              VALUES 
              (?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $id_cabang,
        $nomor_polisi,
        $nama_mobil,
        $kapasitas,
        $status_mobil,
        $harga_sewa,
        $foto_mobil
    ];

    $result = sqlsrv_query($koneksi, $query, $params);

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: mobil.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="main" style="margin-left:0; max-width:800px; margin:40px auto;">
        <div class="card">
            <h2>Tambah Mobil</h2>
            <br>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Cabang</label>
                    <select name="id_cabang" class="form-control" required>
                        <option value="">Pilih Cabang</option>
                        <?php while ($row = sqlsrv_fetch_array($cabang, SQLSRV_FETCH_ASSOC)) { ?>
                            <option value="<?= $row['id_cabang']; ?>">
                                <?= $row['nama_cabang']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nomor Polisi</label>
                    <input type="text" name="nomor_polisi" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Nama Mobil</label>
                    <input type="text" name="nama_mobil" class="form-control" placeholder="Contoh: Toyota Avanza"
                        required>
                </div>

                <div class="form-group">
                    <label>Kapasitas</label>
                    <input type="number" name="kapasitas" class="form-control">
                </div>

                <div class="form-group">
                    <label>Status Mobil</label>
                    <select name="status_mobil" class="form-control">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Dipinjam">Dipinjam</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Harga Sewa per Hari</label>
                    <input type="number" name="harga_sewa" class="form-control">
                </div>

                <div class="form-group">
                    <label>Foto Mobil</label>
                    <input type="file" name="foto_mobil" class="form-control">
                </div>

                <button type="submit" name="simpan" class="btn">Simpan</button>
                <a href="mobil.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>

</body>

</html>