<?php
session_start();
include "../config/database.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Ambil data produk berdasarkan id
$id_produk = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "
    SELECT produk.*, kategori.nama_kategori,
           COALESCE(fs.harga_diskon, produk.harga) as harga_tampil,
           COALESCE(fs.diskon_persen, 0) as diskon,
           CASE
               WHEN fs.id_flash IS NOT NULL
                    AND fs.status = 'aktif'
                    AND fs.waktu_mulai <= NOW()
                    AND fs.waktu_selesai >= NOW()
               THEN 1
               ELSE 0
           END as is_flash_sale
    FROM produk
    LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori
    LEFT JOIN flash_sale fs ON produk.id_produk = fs.id_produk
    WHERE produk.id_produk='$id_produk'
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

if(mysqli_num_rows($query)==0){
    echo "Produk tidak ditemukan";
    exit;
}

$p = mysqli_fetch_assoc($query);

// Cek gambar
$foto = isset($p['foto']) ? $p['foto'] : (isset($p['gambar']) ? $p['gambar'] : '');
$img_path = !empty($foto) ? "../assets/images/produk/" . $foto : "";

if(empty($img_path) || !file_exists($img_path)) {
    $img_path = "https://via.placeholder.com/400x400?text=Produk";
}

// Get cart count - hitung dari session
$cart_count = 0;
if(isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach($_SESSION['keranjang'] as $item) {
        $cart_count += $item['qty'];
    }
}

// Cek status wishlist
$in_wishlist = false;
$id_user_escaped = mysqli_real_escape_string($conn, $_SESSION['id_user']);
$check_w = mysqli_query($conn, "SELECT id_wishlist FROM wishlist WHERE id_user='{$id_user_escaped}' AND id_produk='$id_produk'");

if (!$check_w) {
    die("Wishlist Query Error: " . mysqli_error($conn));
}

if(mysqli_num_rows($check_w) > 0) $in_wishlist = true;

// NOTE: The file 'wishlist_action.php' is currently missing.
// The JavaScript for wishlist functionality will not work until this file is created.
// It should handle adding/removing items from the wishlist and return a JSON response.
?>  

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($p['nama_produk']); ?> | Warungku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
:root {
    --primary-color: #198754;
    --secondary-color: #20c997;
}

* {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body { 
    background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
    padding-top: 20px;
    padding-bottom: 50px;
}

.navbar-custom {
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 15px 0;
    margin-bottom: 30px;
}

.breadcrumb-custom {
    background: transparent;
    padding: 10px 0;
    margin-bottom: 30px;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #666;
}

.card-product {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    background: white;
}

.product-image {
    width: 100%;
    height: 450px;
    object-fit: cover;
    border-radius: 15px;
    background: #f0f0f0;
}

.product-gallery {
    gap: 10px;
    margin-top: 15px;
}

.product-gallery img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: 0.3s;
}

.product-gallery img:hover,
.product-gallery img.active {
    border-color: var(--primary-color);
}

.product-details {
    padding: 0;
}

.product-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 15px;
    line-height: 1.3;
}

