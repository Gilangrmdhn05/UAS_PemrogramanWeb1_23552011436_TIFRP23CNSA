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

$pesanan = mysqli_query($conn,"
    SELECT pesanan.*, users.nama
    FROM pesanan
    JOIN users ON pesanan.id_user = users.id_user
    ORDER BY id_pesanan DESC
");

/* Mulai buffer */
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Pesanan</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border:1px solid #000; padding:5px; text-align:left; }
th { background-color:#ccc; }
</style>
</head>
<body>
<h2>Laporan Pesanan Warungku</h2>
<p style="text-align:right; margin-top:-15px;">Tanggal Cetak: <?= date('d-m-Y H:i') ?></p>
<table>
<tr>
<th>No</th>
<th>User</th>
<th>Produk</th>
<th>Subtotal</th>
<th>Ongkir</th>
<th>Total Bayar</th>
<th>Metode Pembayaran</th>
<th>Status Pesanan</th>
<th>Tanggal Pesanan</th>
</tr>
<?php $no=1; while($p=mysqli_fetch_assoc($pesanan)){ 
    $status_pesanan = $p['status_pesanan'] ?? 'menunggu_pembayaran';
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($p['nama']) ?></td>
<td><?= getProdukPesanan($p['id_pesanan'], $conn) ?></td>
<td style="text-align:right;">Rp <?= number_format($p['total_produk'] ?? 0) ?></td>
<td style="text-align:right;">Rp <?= number_format($p['biaya_pengiriman'] ?? 0) ?></td>
<td style="text-align:right;"><strong>Rp <?= number_format($p['total_bayar'] ?? 0) ?></strong></td>
<td><?= htmlspecialchars($p['metode_pembayaran'] ?? '-') ?></td>
<td><?= str_replace('_', ' ', ucfirst($status_pesanan)) ?></td>
<td><?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?></td>
</tr>
<?php } ?>
</table>

<script>
window.print(); // langsung tampil dialog print
</script>
</body>
</html>
