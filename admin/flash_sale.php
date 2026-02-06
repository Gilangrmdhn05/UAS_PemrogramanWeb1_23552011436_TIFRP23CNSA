<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

// Add Flash Sale
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_flash'])){
    $id_produk = $_POST['id_produk'];
    $harga_diskon = $_POST['harga_diskon'];
    $harga_normal = $_POST['harga_normal'];
    $diskon_persen = round((($harga_normal - $harga_diskon) / $harga_normal) * 100);
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    
    $q = mysqli_query($conn,"INSERT INTO flash_sale(id_produk,harga_diskon,diskon_persen,waktu_mulai,waktu_selesai,status)
    VALUES('$id_produk','$harga_diskon','$diskon_persen','$waktu_mulai','$waktu_selesai','nonaktif')");
    
    if($q) {
        $_SESSION['success_msg'] = 'Flash Sale Ditambahkan';
        header("Location: flash_sale.php");
        exit;
    }
}

// Update Status Flash Sale
if(isset($_GET['update_status'])){
    $id_flash = $_GET['update_status'];
    $status = $_GET['status'];
    
    $q = mysqli_query($conn,"UPDATE flash_sale SET status='$status' WHERE id_flash='$id_flash'");
    
    if($q) {
        $_SESSION['success_msg'] = 'Status Diperbarui';
        header("Location: flash_sale.php");
        exit;
    }
}

// Delete Flash Sale
if(isset($_GET['delete'])){
    $id_flash = $_GET['delete'];
    
    $q = mysqli_query($conn,"DELETE FROM flash_sale WHERE id_flash='$id_flash'");
    
    if($q) {
        $_SESSION['success_msg'] = 'Flash Sale Dihapus';
        header("Location: flash_sale.php");
        exit;
    }
}

// Update status 'selesai' secara otomatis
$current_time = date('Y-m-d H:i:s');
mysqli_query($conn, "UPDATE flash_sale SET status='selesai' WHERE status='aktif' AND waktu_selesai < '$current_time'");

// Get Flash Sales
$flash_sales = mysqli_query($conn,"
    SELECT fs.*, p.nama_produk, p.harga
    FROM flash_sale fs
    JOIN produk p ON fs.id_produk = p.id_produk
    ORDER BY fs.created_at DESC
");
?>

<div class="container-fluid">
<h4 class="mb-3">⚡ Data Flash Sale</h4>

<!-- FORM TAMBAH -->
<div class="card shadow mb-4">
<div class="card-body">
<form method="POST" class="row g-2">
    <div class="col-md-3">
        <select name="id_produk" class="form-select" required onchange="updateHarga(this)">
            <option value="">-- Pilih Produk --</option>
            <?php 
            $produk_list = mysqli_query($conn,"SELECT id_produk, nama_produk, harga FROM produk ORDER BY nama_produk");
            while($p = mysqli_fetch_assoc($produk_list)){
            ?>
                <option value="<?= $p['id_produk']; ?>" data-harga="<?= $p['harga']; ?>">
                    <?= $p['nama_produk']; ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-2">
        <input type="number" id="harga_normal" name="harga_normal" class="form-control" placeholder="Harga Normal" readonly>
    </div>
    <div class="col-md-2">
        <input type="number" name="harga_diskon" class="form-control" placeholder="Harga Diskon" required>
    </div>
    <div class="col-md-2">
        <input type="datetime-local" name="waktu_mulai" class="form-control" required>
    </div>
    <div class="col-md-2">
        <input type="datetime-local" name="waktu_selesai" class="form-control" required>
    </div>
    <div class="col-md-1">
        <button name="add_flash" class="btn btn-success w-100">+ Tambah</button>
    </div>
</form>
</div>
</div>

<!-- TABEL FLASH SALE -->
<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead class="table-dark">
<tr>
<th>No</th>
<th>Produk</th>
<th>Harga Normal</th>
<th>Harga Diskon</th>
<th>Diskon</th>
<th>Mulai</th>
<th>Selesai</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php $no=1; while($fs=mysqli_fetch_assoc($flash_sales)){ 
    $waktu_sekarang = strtotime(date('Y-m-d H:i:s'));
    $waktu_mulai = strtotime($fs['waktu_mulai']);
    $waktu_selesai = strtotime($fs['waktu_selesai']);
    
    if($fs['status'] == 'aktif') {
        if($waktu_sekarang > $waktu_selesai) {
            $display_status = 'selesai';
        } else {
            $display_status = 'aktif';
        }
    } else {
        $display_status = $fs['status'];
    }
?>
<tr>
<td><?= $no++ ?></td>
<td><?= $fs['nama_produk'] ?></td>
<td>Rp <?= number_format($fs['harga']) ?></td>
<td><span style="color:#198754;font-weight:600">Rp <?= number_format($fs['harga_diskon']) ?></span></td>
<td><span class="badge bg-danger"><?= $fs['diskon_persen'] ?>%</span></td>
<td><small><?= date('d/m/Y H:i', strtotime($fs['waktu_mulai'])) ?></small></td>
<td><small><?= date('d/m/Y H:i', strtotime($fs['waktu_selesai'])) ?></small></td>
<td>
<span class="badge 
<?= $display_status=='aktif'?'bg-success':($display_status=='nonaktif'?'bg-warning':'bg-info') ?>">
<?= ucfirst($display_status) ?>
</span>
</td>
<td>
<?php if($fs['status']=='nonaktif'){ ?>
    <a href="?update_status=<?= $fs['id_flash'] ?>&status=aktif" class="btn btn-success btn-sm" onclick="return confirm('Aktifkan?')">Aktifkan</a>
<?php } elseif($fs['status']=='aktif'){ ?>
    <a href="?update_status=<?= $fs['id_flash'] ?>&status=nonaktif" class="btn btn-warning btn-sm" onclick="return confirm('Nonaktifkan?')">Nonaktifkan</a>
<?php } ?>
<a href="?delete=<?= $fs['id_flash'] ?>" onclick="return confirm('Hapus flash sale ini?')" class="btn btn-danger btn-sm">Hapus</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<script>
function updateHarga(select) {
    const harga = select.options[select.selectedIndex].getAttribute('data-harga');
    document.getElementById('harga_normal').value = harga || '';
}
</script>

</div> <!-- /container-fluid -->

<?php include "layout/footer.php"; ?>
