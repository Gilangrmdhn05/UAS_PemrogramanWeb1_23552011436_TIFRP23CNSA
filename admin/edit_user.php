<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

$id_user = $_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id_user='$id_user'"));

if(!$user){
    echo "<script>alert('User tidak ditemukan'); window.location='user.php';</script>";
    exit;
}

/* UPDATE USER */
if(isset($_POST['simpan'])){
    $nama = $_POST['nama'];
    $email = $_POST['email'];

    mysqli_query($conn,"UPDATE users SET nama='$nama', email='$email' WHERE id_user='$id_user'");
    header("Location: user.php");
    exit;
}
?>

<h4 class="mb-3">✏️ Edit User</h4>

<div class="card shadow">
<div class="card-body">
<form method="POST">
<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
</div>
<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
</div>
<div class="mb-3">
<label>No HP</label>
<input type="text" name="no_hp" class="form-control" value="<?= $user['no_hp'] ?>" required>
</div>
<div class="mb-3">
<label>Alamat</label>
<input type="text" name="alamat" class="form-control" value="<?= $user['alamat'] ?>" required>
</div>
<button type="submit" name="simpan" class="btn btn-success">Simpan</button>
<a href="user.php" class="btn btn-secondary">Batal</a>
</form>
</div>
</div>

<?php include "layout/footer.php"; ?>
