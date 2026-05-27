<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama = $_POST['nama_pegawai'];
    $telepon = $_POST['nomor_telepon'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $jabatan = $_POST['jabatan'];

    $query = "INSERT INTO pegawai
              (id_cabang, nama_pegawai, nomor_telepon, email, alamat, jabatan)
              VALUES
              (NULL, ?, ?, ?, ?, ?)";

    $params = [$nama, $telepon, $email, $alamat, $jabatan];

    $result = sqlsrv_query($koneksi, $query, $params);

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error = "Email harus menggunakan @gmail.com";
    }

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    header("Location: pegawai.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Pegawai - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Admin Panel</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="mobil.php">Data Mobil</a>
            <a href="pegawai.php" class="active">Data Pegawai</a>
            <a href="customer.php">Data Customer</a>
            <a href="transaksi.php">Data Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Tambah Pegawai</h1>
                <p>Tambahkan data pegawai yang bertugas melakukan pengecekan mobil.</p>
            </div>

            <a href="pegawai.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Pegawai</label>
                        <input type="text" name="nama_pegawai" class="form-control" placeholder="Masukkan nama pegawai"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="nomor_telepon" class="form-control" placeholder="Contoh: 081234567890"
                            maxlength="15" pattern="[0-9]{10,15}" inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: nama@gmail.com"
                            pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Email harus menggunakan @gmail.com"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Jabatan</label>
                        <select name="jabatan" class="form-control" required>
                            <option value="">Pilih Jabatan</option>
                            <option value="Pemilik">Pemilik</option>
                            <option value="Admin Cabang">Admin Cabang</option>
                            <option value="Petugas Cek Kondisi">Petugas Cek Kondisi</option>
                            <!-- <option value="Customer Service">Customer Service</option> -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="4" placeholder="Masukkan alamat pegawai"
                        required></textarea>
                </div>

                <div class="actions">
                    <button type="submit" name="submit" class="btn">Simpan Pegawai</button>
                    <a href="pegawai.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>