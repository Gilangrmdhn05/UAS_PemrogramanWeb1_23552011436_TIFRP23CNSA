<?php
session_start();
include "config/database.php";

echo "<h1>Database Pesanan Analysis</h1>";

// Tampilkan current user
echo "<h2>Current User (dari session)</h2>";
if (isset($_SESSION['id_user'])) {
    echo "<p><strong>ID User:</strong> " . $_SESSION['id_user'] . "</p>";
    echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";
} else {
    echo "<p style='color: red;'>Tidak ada user yang login</p>";
}

// Tampilkan semua user dan pesanan mereka
echo "<h2>Semua User dan Pesanan Mereka</h2>";

$users_query = "SELECT DISTINCT u.id_user, u.nama, u.email, COUNT(p.id_pesanan) as jumlah_pesanan
                FROM users u
                LEFT JOIN pesanan p ON u.id_user = p.id_user
                GROUP BY u.id_user";
$users_result = mysqli_query($conn, $users_query);

echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>ID User</th><th>Nama</th><th>Email</th><th>Jumlah Pesanan</th></tr>";

while($user = mysqli_fetch_assoc($users_result)) {
    echo "<tr>";
    echo "<td>" . $user['id_user'] . "</td>";
    echo "<td>" . htmlspecialchars($user['nama']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . $user['jumlah_pesanan'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Detail pesanan per user
echo "<h2>Detail Pesanan Per User</h2>";

$pesanan_query = "SELECT p.id_pesanan, p.id_user, p.total_bayar, p.status_pesanan, p.tanggal
                  FROM pesanan p
                  ORDER BY p.id_user DESC, p.id_pesanan DESC";
$pesanan_result = mysqli_query($conn, $pesanan_query);

echo "<table border='1' cellpadding='10' style='width: 100%;'>";
echo "<tr><th>ID Pesanan</th><th>ID User</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>";

while($pesanan = mysqli_fetch_assoc($pesanan_result)) {
    $bg = (isset($_SESSION['id_user']) && $pesanan['id_user'] == $_SESSION['id_user']) ? 'style="background-color: yellow;"' : '';
    echo "<tr $bg>";
    echo "<td>" . $pesanan['id_pesanan'] . "</td>";
    echo "<td>" . $pesanan['id_user'] . "</td>";
    echo "<td>Rp " . number_format($pesanan['total_bayar'], 0, ',', '.') . "</td>";
    echo "<td>" . $pesanan['status_pesanan'] . "</td>";
    echo "<td>" . $pesanan['tanggal'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// SQL untuk melihat struktur tabel pesanan
echo "<h2>Struktur Tabel Pesanan</h2>";
$structure = "DESC pesanan";
$struct_result = mysqli_query($conn, $structure);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while($field = mysqli_fetch_assoc($struct_result)) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

mysqli_close($conn);
?>
