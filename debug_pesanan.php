<?php
include "config/database.php";

echo "<h2>Debug Pesanan & Produk</h2>";

// Cek data di pesanan_detail
echo "<h3>Data Pesanan Detail</h3>";
$query = "SELECT * FROM pesanan_detail LIMIT 10";
$result = mysqli_query($conn, $query);
echo "<pre>";
while($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}
echo "</pre>";

// Cek data di produk
echo "<h3>Data Produk (ID 0, 18, 38)</h3>";
$query2 = "SELECT id_produk, nama_produk, gambar FROM produk WHERE id_produk IN (0, 18, 38)";
$result2 = mysqli_query($conn, $query2);
echo "<pre>";
while($row = mysqli_fetch_assoc($result2)) {
    print_r($row);
}
echo "</pre>";

// Cek JOIN result untuk pesanan ID 0
echo "<h3>Test JOIN (untuk id_pesanan=0)</h3>";
$query3 = "SELECT pd.*, p.nama_produk, p.gambar FROM pesanan_detail pd LEFT JOIN produk p ON pd.id_produk = p.id_produk WHERE pd.id_pesanan=0";
$result3 = mysqli_query($conn, $query3);
echo "<pre>";
while($row = mysqli_fetch_assoc($result3)) {
    print_r($row);
}
echo "</pre>";

// Cek semua pesanan user
echo "<h3>Semua Pesanan User</h3>";
$query4 = "SELECT * FROM pesanan";
$result4 = mysqli_query($conn, $query4);
echo "<pre>";
while($row = mysqli_fetch_assoc($result4)) {
    print_r($row);
}
echo "</pre>";

mysqli_close($conn);
?>
