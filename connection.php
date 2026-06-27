<?php
$servername = "localhost";
$username = "root";
$pass = "";
$dbname = "sirakelilka";

$conn = new mysqli ("localhost", "root", "", "sirakelilka");

if ($conn->connect_error){
    die("Koneksi gagal: " . $conn->connect_error);

}

?>