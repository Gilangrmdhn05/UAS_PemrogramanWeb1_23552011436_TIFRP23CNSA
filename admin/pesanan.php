<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* === LOGIC UPDATE STATUS === */
if(isset($_POST['update_status'])){
    $id_pesanan = $_POST['id_pesanan'];
    $status_baru = $_POST['status'];

    mysqli_query($conn,"UPDATE pesanan SET status_pesanan='$status_baru' WHERE id_pesanan='$id_pesanan'");
    header("Location: pesanan.php"); // reload halaman agar update terlihat
    exit;
}

/* === LOGIC UPDATE STATUS PEMBAYARAN === */
if(isset($_POST['update_pembayaran'])){
    $id_pesanan = mysqli_real_escape_string($conn, $_POST['id_pesanan']);
    $status_bayar = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);

    $update = mysqli_query($conn,"UPDATE pesanan SET status_pembayaran='$status_bayar' WHERE id_pesanan='$id_pesanan'");
    
    if($update){
        $msg = "Status pembayaran berhasil diperbarui.";
        
        // LOGIKA OTOMATIS: Update status pesanan berdasarkan pembayaran
        if($status_bayar == 'lunas'){
            // Hanya ubah ke 'diproses' jika status saat ini bukan dikirim/selesai
            // Kita gunakan query langsung agar lebih cepat
            $q_auto = "UPDATE pesanan SET status_pesanan='diproses' WHERE id_pesanan='$id_pesanan' AND status_pesanan NOT IN ('dikirim', 'selesai')";
            if(mysqli_query($conn, $q_auto)){
                $msg .= " Status pesanan otomatis diubah menjadi Diproses.";
            }
        }
        elseif($status_bayar == 'gagal'){
            $q_auto = "UPDATE pesanan SET status_pesanan='dibatalkan' WHERE id_pesanan='$id_pesanan' AND status_pesanan NOT IN ('dikirim', 'selesai')";
            if(mysqli_query($conn, $q_auto)){
                $msg .= " Status pesanan otomatis Dibatalkan.";
            }
        }
        header("Location: pesanan.php?msg=" . urlencode($msg));
    } else {
        header("Location: pesanan.php?error=Gagal update: " . mysqli_error($conn));
    }
    exit;
}

/* === HAPUS DATA PESANAN (HARD DELETE) === */
if(isset($_GET['hapus'])){
    $id_pesanan = $_GET['hapus'];
    
    // Hapus detail pesanan dulu
    mysqli_query($conn, "DELETE FROM pesanan_detail WHERE id_pesanan='$id_pesanan'");
    
    // Hapus pesanan
    mysqli_query($conn, "DELETE FROM pesanan WHERE id_pesanan='$id_pesanan'");
    
    header("Location: pesanan.php?msg=Pesanan berhasil dihapus");
    exit;
}

/* === DATA PESANAN === */
$where = "";
if(isset($_GET['start_date']) && isset($_GET['end_date']) && $_GET['start_date'] != '' && $_GET['end_date'] != ''){
    $start = mysqli_real_escape_string($conn, $_GET['start_date']);
    $end = mysqli_real_escape_string($conn, $_GET['end_date']);
    $where = "WHERE DATE(pesanan.tanggal) BETWEEN '$start' AND '$end'";
}

$query_str = "
SELECT pesanan.*, users.nama
FROM pesanan JOIN users ON pesanan.id_user=users.id_user
$where
ORDER BY id_pesanan DESC
";
$data = mysqli_query($conn, $query_str);

// Function untuk mendapatkan daftar produk dalam pesanan
function getProdukPesanan($id_pesanan, $conn) {
    $produk_list = [];
    $query = mysqli_query($conn,"
    SELECT 
        pd.id_detail,
        pd.harga, 
        pd.qty, 
        pd.id_produk,
        pd.nama_produk as snapshot_nama,
        pd.gambar as snapshot_gambar,
        p.nama_produk, 
        p.gambar
    FROM pesanan_detail pd
    LEFT JOIN produk p ON pd.id_produk = p.id_produk
    WHERE pd.id_pesanan = '" . mysqli_real_escape_string($conn, $id_pesanan) . "'
    ");
    
    if ($query) {
        while($item = mysqli_fetch_assoc($query)){
            $produk_list[] = $item;
        }
    }
    return $produk_list;
}
?>

<div class="container-fluid">
<h4 class="mb-3">🧾 Data Pesanan</h4>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    .order-row-details {
        display: none;
        background: #f8f9fa;
    }

    .order-row-details.show {
        display: table-row;
    }

    .product-item-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .product-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.75rem;
        flex: 0 0 calc(50% - 0.375rem);
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .product-card img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    .product-info {
        flex: 1;
        min-width: 0;
    }

    .product-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        word-break: break-word;
    }

    .product-qty-price {
        font-size: 0.85rem;
        color: #7f8c8d;
    }

    @media (max-width: 768px) {
        .product-card {
            flex: 0 0 100%;
        }
    }
</style>

<!-- Filter Tanggal -->
<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" name="start_date" class="form-control" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>" required>
    </div>
    <div class="col-auto">
        <input type="date" name="end_date" class="form-control" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>" required>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter Tanggal</button>
        <a href="pesanan.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-hover mb-0">
