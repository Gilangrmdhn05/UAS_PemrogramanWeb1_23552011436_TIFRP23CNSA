<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* Function untuk mendapatkan daftar produk dalam pesanan */
function getProdukPesanan($id_pesanan, $conn) {
    $produk_list = [];
    $query = mysqli_query($conn,"
    SELECT pd.harga, pd.qty, p.nama_produk
    FROM pesanan_detail pd
    LEFT JOIN produk p ON pd.id_produk = p.id_produk
    WHERE pd.id_pesanan = '$id_pesanan'
    ");
    while($item = mysqli_fetch_assoc($query)){
        $nama = $item['nama_produk'] ?? '[Produk Tidak Tersedia]';
        $produk_list[] = [
            'nama_produk' => $nama,
            'harga' => $item['harga'],
            'qty' => $item['qty']
        ];
    }
    return $produk_list;
}

/* Ambil data pesanan */
$pesanan = mysqli_query($conn,"
    SELECT pesanan.*, users.nama
    FROM pesanan
    JOIN users ON pesanan.id_user = users.id_user
    ORDER BY pesanan.tanggal DESC
");
?>

<div class="container-fluid">
<h4 class="mb-3"><i class="bi bi-bar-chart me-2"></i>Laporan Pesanan</h4>

<div class="mb-3">
    <a href="laporan_pdf.php" class="btn btn-danger">Cetak PDF</a>
    <a href="laporan_excel.php" class="btn btn-success">Export Excel</a>
</div>

<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-dark">
<tr>
<th>No</th>
<th>User</th>
<th>Produk</th>
<th style="width:130px;">Total Bayar</th>
<th>Metode</th>
<th>Status</th>
<th>Tanggal</th>
</tr>
</thead>
<tbody>

<?php $no=1; while($p=mysqli_fetch_assoc($pesanan)){ 
    $produk_list = getProdukPesanan($p['id_pesanan'], $conn);
    $produk_text = '';
    foreach($produk_list as $prod){
        $produk_text .= $prod['nama_produk'] . ' (x' . $prod['qty'] . ')<br>';
    }
    $status_pesanan = $p['status_pesanan'] ?? 'menunggu_pembayaran';
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($p['nama']) ?></td>
<td><small><?= $produk_text ?></small></td>
<td style="text-align:right;"><strong>Rp <?= number_format($p['total_bayar'] ?? 0, 0, ',', '.') ?></strong></td>
<td><small><?= htmlspecialchars($p['metode_pembayaran'] ?? '-') ?></small></td>
<td>
    <span class="badge 
    <?= $status_pesanan=='selesai'?'bg-success':
      ($status_pesanan=='diproses'?'bg-primary':
      ($status_pesanan=='dikirim'?'bg-info':
      ($status_pesanan=='dibatalkan'?'bg-danger':
      ($status_pesanan=='menunggu_pembayaran'?'bg-warning text-dark':'bg-secondary')))) ?>">
    <?= str_replace('_', ' ', ucfirst($status_pesanan)) ?>
    </span>
</td>
<td><?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

</div> <!-- /container-fluid -->

<?php include "layout/footer.php"; ?>
