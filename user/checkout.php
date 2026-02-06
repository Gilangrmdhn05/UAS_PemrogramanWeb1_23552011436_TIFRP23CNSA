<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

if (empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

// Ambil data user untuk pre-fill form
$id_user = $_SESSION['id_user'];
// User confirmed no_hp, alamat, and kota exist.
$user_query = mysqli_query($conn, "SELECT nama, email, no_hp, alamat, kota FROM users WHERE id_user = '$id_user'");
$user_data = mysqli_fetch_assoc($user_query);

// Provide fallback for new/unconfirmed columns
$user_data['kode_pos'] = $user_data['kode_pos'] ?? '';
$user_data['no_hp'] = $user_data['no_hp'] ?? '';
$user_data['alamat'] = $user_data['alamat'] ?? '';
$user_data['kota'] = $user_data['kota'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Alamat Pengiriman - Warungku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
        <div class="d-flex">
            <a class="nav-link text-white" href="keranjang.php"><i class="bi bi-cart-fill"></i> Keranjang</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-7">
            <h4><i class="bi bi-geo-alt-fill"></i> Alamat Pengiriman</h4>
            <hr>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="payment.php" method="POST">
                        <div class="mb-3">
                            <label for="nama_penerima" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" value="<?= htmlspecialchars($user_data['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_telepon_penerima" class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon_penerima" name="no_telepon_penerima" value="<?= htmlspecialchars($user_data['no_hp']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat_pengiriman" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required><?= htmlspecialchars($user_data['alamat']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kota" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="kota" name="kota" value="<?= htmlspecialchars($user_data['kota']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kode_pos" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?= htmlspecialchars($user_data['kode_pos']); ?>" required>
                            </div>
                        </div>
                        <!-- Shipping method simulation -->
                        <div class="mb-3">
                            <label class="form-label">Metode Pengiriman</label>
                            <select class="form-select" name="biaya_pengiriman" required>
                                <option value="15000" selected>Reguler (Rp 15.000)</option>
                                <option value="30000">Express (Rp 30.000)</option>
                            </select>
                        </div>
                        
                        <input type="submit" name="lanjut_pembayaran" class="d-none" id="submit-form-btn">
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <h4><i class="bi bi-bag-check-fill"></i> Ringkasan Belanja</h4>
            <hr>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php
                    $total_produk = 0;
                    foreach ($_SESSION['keranjang'] as $id => $item) {
                        $subtotal = $item['harga'] * $item['qty'];
                        $total_produk += $subtotal;
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?= htmlspecialchars($item['nama']); ?> (x<?= $item['qty']; ?>)</span>
                        <strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong>
                    </div>
                    <?php } ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal Produk</span>
                        <strong>Rp <?= number_format($total_produk, 0, ',', '.'); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Biaya Pengiriman</span>
                        <strong id="shipping-cost-display">Rp 15.000</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <strong>Total Belanja</strong>
                        <strong id="total-display" class="text-success">Rp <?= number_format($total_produk + 15000, 0, ',', '.'); ?></strong>
                    </div>
                </div>
                <div class="card-footer">
                    <button id="btn-lanjut-pembayaran" class="btn btn-success w-100 fw-bold">
                        Lanjutkan ke Pembayaran <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shippingSelect = document.querySelector('select[name="biaya_pengiriman"]');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const totalDisplay = document.getElementById('total-display');
    const totalProduk = <?= $total_produk; ?>;

    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    shippingSelect.addEventListener('change', function() {
        const shippingCost = parseInt(this.value);
        const totalBelanja = totalProduk + shippingCost;
        
        shippingCostDisplay.textContent = formatRupiah(shippingCost);
        totalDisplay.textContent = formatRupiah(totalBelanja);
    });

    document.getElementById('btn-lanjut-pembayaran').addEventListener('click', function() {
        // Trigger the real form submission
        document.getElementById('submit-form-btn').click();
    });
});
</script>

</body>
</html>
