<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* =======================
   TAMBAH KATEGORI
======================= */
if(isset($_POST['simpan'])){
    mysqli_query($conn,"INSERT INTO kategori VALUES(null,'$_POST[nama]')");
    header("Location:kategori.php");
}

/* =======================
   UPDATE KATEGORI
======================= */
if(isset($_POST['update'])){
    mysqli_query($conn,"UPDATE kategori SET
        nama_kategori='$_POST[nama]'
        WHERE id_kategori='$_POST[id]'
    ");
    header("Location:kategori.php");
}

/* =======================
   HAPUS KATEGORI
======================= */
if(isset($_GET['hapus'])){
    mysqli_query($conn,"DELETE FROM kategori WHERE id_kategori='$_GET[hapus]'");
    header("Location:kategori.php");
}

/* =======================
   DATA KATEGORI
======================= */
$data = mysqli_query($conn,"SELECT * FROM kategori");
?>

<div class="container-fluid">
<h4 class="mb-3"><i class="bi bi-tags me-2"></i>Data Kategori</h4>

<!-- FORM TAMBAH -->
<div class="card shadow mb-4">
<div class="card-body">
<form method="POST" class="d-flex gap-2">
    <input name="nama" class="form-control" placeholder="Nama Kategori" required>
    <button name="simpan" class="btn btn-success">+ Tambah</button>
</form>
</div>
</div>

<!-- TABEL KATEGORI -->
<div class="card shadow">
<div class="card-body">
<table class="table table-bordered table-hover">
<tr class="table-dark">
    <th width="60">No</th>
    <th>Nama Kategori</th>
    <th width="160">Aksi</th>
</tr>

<?php $no=1; while($k=mysqli_fetch_assoc($data)){ ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $k['nama_kategori'] ?></td>
    <td>
        <button class="btn btn-warning btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#edit<?= $k['id_kategori'] ?>">
        Edit
        </button>

        <a href="?hapus=<?= $k['id_kategori'] ?>"
        onclick="return confirm('Yakin hapus kategori ini?')"
        class="btn btn-danger btn-sm">
        Hapus
        </a>
    </td>
</tr>

<!-- MODAL EDIT -->
<div class="modal fade" id="edit<?= $k['id_kategori'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form method="POST">
<div class="modal-header">
<h5 class="modal-title">Edit Kategori</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id" value="<?= $k['id_kategori'] ?>">

<div class="mb-2">
<label>Nama Kategori</label>
<input name="nama" class="form-control" value="<?= $k['nama_kategori'] ?>" required>
</div>
</div>

<div class="modal-footer">
<button name="update" class="btn btn-success">Update</button>
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
