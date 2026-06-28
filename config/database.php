<?php
// =====================================================
// config/database.php
// Koneksi database SIRAKELIKA
// =====================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti jika bukan root
define('DB_PASS', '');           // Ganti jika ada password
define('DB_NAME', 'sirakelika');

// Buat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset agar karakter Indonesia tampil benar
$conn->set_charset("utf8mb4");

// Fungsi helper — query aman dari SQL Injection
function db_query($conn, $sql, $params = [], $types = '') {
    if (empty($params)) {
        return $conn->query($sql);
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

