<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = sqlsrv_query($koneksi, "SELECT * FROM member ORDER BY id_member DESC");

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Customer - Pinjem Mobil</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
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

        .badge {
            background: #dbeafe;
            color: #2563eb;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: bold;
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
            <a href="pegawai.php">Data Pegawai</a>
            <a href="customer.php" class="active">Data Customer</a>
            <a href="transaksi.php">Data Peminjaman</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <div class="page-title">
                <h1>Data Customer</h1>
                <p>Kelola data customer rental mobil.</p>
            </div>

            <!-- <a href="tambah_customer.php" class="btn">
                + Tambah Customer
            </a> -->
        </div>

        <div class="card">
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Nama Customer</th>
                    <th>Email</th>
                    <th>No Telepon</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                    <!-- <th>Status</th> -->
                    <!-- <th>Aksi</th> -->
                </tr>

                <?php
                $no = 1;
                while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <strong><?= $row['nama_member']; ?></strong>
                        </td>

                        <td><?= $row['email']; ?></td>
                        <td><?= $row['nomor_telepon']; ?></td>
                        <td><?= $row['alamat']; ?></td>
                        <td>
                            <div class="action">

                                <a href="detail_customer.php?id=<?= $row['id_member']; ?>">
                                class="btn-edit">
                                Detail
                                </a>
                            </div>
                        </td>

                        <!-- <td>
                            <span class="badge">Aktif</span>
                        </td> -->

                        <!-- <td>
                            <div class="action">
                                <a href="edit_customer.php?id=<?= $row['id_member']; ?>" class="btn-edit">
                                    Edit
                                </a>

                                <a href="hapus_customer.php?id=<?= $row['id_member']; ?>" class="btn-delete"
                                    onclick="return confirm('Yakin hapus customer?')">
                                    Hapus
                                </a>
                            </div>
                        </td> -->
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>

</body>

</html>