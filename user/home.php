<?php
session_start();
include "../config/database.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Get active flash sales
$flash_sales = mysqli_query($conn, "
    SELECT fs.*, p.nama_produk, p.harga, p.id_produk
    FROM flash_sale fs
    JOIN produk p ON fs.id_produk = p.id_produk
    WHERE fs.status = 'aktif' 
    AND fs.waktu_mulai <= NOW() 
    AND fs.waktu_selesai >= NOW()
    ORDER BY fs.waktu_selesai ASC
    LIMIT 6
");

// Get closest flash sale start time
$next_flash = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT waktu_mulai, waktu_selesai
    FROM flash_sale
    WHERE status = 'nonaktif'
    AND waktu_mulai > NOW()
    ORDER BY waktu_mulai ASC
    LIMIT 1
"));

/* === DATA KATEGORI === */
$kategori = mysqli_query($conn,"SELECT * FROM kategori");

/* === FILTER & SEARCH === */
$where = [];

if (!empty($_GET['kategori'])) {
    $where[] = "produk.id_kategori='$_GET[kategori]'";
}

if (!empty($_GET['search'])) {
    $where[] = "produk.nama_produk LIKE '%$_GET[search]%'";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* === DATA PRODUK === */
$produk = mysqli_query($conn,"
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
    $whereSQL
    GROUP BY produk.id_produk
");

// Get cart count - hitung dari session
$cart_count = 0;
if(isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach($_SESSION['keranjang'] as $item) {
        $cart_count += $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warungku - Belanja Online Mudah & Cepat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #2d3436;
        }
        
        /* Top Bar */
        .top-bar {
            display: none;
        }
        
        /* Header */
        .navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .search-container {
            position: relative;
            margin: 0 20px;
            flex: 1;
            max-width: 500px;
        }
        
        .search-container input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            transition: border 0.3s;
        }
        
        .search-container input:focus {
            outline: none;
            border-color: #198754;
            box-shadow: 0 0 8px rgba(25,135,84,0.2);
        }
        
        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .nav-icons {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-icon-item {
            position: relative;
            cursor: pointer;
            text-decoration: none;
            color: #2d3436;
            font-size: 1.3rem;
            transition: color 0.3s;
        }
        
        .nav-icon-item:hover {
            color: #198754;
        }
        
        .badge-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        /* Flash Sale Banner */
        .flash-sale-section {
            background: white;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        
        .flash-sale-header {
            background: linear-gradient(135deg, #ff4757 0%, #ff6b7a 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .flash-sale-icon {
            font-size: 2.5rem;
        }
        
        .flash-sale-info h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .countdown {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .flash-timer {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .timer-item {
            background: rgba(0,0,0,0.2);
            padding: 3px 8px;
            border-radius: 4px;
            min-width: 35px;
            text-align: center;
        }
        
        /* Greeting Banner */
        .greeting-banner {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .greeting-content h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .greeting-content p {
            opacity: 0.95;
            font-size: 1rem;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid var(--primary-color);
        }
        
        .filter-title {
            font-weight: 700;
            margin-bottom: 20px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .filter-title i {
            color: #198754;
            font-size: 1.3rem;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 250px auto auto;
            gap: 12px;
            align-items: flex-end;
        }
        
        .filter-form input,
        .filter-form select {
            padding: 12px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: white;
        }
        
        .filter-form input {
            background: #f8f9fa;
            color: #333;
        }
        
        .filter-form input::placeholder {
            color: #999;
        }
        
        .filter-form input:focus,
        .filter-form select:focus {
            outline: none;
            border-color: #198754;
            background: white;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }
        
        .filter-form select {
            cursor: pointer;
            background: white;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23198754' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }
        
        .filter-form select:hover {
            border-color: #198754;
        }
        
        .filter-form button {
            padding: 12px 28px;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(25, 135, 84, 0.15);
        }
        
        .filter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
        }
        
        .filter-form button:active {
            transform: translateY(0);
        }
        
        .filter-reset {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.15) !important;
        }
        
        .filter-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.25) !important;
        }
        
        /* Products Section */
        .products-section {
            margin-bottom: 50px;
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .products-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3436;
        }
        
        .product-count {
            background: #e7f5f1;
            color: #198754;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .no-products {
            background: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 8px;
        }
        
        .no-products i {
            font-size: 3rem;
            opacity: 0.3;
            display: block;
            margin-bottom: 20px;
        }
        
        .no-products p {
            color: #999;
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        /* Product Card */
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-5px);
        }
        
        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f0f0f0;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #198754;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .product-stock-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .product-stock-badge.habis {
            background: #ff4757;
        }
        
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            background: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-size: 1.1rem;
            color: #999;
        }
        
        .wishlist-btn:hover {
            background: #ff4757;
            color: white;
        }
        
        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-category {
            display: inline-block;
            background: #e7f5f1;
            color: #198754;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            width: fit-content;
            font-weight: 600;
        }
        
        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #198754;
            margin: 10px 0;
        }
        
        .product-footer {
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        
        .btn-product {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-detail {
            background: #f0f0f0;
            color: #198754;
            border: 2px solid #198754;
        }
        
        .btn-detail:hover {
            background: #198754;
            color: white;
        }
        
        .btn-cart {
            background: #198754;
            color: white;
        }
        
        .btn-cart:hover {
            background: #157347;
        }
        
        .btn-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Footer */
        footer {
            background: #1e1e1e;
            color: #ddd;
            padding: 50px 0 20px;
            margin-top: 60px;
        }
        
        footer a {
            color: #20c997;
            text-decoration: none;
        }
        
        footer a:hover {
            color: #198754;
            text-decoration: underline;
        }
        
        .footer-section h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 12px;
            font-size: 0.9rem;
        }
        
        .footer-divider {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 30px 0;
        }
        
        .footer-bottom {
            text-align: center;
            font-size: 0.9rem;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .filter-form {
                grid-template-columns: 1fr 200px auto auto;
            }
        }
        
        @media (max-width: 992px) {
            .filter-form {
                grid-template-columns: 1fr auto;
            }
            
            .filter-form button,
            .filter-reset {
                grid-column: 2;
            }
        }
        
        @media (max-width: 768px) {
            .search-container {
                display: none;
            }
            
            .filter-section {
                padding: 20px;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .filter-form input,
            .filter-form select,
            .filter-form button,
            .filter-reset {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar sticky-top">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand me-4" href="home.php">
                <i class="bi bi-shop"></i> Warungku
            </a>
            
            <form method="GET" class="search-container">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Cari produk..." value="<?= $_GET['search'] ?? '' ?>">
            </form>
            
            <div class="nav-icons ms-4">
                <a href="keranjang.php" class="nav-icon-item" title="Keranjang">
                    <i class="bi bi-cart3"></i>
                    <span class="badge-count" id="cartBadge"><?= $cart_count; ?></span>
                </a>
                <a href="pesanan.php" class="nav-icon-item" title="Pesanan Saya">
                    <i class="bi bi-bag-check"></i>
                </a>
                <a href="profil.php" class="nav-icon-item" title="Profil">
                    <i class="bi bi-person-circle"></i>
                </a>
               
            </div>
        </div>
    </nav>

    <!-- Greeting Banner -->
    <section class="greeting-banner">
        <div class="container">
            <div class="greeting-content">
                <h2>Selamat Datang Kembali, <?= $_SESSION['nama']; ?> 👋</h2>
                <p>Temukan produk favorit Anda dengan harga terbaik</p>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Flash Sale Section -->
        <section class="flash-sale-section">
            <?php if(mysqli_num_rows($flash_sales) > 0) { 
                // There are active flash sales
                $first_flash = mysqli_fetch_assoc($flash_sales);
                $waktu_selesai = strtotime($first_flash['waktu_selesai']);
                $waktu_sekarang = strtotime(date('Y-m-d H:i:s'));
                $selisih = $waktu_selesai - $waktu_sekarang;
                
                $jam = floor($selisih / 3600);
                $menit = floor(($selisih % 3600) / 60);
                $detik = $selisih % 60;
            ?>
                <div class="flash-sale-header">
                    <div class="flash-sale-icon">⚡</div>
                    <div class="flash-sale-info">
                        <h3>Flash Sale!</h3>
                        <div class="countdown">Penawaran terbatas! Berakh   ir dalam:
                            <div class="flash-timer" data-seconds="<?= $selisih ?>">
                                <div class="timer-item" id="jam"><?= str_pad($jam, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="timer-item">:</div>
                                <div class="timer-item" id="menit"><?= str_pad($menit, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="timer-item">:</div>
                                <div class="timer-item" id="detik"><?= str_pad($detik, 2, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } elseif($next_flash) { 
                // Upcoming flash sale
                $waktu_mulai = strtotime($next_flash['waktu_mulai']);
                $waktu_sekarang = strtotime(date('Y-m-d H:i:s'));
                $selisih = $waktu_mulai - $waktu_sekarang;
                
                $hari = floor($selisih / 86400);
                $jam = floor(($selisih % 86400) / 3600);
            ?>
                <div class="flash-sale-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                    <div class="flash-sale-icon">🎉</div>
                    <div class="flash-sale-info">
                        <h3>Flash Sale Akan Datang!</h3>
                        <div class="countdown">Flash Sale dimulai dalam <?= $hari; ?> hari <?= $jam; ?> jam lagi</div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="flash-sale-header" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">
                    <div class="flash-sale-icon">📢</div>
                    <div class="flash-sale-info">
                        <h3>Tunggu Flash Sale Berikutnya</h3>
                        <div class="countdown">Flash sale akan segera hadir dengan penawaran spesial!</div>
                    </div>
                </div>
            <?php } ?>
        </section>

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="filter-title">
                <i class="bi bi-funnel"></i> Filter & Pencarian
            </div>
            <form method="GET" class="filter-form">
                <input type="text" name="search" placeholder="🔍 Cari produk, merek, atau kategori..." 
                       value="<?= $_GET['search'] ?? '' ?>">
                
                <select name="kategori">
                    <option value="">-- Semua Kategori --</option>
                    <?php 
                    $kategori_reset = mysqli_query($conn,"SELECT * FROM kategori");
                    while($k = mysqli_fetch_assoc($kategori_reset)){ 
                    ?>
                        <option value="<?= $k['id_kategori']; ?>"
                            <?= (($_GET['kategori'] ?? '')==$k['id_kategori'])?'selected':'' ?>>
                            <?= $k['nama_kategori']; ?>
                        </option>
                    <?php } ?>
                </select>
                
                <button type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <?php if(!empty($_GET['search']) || !empty($_GET['kategori'])): ?>
                    <a href="home.php" class="btn-product filter-reset" style="text-decoration: none;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="products-header">
                <h2 class="products-title">
                    <i class="bi bi-shop"></i> Produk Tersedia
                </h2>
                <?php if(mysqli_num_rows($produk) > 0): ?>
                    <span class="product-count"><?= mysqli_num_rows($produk); ?> Produk</span>
                <?php endif; ?>
            </div>

            <?php if (mysqli_num_rows($produk) == 0){ ?>
                <div class="no-products">
                    <i class="bi bi-inbox"></i>
                    <p>Produk tidak ditemukan</p>
                    <a href="home.php" class="btn btn-outline-success">Lihat Semua Produk</a>
                </div>
            <?php } else { ?>
                <div class="row g-3">
                    <?php while($p = mysqli_fetch_assoc($produk)){ 
                        // Cek field gambar (bisa "foto" atau "gambar")
                        $foto = isset($p['foto']) ? $p['foto'] : (isset($p['gambar']) ? $p['gambar'] : '');
                        $img_path = !empty($foto) ? "../assets/images/produk/" . $foto : "";
                        
                        if(empty($img_path) || !file_exists($img_path)) {
                            $img_path = "https://via.placeholder.com/300x200?text=Produk";
                        }
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card">
                                <div class="product-image-wrapper">
                                    <img src="<?= $img_path ?>" alt="<?= $p['nama_produk']; ?>" class="product-image">
                                    
                                    <span class="product-badge">
                                        <?= $p['nama_kategori'] ?? 'Lainnya'; ?>
                                    </span>
                                    
                                    <?php if($p['is_flash_sale']): ?>
                                        <span class="product-badge" style="background: #ff4757; position: absolute; top: 50px; right: 10px; animation: pulse 1s infinite;">
                                            ⚡ Flash Sale
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="product-stock-badge <?= ($p['stok'] <= 0) ? 'habis' : '' ?>">
                                        <?= ($p['stok'] > 0) ? 'Stok: ' . $p['stok'] : 'Stok Habis'; ?>
                                    </span>
                                    
                                    <button class="wishlist-btn" title="Tambah ke Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>

                                <div class="product-info">
                                    <span class="product-category"><?= $p['nama_kategori'] ?? 'Uncategorized'; ?></span>
                                    <h5 class="product-name"><?= htmlspecialchars($p['nama_produk']); ?></h5>
                                    <div class="product-price">
                                        <?php if($p['is_flash_sale']): ?>
                                            <span style="text-decoration: line-through; color: #999; font-size: 0.9rem;">
                                                Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
                                            </span>
                                            <span style="color: #ff4757; font-weight: 700; font-size: 1.1rem;">
                                                Rp <?= number_format($p['harga_tampil'], 0, ',', '.'); ?>
                                            </span>
                                            <span style="background: #ff4757; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 5px;">
                                                -<?= $p['diskon']; ?>%
                                            </span>
                                        <?php else: ?>
                                            <span>Rp <?= number_format($p['harga'], 0, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-footer">
                                        <a href="detail.php?id=<?= $p['id_produk']; ?>" class="btn-product btn-detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <form method="POST" action="keranjang.php" style="flex: 1;">
                                            <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                                            <button type="submit" class="btn-product btn-cart w-100" 
                                                    <?= ($p['stok'] <= 0) ? 'disabled' : '' ?>>
                                                <i class="bi bi-bag-plus"></i> <?= ($p['stok'] > 0) ? 'Tambah Keranjang' : 'Habis' ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5><i class="bi bi-shop"></i> Warungku</h5>
                        <p style="font-size: 0.9rem;">Platform belanja online terpercaya dengan produk berkualitas dan dijamin keaslianya.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Menu Utama</h5>
                        <ul>
                            <li><a href="home.php">Belanja</a></li>
                            <li><a href="keranjang.php">Keranjang</a></li>
                            <li><a href="pesanan.php">Pesanan Saya</a></li>
                            <li><a href="profil.php">Profil Saya</a></li>
                            <li><a href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Bantuan</h5>
                        <ul>
                            <li><a href="#">Pusat Bantuan</a></li>
                            <li><a href="#">Kebijakan Privasi</a></li>
                            <li><a href="#">Syarat & Ketentuan</a></li>
                            <li><a href="#">Hubungi Kami</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Hubungi Kami</h5>
                        <ul style="gap: 10px; display: flex; flex-direction: column;">
                            <li><i class="bi bi-telephone"></i> +6283131811032</li>
                            <li><i class="bi bi-envelope"></i> ujangbedog024@gmail.com</li>
                            <li><i class="bi bi-geo-alt"></i> Jl. Ciparay No. 23, Bandung</li>
                            <li><i class="bi bi-clock"></i> Buka 24 jam</li>
                        </ul>
                    </div>  
                </div>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-bottom">
                <p>&copy; Copyright by 23552011436_GILANG RAMADHAN HERDIAN_TIF RP 23 CNS A_UAS WEB 1</p>
            </div>
        </div>
    </footer>

    <!-- Add to Cart Modal -->
    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Tambah ke Keranjang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="modalProductImage" src="" class="img-fluid rounded" alt="Product Image">
                        </div>
                        <div class="col-md-8">
                            <h5 id="modalProductName">Nama Produk</h5>
                            <p id="modalProductPrice" class="fs-4 fw-bold text-success">Rp 0</p>
                            <div class="d-flex align-items-center">
                                <label for="modalProductQty" class="form-label me-3">Jumlah:</label>
                                <div class="input-group" style="width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button" id="qty-minus">-</button>
                                    <input type="number" id="modalProductQty" class="form-control text-center" value="1" min="1">
                                    <button class="btn btn-outline-secondary" type="button" id="qty-plus">+</button>
                                </div>
                            </div>
                            <small id="modalProductStock" class="text-muted"></small>
                            <input type="hidden" id="modalProductId">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="modalAddToCartBtn"><i class="bi bi-bag-plus"></i> Masukkan Keranjang</button>
                    <button type="button" class="btn btn-success w-100" id="modalCheckoutBtn"><i class="bi bi-credit-card"></i> Langsung Checkout</button>
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
            }
        }

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

        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                if(this.classList.contains('active')) {
                    this.style.color = '#ff4757';
                } else {
                    this.style.color = '#999';
                }
            });
        });

        const addToCartModal = new bootstrap.Modal(document.getElementById('addToCartModal'));
        
        // Handle click on product card "Tambah Keranjang" button
        document.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!this.disabled) {
                    e.preventDefault();
                    
                    const productCard = this.closest('.product-card');
                    const productId = this.closest('form').querySelector('input[name="id_produk"]').value;
                    const productName = productCard.querySelector('.product-name').textContent;
                    const productPrice = productCard.querySelector('.product-price').innerHTML; // Get HTML to keep styling
                    const productImage = productCard.querySelector('.product-image').src;
                    const stockText = productCard.querySelector('.product-stock-badge').textContent.trim();
                    const stock = parseInt(stockText.replace(/[^0-9]/g, ''), 10);
                    
                    document.getElementById('modalProductId').value = productId;
                    document.getElementById('modalProductName').textContent = productName;
                    document.getElementById('modalProductPrice').innerHTML = productPrice;
                    document.getElementById('modalProductImage').src = productImage;
                    document.getElementById('modalProductStock').textContent = stockText;
                    
                    const qtyInput = document.getElementById('modalProductQty');
                    qtyInput.value = 1;
                    qtyInput.max = stock;

                    addToCartModal.show();
                }
            });
        });

        // Handle quantity controls in modal
        document.getElementById('qty-plus').addEventListener('click', function() {
            const qtyInput = document.getElementById('modalProductQty');
            let currentValue = parseInt(qtyInput.value);
            const maxStock = parseInt(qtyInput.max);
            if (currentValue < maxStock) {
                qtyInput.value = currentValue + 1;
            }
        });

        document.getElementById('qty-minus').addEventListener('click', function() {
            const qtyInput = document.getElementById('modalProductQty');
            let currentValue = parseInt(qtyInput.value);
            if (currentValue > 1) {
                qtyInput.value = currentValue - 1;
            }
        });
        
        // Function to add item to cart via AJAX
        function addItemToCart(andCheckout = false) {
            const productId = document.getElementById('modalProductId').value;
            const quantity = document.getElementById('modalProductQty').value;
            
            const formData = new FormData();
            formData.append('id_produk', productId);
            formData.append('qty', quantity);
            formData.append('ajax', '1');
            
            fetch('keranjang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateCartBadge(data.count);
                    addToCartModal.hide();
                    if (andCheckout) {
                        showNotification('✓ Berhasil! Mengarahkan ke checkout...', 'success');
                        setTimeout(() => {
                           window.location.href = 'checkout.php';
                        }, 1000);
                    } else {
                        showNotification('✓ ' + data.message, 'success');
                    }
                } else {
                     showNotification(data.message || 'Gagal menambahkan produk.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan', 'error');
            });
        }
        
        // Handle clicks on buttons inside the modal
        document.getElementById('modalAddToCartBtn').addEventListener('click', function() {
            addItemToCart(false);
        });

        document.getElementById('modalCheckoutBtn').addEventListener('click', function() {
            addItemToCart(true);
        });

    </script>
</body>
</html>
