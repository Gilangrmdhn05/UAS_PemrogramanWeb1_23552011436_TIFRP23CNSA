<?php
session_start();
include "../config/database.php";

// Authenticate user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Redirect if cart is empty or address form was not submitted
if (empty($_SESSION['keranjang']) || !isset($_POST['lanjut_pembayaran'])) {
    header("Location: checkout.php");
    exit;
}

// Store shipping details in session to pass to the next step
$_SESSION['shipping_details'] = [
    'nama_penerima' => $_POST['nama_penerima'],
    'no_telepon_penerima' => $_POST['no_telepon_penerima'],
    'alamat_pengiriman' => $_POST['alamat_pengiriman'],
    'kota' => $_POST['kota'],
    'kode_pos' => $_POST['kode_pos'],
    'biaya_pengiriman' => (int)$_POST['biaya_pengiriman']
];

// Calculate totals
$total_produk = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_produk += $item['harga'] * $item['qty'];
}
$total_bayar = $total_produk + $_SESSION['shipping_details']['biaya_pengiriman'];

// Store totals in session
$_SESSION['order_totals'] = [
    'total_produk' => $total_produk,
    'biaya_pengiriman' => $_SESSION['shipping_details']['biaya_pengiriman'],
    'total_bayar' => $total_bayar
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Warungku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .payment-option {
            border: 1px solid #ddd;
            border-radius: .5rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .payment-option:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }
        .payment-option.selected {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
            background-color: #e9f2ff;
        }
        .payment-option img {
            max-height: 40px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-7">
            <h4><i class="bi bi-wallet2"></i> Pilih Metode Pembayaran</h4>
            <hr>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="proses_order.php" method="POST" id="payment-form">
                        <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="">
                        
                        <div class="d-grid gap-3">
                            <!-- Bank Transfer -->
                            <div class="payment-option" data-value="Transfer Bank (Virtual Account)">
                                <div class="d-flex align-items-center">
                                    <img src="https://assets.website-files.com/6203f7a892b45846546376c7/620409a8f65e8a715f20f04c_logo-bca.png" alt="BCA">
                                    <div>
                                        <h6 class="mb-0">Transfer Bank (Virtual Account)</h6>
                                        <small class="text-muted">Bayar melalui BCA, BNI, Mandiri, dan lainnya.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- E-Wallet -->
                            <div class="payment-option" data-value="E-Wallet (QRIS)">
                                <div class="d-flex align-items-center">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/1200px-Logo_dana_blue.svg.png" alt="DANA">
                                     <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Logo_ovo_purple.svg/1200px-Logo_ovo_purple.svg.png" style="margin-left: 10px;" alt="OVO">
                                    <div>
                                        <h6 class="mb-0">E-Wallet (QRIS)</h6>
                                        <small class="text-muted">Bayar dengan GoPay, OVO, DANA, dan lainnya.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- COD -->
                            <div class="payment-option" data-value="Bayar di Tempat (COD)">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck fs-2 me-3"></i>
                                    <div>
                                        <h6 class="mb-0">Bayar di Tempat (COD)</h6>
                                        <small class="text-muted">Siapkan uang pas saat kurir tiba.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <h4><i class="bi bi-receipt"></i> Ringkasan</h4>
            <hr>
            <div class="card shadow-sm">
                <div class="card-body">
                    <strong>Alamat Pengiriman</strong>
                    <p class="mb-2">
                        <?= htmlspecialchars($_SESSION['shipping_details']['nama_penerima']); ?><br>
                        <?= htmlspecialchars($_SESSION['shipping_details']['no_telepon_penerima']); ?><br>
                        <?= htmlspecialchars($_SESSION['shipping_details']['alamat_pengiriman']); ?><br>
                        <?= htmlspecialchars($_SESSION['shipping_details']['kota']); ?>, <?= htmlspecialchars($_SESSION['shipping_details']['kode_pos']); ?>
                    </p>
                    <a href="checkout.php">Ubah Alamat</a>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal Produk</span>
                        <strong>Rp <?= number_format($total_produk, 0, ',', '.'); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Biaya Pengiriman</span>
                        <strong>Rp <?= number_format($_SESSION['shipping_details']['biaya_pengiriman'], 0, ',', '.'); ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <strong>Total Bayar</strong>
                        <strong class="text-success">Rp <?= number_format($total_bayar, 0, ',', '.'); ?></strong>
                    </div>
                </div>
                <div class="card-footer">
                    <button id="btn-bayar" class="btn btn-success w-100 fw-bold" disabled>
                        Bayar Sekarang <i class="bi bi-shield-check"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentMethodInput = document.getElementById('metode_pembayaran');
    const payButton = document.getElementById('btn-bayar');
    const paymentForm = document.getElementById('payment-form');

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove 'selected' from all options
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add 'selected' to the clicked option
            this.classList.add('selected');
            
            // Update hidden input and enable button
            paymentMethodInput.value = this.dataset.value;
            payButton.disabled = false;
        });
    });

    payButton.addEventListener('click', function() {
        if (paymentMethodInput.value) {
            paymentForm.submit();
        } else {
            alert('Silakan pilih metode pembayaran terlebih dahulu.');
        }
    });
});
</script>

</body>
</html>
