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

$query = "SELECT peminjaman.*, member.nama_member, mobil.id_mobil, mobil.nama_mobil, mobil.nomor_polisi, mobil.foto_mobil
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
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $keterangan = $_POST['keterangan_kondisi'];

    $tgl_rencana = $data['rencana_kembali'];
    $tgl_kembali = new DateTime($tanggal_kembali);

    $selisih = $tgl_rencana->diff($tgl_kembali);

    if ($tgl_kembali > $tgl_rencana) {
        $keterlambatan = $selisih->days;
    } else {
        $keterlambatan = 0;
    }

    $denda_per_hari = 50000;
    $denda_terlambat = $keterlambatan * $denda_per_hari;

    $total_denda_kondisi = 0;
    $detail_denda_terpilih = [];

    if (isset($_POST['denda_kondisi'])) {
        foreach ($_POST['denda_kondisi'] as $denda) {
            $pecah = explode('|', $denda);

            $nama_denda = $pecah[0];
            $nominal_denda = (int) $pecah[1];

            $total_denda_kondisi += $nominal_denda;

            $detail_denda_terpilih[] = [
                'nama_denda' => $nama_denda,
                'nominal_denda' => $nominal_denda
            ];
        }
    }

    $total_denda = $denda_terlambat + $total_denda_kondisi;
    $total_bayar_sewa = $data['total_sewa'] + $total_denda;
    $sisa_bayar = $total_bayar_sewa - $data['pembayaran_dp'];

    $foto_kondisi = "";

    if ($_FILES['foto_kondisi']['name'] != "") {
        $nama_file = time() . "_" . $_FILES['foto_kondisi']['name'];
        $tmp = $_FILES['foto_kondisi']['tmp_name'];
        $tujuan = "../uploads/kondisi/" . $nama_file;

        move_uploaded_file($tmp, $tujuan);

        $foto_kondisi = "uploads/kondisi/" . $nama_file;
    }

    sqlsrv_begin_transaction($koneksi);

    $insert_kondisi = "INSERT INTO kondisi_mobil
        (id_peminjaman, id_pegawai, jenis_kondisi, foto_kondisi, keterangan_kondisi, tanggal_upload)
        VALUES
        (?, ?, 'Akhir', ?, ?, CAST(GETDATE() AS DATE))";

    $result_kondisi = sqlsrv_query(
        $koneksi,
        $insert_kondisi,
        [$id_peminjaman, $id_pegawai, $foto_kondisi, $keterangan]
    );

    if ($result_kondisi === false) {
        sqlsrv_rollback($koneksi);
        die(print_r(sqlsrv_errors(), true));
    }

    $insert_pengembalian = "INSERT INTO pengembalian
        (id_peminjaman, id_pegawai, tanggal_kembali, keterlambatan, total_denda, total_bayar_sewa, sisa_bayar, status_pembayaran)
        OUTPUT INSERTED.id_pengembalian
        VALUES
        (?, ?, ?, ?, ?, ?, ?, 'Belum Lunas')";

    $result_pengembalian = sqlsrv_query(
        $koneksi,
        $insert_pengembalian,
        [
            $id_peminjaman,
            $id_pegawai,
            $tanggal_kembali,
            $keterlambatan,
            $total_denda,
            $total_bayar_sewa,
            $sisa_bayar
        ]
    );

    if ($result_pengembalian === false) {
        sqlsrv_rollback($koneksi);
        die(print_r(sqlsrv_errors(), true));
    }

    $row_pengembalian = sqlsrv_fetch_array($result_pengembalian, SQLSRV_FETCH_ASSOC);

    if (!$row_pengembalian || empty($row_pengembalian['id_pengembalian'])) {
        sqlsrv_rollback($koneksi);
        echo "Gagal mengambil ID pengembalian.";
        exit;
    }

    $id_pengembalian = $row_pengembalian['id_pengembalian'];

    foreach ($detail_denda_terpilih as $detail) {
        $insert_detail = "INSERT INTO detail_denda
            (id_pengembalian, nama_denda, nominal_denda)
            VALUES
            (?, ?, ?)";

        $result_detail = sqlsrv_query(
            $koneksi,
            $insert_detail,
            [
                $id_pengembalian,
                $detail['nama_denda'],
                $detail['nominal_denda']
            ]
        );

        if ($result_detail === false) {
            sqlsrv_rollback($koneksi);
            die(print_r(sqlsrv_errors(), true));
        }
    }

    $update_peminjaman = "UPDATE peminjaman
                          SET status_transaksi = 'Menunggu Pelunasan'
                          WHERE id_peminjaman = ?";

    $result_update = sqlsrv_query($koneksi, $update_peminjaman, [$id_peminjaman]);

    if ($result_update === false) {
        sqlsrv_rollback($koneksi);
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_commit($koneksi);

    header("Location: cek_akhir.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Kondisi Akhir - Pinjem Mobil</title>
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
            <a href="cek_awal.php">Cek Kondisi Awal</a>
            <a href="cek_akhir.php" class="active">Cek Kondisi Akhir</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h1>Upload Kondisi Akhir</h1>
                <p>Catat kondisi mobil setelah dikembalikan dan pilih kualifikasi denda jika ada kerusakan.</p>
            </div>
            <a href="cek_akhir.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="booking-layout">
            <div class="card">
                <?php if (!empty($data['foto_mobil'])) { ?>
                    <img src="../<?= $data['foto_mobil']; ?>" class="booking-img">
                <?php } ?>

                <h2><?= $data['nama_mobil']; ?></h2>
                <p class="car-location"><?= $data['nomor_polisi']; ?></p>

                <div class="summary-box">
                    <p>Customer</p>
                    <h3><?= $data['nama_member']; ?></h3>

                    <p>Rencana Kembali</p>
                    <h3><?= $data['rencana_kembali'] ? $data['rencana_kembali']->format('Y-m-d') : '-'; ?></h3>

                    <p>Total Sewa</p>
                    <h3>Rp <?= number_format($data['total_sewa'], 0, ',', '.'); ?></h3>

                    <p>DP yang Sudah Dibayar</p>
                    <h3>Rp <?= number_format($data['pembayaran_dp'], 0, ',', '.'); ?></h3>
                </div>
            </div>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Tanggal Kembali Aktual</label>
                        <input type="date" name="tanggal_kembali" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Foto Kondisi Akhir</label>
                        <input type="file" name="foto_kondisi" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Kualifikasi Denda Kondisi Mobil</label>

                        <div class="fine-list">
                            <label class="fine-item">
                                <input type="checkbox" name="denda_kondisi[]" value="Light Damage|50000"
                                    data-nominal="50000">
                                <span>
                                    <strong>Light Damage</strong>
                                    <small>Kerusakan ringan, misalnya baret kecil</small>
                                </span>
                                <b>Rp 50.000</b>
                            </label>

                            <label class="fine-item">
                                <input type="checkbox" name="denda_kondisi[]" value="Medium Damage|150000"
                                    data-nominal="150000">
                                <span>
                                    <strong>Medium Damage</strong>
                                    <small>Kerusakan sedang, misalnya penyok kecil</small>
                                </span>
                                <b>Rp 150.000</b>
                            </label>

                            <label class="fine-item">
                                <input type="checkbox" name="denda_kondisi[]" value="Heavy Damage|300000"
                                    data-nominal="300000">
                                <span>
                                    <strong>Heavy Damage</strong>
                                    <small>Kerusakan berat, misalnya bumper rusak</small>
                                </span>
                                <b>Rp 300.000</b>
                            </label>

                            <label class="fine-item">
                                <input type="checkbox" name="denda_kondisi[]" value="Maximum Damage|500000"
                                    data-nominal="500000">
                                <span>
                                    <strong>Maximum Damage</strong>
                                    <small>Kerusakan maksimal, misalnya komponen utama rusak</small>
                                </span>
                                <b>Rp 500.000</b>
                            </label>
                        </div>

                        <div class="summary-box">
                            <p>Total Denda Kondisi</p>
                            <h3>Rp <span id="total_denda_kondisi">0</span></h3>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Kondisi Akhir</label>
                        <textarea name="keterangan_kondisi" class="form-control" rows="6"
                            placeholder="Contoh: kondisi mobil baik / ada baret baru di bumper belakang"
                            required></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-full">
                        Simpan Kondisi Akhir
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const checkboxDenda = document.querySelectorAll('input[name="denda_kondisi[]"]');
        const totalDendaKondisi = document.getElementById('total_denda_kondisi');

        function formatRupiah(angka) {
            return angka.toLocaleString('id-ID');
        }

        function hitungDendaKondisi() {
            let total = 0;

            checkboxDenda.forEach(function (item) {
                if (item.checked) {
                    total += parseInt(item.dataset.nominal);
                }
            });

            totalDendaKondisi.innerText = formatRupiah(total);
        }

        checkboxDenda.forEach(function (item) {
            item.addEventListener('change', hitungDendaKondisi);
        });
    </script>

</body>

</html>