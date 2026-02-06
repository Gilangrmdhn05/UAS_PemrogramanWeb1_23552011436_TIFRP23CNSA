<?php
session_start();
include "../config/database.php";

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    die("User tidak login");
}

echo "<style>
    body { font-family: Arial; margin: 20px; }
    .box { 
        border: 2px solid #ddd; 
        padding: 15px; 
        margin: 10px 0; 
        border-radius: 5px;
    }
    .success { border-color: green; background-color: #f0fff0; }
    .error { border-color: red; background-color: #fff0f0; }
    .warning { border-color: orange; background-color: #fff9f0; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background-color: #f0f0f0; }
</style>";

echo "<h1>🔍 Full Order Status Check</h1>";

echo "<div class='box warning'>";
echo "<h3>Current User: ID " . $id_user . "</h3>";
echo "</div>";

// Query semua pesanan user
$query = "SELECT * FROM pesanan WHERE id_user = '$id_user' ORDER BY id_pesanan DESC";
$result = mysqli_query($conn, $query);

$count = mysqli_num_rows($result);

if ($count > 0) {
    echo "<div class='box success'>";
    echo "<h3>✅ DITEMUKAN " . $count . " Pesanan</h3>";
    echo "</div>";
    
    echo "<table>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Total</th>";
    echo "<th>Status</th>";
    echo "<th>Tanggal</th>";
    echo "<th>is_hidden</th>";
    echo "<th>Detail</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_pesanan'] . "</td>";
        echo "<td>Rp " . number_format($row['total_bayar'] ?? 0, 0, ',', '.') . "</td>";
        echo "<td>" . $row['status_pesanan'] . "</td>";
        echo "<td>" . $row['tanggal'] . "</td>";
        echo "<td>" . ($row['is_hidden'] ?? 'NULL') . "</td>";
        echo "<td>";
        
        // Cek detail
        $detail_query = "SELECT COUNT(*) as cnt FROM pesanan_detail WHERE id_pesanan = " . $row['id_pesanan'];
        $detail_result = mysqli_query($conn, $detail_query);
        $detail_row = mysqli_fetch_assoc($detail_result);
        echo $detail_row['cnt'] . " produk";
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='box error'>";
    echo "<h3>❌ TIDAK ADA PESANAN</h3>";
    echo "<p>User ini belum membuat pesanan apapun.</p>";
    echo "</div>";
    
    // Cek apakah user lain punya pesanan
    echo "<div class='box warning'>";
    echo "<h3>Pengecekan Lanjutan</h3>";
    
    $check_other = "SELECT DISTINCT id_user FROM pesanan LIMIT 5";
    $check_result = mysqli_query($conn, $check_other);
    
    echo "<p>User dengan pesanan: ";
    $users = [];
    while ($row = mysqli_fetch_assoc($check_result)) {
        $users[] = $row['id_user'];
    }
    echo implode(", ", $users) ?: "Tidak ada pesanan di database";
    echo "</p>";
    
    echo "</div>";
}

mysqli_close($conn);
?>
