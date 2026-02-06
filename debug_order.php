<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['id_user'])) {
    die("Silakan login terlebih dahulu");
}

$id_user = $_SESSION['id_user'];

echo "<h2>Debug Pesanan - User ID: " . $id_user . "</h2>";

// Cek semua pesanan user
echo "<h3>1. Semua Pesanan User</h3>";
$query = "SELECT * FROM pesanan WHERE id_user = '$id_user' ORDER BY id_pesanan DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_pesanan'] . "</td>";
        echo "<td>Rp " . number_format($row['total_bayar'], 0, ',', '.') . "</td>";
        echo "<td>" . $row['status_pesanan'] . "</td>";
        echo "<td>" . $row['tanggal'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Tidak ada pesanan ditemukan untuk user ini</p>";
}

// Cek detail pesanan terbaru
echo "<h3>2. Detail Pesanan Terbaru</h3>";
$query2 = "SELECT pd.*, p.nama_produk FROM pesanan_detail pd 
           LEFT JOIN produk p ON pd.id_produk = p.id_produk 
           WHERE pd.id_pesanan = (SELECT MAX(id_pesanan) FROM pesanan WHERE id_user = '$id_user')";
$result2 = mysqli_query($conn, $query2);

if ($result2 && mysqli_num_rows($result2) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Produk</th><th>Qty</th><th>Harga</th></tr>";
    while($row = mysqli_fetch_assoc($result2)) {
        echo "<tr>";
        echo "<td>" . ($row['nama_produk'] ?? 'Produk Tidak Tersedia') . "</td>";
        echo "<td>" . $row['qty'] . "</td>";
        echo "<td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Tidak ada detail pesanan</p>";
}

// Cek session final_order
echo "<h3>3. Session Final Order</h3>";
if (isset($_SESSION['final_order'])) {
    echo "<pre>";
    print_r($_SESSION['final_order']);
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠️ Session final_order tidak ditemukan</p>";
}

// Cek error log
echo "<h3>4. Last Insert ID</h3>";
$check = mysqli_query($conn, "SELECT MAX(id_pesanan) as last_id FROM pesanan");
$last = mysqli_fetch_assoc($check);
echo "<p>Last Pesanan ID: " . $last['last_id'] . "</p>";

mysqli_close($conn);
?>
