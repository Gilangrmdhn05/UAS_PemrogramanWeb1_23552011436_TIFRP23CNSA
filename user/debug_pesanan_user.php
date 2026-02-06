<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['id_user'])) {
    die("Silakan login terlebih dahulu");
}

$id_user = $_SESSION['id_user'];

echo "<h2>Debug Pesanan User</h2>";
echo "<p><strong>ID User (dari session):</strong> " . htmlspecialchars($id_user) . "</p>";
echo "<p><strong>Tipe Data:</strong> " . gettype($id_user) . "</p>";

echo "<hr>";

// Test 1: Query tanpa filter id_user
echo "<h3>Test 1: Semua Pesanan (tanpa filter user)</h3>";
$query1 = "SELECT id_pesanan, id_user, total_bayar, status_pesanan, tanggal FROM pesanan ORDER BY id_pesanan DESC LIMIT 10";
$result1 = mysqli_query($conn, $query1);

if ($result1 && mysqli_num_rows($result1) > 0) {
    echo "<table border='1' cellpadding='10' style='width: 100%;'>";
    echo "<tr><th>ID Pesanan</th><th>ID User</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>";
    while($row = mysqli_fetch_assoc($result1)) {
        $bg = ($row['id_user'] == $id_user) ? 'style="background-color: yellow;"' : '';
        echo "<tr $bg>";
        echo "<td>" . $row['id_pesanan'] . "</td>";
        echo "<td>" . $row['id_user'] . "</td>";
        echo "<td>Rp " . number_format($row['total_bayar'], 0, ',', '.') . "</td>";
        echo "<td>" . $row['status_pesanan'] . "</td>";
        echo "<td>" . $row['tanggal'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada pesanan di database";
}

echo "<hr>";

// Test 2: Query dengan filter id_user
echo "<h3>Test 2: Pesanan User '" . $id_user . "'</h3>";
$query2 = "SELECT * FROM pesanan WHERE id_user = '$id_user' ORDER BY id_pesanan DESC";
echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($query2) . "</code></p>";

$result2 = mysqli_query($conn, $query2);

if (!$result2) {
    echo "<p style='color: red;'><strong>❌ ERROR:</strong> " . mysqli_error($conn) . "</p>";
} elseif (mysqli_num_rows($result2) == 0) {
    echo "<p style='color: orange;'><strong>⚠️ TIDAK ADA PESANAN</strong> ditemukan untuk user ini</p>";
    
    // Test 3: Cek apakah ada pesanan untuk user lain
    echo "<hr>";
    echo "<h3>Test 3: Cek User Mana Saja yang Punya Pesanan</h3>";
    $query3 = "SELECT DISTINCT id_user FROM pesanan";
    $result3 = mysqli_query($conn, $query3);
    
    if ($result3 && mysqli_num_rows($result3) > 0) {
        echo "<p>User yang memiliki pesanan: ";
        $users = [];
        while($row = mysqli_fetch_assoc($result3)) {
            $users[] = $row['id_user'];
        }
        echo implode(", ", $users);
        echo "</p>";
    }
    
} else {
    echo "<p style='color: green;'><strong>✅ DITEMUKAN</strong> " . mysqli_num_rows($result2) . " pesanan</p>";
    
    while($row = mysqli_fetch_assoc($result2)) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Pesanan #" . $row['id_pesanan'] . "</strong> - " . $row['status_pesanan'] . "<br>";
        echo "Total: Rp " . number_format($row['total_bayar'], 0, ',', '.') . "<br>";
        echo "Tanggal: " . $row['tanggal'] . "<br>";
        
        // Cek detail pesanan
        $detail_query = "SELECT * FROM pesanan_detail WHERE id_pesanan = " . $row['id_pesanan'];
        $detail_result = mysqli_query($conn, $detail_query);
        echo "Detail: " . mysqli_num_rows($detail_result) . " produk<br>";
        echo "</div>";
    }
}

mysqli_close($conn);
?>
