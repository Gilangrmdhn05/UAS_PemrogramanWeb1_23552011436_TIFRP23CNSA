<?php
// File ini untuk debugging proses order
// Panggil dari halaman payment SEBELUM di-redirect ke proses_order

session_start();
include "config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Silakan login terlebih dahulu");
}

echo "<h2>Verifikasi Data Sebelum Order Diproses</h2>";

echo "<h3>1. Session Data</h3>";
echo "<ul>";
echo "<li><strong>ID User:</strong> " . ($_SESSION['id_user'] ?? 'TIDAK ADA') . "</li>";
echo "<li><strong>Role:</strong> " . ($_SESSION['role'] ?? 'TIDAK ADA') . "</li>";
echo "</ul>";

echo "<h3>2. Keranjang</h3>";
if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
    echo "<p>Jumlah produk: " . count($_SESSION['keranjang']) . "</p>";
    echo "<ul>";
    foreach ($_SESSION['keranjang'] as $key => $item) {
        echo "<li>" . $item['nama_produk'] . " x " . $item['qty'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Keranjang kosong atau tidak ada</p>";
}

echo "<h3>3. Shipping Details</h3>";
if (isset($_SESSION['shipping_details'])) {
    echo "<ul>";
    echo "<li><strong>Nama:</strong> " . $_SESSION['shipping_details']['nama_penerima'] . "</li>";
    echo "<li><strong>Alamat:</strong> " . $_SESSION['shipping_details']['alamat_pengiriman'] . "</li>";
    echo "<li><strong>Biaya Pengiriman:</strong> Rp " . number_format($_SESSION['shipping_details']['biaya_pengiriman'], 0, ',', '.') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Shipping details tidak ada</p>";
}

echo "<h3>4. Order Totals</h3>";
if (isset($_SESSION['order_totals'])) {
    echo "<ul>";
    echo "<li><strong>Total Produk:</strong> Rp " . number_format($_SESSION['order_totals']['total_produk'], 0, ',', '.') . "</li>";
    echo "<li><strong>Total Bayar:</strong> Rp " . number_format($_SESSION['order_totals']['total_bayar'], 0, ',', '.') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Order totals tidak ada</p>";
}

echo "<h3>5. Simulasi Proses Order</h3>";
echo "<p>Jika Anda melanjutkan order dengan data di atas, pesanan akan dibuat dengan:</p>";

$id_user = $_SESSION['id_user'] ?? null;
if ($id_user) {
    echo "<ul>";
    foreach ($_SESSION['keranjang'] ?? [] as $item) {
        echo "<li>Pesanan untuk produk ID " . $item['id_produk'] . " akan dibuat dengan id_user=" . $id_user . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ MASALAH: id_user tidak ada! Pesanan tidak akan tersimpan dengan benar.</p>";
}

mysqli_close($conn);
?>
