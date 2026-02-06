<?php
// Konfigurasi koneksi database
$host     = 'localhost';
$db_name  = 'warungku'; // Nama database Anda
$username = 'root';
$password = ''; // Kosongkan jika menggunakan XAMPP default

// Membuat koneksi menggunakan mysqli
$conn = mysqli_connect($host, $username, $password, $db_name);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>