<?php
session_start();
include "../config/database.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* === BATALKAN PESANAN === */
if(isset($_POST['batalkan_pesanan'])){
    $id_pesanan = $_POST['id_pesanan'];

    // Check status pesanan
    $query_cek = "SELECT status_pesanan FROM pesanan WHERE id_pesanan='$id_pesanan' AND id_user='$id_user'";
    $cek_status_res = mysqli_query($conn, $query_cek);
    
    if ($cek_status_res && mysqli_num_rows($cek_status_res) > 0) {
        $cek_status = mysqli_fetch_assoc($cek_status_res);
        $status_pesanan = $cek_status['status_pesanan'] ?? null;

        if(in_array($status_pesanan, ['pending', 'menunggu_pembayaran'])){
            // Ambil detail pesanan untuk kembalikan stok
            $detail = mysqli_query($conn, "SELECT id_produk, qty FROM pesanan_detail WHERE id_pesanan='$id_pesanan'");
            
            while($d = mysqli_fetch_assoc($detail)){
                // Kembalikan stok produk
                if($d['id_produk'] > 0) {
                    mysqli_query($conn, "UPDATE produk SET stok = stok + '{$d['qty']}' WHERE id_produk = '{$d['id_produk']}'");
                }
            }
            
            // Update status pesanan menjadi dibatalkan
            mysqli_query($conn, "UPDATE pesanan SET status_pesanan='dibatalkan' WHERE id_pesanan='$id_pesanan'");
            
            header("Location: pesanan.php?msg=Pesanan berhasil dibatalkan");
        } else {
            header("Location: pesanan.php?error=Pesanan tidak dapat dibatalkan (status: " . $status_pesanan . ")");
        }
    } else {
         header("Location: pesanan.php?error=Pesanan tidak ditemukan.");
    }
    exit;
}

/* === HAPUS RIWAYAT PESANAN (SOFT DELETE) === */
if(isset($_POST['hapus_riwayat'])){
    $id_pesanan = $_POST['id_pesanan'];

    // Check status pesanan - hanya bisa hapus jika status selesai
    $cek_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status_pesanan FROM pesanan WHERE id_pesanan='$id_pesanan' AND id_user='$id_user'"));

    if($cek_status && $cek_status['status_pesanan'] == 'selesai'){
        // Soft delete - update field is_hidden menjadi 1
        mysqli_query($conn, "UPDATE pesanan SET is_hidden=1 WHERE id_pesanan='$id_pesanan'");
        
        header("Location: pesanan.php?msg=Riwayat pesanan berhasil dihapus");
    } else {
        header("Location: pesanan.php?error=Riwayat pesanan hanya bisa dihapus jika status selesai");
    }
    exit;
}

// Helper function untuk status badge
function getStatusBadge($status) {
    $badges = [
        'menunggu_pembayaran' => ['bg-warning text-dark', '<i class="bi bi-hourglass-split"></i> Menunggu Pembayaran'],
        'pending' => ['bg-info text-dark', '<i class="bi bi-clock"></i> Pending'],
        'diproses' => ['bg-info text-dark', '<i class="bi bi-gear"></i> Diproses'],
        'dikirim' => ['bg-primary text-dark', '<i class="bi bi-truck"></i> Dikirim'],
        'selesai' => ['bg-success text-dark', '<i class="bi bi-check-circle"></i> Selesai'],
        'dibatalkan' => ['bg-danger text-dark', '<i class="bi bi-x-circle"></i> Dibatalkan']
    ];
    
    $key = $status ?? 'pending';
    return isset($badges[$key]) ? $badges[$key] : ['bg-secondary', '<i class="bi bi-question-circle"></i> ' . ucfirst($key)];
}

