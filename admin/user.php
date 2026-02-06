<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* === HAPUS USER === */
if (isset($_POST['hapus'])) {
    $id_user = $_POST['id_user'];

    // Ambil role user
    $cek = mysqli_query($conn, "SELECT role FROM users WHERE id_user='$id_user'");
    $u = mysqli_fetch_assoc($cek);

    if ($u['role'] == 'admin') {
        echo "<script>alert('User admin tidak bisa dihapus!');</script>";
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id_user='$id_user'");
        header("Location: user.php");
        exit;
    }
}


/* Ambil data user */
$data = mysqli_query($conn,"SELECT * FROM users");
?>

<div class="container-fluid">
<h4 class="mb-3"><i class="bi bi-people me-2"></i>Data User</h4>

<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>No</th>
<th>Nama</th>
<th>Email</th>
<th>No HP</th>
<th>Alamat</th>
<th>Role</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php $no=1; while($u=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $u['nama'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['no_hp'] ?></td>
<td><?= $u['alamat'] ?></td>
<td>
<span class="badge <?= $u['role']=='admin'?'bg-danger':'bg-secondary' ?>">
<?= $u['role'] ?>
</span>
</td>
<td>
<?php if($u['role'] != 'admin'){ ?>
    <!-- Tombol Edit -->
    <a href="edit_user.php?id=<?= $u['id_user'] ?>" class="btn btn-warning btn-sm">Edit</a>

    <!-- Tombol Hapus -->
    <form method="POST" style="display:inline-block;" 
          onsubmit="return confirm('Yakin ingin hapus user ini?');">
        <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
        <button type="submit" name="hapus" class="btn btn-danger btn-sm">Hapus</button>
    </form>
<?php } else { ?>
    <span class="text-muted">-</span>
<?php } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

</div> <!-- /container-fluid -->

<?php include "layout/footer.php"; ?>
