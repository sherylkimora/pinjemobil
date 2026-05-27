<?php
if (function_exists('sqlsrv_connect')) {
    echo "Driver sqlsrv aktif!";
} else {
    echo "Driver sqlsrv BELUM aktif.";
}
?>