// Helper function untuk status icon dan warna progress
function getStatusProgress($status) {
    $progress = [
        'menunggu_pembayaran' => ['warning', 25],
        'pending' => ['info', 25],
        'diproses' => ['info', 50],
        'dikirim' => ['primary', 75],
        'selesai' => ['success', 100],
        'dibatalkan' => ['danger', 0]
    ];
    
    $key = $status ?? 'pending';
    return isset($progress[$key]) ? $progress[$key] : ['secondary', 0];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - Warungku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Order Card Styles */
        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid #27ae60;
        }

        .order-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .order-card.cancelled {
            border-left-color: #e74c3c;
            opacity: 0.8;
        }

        .order-card.pending {
            border-left-color: #f39c12;
        }

        .order-card.processing {
            border-left-color: #3498db;
        }

        .order-header {
            padding: 1.25rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-id {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .order-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .order-body {
            padding: 1.5rem;
        }

        .product-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            align-items: center;
        }

        .product-item:last-child {
            margin-bottom: 0;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-details {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .product-price {
            font-weight: 600;
            color: #27ae60;
            font-size: 1rem;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }

        .summary-row.total {
            border-top: 2px solid #dee2e6;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .summary-row.total .amount {
            color: #27ae60;
            font-size: 1.2rem;
        }

        .order-footer {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .order-actions form {
            margin: 0;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 3rem;
            color: #bdc3c7;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #7f8c8d;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #95a5a6;
            margin: 0;
        }

        .progress-bar-animated-custom {
            height: 4px;
            border-radius: 2px;
            overflow: hidden;
        }

        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .badge-deleted {
            background-color: #e74c3c !important;
            color: white;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .order-actions {
                justify-content: stretch;
            }

            .order-actions form {
                width: 100%;
            }

            .order-actions .btn-action {
                width: 100%;
            }

            .product-item {
                flex-direction: column;
                text-align: center;
            }

            .product-image {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="home.php"><i class="bi bi-house"></i> Home</a>
                <a class="nav-link" href="keranjang.php"><i class="bi bi-cart"></i> Keranjang</a>
                <a class="nav-link active" href="#"><i class="bi bi-clipboard-check"></i> Pesanan</a>
                <a class="nav-link" href="profil.php"><i class="bi bi-person-circle"></i> Profil</a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-bag-check"></i> Riwayat Pesanan Anda</h1>
        <p>Kelola dan pantau semua pesanan Anda di sini</p>
    </div>

    <!-- Alerts -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Orders Container -->
    <?php
    // Sanitation untuk id_user
    $id_user_safe = mysqli_real_escape_string($conn, $id_user);
    
    // Query untuk mengambil pesanan yang TIDAK disembunyikan oleh user
    $pesanan = mysqli_query($conn,"
        SELECT id_pesanan, tanggal, status_pesanan, total_bayar, total_produk, biaya_pengiriman, is_hidden FROM pesanan
        WHERE id_user='$id_user_safe' AND (is_hidden IS NULL OR is_hidden = 0)
        ORDER BY id_pesanan DESC
    ");

    if (!$pesanan) {
        echo '<div class="alert alert-danger"><strong>Database Error:</strong> ' . mysqli_error($conn) . '</div>';
    } elseif (mysqli_num_rows($pesanan) == 0) {
        echo '<div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Tidak ada data pesanan untuk akun ini (ID: '.$id_user.'). <a href="home.php">Belanja Sekarang</a></p>
              </div>';
    } else {
        while($p = mysqli_fetch_assoc($pesanan)) {
            // Ambil detail pesanan (LEFT JOIN tanpa filter id_produk > 0 agar data corrupt tetap muncul)
            // FIX: Hapus p.foto karena kolom tidak ada di database
            $detail_query = mysqli_query($conn,"
                SELECT 
                    pd.id_detail, 
                    pd.qty, 
                    COALESCE(pd.harga, 0) as harga_beli, 
                    pd.id_produk as original_id_produk,
                    COALESCE(pd.nama_produk, '') as snapshot_nama,
                    COALESCE(pd.gambar, '') as snapshot_gambar,
                    p.id_produk,
                    p.nama_produk, 
                    p.gambar
                FROM pesanan_detail pd
                LEFT JOIN produk p ON pd.id_produk = p.id_produk
                WHERE pd.id_pesanan='" . mysqli_real_escape_string($conn, $p['id_pesanan']) . "'
            ");
            
            if (!$detail_query) {
                // Jika query detail gagal, tampilkan error (untuk debugging)
                echo '<div class="alert alert-warning">Gagal memuat detail pesanan #' . $p['id_pesanan'] . ': ' . mysqli_error($conn) . '</div>';
                continue;
            }
            
            $details = [];
            while($d = mysqli_fetch_assoc($detail_query)){
                $details[] = $d;
            }
            
            // if(count($details) == 0) continue; // Jangan skip order meskipun detail kosong

            $status_pesanan = $p['status_pesanan'] ?? 'menunggu_pembayaran';
            $status_badge = getStatusBadge($status_pesanan);
            $status_progress = getStatusProgress($status_pesanan);
            $card_class = 'order-card';
            if($status_pesanan == 'dibatalkan') $card_class .= ' cancelled';
            elseif($status_pesanan == 'menunggu_pembayaran') $card_class .= ' pending';
            elseif(in_array($status_pesanan, ['diproses', 'pending'])) $card_class .= ' processing';

            echo '<div class="' . $card_class . '">';

            // Order Header
            echo '<div class="order-header">';
            echo '<div>';
            echo '<div class="order-id">#' . $p['id_pesanan'] . '</div>';
            echo '<div class="order-date">' . date('d F Y • H:i', strtotime($p['tanggal'])) . '</div>';
            echo '</div>';
            echo '<span class="order-status-badge bg-' . $status_badge[0] . '">' . $status_badge[1] . '</span>';
            echo '</div>';

            // Progress Bar
            if($status_pesanan != 'dibatalkan') {
                $progress_color = $status_progress[0];
                echo '<div class="progress progress-bar-animated-custom" role="progressbar" style="height: 4px;">
                        <div class="progress-bar bg-' . $progress_color . '" style="width: ' . $status_progress[1] . '%"></div>
                      </div>';
            }

            // Order Body
            echo '<div class="order-body">';
            
            if (count($details) == 0) {
                echo '<div class="text-muted fst-italic p-3">Detail produk tidak tersedia.</div>';
            }

            // Products
            foreach($details as $item) {
                // Cek apakah produk ada atau tidak
                $nama_produk = !empty($item['snapshot_nama']) ? $item['snapshot_nama'] : ($item['nama_produk'] ?? null);
                
                // Coba dua kolom gambar (gambar dan foto)
                $file_gambar = !empty($item['snapshot_gambar']) ? $item['snapshot_gambar'] : ($item['gambar'] ?? $item['foto'] ?? '');
                
                // Default placeholder jika tidak ada gambar
                $img_src = "https://via.placeholder.com/80/cccccc/999999?text=No+Image";
                
                // Jika file_gambar ada, cek apakah file fisik tersedia
                if (!empty($file_gambar)) {
                    $gambar_path = "../assets/images/produk/" . $file_gambar;
                    if (file_exists($gambar_path)) {
                        $img_src = $gambar_path;
                    }
                }
                
                echo '<div class="product-item">';
                echo '<div class="product-image"><img src="' . htmlspecialchars($img_src) . '" alt="Product" onerror="this.src=\'https://via.placeholder.com/80/cccccc/999999?text=No+Image\'"></div>';
                echo '<div class="product-info">';
                
                if ($nama_produk) {
                    echo '<div class="product-name">';
                    echo htmlspecialchars($nama_produk);
                    echo '</div>';
                } else {
                    echo '<div class="product-name text-muted"><em>Produk Tidak Tersedia (ID: ' . htmlspecialchars($item['original_id_produk']) . ')</em></div>';
                }
                
                echo '<div class="product-details">' . $item['qty'] . ' item × Rp ' . number_format((float)$item['harga_beli'], 0, ',', '.') . '</div>';
                echo '</div>';
                echo '<div class="product-price">Rp ' . number_format($item['qty'] * $item['harga_beli'], 0, ',', '.') . '</div>';
                echo '</div>';
            }

            // Order Summary
            echo '<div class="order-summary">';
            $subtotal = $p['total_produk'] ?? 0;
            $shipping = $p['biaya_pengiriman'] ?? 0;
            $total = $p['total_bayar'] ?? 0;
            
            echo '<div class="summary-row">';
            echo '<span>Subtotal Produk:</span>';
            echo '<span>Rp ' . number_format($subtotal, 0, ',', '.') . '</span>';
            echo '</div>';
            
            echo '<div class="summary-row">';
            echo '<span>Biaya Pengiriman:</span>';
            echo '<span>Rp ' . number_format($shipping, 0, ',', '.') . '</span>';
            echo '</div>';
            
            echo '<div class="summary-row total">';
            echo '<span>Total Pembayaran:</span>';
            echo '<span class="amount">Rp ' . number_format($total, 0, ',', '.') . '</span>';
            echo '</div>';
            echo '</div>';

            echo '</div>';

            // Order Footer
            echo '<div class="order-footer">';
            
            echo '<div style="font-size: 0.9rem; color: #7f8c8d;">';
            if($status_pesanan == 'menunggu_pembayaran') {
                echo '<i class="bi bi-info-circle"></i> Menunggu bukti pembayaran Anda';
            } elseif($status_pesanan == 'pending') {
                echo '<i class="bi bi-info-circle"></i> Pesanan sedang dikonfirmasi';
            } elseif($status_pesanan == 'diproses') {
                echo '<i class="bi bi-info-circle"></i> Pesanan sedang disiapkan';
            } elseif($status_pesanan == 'dikirim') {
                echo '<i class="bi bi-info-circle"></i> Pesanan sedang dalam perjalanan';
            } elseif($status_pesanan == 'selesai') {
                echo '<i class="bi bi-check2"></i> Pesanan telah diterima';
            } elseif($status_pesanan == 'dibatalkan') {
                echo '<i class="bi bi-x-circle"></i> Pesanan telah dibatalkan';
            }
            echo '</div>';

            echo '<div class="order-actions">';
            
            if(in_array($status_pesanan, ['pending', 'menunggu_pembayaran'])){
                echo '<form method="POST" onsubmit="return confirm(\'Yakin ingin membatalkan pesanan ini?\');">';
                echo '<input type="hidden" name="id_pesanan" value="' . $p['id_pesanan'] . '">';
                echo '<button type="submit" name="batalkan_pesanan" class="btn-action btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i> Batalkan</button>';
                echo '</form>';
            } elseif($status_pesanan == 'selesai') {
                echo '<form method="POST" onsubmit="return confirm(\'Hapus riwayat pesanan ini?\');">';
                echo '<input type="hidden" name="id_pesanan" value="' . $p['id_pesanan'] . '">';
                echo '<button type="submit" name="hapus_riwayat" class="btn-action btn btn-outline-secondary btn-sm"><i class="bi bi-trash"></i> Hapus</button>';
                echo '</form>';
            }
            
            echo '</div>';

            echo '</div>';

            echo '</div>';
        }
    }
    ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
