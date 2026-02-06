<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* =======================
   TAMBAH PRODUK + GAMBAR
======================= */
if(isset($_POST['simpan'])){
    $nama_file = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];

    if($nama_file!=""){
        if(!is_dir("../assets/images/produk")){
            mkdir("../assets/images/produk", 0777, true);
        }
        move_uploaded_file($tmp,"../assets/images/produk/".$nama_file);
    }else{
        $nama_file = "default.jpg";
    }

    $sql = "INSERT INTO produk 
    (id_kategori,nama_produk,harga,stok,deskripsi,gambar)
    VALUES(
        '$_POST[id_kategori]',
        '$_POST[nama]',
        '$_POST[harga]',
        '$_POST[stok]',
        '$_POST[deskripsi]',
        '$nama_file'
    )";

    mysqli_query($conn,$sql) or die(mysqli_error($conn));
    header("Location:produk.php");
}

/* =======================
   UPDATE / EDIT PRODUK
======================= */
if(isset($_POST['update'])){
    if(!empty($_FILES['gambar']['name'])){
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        move_uploaded_file($tmp, "../assets/images/produk/".$gambar);

        mysqli_query($conn,"UPDATE produk SET
            nama_produk='$_POST[nama]',
            harga='$_POST[harga]',
            stok='$_POST[stok]',
            deskripsi='$_POST[deskripsi]',
            id_kategori='$_POST[id_kategori]',
            gambar='$gambar'
            WHERE id_produk='$_POST[id]'
        ") or die(mysqli_error($conn));

    }else{
        mysqli_query($conn,"UPDATE produk SET
            nama_produk='$_POST[nama]',
            harga='$_POST[harga]',
            stok='$_POST[stok]',
            deskripsi='$_POST[deskripsi]',
            id_kategori='$_POST[id_kategori]'
            WHERE id_produk='$_POST[id]'
        ") or die(mysqli_error($conn));
    }
    header("Location:produk.php");
}

/* =======================
   HAPUS PRODUK + GAMBAR
======================= */
if(isset($_GET['hapus'])){
    $q = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT gambar FROM produk WHERE id_produk='$_GET[hapus]'"
    ));
    if($q['gambar']!='default.jpg' && file_exists("../assets/images/produk/".$q['gambar'])){
        unlink("../assets/images/produk/".$q['gambar']);
    }
    mysqli_query($conn,"DELETE FROM produk WHERE id_produk='$_GET[hapus]'");
    header("Location:produk.php");
}

/* =======================
   DATA
======================= */
$kategori = mysqli_query($conn,"SELECT * FROM kategori");
$data = mysqli_query($conn,"
    SELECT produk.*, kategori.nama_kategori
    FROM produk JOIN kategori ON produk.id_kategori=kategori.id_kategori
");
?>

<div class="container-fluid">
<h4 class="mb-3"><i class="bi bi-box-seam me-2"></i>Data Produk</h4>

<!-- FORM TAMBAH -->
<div class="card shadow mb-4">
<div class="card-body">
<form method="POST" enctype="multipart/form-data" class="row g-2">
    <div class="col-md-3">
        <input name="nama" class="form-control" placeholder="Nama Produk" required>
    </div>
    <div class="col-md-2">
        <input name="harga" class="form-control" placeholder="Harga" required>
    </div>
    <div class="col-md-2">
        <input name="stok" class="form-control" placeholder="Stok" required>
    </div>
    <div class="col-md-3">
        <select name="id_kategori" class="form-select">
        <?php while($k=mysqli_fetch_assoc($kategori)){ ?>
            <option value="<?= $k['id_kategori'] ?>">
                <?= $k['nama_kategori'] ?>
            </option>
        <?php } ?>
        </select>
    </div>
    <div class="col-md-2">
        <input type="file" name="gambar" class="form-control">
    </div>
    <div class="col-md-12 mt-2">
        <textarea name="deskripsi" class="form-control" placeholder="Deskripsi Produk" rows="2"></textarea>
    </div>
    <div class="col-md-12 mt-2">
        <button name="simpan" class="btn btn-success w-100">+ Tambah Produk</button>
    </div>
</form>
</div>
</div>

<!-- TABEL PRODUK -->
<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead class="table-dark">
<tr>
<th>No</th>
<th>Gambar</th>
<th>Produk</th>
<th>Kategori</th>
<th>Harga</th>
<th>Stok</th>
<th>Deskripsi</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php $no=1; while($p=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++ ?></td>
<td>
<img src="../assets/images/produk/<?= $p['gambar'] ?>" width="60" class="rounded">
</td>
<td><?= htmlspecialchars($p['nama_produk']) ?></td>
<td><?= htmlspecialchars($p['nama_kategori']) ?></td>
<td>Rp <?= number_format($p['harga']) ?></td>
<td><?= $p['stok'] ?></td>
<td><small><?= htmlspecialchars(substr($p['deskripsi'], 0, 50) . (strlen($p['deskripsi']) > 50 ? '...' : '')) ?></small></td>
<td>
<div class="d-flex gap-2">
<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $p['id_produk'] ?>">Edit</button>

<a href="?hapus=<?= $p['id_produk'] ?>" onclick="return confirm('Hapus produk ini?')" class="btn btn-danger btn-sm">Hapus</a>
</div>
</td>
</tr>

<!-- MODAL EDIT -->
<div class="modal fade" id="edit<?= $p['id_produk'] ?>">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST" enctype="multipart/form-data">
<div class="modal-header">
<h5 class="modal-title">Edit Produk</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id" value="<?= $p['id_produk'] ?>">

<div class="mb-2">
<label>Nama Produk</label>
<input name="nama" class="form-control" value="<?= $p['nama_produk'] ?>" required>
</div>

<div class="mb-2">
<label>Harga</label>
<input name="harga" class="form-control" value="<?= $p['harga'] ?>" required>
</div>

<div class="mb-2">
<label>Stok</label>
<input name="stok" class="form-control" value="<?= $p['stok'] ?>" required>
</div>

<div class="mb-2">
<label>Kategori</label>
<select name="id_kategori" class="form-select">
<?php
$kat2 = mysqli_query($conn,"SELECT * FROM kategori");
while($k=mysqli_fetch_assoc($kat2)){
$sel = $k['id_kategori']==$p['id_kategori']?'selected':'';
echo "<option value='$k[id_kategori]' $sel>$k[nama_kategori]</option>";
}
?>
</select>
</div>

<div class="mb-2">
<label>Deskripsi</label>
<textarea name="deskripsi" class="form-control" rows="3"><?= $p['deskripsi'] ?></textarea>
</div>

<div class="mb-2">
<label>Gambar Produk</label><br>
<img src="../assets/images/produk/<?= $p['gambar'] ?>" width="80" class="mb-2">
<input type="file" name="gambar" class="form-control">
<small class="text-muted">Kosongkan jika tidak diganti</small>
</div>
</div>

<div class="modal-footer">
    <button type="submit" name="update" class="btn btn-success">Update</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
</div>

</form>

</div>
</div>
</div>
<?php } ?>
</table>
</div>
</div>

</div> <!-- /container-fluid -->

<?php include "layout/footer.php"; ?>
