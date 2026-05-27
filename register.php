<?php
session_start();
include 'koneksi.php';

$cabang_query = sqlsrv_query($koneksi, "SELECT id_cabang, nama_cabang, alamat FROM cabang ORDER BY nama_cabang ASC");

if ($cabang_query === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama_member']);
    $ktp = "";
    $sim = "";

    $folder_identitas = "uploads/identitas/";

    if (!is_dir($folder_identitas)) {
        mkdir($folder_identitas, 0777, true);
    }

    if (!empty($_FILES['ktp']['name'])) {
        $nama_ktp = time() . "_ktp_" . $_FILES['ktp']['name'];
        $tmp_ktp = $_FILES['ktp']['tmp_name'];
        $tujuan_ktp = $folder_identitas . $nama_ktp;

        move_uploaded_file($tmp_ktp, $tujuan_ktp);

        $ktp = $tujuan_ktp;
    }

    if (!empty($_FILES['sim']['name'])) {
        $nama_sim = time() . "_sim_" . $_FILES['sim']['name'];
        $tmp_sim = $_FILES['sim']['tmp_name'];
        $tujuan_sim = $folder_identitas . $nama_sim;

        move_uploaded_file($tmp_sim, $tujuan_sim);

        $sim = $tujuan_sim;
    }
    $alamat = trim($_POST['alamat']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $id_cabang = $_POST['id_cabang'];
    $setuju_cabang = isset($_POST['setuju_cabang']) ? 1 : 0;

    if (!preg_match('/^[0-9]{10,15}$/', $nomor_telepon)) {
        $error = "Nomor telepon harus berupa angka dan terdiri dari 10 sampai 15 digit.";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error = "Email harus menggunakan @gmail.com.";
    } elseif (!preg_match('/^[a-z0-9]{1,8}$/', $username)) {
        $error = "Username hanya boleh huruf kecil dan angka, tanpa simbol, maksimal 8 karakter.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $password)) {
        $error = "Password minimal 8 karakter dan wajib mengandung 1 huruf besar, 1 angka, dan 1 simbol.";
    } elseif (empty($id_cabang)) {
        $error = "Silakan pilih cabang.";
    } elseif (!$setuju_cabang) {
        $error = "Kamu harus menyetujui ketentuan cabang terlebih dahulu.";
    } else {
        $cek_username = sqlsrv_query(
            $koneksi,
            "SELECT id_user FROM users WHERE username = ?",
            [$username]
        );

        if ($cek_username === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $username_exists = sqlsrv_fetch_array($cek_username, SQLSRV_FETCH_ASSOC);

        $cek_email = sqlsrv_query(
            $koneksi,
            "SELECT id_member FROM member WHERE email = ?",
            [$email]
        );

        if ($cek_email === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $email_exists = sqlsrv_fetch_array($cek_email, SQLSRV_FETCH_ASSOC);

        if ($username_exists) {
            $error = "Username sudah digunakan.";
        } elseif ($email_exists) {
            $error = "Email sudah terdaftar.";
        } else {
            sqlsrv_begin_transaction($koneksi);

            $insert_member = "INSERT INTO member
                (nama_member, ktp, sim, alamat, nomor_telepon, email, tanggal_registrasi, id_cabang)
                OUTPUT INSERTED.id_member
                VALUES
                (?, ?, ?, ?, ?, ?, CAST(GETDATE() AS DATE), ?)";

            $params_member = [
                $nama,
                $ktp,
                $sim,
                $alamat,
                $nomor_telepon,
                $email,
                $id_cabang
            ];

            $result_member = sqlsrv_query($koneksi, $insert_member, $params_member);

            if ($result_member === false) {
                sqlsrv_rollback($koneksi);
                die(print_r(sqlsrv_errors(), true));
            }

            $member_baru = sqlsrv_fetch_array($result_member, SQLSRV_FETCH_ASSOC);
            $id_member_baru = $member_baru['id_member'];

            $insert_user = "INSERT INTO users
                (nama, username, password, role, id_member, id_pegawai)
                VALUES
                (?, ?, ?, 'customer', ?, NULL)";

            $params_user = [
                $nama,
                $username,
                $password,
                $id_member_baru
            ];

            $result_user = sqlsrv_query($koneksi, $insert_user, $params_user);

            if ($result_user === false) {
                sqlsrv_rollback($koneksi);
                die(print_r(sqlsrv_errors(), true));
            }

            sqlsrv_commit($koneksi);

            header("Location: login.php?register=success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Akun</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body.register-page {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .register-shell {
            width: 100%;
            max-width: 1120px;
            display: grid;
            grid-template-columns: 420px 1fr;
            background: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.10);
        }

        .register-left {
            background: linear-gradient(180deg, #08142f 0%, #0f1d40 100%);
            color: white;
            padding: 42px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand h1 {
            margin: 0 0 10px;
            font-size: 36px;
            line-height: 1.2;
        }

        .brand p {
            margin: 0;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.7;
            font-size: 15px;
        }

        .benefit-box {
            margin-top: 34px;
            display: grid;
            gap: 14px;
        }

        .benefit-item {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 16px 18px;
        }

        .benefit-item h4 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .benefit-item p {
            margin: 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.5;
        }

        .left-footer {
            margin-top: 32px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
        }

        .register-right {
            padding: 42px;
            background: #ffffff;
        }

        .register-header h2 {
            margin: 0;
            font-size: 40px;
            color: #111827;
        }

        .register-header p {
            margin: 10px 0 0;
            color: #6b7280;
            font-size: 15px;
        }

        .alert-error {
            margin-top: 20px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 14px;
        }

        .register-form {
            margin-top: 28px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .form-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
            background: #f9fafb;
        }

        .form-control:focus {
            border-color: #08142f;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(8, 20, 47, 0.08);
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .input-note {
            margin-top: 7px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        .agreement-box {
            margin-top: 4px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 16px;
        }

        .agreement-box label {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            font-weight: normal;
            margin: 0;
        }

        .agreement-box input {
            margin-top: 4px;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 26px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #08142f;
            color: white;
            border: none;
            padding: 14px 22px;
            border-radius: 16px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background: #122149;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
            border: none;
            padding: 14px 22px;
            border-radius: 16px;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
        }

        .bottom-login {
            margin-top: 24px;
            font-size: 14px;
            color: #6b7280;
        }

        .bottom-login a {
            color: #111827;
            font-weight: bold;
            text-decoration: none;
        }

        @media (max-width: 980px) {
            .register-shell {
                grid-template-columns: 1fr;
            }

            .register-left {
                padding: 30px;
            }

            .register-right {
                padding: 30px;
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .register-header h2 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body class="register-page">

    <div class="register-shell">

        <div class="register-left">
            <div>
                <div class="brand">
                    <h1>Pinjem Mobil</h1>
                    <p>
                        Buat akun customer untuk mulai melakukan peminjaman mobil
                        dengan proses yang lebih mudah, rapi, dan sesuai cabang pilihanmu.
                    </p>
                </div>

                <div class="benefit-box">
                    <div class="benefit-item">
                        <h4>Pilih cabang dari awal</h4>
                        <p>Setelah memilih cabang, kamu hanya bisa melakukan peminjaman pada cabang tersebut.</p>
                    </div>

                    <div class="benefit-item">
                        <h4>Proses rental lebih terarah</h4>
                        <p>Mobil yang tampil nanti akan otomatis menyesuaikan dengan cabang yang kamu pilih.</p>
                    </div>

                    <div class="benefit-item">
                        <h4>Akun langsung siap dipakai</h4>
                        <p>Setelah register berhasil, kamu bisa langsung login sebagai customer.</p>
                    </div>
                </div>
            </div>

            <div class="left-footer">
                Sudah punya akun? Login setelah registrasi berhasil.
            </div>
        </div>

        <div class="register-right">
            <div class="register-header">
                <h2>Daftar Akun</h2>
                <p>Buat akun customer untuk mulai melakukan peminjaman mobil.</p>
            </div>

            <?php if (isset($error)) { ?>
                <div class="alert-error">
                    <?= $error; ?>
                </div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data" class="register-form">
                <div class="section-title">Informasi Akun & Identitas</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_member" class="form-control" placeholder="Masukkan nama lengkap"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh@gmail.com" required>
                        <div class="input-note">Wajib menggunakan alamat email @gmail.com</div>
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="nomor_telepon" class="form-control" placeholder="081234567890"
                            maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Upload KTP</label>
                        <input type="file" name="ktp" class="form-control" accept="image/*,.pdf" required>
                        <div class="input-note">
                            Upload foto KTP atau file PDF.
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Upload SIM</label>
                        <input type="file" name="sim" class="form-control" accept="image/*,.pdf" required>
                        <div class="input-note">
                            Upload foto SIM atau file PDF.
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pilih Cabang</label>
                        <select name="id_cabang" class="form-control" required>
                            <option value="">Pilih cabang</option>
                            <?php
                            $cabang_query_ulang = sqlsrv_query($koneksi, "SELECT id_cabang, nama_cabang, alamat FROM cabang ORDER BY nama_cabang ASC");
                            while ($cabang = sqlsrv_fetch_array($cabang_query_ulang, SQLSRV_FETCH_ASSOC)) {
                                ?>
                                <option value="<?= $cabang['id_cabang']; ?>">
                                    <?= $cabang['nama_cabang']; ?> - <?= $cabang['alamat']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="contoh: misa01"
                            maxlength="8" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9]/g, '')"
                            required>
                        <div class="input-note">
                            Hanya huruf kecil dan angka, tanpa simbol, maksimal 8 karakter.
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Buat password"
                            required>
                        <div class="input-note">
                            Minimal 8 karakter, wajib mengandung 1 huruf besar, 1 angka, dan 1 simbol.
                        </div>
                    </div>

                    <div class="form-group full">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" placeholder="Masukkan alamat lengkap"
                            required></textarea>
                    </div>

                    <div class="form-group full">
                        <div class="agreement-box">
                            <label>
                                <input type="checkbox" name="setuju_cabang" value="1">
                                <span>
                                    Saya menyetujui bahwa akun ini terdaftar pada cabang yang saya pilih, dan saya hanya
                                    dapat melakukan peminjaman mobil pada cabang tersebut.
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" name="register" class="btn-primary">Daftar Sekarang</button>
                    <a href="login.php" class="btn-secondary">Kembali ke Login</a>
                </div>

                <div class="bottom-login">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>