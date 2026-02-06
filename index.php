<?php
session_start();
include "config/database.php";

// Redirect based on role if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/home.php");
    }
    exit;
}

// Get statistics
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk"))['count'];
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM kategori"))['count'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pesanan"))['count'];

// Get featured products (limit 6)
$produk_unggulan = mysqli_query($conn, "
    SELECT produk.*, kategori.nama_kategori 
    FROM produk 
    LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori 
    ORDER BY produk.id_produk DESC 
    LIMIT 6
");
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
        
        /* Hero Banner */
        .hero-section {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            padding: 50px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .hero-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .hero-text p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            opacity: 0.95;
        }
        
        .btn-hero {
            display: inline-block;
            padding: 12px 35px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary-hero {
            background: white;
            color: #198754;
        }
        
        .btn-primary-hero:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .btn-secondary-hero {
            background: rgba(255,255,255,0.2);
            color: white;
            margin-left: 10px;
            border: 2px solid white;
        }
        
        .btn-secondary-hero:hover {
            background: white;
            color: #198754;
        }
        
        .hero-image {
            font-size: 8rem;
            text-align: right;
            opacity: 0.8;
        }
        
        /* Statistics */
        .stats-section {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #198754;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
            margin-top: 5px;
        }
        
        /* Categories Section */
        .categories-section {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        .categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .categories-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3436;
        }
        
        .category-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: #2d3436;
        }
        
        .category-item:hover {
            background: #f0f0f0;
            transform: translateY(-5px);
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .category-name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        /* Products Section */
        .products-section {
            padding: 30px 0;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3436;
        }
        
        .section-link {
            color: #198754;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .section-link:hover {
            color: #157347;
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
            background: #ff4757;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .product-badge.new {
            background: #198754;
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
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        
        .stars {
            color: #ffc107;
        }
        
        .rating-count {
            color: #999;
            font-size: 0.8rem;
        }
        
        .product-price-section {
            margin-top: auto;
        }
        
        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #198754;
            margin-bottom: 5px;
        }
        
        .product-original-price {
            font-size: 0.85rem;
            color: #999;
            text-decoration: line-through;
            margin-right: 10px;
        }
        
        .product-discount {
            display: inline-block;
            background: #ff4757;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .product-footer {
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
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
        
        /* Features */
        .features-section {
            background: white;
            padding: 40px 0;
            margin: 40px 0;
            border-radius: 10px;
        }
        
        .feature-item {
            text-align: center;
            padding: 20px;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #198754;
            margin-bottom: 12px;
        }
        
        .feature-item h4 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 8px;
        }
        
        .feature-item p {
            color: #666;
            font-size: 0.9rem;
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
        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 1.8rem;
            }
            
            .search-container {
                display: none;
            }
            
            .hero-content {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-image {
                text-align: center;
            }
            
            .btn-secondary-hero {
                display: block;
                margin: 10px 0 0 0;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
            
            .product-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar sticky-top">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="navbar-brand me-4" href="index.php">
                <i class="bi bi-shop"></i> Warungku
            </a>    
            
            <div class="search-container">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Cari produk, merek, dan lainnya">
            </div>
            
            <div class="nav-icons ms-4">
                <a href="auth/login.php" class="btn btn-outline-success btn-sm" style="border-radius: 20px; padding: 8px 25px; font-weight: 600; text-decoration: none;">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="auth/register.php" class="btn btn-success btn-sm" style="border-radius: 20px; padding: 8px 25px; font-weight: 600; text-decoration: none; margin-left: 10px;">
                    <i class="bi bi-person-plus"></i> Daftar
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text text-start">
                    <h1>Belanja Produk Berkualitas</h1>
                    <p>Dapatkan harga terbaik dengan jaminan keaslian produk</p>
                    <a href="auth/login.php" class="btn-hero btn-primary-hero">Mulai Belanja Sekarang</a>
                    <a href="#produk" class="btn-hero btn-secondary-hero">Lihat Produk <i class="bi bi-chevron-right"></i></a>  
                </div>
                <div class="hero-image">
                    <i class="bi bi-bag-check"></i>
                </div>
            </div>
        </div>
    </section>



    <div class="container">
        

        <!-- Categories -->
        <section class="categories-section">
            <div class="categories-header">
                <h3>Kategori Unggulan</h3>
                <a href="#" class="section-link">Lihat Semua <i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-cup-hot"></i></div>
                        <div class="category-name">Minuman</div>
                    </a>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-handbag"></i></div>
                        <div class="category-name">Fashion</div>
                    </a>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-pc-display"></i></div>
                        <div class="category-name">Elektronik</div>
                    </a>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-apple"></i></div>
                        <div class="category-name">Makanan</div>
                    </a>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-heart"></i></div>
                        <div class="category-name">Kecantikan</div>
                    </a>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <a href="#" class="category-item">
                        <div class="category-icon"><i class="bi bi-house"></i></div>
                        <div class="category-name">Rumah</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Products -->
        <section class="products-section" id="produk">
            <div class="section-header">
                <h2 class="section-title">Produk Terbaru</h2>
                <a href="#" class="section-link">Lihat Semua <i class="bi bi-chevron-right"></i></a>
            </div>

            <div class="row g-3">
                <?php if(mysqli_num_rows($produk_unggulan) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($produk_unggulan)): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card">
                                <div class="product-image-wrapper">
                                    <?php 
                                        $foto = isset($row['foto']) ? $row['foto'] : (isset($row['gambar']) ? $row['gambar'] : '');
                                        $image = !empty($foto) ? 'assets/images/produk/' . $foto : 'https://via.placeholder.com/300x200?text=Produk';
                                    ?>
                                    <img src="<?php echo $image; ?>" alt="<?php echo $row['nama_produk']; ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=Produk'">>
                                    
                                    <span class="product-badge new">NEW</span>
                                    <button class="wishlist-btn" title="Tambah ke Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>

                                <div class="product-info">
                                    <h5 class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                                    
                                    <div class="product-rating">
                                        <span class="stars">★★★★★</span>
                                        <span class="rating-count">(128)</span>
                                    </div>

                                    <div class="product-price-section">
                                        <div class="product-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                        
                                    </div>

                                    <div class="product-footer">
                                        <a href="user/detail.php?id=<?php echo $row['id_produk']; ?>" class="btn-product btn-detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="auth/login.php" class="btn-product btn-cart">
                                            <i class="bi bi-bag-plus"></i> Beli
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted" style="font-size: 1.1rem;">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i><br><br>
                            Belum ada produk yang tersedia
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Features -->
        <section class="features-section">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-truck"></i></div>
                        <h4>Pengiriman Gratis</h4>
                        <p>Gratis ongkir ke seluruh Indonesia untuk pembelian minimal</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <h4>Produk Original</h4>
                        <p>100% asli atau uang kembali dijamin</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                        <h4>Mudah Ditukar</h4>
                        <p>Garansi uang kembali dalam 30 hari tanpa pertanyaan</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-headset"></i></div>
                        <h4>Dukungan 24/7</h4>
                        <p>Tim customer service siap membantu Anda kapan saja</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5><i class="bi bi-shop"></i> Warungku</h5>
                        <p style="font-size: 0.9rem; margin-bottom: 15px;">Platform belanja online terpercaya dengan produk berkualitas dan dijamin keaslianya.</p>
                        <div style="font-size: 1.2rem; gap: 10px; display: flex;">
                            <a href="https://www.facebook.com/gilang.rmdhn.1656" target="_blank" style="color: #20c997;"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.instagram.com/gilanggggrh_/" target="_blank" style="color: #20c997;"><i class="bi bi-instagram"></i></a>
                            <a href="https://github.com/Gilangrmdhn05" target="_blank" style="color: #20c997;"><i class="bi bi-github"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Kategori Populer</h5>
                        <ul>
                            <li><a href="#">Fashion Pria</a></li>
                            <li><a href="#">Fashion Wanita</a></li>
                            <li><a href="#">Elektronik</a></li>
                            <li><a href="#">Peralatan Rumah</a></li>
                            <li><a href="#">Perlengkapan Olahraga</a></li>
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
                            <li><a href="#">Kebijakan Pengembalian</a></li>
                            <li><a href="#">Hubungi Kami</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Informasi Kontak</h5>
                        <ul style="gap: 10px; display: flex; flex-direction: column;">
                            <li><i class="bi bi-telephone"></i> +6283131811032</li>
                            <li><i class="bi bi-envelope"></i> ujangbedog024@gmail.com</li>
                            <li><i class="bi bi-geo-alt"></i> Jl. Ciparay No. 23, Bandung</li>
                            <li><i class="bi bi-clock"></i> Buka 24 jam setiap hari</li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                if(this.classList.contains('active')) {
                    this.style.background = '#ff4757';
                    this.style.color = 'white';
                } else {
                    this.style.background = 'white';
                    this.style.color = '#2d3436';
                }
            });
        });

        // Search functionality
        document.querySelector('.search-container input')?.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                // Redirect ke user/home.php dengan parameter search
                window.location.href = 'auth/login.php';
            }
        });

        // Countdown Timer untuk Flash Sale
        function updateCountdown() {
            const jamEl = document.getElementById('jam');
            const menitEl = document.getElementById('menit');
            const detikEl = document.getElementById('detik');
            
            if(!jamEl || !menitEl || !detikEl) return;
            
            let jam = parseInt(jamEl.textContent);
            let menit = parseInt(menitEl.textContent);
            let detik = parseInt(detikEl.textContent);
            
            if(detik > 0) {
                detik--;
            } else if(menit > 0) {
                detik = 59;
                menit--;
            } else if(jam > 0) {
                detik = 59;
                menit = 59;
                jam--;
            } else {
                location.reload();
                return;
            }
            
            jamEl.textContent = String(jam).padStart(2, '0');
            menitEl.textContent = String(menit).padStart(2, '0');
            detikEl.textContent = String(detik).padStart(2, '0');
        }
        
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
