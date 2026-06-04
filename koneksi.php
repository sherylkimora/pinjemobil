<?php
$serverName = "localhost\\SQLEXPRESS01";

$connectionOptions = [
    "Database" => "pinjemobil",
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];

$koneksi = sqlsrv_connect($serverName, $connectionOptions);

if (!$koneksi) {
    die(print_r(sqlsrv_errors(), true));
}
?>