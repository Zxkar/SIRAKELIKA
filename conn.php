<?php
$servername = "localhost";
$username = "root";
$pass = "";
$dbname = "sirakelika"; // Pastikan nama databasenya benar

$conn = new mysqli($servername, $username, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>