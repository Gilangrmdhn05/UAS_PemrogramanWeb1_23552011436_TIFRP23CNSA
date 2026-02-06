<?php
include "../config/database.php";

/* Function untuk mendapatkan daftar produk dalam pesanan */
function getProdukPesanan($id_pesanan, $conn) {
    $produk_list = '';
    $query = mysqli_query($conn,"
    SELECT pd.harga, pd.qty, p.nama_produk
    FROM pesanan_detail pd
    LEFT JOIN produk p ON pd.id_produk = p.id_produk
    WHERE pd.id_pesanan = '$id_pesanan'
    ");
    while($item = mysqli_fetch_assoc($query)){
        $nama = $item['nama_produk'] ?? '[Produk Tidak Tersedia]';
        $produk_list .= $nama . ' (x' . $item['qty'] . '), ';
    }
    return rtrim($produk_list, ', ');
}

/* Ambil data pesanan */
$pesanan = mysqli_query($conn,"
    SELECT pesanan.*, users.nama
    FROM pesanan
    JOIN users ON pesanan.id_user = users.id_user
    ORDER BY id_pesanan DESC
");

/* Header agar browser mendeteksi file Excel */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_pesanan.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* Tulis header tabel */
echo "No\tUser\tProduk\tSubtotal Produk\tBiaya Pengiriman\tTotal Bayar\tMetode Pembayaran\tStatus\tTanggal\n";

$no = 1;
while($p = mysqli_fetch_assoc($pesanan)){
    // Format nilai
    $total_produk = "Rp " . number_format($p['total_produk'] ?? 0, 0, ",", ".");
    $biaya_pengiriman = "Rp " . number_format($p['biaya_pengiriman'] ?? 0, 0, ",", ".");
    $total_bayar = "Rp " . number_format($p['total_bayar'] ?? 0, 0, ",", ".");
    $metode = $p['metode_pembayaran'] ?? '-';
    $status = $p['status_pesanan'] ?? 'menunggu_pembayaran';
    $tanggal = date('d-m-Y H:i', strtotime($p['tanggal']));

    // Cetak baris
    echo $no++ . "\t";
    echo $p['nama'] . "\t";
    echo getProdukPesanan($p['id_pesanan'], $conn) . "\t";
    echo $total_produk . "\t";
    echo $biaya_pengiriman . "\t";
    echo $total_bayar . "\t";
    echo $metode . "\t";
    echo str_replace('_', ' ', ucfirst($status)) . "\t";
    echo $tanggal . "\n";
}
