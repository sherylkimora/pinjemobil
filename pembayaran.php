<!DOCTYPE html>
<html>
<head>

<title>Pembayaran</title>

<style>

body{
    background:#f2f2f2;
    font-family:Arial;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    width:350px;
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

input,select{
    width:100%;
    padding:12px;
    margin-top:10px;
    margin-bottom:15px;
    border-radius:10px;
    border:1px solid #ccc;
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#1677ff;
    color:white;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#005ce6;
}

</style>

</head>
<body>

<div class="card">

<h2>Pembayaran Rental</h2>

<form action="trans.php" method="POST">

<label>Tanggal Pinjam</label>
<input type="date" name="tgl_pinjam">

<label>Tanggal Kembali</label>
<input type="date" name="tgl_kembali">

<label>Metode Pembayaran</label>

<input type="text"
value="QRIS"
readonly>

<button type="submit">
    Pay Now
</button>

</form>

</div>

</body>
</html>