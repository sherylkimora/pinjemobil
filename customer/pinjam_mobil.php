<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID mobil tidak ditemukan.";
    exit;
}

$id_mobil = $_GET['id'];

$query_mobil = "SELECT mobil.*, cabang.nama_cabang
                FROM mobil
                LEFT JOIN cabang ON mobil.id_cabang = cabang.id_cabang
                WHERE mobil.id_mobil = ?";

$result_mobil = sqlsrv_query($koneksi, $query_mobil, [$id_mobil]);

if ($result_mobil === false) {
    die(print_r(sqlsrv_errors(), true));
}

$mobil = sqlsrv_fetch_array($result_mobil, SQLSRV_FETCH_ASSOC);

if (!$mobil) {
    echo "Mobil tidak ditemukan.";
    exit;
}

if (isset($_POST['pinjam'])) {
    $id_member = $_SESSION['id_member'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $rencana_kembali = $_POST['rencana_kembali'];

    $tgl_pinjam = new DateTime($tanggal_pinjam);
    $tgl_kembali = new DateTime($rencana_kembali);

    $selisih = $tgl_pinjam->diff($tgl_kembali);
    $lama_sewa = $selisih->days;

    if ($lama_sewa <= 0) {
        $error = "Tanggal kembali harus setelah tanggal pinjam.";
    } else {
        $harga_sewa = $mobil['harga_sewa'];
        $total_sewa = $harga_sewa * $lama_sewa;
        $pembayaran_dp = $total_sewa * 0.5;

        $query_insert = "INSERT INTO peminjaman
    (id_member, id_mobil, tanggal_pinjam, rencana_kembali, lama_sewa, total_sewa, status_transaksi, pembayaran_dp)
    OUTPUT INSERTED.id_peminjaman
    VALUES
    (?, ?, ?, ?, ?, ?, 'Menunggu Cek Awal', ?)";

        $params_insert = [
            $id_member,
            $id_mobil,
            $tanggal_pinjam,
            $rencana_kembali,
            $lama_sewa,
            $total_sewa,
            $pembayaran_dp
        ];

        $insert = sqlsrv_query($koneksi, $query_insert, $params_insert);

        if ($insert === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row_id = sqlsrv_fetch_array($insert, SQLSRV_FETCH_NUMERIC);
        $id_peminjaman_baru = $row_id[0];

        if (!$id_peminjaman_baru) {
            echo "Gagal mengambil ID peminjaman baru.";
            exit;
        }

        $update_mobil = sqlsrv_query(
            $koneksi,
            "UPDATE mobil SET status_mobil = 'Dipinjam' WHERE id_mobil = ?",
            [$id_mobil]
        );

        if ($update_mobil === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        header("Location: qris.php?id=" . $id_peminjaman_baru . "&jenis=dp");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pinjam Mobil - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Pinjem Mobil
            <span>Customer</span>
        </div>

        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="daftar_mobil.php" class="active">Daftar Mobil</a>
            <a href="riwayat.php">Riwayat Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Pinjam Mobil</h1>
                <p>Pilih tanggal peminjaman. Total sewa dan DP akan dihitung otomatis oleh sistem.</p>
            </div>
            <a href="daftar_mobil.php" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($error)) { ?>
            <div class="alert-error"><?= $error; ?></div>
        <?php } ?>

        <div class="booking-layout">
            <div class="card">
                <?php if (!empty($mobil['foto_mobil'])) { ?>
                    <img src="../<?= $mobil['foto_mobil']; ?>" class="booking-img">
                <?php } ?>

                <h2><?= $mobil['nama_mobil']; ?></h2>
                <p class="car-location"><?= $mobil['nama_cabang']; ?></p>

                <div class="car-info">
                    <span><?= $mobil['kapasitas']; ?> orang</span>
                    <span><?= $mobil['nomor_polisi']; ?></span>
                    <span>Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?>/hari</span>
                </div>
            </div>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Rencana Kembali</label>
                        <input type="date" name="rencana_kembali" id="rencana_kembali" class="form-control" required>
                    </div>

                    <div class="summary-box">
                        <p>Harga Sewa per Hari</p>
                        <h3>Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?></h3>

                        <p>Lama Sewa</p>
                        <h3><span id="lama_sewa">0</span> hari</h3>

                        <p>Total Sewa</p>
                        <h3>Rp <span id="total_sewa">0</span></h3>

                        <p>DP 50%</p>
                        <h3>Rp <span id="dp">0</span></h3>
                    </div>

                    <button type="submit" name="pinjam" class="btn btn-full">
                        Ajukan Peminjaman
                    </button>
                </form>
            </div>
        </div>

        <script>
            const hargaSewa = <?= $mobil['harga_sewa']; ?>;

            function formatRupiah(angka) {
                return angka.toLocaleString('id-ID');
            }

            function hitungBiaya() {
                const tanggalPinjam = document.getElementById('tanggal_pinjam').value;
                const rencanaKembali = document.getElementById('rencana_kembali').value;

                if (tanggalPinjam && rencanaKembali) {
                    const tglPinjam = new Date(tanggalPinjam);
                    const tglKembali = new Date(rencanaKembali);

                    const selisihWaktu = tglKembali - tglPinjam;
                    const lamaSewa = selisihWaktu / (1000 * 60 * 60 * 24);

                    if (lamaSewa > 0) {
                        const totalSewa = lamaSewa * hargaSewa;
                        const dp = totalSewa * 0.5;

                        document.getElementById('lama_sewa').innerText = lamaSewa;
                        document.getElementById('total_sewa').innerText = formatRupiah(totalSewa);
                        document.getElementById('dp').innerText = formatRupiah(dp);
                    } else {
                        document.getElementById('lama_sewa').innerText = 0;
                        document.getElementById('total_sewa').innerText = 0;
                        document.getElementById('dp').innerText = 0;
                    }
                }
            }

            document.getElementById('tanggal_pinjam').addEventListener('change', hitungBiaya);
            document.getElementById('rencana_kembali').addEventListener('change', hitungBiaya);
        </script>

</body>

</html>