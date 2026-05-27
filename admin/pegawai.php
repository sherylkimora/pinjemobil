<?php

session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = sqlsrv_query($koneksi, "SELECT * FROM pegawai ORDER BY id_pegawai DESC");

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Pegawai - Pinjem Mobil</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-jabatan {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .badge-nonaktif {
            background: #fee2e2;
            color: #dc2626;
        }

        .action {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            background: #2563eb;
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
        }
    </style>

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

                <h1>Data Pegawai</h1>

                <p>Kelola data pegawai rental mobil.</p>

            </div>

            <a href="tambah_pegawai.php" class="btn">

                + Tambah Pegawai

            </a>

        </div>

        <div class="card">

            <table class="table">

                <tr>

                    <th>No</th>
                    <th>Nama Pegawai</th>
                    <th>Email</th>
                    <th>No Telepon</th>
                    <th>Alamat</th>
                    <th>Jabatan</th>
                    <!-- <th>Status</th> -->
                    <th>Aksi</th>

                </tr>

                <?php

                $no = 1;

                while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <strong>
                                <?= $row['nama_pegawai']; ?>
                            </strong>
                        </td>

                        <td><?= $row['email']; ?></td>

                        <td><?= $row['nomor_telepon']; ?></td>

                        <td><?= $row['alamat']; ?></td>

                        <td>

                            <span class="badge badge-jabatan">
                                <?= $row['jabatan']; ?>
                            </span>

                        </td>

                        <!-- <td>
                            <?php if (($row['status_pegawai'] ?? 'Aktif') == 'Aktif') { ?>
                                <span class="badge">Aktif</span>
                            <?php } else { ?>
                                <span class="badge badge-nonaktif">Nonaktif</span>
                            <?php } ?>
                        </td> -->

                        <td>

                            <div class="action">

                                <a href="edit_pegawai.php?id=<?= $row['id_pegawai']; ?>" class="btn-edit">

                                    Edit

                                </a>

                                <!-- <?php if (($row['status_pegawai'] ?? 'Aktif') == 'Aktif') { ?>
                                    <a href="hapus_pegawai.php?id=<?= $row['id_pegawai']; ?>" class="btn-delete"
                                        onclick="return confirm('Yakin nonaktifkan pegawai?')">
                                        Nonaktifkan
                                    </a>
                                <?php } else { ?>
                                    <span class="badge badge-nonaktif">Nonaktif</span>
                                <?php } ?> -->

                            </div>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</body>

</html>