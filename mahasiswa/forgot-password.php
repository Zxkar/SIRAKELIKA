<?php
include '../config/conn.php';

if(isset($_POST['reset'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $cek = mysqli_query($conn,
    "SELECT * FROM mahasiswa WHERE email='$email'");

    if(mysqli_num_rows($cek) > 0){

        mysqli_query($conn,
        "UPDATE mahasiswa
        SET password='$hash'
        WHERE email='$email'");

        echo "<script>
        alert('Password berhasil diubah');
        window.location='login.php';
        </script>";

    } else {

        echo "<script>alert('Email tidak ditemukan');</script>";

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password</title>

<style>

body{
    font-family:Arial;
    background:#0f172a;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box{
    background:white;
    padding:30px;
    border-radius:15px;
    width:350px;
}

h2{
    margin-bottom:20px;
    text-align:center;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:10px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#2563eb;
    color:white;
    border-radius:10px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

a{
    display:block;
    text-align:center;
    margin-top:15px;
    text-decoration:none;
}

</style>
</head>

<body>

<div class="box">

    <h2>Reset Password</h2>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Masukkan Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password Baru"
            required
        >

        <button type="submit" name="reset">
            Simpan Password Baru
        </button>

    </form>

    <a href="login.php">
        Kembali ke Login
    </a>

</div>

</body>
</html>