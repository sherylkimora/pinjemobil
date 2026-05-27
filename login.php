<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $params = [$username, $password];

    $query = sqlsrv_query($koneksi, $sql, $params);

    if ($query === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

    if ($user) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id_member'] = $user['id_member'];
        $_SESSION['id_pegawai'] = $user['id_pegawai'];

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else if ($user['role'] == 'pegawai') {
            header("Location: pegawai/dashboard.php");
        } else if ($user['role'] == 'customer') {
            header("Location: customer/dashboard.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Pinjem Mobil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page">

    <div class="login-wrapper">
        <div class="login-brand">
            <img src="assets/images/logo.png" class="login-logo" alt="Logo Pinjem Mobil">
            <h1>Pinjem Mobil</h1>
            <p>Minjem Dulu, Balikin Nanti!</p>
        </div>

        <div class="login-card">
            <h2>Login</h2>
            <!-- <p class="login-subtitle">Masuk ke akun kamu untuk melanjutkan.</p> -->

            <?php if (isset($error)) { ?>
                <div class="alert-error"><?= $error; ?></div>
            <?php } ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password"
                        required>
                </div>

                <button type="submit" name="login" class="btn btn-full">Login</button>

                <div class="register-footer">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
            </form>

            <!-- <div class="login-help">
                <p><b>Admin:</b> admin / 123</p>
                <p><b>Pegawai:</b> pegawai / 123</p>
                <p><b>Customer:</b> customer / 123</p>
            </div> -->
        </div>
    </div>

</body>

</html>