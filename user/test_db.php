<?php
include "../config/database.php";

echo "<h2>Test Database Connection</h2>";

// Test 1: Check produk table
echo "<h3>Tabel Produk:</h3>";
$produk_result = mysqli_query($conn, "SELECT id_produk, nama_produk, gambar FROM produk");
if ($produk_result) {
    echo "Jumlah produk: " . mysqli_num_rows($produk_result) . "<br>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nama</th><th>Gambar</th></tr>";
    while($row = mysqli_fetch_assoc($produk_result)) {
        echo "<tr><td>" . $row['id_produk'] . "</td><td>" . $row['nama_produk'] . "</td><td>" . $row['gambar'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($conn);
}

// Test 2: Check pesanan table
echo "<h3>Tabel Pesanan:</h3>";
$pesanan_result = mysqli_query($conn, "SELECT id_pesanan, id_user FROM pesanan LIMIT 3");
if ($pesanan_result) {
    echo "Jumlah pesanan: " . mysqli_num_rows($pesanan_result) . "<br>";
    while($row = mysqli_fetch_assoc($pesanan_result)) {
        echo "ID Pesanan: " . $row['id_pesanan'] . ", ID User: " . $row['id_user'] . "<br>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

// Test 3: Check pesanan_detail with JOIN
echo "<h3>Test Query JOIN:</h3>";
$detail_result = mysqli_query($conn, "
    SELECT pesanan_detail.*, produk.nama_produk, produk.gambar
    FROM pesanan_detail
    LEFT JOIN produk ON pesanan_detail.id_produk = produk.id_produk
    LIMIT 5
");

if ($detail_result) {
    echo "Jumlah detail: " . mysqli_num_rows($detail_result) . "<br>";
    echo "<table border='1'>";
    echo "<tr><th>ID Detail</th><th>ID Pesanan</th><th>ID Produk</th><th>Nama Produk</th><th>Gambar</th><th>Harga</th><th>Qty</th></tr>";
    while($row = mysqli_fetch_assoc($detail_result)) {
        echo "<tr>";
        echo "<td>" . $row['id_detail'] . "</td>";
        echo "<td>" . $row['id_pesanan'] . "</td>";
        echo "<td>" . $row['id_produk'] . "</td>";
        echo "<td>" . $row['nama_produk'] . "</td>";
        echo "<td>" . $row['gambar'] . "</td>";
        echo "<td>" . $row['harga'] . "</td>";
        echo "<td>" . $row['qty'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