<thead class="table-dark">
<tr>
<th style="width: 50px;">No</th>
<th>User</th>
<th>Produk</th>
<th style="width: 120px;">Total</th>
<th style="width: 160px;">Pembayaran</th>
<th style="width: 140px;">Status</th>
<th style="width: 150px;">Tanggal</th>
<th style="width: 200px;">Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; while($p=mysqli_fetch_assoc($data)){ 
    $produk_list = getProdukPesanan($p['id_pesanan'], $conn);
    
    // Fallbacks for updated column names
    $total = $p['total_bayar'] ?? $p['total_produk'] ?? $p['total'] ?? 0;
    $status_pesanan = $p['status_pesanan'] ?? $p['status'] ?? 'pending';
    $status_pembayaran = $p['status_pembayaran'] ?? 'menunggu';
    $is_hidden = $p['is_hidden'] ?? 0;
?>
<tr>
<td><?= $no ?></td>
<td><?= htmlspecialchars($p['nama']) ?></td>
<td>
    <div class="product-item-grid">
    <?php foreach($produk_list as $prod): 
        // Cek nama produk
        $prod_name = !empty($prod['snapshot_nama']) ? $prod['snapshot_nama'] : ($prod['nama_produk'] ?? 'Produk Tidak Tersedia');
        
        // Cek gambar (prioritas: gambar, kemudian foto)
        $file_gambar = !empty($prod['snapshot_gambar']) ? $prod['snapshot_gambar'] : ($prod['gambar'] ?? $prod['foto'] ?? '');
        $img_src = "https://via.placeholder.com/60/cccccc/999999?text=No+Image";
        
        if (!empty($file_gambar)) {
            $gambar_path = "../assets/images/produk/" . $file_gambar;
            if (file_exists($gambar_path)) {
                $img_src = $gambar_path;
            }
        }
    ?>
        <div class="product-card">
            <img src="<?= htmlspecialchars($img_src) ?>" alt="Product" title="<?= htmlspecialchars($prod_name) ?>" onerror="this.src='https://via.placeholder.com/60/cccccc/999999?text=No+Image'">
            <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($prod_name) ?></div>
                <div class="product-qty-price">
                    <?= $prod['qty'] ?>x @ Rp <?= number_format($prod['harga'], 0, ',', '.') ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</td>
<td class="text-end fw-bold text-success">
    Rp <?= number_format($total, 0, ',', '.') ?>
</td>
<td>
    <span class="badge mb-2 
    <?= $status_pembayaran=='lunas'?'bg-success':
      ($status_pembayaran=='gagal'?'bg-danger':'bg-warning text-dark') ?>">
    <?= ucfirst($status_pembayaran) ?>
    </span>
    
    <form method="POST">
        <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan'] ?>">
        <div class="input-group input-group-sm">
            <select name="status_pembayaran" class="form-select">
                <option value="menunggu" <?= ($status_pembayaran=='menunggu')?'selected':'' ?>>Menunggu</option>
                <option value="lunas" <?= ($status_pembayaran=='lunas')?'selected':'' ?>>Lunas</option>
                <option value="gagal" <?= ($status_pembayaran=='gagal')?'selected':'' ?>>Gagal</option>
            </select>
            <button type="submit" name="update_pembayaran" class="btn btn-primary"><i class="bi bi-check"></i></button>
        </div>
    </form>
</td>
<td>
    <span class="badge 
    <?= $status_pesanan=='selesai'?'bg-success':
      ($status_pesanan=='diproses'?'bg-primary':
      ($status_pesanan=='dikirim'?'bg-info':
      ($status_pesanan=='dibatalkan'?'bg-danger':
      ($status_pesanan=='menunggu_pembayaran'?'bg-warning text-dark':'bg-secondary')))) ?>">
    <?= str_replace('_', ' ', ucfirst($status_pesanan)) ?>
    </span>
    <?php if($is_hidden == 1): ?>
        <span class="badge bg-secondary" title="Disembunyikan oleh user"><i class="bi bi-eye-slash-fill"></i></span>
    <?php endif; ?>
</td>
<td><?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?></td>

<td>
    <div class="d-flex gap-2" style="flex-wrap: wrap;">
        <form method="POST" class="flex-grow-1" style="min-width: 150px;">
            <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan'] ?>">
            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                <select name="status" class="form-select form-select-sm" style="flex: 1; min-width: 120px;">
                    <option value="menunggu_pembayaran" <?= ($status_pesanan=='menunggu_pembayaran')?'selected':'' ?>>Menunggu Pembayaran</option>
                    <option value="diproses" <?= ($status_pesanan=='diproses')?'selected':'' ?>>Diproses</option>
                    <option value="dikirim" <?= ($status_pesanan=='dikirim')?'selected':'' ?>>Dikirim</option>
                    <option value="selesai" <?= ($status_pesanan=='selesai')?'selected':'' ?>>Selesai</option>
                    <option value="dibatalkan" <?= ($status_pesanan=='dibatalkan')?'selected':'' ?>>Dibatalkan</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-success btn-sm" style="white-space: nowrap;">Update</button>
            </div>
        </form>
        <a href="?hapus=<?= $p['id_pesanan'] ?>" onclick="return confirm('Yakin ingin menghapus pesanan ini secara permanen? Data tidak bisa dipulihkan.');" class="btn btn-danger btn-sm">Hapus</a>
    </div>
</td>
</tr>
<?php $no++; } ?>
</tbody>
</table>
</div>
</div>
</div>

</div>

<?php include "layout/footer.php"; ?>
