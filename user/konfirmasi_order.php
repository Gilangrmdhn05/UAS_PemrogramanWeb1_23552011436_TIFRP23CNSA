<?php
session_start();
include "../config/database.php";

// Authenticate user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// If there's no final order data, redirect to home
if (!isset($_SESSION['final_order'])) {
    header("Location: home.php");
    exit;
}

$order = $_SESSION['final_order'];

// Clear the session variable so the page can't be refreshed
unset($_SESSION['final_order']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan - Warungku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm text-center">
                <div class="card-header bg-success text-white">
                    <h3><i class="bi bi-check-circle-fill"></i> Pesanan Berhasil Dibuat!</h3>
                </div>
                <div class="card-body">
                    <p class="lead">Terima kasih telah berbelanja di Warungku.</p>
                    <?php 
                    $order_count = count($order['order_ids']);
                    if ($order_count > 1) {
                        echo "<p class='text-success fw-bold'>✓ " . $order_count . " pesanan terpisah telah dibuat untuk setiap produk Anda</p>";
                    }
                    ?>
                    <p>ID Pesanan Utama Anda adalah: <strong><?= htmlspecialchars($order['order_id']); ?></strong></p>
                    
                    <?php if ($order_count > 1): ?>
                    <div class="alert alert-info">
                        <strong>ID Pesanan Terpisah untuk Setiap Produk:</strong>
                        <ul class="mt-2 mb-0">
                            <?php foreach($order['order_ids'] as $id): ?>
                                <li>ID Pesanan: <code><?= htmlspecialchars($id); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <hr>

                    <h5>Instruksi Pembayaran</h5>
                    <div class="alert alert-info">
                        <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order['metode_pembayaran']); ?>
                    </div>

                    <?php if ($order['metode_pembayaran'] == 'E-Wallet (QRIS)'): ?>
                        <div class="mb-3">
                            <img src="<?= htmlspecialchars($order['kode_pembayaran']); ?>" alt="QR Code" class="img-fluid">
                        </div>
                    <?php else: ?>
                        <p class="fs-4 fw-bold"><?= htmlspecialchars($order['kode_pembayaran']); ?></p>
                    <?php endif; ?>
                    
                    <p class="text-muted"><?= htmlspecialchars($order['instruksi_pembayaran']); ?></p>
                    
                    <div class="my-4 p-3 bg-light rounded">
                        <p class="mb-1">Total yang harus dibayar (untuk semua pesanan):</p>
                        <h4 class="text-success fw-bold">Rp <?= number_format($order['order_totals']['total_bayar'], 0, ',', '.'); ?></h4>
                        <small class="text-danger">Batas waktu pembayaran: 24 jam</small>
                    </div>

                    <hr>
                    <a href="home.php" class="btn btn-primary">Kembali ke Beranda</a>
                    <a href="pesanan.php" class="btn btn-outline-primary">Lihat Riwayat Pesanan</a>
                </div>
                <div class="card-footer text-muted">
                    Tanggal Pesanan: <?= htmlspecialchars($order['tanggal']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
