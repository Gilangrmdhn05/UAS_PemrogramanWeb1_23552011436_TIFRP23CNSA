<?php
session_start();
include "../config/database.php";

// Cek admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Silakan login sebagai admin untuk menjalankan perbaikan ini.");
}

include "layout/header.php";
include "layout/sidebar.php";
echo '<div class="container-fluid">';
echo '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Perbaikan Database</li></ol></nav>';
echo '<div class="card shadow"><div class="card-body">';
echo "<h3><i class='bi bi-tools me-2'></i> Perbaikan Database Pesanan</h3>";
echo "<p>Sedang memeriksa struktur database...</p>";

// 1. Tambah kolom nama_produk jika belum ada
$check = mysqli_query($conn, "SHOW COLUMNS FROM pesanan_detail LIKE 'nama_produk'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan_detail ADD COLUMN nama_produk VARCHAR(255) NULL AFTER id_produk");
    echo "<i class='bi bi-check2-circle text-success me-1'></i> Kolom 'nama_produk' berhasil ditambahkan.<br>";
} else {
    echo "<i class='bi bi-info-circle text-info me-1'></i> Kolom 'nama_produk' sudah ada.<br>";
}

// 2. Tambah kolom gambar jika belum ada
$check = mysqli_query($conn, "SHOW COLUMNS FROM pesanan_detail LIKE 'gambar'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan_detail ADD COLUMN gambar VARCHAR(255) NULL AFTER nama_produk");
    echo "<i class='bi bi-check2-circle text-success me-1'></i> Kolom 'gambar' berhasil ditambahkan.<br>";
} else {
    echo "<i class='bi bi-info-circle text-info me-1'></i> Kolom 'gambar' sudah ada.<br>";
}

// 3. Isi data kosong (Backfill) dari tabel produk
// Ini akan mengambil nama & gambar dari tabel produk untuk pesanan yang sudah ada
$query = "UPDATE pesanan_detail pd
          JOIN produk p ON pd.id_produk = p.id_produk
          SET pd.nama_produk = p.nama_produk, 
              pd.gambar = p.gambar
          WHERE pd.nama_produk IS NULL OR pd.nama_produk = ''";

if (mysqli_query($conn, $query)) {
    $affected = mysqli_affected_rows($conn);
    echo "<i class='bi bi-check2-circle text-success me-1'></i> Berhasil memperbarui <strong>$affected</strong> data riwayat pesanan (Backfill Data).<br>";
} else {
    echo "<i class='bi bi-x-circle text-danger me-1'></i> Gagal update data: " . mysqli_error($conn) . "<br>";
// 4. Tambah/Perbaiki kolom status_pembayaran di tabel pesanan
$check = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'status_pembayaran'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN status_pembayaran ENUM('menunggu','lunas','gagal') NOT NULL DEFAULT 'menunggu' AFTER status_pesanan");
    echo "<i class='bi bi-check2-circle text-success me-1'></i> Kolom 'status_pembayaran' berhasil ditambahkan ke tabel pesanan.<br>";
} else {
    // Pastikan enum values sesuai
    mysqli_query($conn, "ALTER TABLE pesanan MODIFY COLUMN status_pembayaran ENUM('menunggu','lunas','gagal') NOT NULL DEFAULT 'menunggu'");
    echo "<i class='bi bi-info-circle text-info me-1'></i> Struktur kolom 'status_pembayaran' divalidasi.<br>";
}

// 5. Pastikan kolom status_pesanan mendukung nilai 'diproses'
mysqli_query($conn, "ALTER TABLE pesanan MODIFY COLUMN status_pesanan ENUM('menunggu_pembayaran','pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran'");
echo "<i class='bi bi-check2-circle text-success me-1'></i> Struktur kolom 'status_pesanan' diperbarui (support 'diproses').<br>";

// 6. Tambah kolom is_hidden untuk soft delete oleh user
$check = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'is_hidden'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN is_hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER status_pembayaran");
    echo "<i class='bi bi-check2-circle text-success me-1'></i> Kolom 'is_hidden' untuk soft delete berhasil ditambahkan.<br>";
} else {
    echo "<i class='bi bi-info-circle text-info me-1'></i> Kolom 'is_hidden' sudah ada.<br>";
}

echo "<br><hr><a href='pesanan.php' style='background:green; color:white; padding:10px; text-decoration:none; border-radius:5px;'>Kembali ke Pesanan Admin</a>";
echo '</div></div></div>';
include "layout/footer.php";
?>