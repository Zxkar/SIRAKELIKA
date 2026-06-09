<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard SIRAKELIKA</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f1f5f9;
}

/* TOPBAR */

.topbar{
    width:100%;
    background:#1e3a8a;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-size:22px;
    font-weight:bold;
}

.user{
    display:flex;
    align-items:center;
    gap:10px;
}

.avatar{
    width:40px;
    height:40px;
    border-radius:50%;
    background:white;
    color:#1e3a8a;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
}

/* CONTENT */

.container{
    padding:30px;
}

.welcome{
    background:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:25px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.welcome h2{
    margin-bottom:10px;
    color:#1e293b;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-bottom:25px;
}

.card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.card h3{
    font-size:30px;
    color:#1e3a8a;
    margin-bottom:10px;
}

.card p{
    color:#475569;
}

/* TABLE */

.table-box{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

table th,
table td{
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:left;
}

table th{
    background:#f8fafc;
}

.status{
    padding:5px 12px;
    border-radius:20px;
    color:white;
    font-size:13px;
}

.red{
    background:red;
}

.orange{
    background:orange;
}

.green{
    background:green;
}

/* BUTTON */

.btn{
    display:inline-block;
    margin-top:20px;
    background:#dc2626;
    color:white;
    padding:10px 20px;
    text-decoration:none;
    border-radius:10px;
}

.btn:hover{
    background:#b91c1c;
}

</style>

</head>
<body>

<div class="topbar">

    <div class="logo">
        🛡️ SIRAKELIKA
    </div>

    <div class="user">

        <div class="avatar">
            <?php echo strtoupper(substr($username,0,1)); ?>
        </div>

        <div>
            <b><?php echo $username; ?></b>
        </div>

    </div>

</div>

<div class="container">

    <div class="welcome">

        <h2>Selamat Datang, <?php echo $username; ?> 👋</h2>

        <p>
            Sistem Pelaporan Kekerasan Kampus
        </p>

        <p>
            Email: <?php echo $email; ?>
        </p>

    </div>

    <!-- CARD -->

    <div class="cards">

        <div class="card">
            <h3>142</h3>
            <p>Total Laporan</p>
        </div>

        <div class="card">
            <h3>7</h3>
            <p>Laporan Baru</p>
        </div>

        <div class="card">
            <h3>24</h3>
            <p>Diproses</p>
        </div>

        <div class="card">
            <h3>111</h3>
            <p>Selesai</p>
        </div>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <h2>Laporan Terbaru</h2>

        <table>

            <tr>
                <th>ID</th>
                <th>Jenis</th>
                <th>Lokasi</th>
                <th>Status</th>
            </tr>

            <tr>
                <td>#KS-001</td>
                <td>Kekerasan Verbal</td>
                <td>Gedung A</td>
                <td>
                    <span class="status red">Baru</span>
                </td>
            </tr>

            <tr>
                <td>#KS-002</td>
                <td>Perundungan</td>
                <td>Kantin</td>
                <td>
                    <span class="status orange">Diproses</span>
                </td>
            </tr>

            <tr>
                <td>#KS-003</td>
                <td>Kekerasan Fisik</td>
                <td>Parkiran</td>
                <td>
                    <span class="status green">Selesai</span>
                </td>
            </tr>

        </table>

        <a href="logout.php" class="btn">
            Logout
        </a>

    </div>

</div>

</body>
</html>