.product-category {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.rating-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.stars {
    color: #ffc107;
    font-size: 14px;
}

.rating-count {
    color: #999;
    font-size: 14px;
}

.price-section {
    margin: 25px 0;
    padding: 20px;
    background: linear-gradient(135deg, #f0f9ff 0%, #f0f5ff 100%);
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
}

.price-current {
    font-size: 36px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.price-original {
    font-size: 18px;
    color: #999;
    text-decoration: line-through;
    margin-right: 10px;
}

.discount-badge {
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.stock-info {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.stock-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stock-item-label {
    color: #666;
    font-size: 14px;
}

.stock-item-value {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
}

.stock-badge {
    background: #d1ecf1;
    color: #0c5460;
    padding: 8px 15px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

.stock-badge.habis {
    background: #f8d7da;
    color: #721c24;
}

.description-section {
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.description-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
    color: #1a1a1a;
}

.description-text {
    color: #666;
    line-height: 1.6;
    font-size: 15px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}

.btn-add-cart {
    flex: 1;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border: none;
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    transition: 0.3s;
    cursor: pointer;
}

.btn-add-cart:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(25, 135, 84, 0.3);
    color: white;
}

.btn-add-cart:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-wishlist-detail {
    width: 50px;
    border: 2px solid #e0e0e0;
    background: white;
    border-radius: 10px;
    color: #999;
}
.btn-wishlist-detail.active { border-color: #ff4757; color: #ff4757; }

.btn-back {
    background: white;
    border: 2px solid #e0e0e0;
    color: #666;
    padding: 13px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: 0.3s;
    text-decoration: none;
}

.btn-back:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.related-products {
    margin-top: 50px;
}

.related-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .product-title {
        font-size: 22px;
    }
    
    .price-current {
        font-size: 28px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-custom sticky-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="home.php" class="text-decoration-none">
                <h5 class="mb-0" style="color: var(--primary-color); font-weight: 700;">
                    <i class="bi bi-shop"></i> Warungku
                </h5>
            </a>
            <div class="d-flex gap-3">
                <a href="keranjang.php" class="text-decoration-none" style="color: var(--primary-color); position: relative;">
                    <i class="bi bi-cart2" style="font-size: 20px;"></i>
                    <span class="badge bg-danger" id="cartBadge" style="position: absolute; top: -8px; right: -8px; font-size: 0.7rem;" <?= $cart_count == 0 ? 'style="display: none;"' : '' ?>>
                        <?= $cart_count; ?>
                    </span>
                </a>
                <a href="home.php" class="text-decoration-none" style="color: var(--primary-color);">
                    <i class="bi bi-person" style="font-size: 20px;"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="breadcrumb-custom">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($p['nama_produk']); ?></li>
        </ol>
    </nav>

    <!-- Product Detail -->
    <div class="row">
        <!-- Gambar Produk -->
        <div class="col-md-6 mb-4">
            <div class="card-product">
                <div style="position: relative;">
                    <img src="<?= $img_path ?>" class="product-image" alt="<?= htmlspecialchars($p['nama_produk']); ?>" id="mainImage">
                </div>
            </div>
        </div>

        <!-- Detail Produk -->
        <div class="col-md-6">
            <div class="card-product p-4">
                <!-- Kategori -->
                <span class="product-category">
                    <i class="bi bi-tag"></i> <?= htmlspecialchars($p['nama_kategori']); ?>
                </span>

                <!-- Judul -->
                <h1 class="product-title"><?= htmlspecialchars($p['nama_produk']); ?></h1>

                <!-- Rating -->
                <div class="rating-section">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <span class="rating-count">(45 reviews)</span>
                </div>

                <!-- Harga -->
                <div class="price-section">
                    <?php if($p['is_flash_sale']): ?>
                        <div class="price-current" style="color: #ff4757;">
                            Rp <?= number_format($p['harga_tampil']); ?>
                        </div>
                        <div>
                            <span class="price-original" style="text-decoration: line-through;">
                                Rp <?= number_format($p['harga']); ?>
                            </span>
                            <span class="discount-badge" style="background: #ff4757;">-<?= $p['diskon']; ?>%</span>
                        </div>
                    <?php else: ?>
                        <div class="price-current">Rp <?= number_format($p['harga']); ?></div>
                        
                    <?php endif; ?>
                </div>

                <!-- Deskripsi Produk -->
                <?php if(!empty($p['deskripsi'])): ?>
                <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid var(--primary-color);">
                    <p style="color: #666; line-height: 1.6; margin: 0; font-size: 14px;"><?= nl2br(htmlspecialchars($p['deskripsi'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- Info Stok -->
                <div class="stock-info">
                    <div class="stock-item">
                        <i class="bi bi-box" style="color: var(--primary-color); font-size: 20px;"></i>
                        <div>
                            <div class="stock-item-label">Stok Tersedia</div>
                            <div class="stock-item-value"><?= $p['stok']; ?> pcs</div>
                        </div>
                    </div>
                </div>

                <!-- Status Stok Badge -->
                <div style="margin: 15px 0; display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php if($p['is_flash_sale']): ?>
                        <span class="stock-badge" style="background: #ff4757;">⚡ Flash Sale!</span>
                    <?php endif; ?>
                    
                    <?php if($p['stok'] > 10): ?>
                        <span class="stock-badge">✓ Stok Terbatas - Segera Pesan!</span>
                    <?php elseif($p['stok'] > 0): ?>
                        <span class="stock-badge" style="background: #fff3cd; color: #856404;">⚠ Stok Terbatas</span>
                    <?php else: ?>
                        <span class="stock-badge habis">✗ Stok Habis</span>
                    <?php endif; ?>
                </div>

                <!-- Tombol Aksi -->
                <form method="POST" action="keranjang.php" style="margin-top: 30px;">
                    <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                    <div class="action-buttons">
                        <button type="submit" class="btn-add-cart" <?= ($p['stok']<=0)?'disabled':'' ?>>
                            <i class="bi bi-cart-plus"></i>
                            <?= ($p['stok']>0)?'Tambah ke Keranjang':'Stok Habis' ?>
                        </button>
                        <button type="button" class="btn-wishlist-detail <?= $in_wishlist?'active':'' ?>" id="btnWishlistDetail" data-id="<?= $p['id_produk'] ?>">
                            <i class="bi bi-heart<?= $in_wishlist?'-fill':'' ?>"></i>
                        </button>
                    </div>
                </form>

                <a href="home.php" class="btn-back" style="display: inline-block; margin-top: 15px; width: 100%;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Produk
                </a>

                <!-- Info Tambahan -->
                <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e0e0e0;">
                    <div class="d-flex gap-3">
                        <div style="text-align: center; flex: 1;">
                            <i class="bi bi-shield-check" style="color: var(--primary-color); font-size: 24px; display: block; margin-bottom: 5px;"></i>
                            <small style="color: #666;">Asli & Terjamin</small>
                        </div>
                        <div style="text-align: center; flex: 1;">
                            <i class="bi bi-truck" style="color: var(--primary-color); font-size: 24px; display: block; margin-bottom: 5px;"></i>
                            <small style="color: #666;">Pengiriman Cepat</small>
                        </div>
                        <div style="text-align: center; flex: 1;">
                            <i class="bi bi-arrow-counterclockwise" style="color: var(--primary-color); font-size: 24px; display: block; margin-bottom: 5px;"></i>
                            <small style="color: #666;">Pengembalian Mudah</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to update cart badge
    function updateCartBadge(count) {
        const badge = document.getElementById('cartBadge');
        if (badge) {
            badge.textContent = count;
            if (count > 0) {
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Handle add to cart via AJAX
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Only prevent default if not disabled
            if (!this.disabled) {
                e.preventDefault();
                
                const form = this.closest('form');
                const formData = new FormData(form);
                formData.append('ajax', '1');
                
                fetch('keranjang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateCartBadge(data.count);
                        // Show success notification
                        showNotification('✓ ' + data.message, 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan', 'error');
                });
            }
        });
    });

    // Wishlist Detail
    document.getElementById('btnWishlistDetail').addEventListener('click', function() {
        const id = this.dataset.id;
        const icon = this.querySelector('i');
        
        const formData = new FormData();
        formData.append('id_produk', id);
        
        fetch('wishlist_action.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                this.classList.toggle('active');
                if(data.action === 'added') {
                    icon.className = 'bi bi-heart-fill';
                } else {
                    icon.className = 'bi bi-heart';
                }
            }
        });
    });

    // Notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Image gallery functionality
    document.querySelectorAll('.product-gallery img').forEach(img => {
        img.addEventListener('click', function() {
            document.getElementById('mainImage').src = this.src;
            document.querySelectorAll('.product-gallery img').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>

</body>
</html>
