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

$id_pegawai = $_GET['id'];

$query = "SELECT * FROM pegawai WHERE id_pegawai = ?";
$result = sqlsrv_query($koneksi, $query, [$id_pegawai]);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

if (!$data) {
    echo "Data pegawai tidak ditemukan.";
    exit;
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];
    $jabatan = $_POST['jabatan'];

    if (!preg_match('/^[0-9]{10,15}$/', $nomor_telepon)) {
        $error = "Nomor telepon harus berupa angka dan terdiri dari 10 sampai 15 digit.";
    } else if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error = "Email harus menggunakan @gmail.com.";
    } else {
        $update = "UPDATE pegawai
                   SET email = ?,
                       nomor_telepon = ?,
                       alamat = ?,
                       jabatan = ?
                   WHERE id_pegawai = ?";

        $params = [$email, $nomor_telepon, $alamat, $jabatan, $id_pegawai];

        $update_result = sqlsrv_query($koneksi, $update, $params);

        if ($update_result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        header("Location: pegawai.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Pegawai - Pinjem Mobil</title>
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
                <h1>Edit Pegawai</h1>
                <p>Perbarui data kontak dan jabatan pegawai.</p>
            </div>

            <a href="pegawai.php" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($error)) { ?>
            <div class="alert-error">
                <?= $error; ?>
            </div>
        <?php } ?>

        <div class="card">
            <form method="POST">

                <div class="form-group">
                    <label>Nama Pegawai</label>
                    <input type="text" class="form-control" value="<?= $data['nama_pegawai']; ?>" disabled>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $data['email']; ?>"
                            placeholder="Contoh: pegawai@gmail.com" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
                            title="Email harus menggunakan @gmail.com" required>
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="nomor_telepon" class="form-control"
                            value="<?= $data['nomor_telepon']; ?>" placeholder="Contoh: 081234567890" maxlength="15"
                            pattern="[0-9]{10,15}" inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            title="Nomor telepon harus berupa angka 10 sampai 15 digit" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="4" placeholder="Masukkan alamat pegawai"
                        required><?= $data['alamat']; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="jabatan" class="form-control" required>
                        <option value="">Pilih Jabatan</option>

                        <option value="Pemilik" <?= $data['jabatan'] == 'Pemilik' ? 'selected' : ''; ?>>
                            Pemilik
                        </option>

                        <option value="Admin Cabang" <?= $data['jabatan'] == 'Admin Cabang' ? 'selected' : ''; ?>>
                            Admin Cabang
                        </option>

                        <option value="Petugas Cek Kondisi" <?= $data['jabatan'] == 'Petugas Cek Kondisi' ? 'selected' : ''; ?>>
                            Petugas Cek Kondisi
                        </option>

                        <option value="Customer Service" <?= $data['jabatan'] == 'Customer Service' ? 'selected' : ''; ?>>
                            Customer Service
                        </option>
                    </select>
                </div>

                <div class="actions">
                    <button type="submit" name="submit" class="btn">
                        Simpan Perubahan
                    </button>

                    <a href="pegawai.php" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

</body>

</html>