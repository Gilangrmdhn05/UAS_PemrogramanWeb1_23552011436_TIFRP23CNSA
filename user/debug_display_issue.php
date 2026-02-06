<?php
session_start();
include "../config/database.php";

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    die("User tidak login");
}

echo "<style>
    body { font-family: Arial; margin: 20px; }
    .section { 
        border: 2px solid #ddd; 
        padding: 15px; 
        margin: 15px 0; 
        border-radius: 5px;
    }
    .success { border-color: green; background-color: #f0fff0; }
    .error { border-color: red; background-color: #fff0f0; }
    .warning { border-color: orange; background-color: #fff9f0; }
    pre { background-color: #f4f4f4; padding: 10px; overflow-x: auto; }
    code { background-color: #f0f0f0; padding: 2px 5px; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background-color: #f0f0f0; }
</style>";

echo "<h1>🔍 Detailed Order Display Debug</h1>";

echo "<div class='section warning'>";
echo "<h3>Current User: ID " . htmlspecialchars($id_user) . "</h3>";
echo "</div>";

// ===== QUERY 1: Main pesanan query =====
echo "<div class='section'>";
echo "<h3>1️⃣ Main Query: SELECT * FROM pesanan</h3>";

$query1 = "SELECT * FROM pesanan WHERE id_user='$id_user' AND id_pesanan > 0 ORDER BY id_pesanan DESC";
echo "<code>" . htmlspecialchars($query1) . "</code>";

$result1 = mysqli_query($conn, $query1);

if (!$result1) {
    echo "<div class='error'><strong>❌ QUERY ERROR:</strong> " . mysqli_error($conn) . "</div>";
} else {
    $count = mysqli_num_rows($result1);
    echo "<div class='success'><strong>✅ Found " . $count . " pesanan</strong></div>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr>
            <th>ID</th>
            <th>Total</th>
            <th>Status</th>
            <th>is_hidden</th>
            <th>tanggal</th>
            <th>Detail Query Result</th>
        </tr>";
        
        while ($pesanan = mysqli_fetch_assoc($result1)) {
            echo "<tr>";
            echo "<td>" . $pesanan['id_pesanan'] . "</td>";
            echo "<td>Rp " . number_format($pesanan['total_bayar'] ?? 0, 0, ',', '.') . "</td>";
            echo "<td>" . $pesanan['status_pesanan'] . "</td>";
            echo "<td>" . ($pesanan['is_hidden'] ?? 'NULL') . "</td>";
            echo "<td>" . $pesanan['tanggal'] . "</td>";
            
            // Query detail untuk pesanan ini
            echo "<td>";
            
            $id_pesanan = $pesanan['id_pesanan'];
            $detail_query = "SELECT pd.id_produk, pd.qty, p.nama_produk FROM pesanan_detail pd 
                            LEFT JOIN produk p ON pd.id_produk = p.id_produk 
                            WHERE pd.id_pesanan='$id_pesanan' AND pd.id_produk > 0";
            
            $detail_result = mysqli_query($conn, $detail_query);
            
            if (!$detail_result) {
                echo "<span style='color: red;'>❌ Query Error</span>";
            } else {
                $detail_count = mysqli_num_rows($detail_result);
                echo "<span style='color: green;'>✅ " . $detail_count . " produk</span>";
                
                if ($detail_count > 0) {
                    echo "<ul>";
                    while ($detail = mysqli_fetch_assoc($detail_result)) {
                        echo "<li>" . htmlspecialchars($detail['nama_produk'] ?? 'No product name') . "</li>";
                    }
                    echo "</ul>";
                }
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "</div>";

// ===== QUERY 2: Dengan is_hidden filter =====
echo "<div class='section'>";
echo "<h3>2️⃣ Query WITH is_hidden Filter</h3>";

$query2 = "SELECT * FROM pesanan WHERE id_user='$id_user' AND id_pesanan > 0 AND (is_hidden IS NULL OR is_hidden=0) ORDER BY id_pesanan DESC";
echo "<code>" . htmlspecialchars($query2) . "</code>";

$result2 = mysqli_query($conn, $query2);

if (!$result2) {
    echo "<div class='error'><strong>❌ QUERY ERROR:</strong> " . mysqli_error($conn) . "</div>";
} else {
    $count2 = mysqli_num_rows($result2);
    echo "<div class='warning'><strong>⚠️ Found " . $count2 . " pesanan</strong></div>";
    
    if ($count2 == 0) {
        echo "<p style='color: red;'><strong>PROBLEM FOUND!</strong> Filter <code>is_hidden</code> is hiding all orders!</p>";
        echo "<p>Let's check the is_hidden values:</p>";
        
        $check_hidden = "SELECT id_pesanan, is_hidden FROM pesanan WHERE id_user='$id_user' AND id_pesanan > 0";
        $check_result = mysqli_query($conn, $check_hidden);
        
        echo "<table>";
        echo "<tr><th>ID Pesanan</th><th>is_hidden Value</th></tr>";
        while ($row = mysqli_fetch_assoc($check_result)) {
            echo "<tr>";
            echo "<td>" . $row['id_pesanan'] . "</td>";
            echo "<td><strong>" . ($row['is_hidden'] ?? 'NULL') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "</div>";

// ===== QUERY 3: Check column information =====
echo "<div class='section'>";
echo "<h3>3️⃣ Check is_hidden Column Info</h3>";

$col_info = "DESC pesanan";
$col_result = mysqli_query($conn, $col_info);

echo "<table>";
echo "<tr><th>Field</th><th>Type</th><th>Default</th></tr>";
while ($col = mysqli_fetch_assoc($col_result)) {
    if ($col['Field'] == 'is_hidden') {
        echo "<tr style='background-color: yellow;'>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "</div>";

// ===== RECOMMENDATION =====
echo "<div class='section warning'>";
echo "<h3>📋 Rekomendasi Fix</h3>";

echo "<p><strong>Option 1: Update query di pesanan.php</strong></p>";
echo "<p>Ganti:</p>";
echo "<pre>WHERE id_user='$id_user' AND id_pesanan > 0 AND (is_hidden IS NULL OR is_hidden=0)</pre>";
echo "<p>Menjadi:</p>";
echo "<pre>WHERE id_user='$id_user' AND id_pesanan > 0</pre>";

echo "<p><strong>Option 2: Set semua is_hidden menjadi 0 atau NULL</strong></p>";
echo "<pre>UPDATE pesanan SET is_hidden=0 WHERE is_hidden IS NULL;</pre>";

echo "</div>";

mysqli_close($conn);
?>
