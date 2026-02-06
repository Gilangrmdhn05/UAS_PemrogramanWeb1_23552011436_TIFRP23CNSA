<?php
session_start();
include "../config/database.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

/* ===== TAMBAH KE KERANJANG ===== */
if (isset($_POST['id_produk'])) {
    $id = $_POST['id_produk'];
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    // Get product with flash sale info
    $produk = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT produk.*,
               COALESCE(fs.harga_diskon, produk.harga) as harga_tampil,
               CASE 
                   WHEN fs.id_flash IS NOT NULL 
                        AND fs.status = 'aktif'
                        AND fs.waktu_mulai <= NOW()
                        AND fs.waktu_selesai >= NOW() 
                   THEN 1 
                   ELSE 0 
               END as is_flash_sale
        FROM produk
        LEFT JOIN flash_sale fs ON produk.id_produk = fs.id_produk
        WHERE produk.id_produk='$id'
    "));

    // Use discount price if flash sale is active, otherwise use normal price
    $harga = $produk['is_flash_sale'] ? $produk['harga_tampil'] : $produk['harga'];

    if (!isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id] = [
            'id_produk' => $id,
            'nama' => $produk['nama_produk'],
            'harga' => $harga,
            'qty' => $qty,
            'gambar' => $produk['gambar']
        ];
    } else {
        $_SESSION['keranjang'][$id]['qty'] += $qty;
    }

    // Check if AJAX request
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        $cart_count = 0;
        foreach ($_SESSION['keranjang'] as $item) {
            $cart_count += $item['qty'];
        }
        echo json_encode(['status' => 'success', 'count' => $cart_count, 'message' => 'Produk ditambahkan ke keranjang']);
        exit;
    }

    header("Location: keranjang.php");
}

/* ===== UPDATE QTY ===== */
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $id => $jumlah) {
        if ($jumlah == 0) {
            unset($_SESSION['keranjang'][$id]);
        } else {
            $_SESSION['keranjang'][$id]['qty'] = $jumlah;
        }
    }
}

/* ===== HAPUS ITEM ===== */
if (isset($_GET['hapus'])) {
    unset($_SESSION['keranjang'][$_GET['hapus']]);
    
    // Check if AJAX request
    if (!empty($_GET['ajax'])) {
        header('Content-Type: application/json');
        $cart_count = 0;
        if (isset($_SESSION['keranjang'])) {
            foreach ($_SESSION['keranjang'] as $item) {
                $cart_count += $item['qty'];
            }
        }
        echo json_encode(['status' => 'success', 'count' => $cart_count, 'message' => 'Produk dihapus dari keranjang']);
        exit;
    }
    
    header("Location: keranjang.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Keranjang - Warungku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f7fa;
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
    .cart-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .cart-item {
        transition: background-color 0.2s;
    }
    .cart-item:hover {
        background-color: #f8f9fa;
    }
    .product-img-cart {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
    }
    .qty-input {
        max-width: 60px;
        text-align: center;
        border: none;
        background: #f1f2f6;
        font-weight: 600;
    }
    .btn-qty {
        background: #f1f2f6;
        border: none;
        color: #2d3436;
        font-weight: bold;
        width: 30px;
    }
    .btn-qty:hover {
        background: #e2e6ea;
    }
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
<div class="container">
    <a class="navbar-brand" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
    <div class="navbar-nav ms-auto">
        <a class="nav-link" href="home.php">Home</a>
        <a class="nav-link active" href="#">Keranjang</a>
        <a class="nav-link" href="pesanan.php">Pesanan</a>
        <a class="nav-link" href="profil.php">Profil</a>
        
    </div>
</div>
</nav>

<div class="container py-5">
    <div class="d-flex align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-cart3 text-success me-2"></i> Keranjang Belanja</h3>
        <span class="badge bg-secondary ms-3 rounded-pill"><?= count($_SESSION['keranjang'] ?? []) ?> Item</span>
    </div>

<?php if (empty($_SESSION['keranjang'])) { ?>
    <div class="text-center py-5 cart-card bg-white">
        <div class="mb-4">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #dee2e6;"></i>
        </div>
        <h4 class="text-muted">Keranjang belanja Anda kosong</h4>
        <p class="text-muted mb-4">Yuk, isi dengan barang-barang impianmu!</p>
        <a href="home.php" class="btn btn-success px-4 py-2 rounded-pill fw-bold">Mulai Belanja</a>
    </div>
<?php } else { ?>

<form method="POST">
    <div class="row g-4">
        <!-- Cart Items Column -->
        <div class="col-lg-8">
            <div class="cart-card bg-white">
                <div class="card-body p-0">
                    <?php
                    $total = 0;
                    foreach ($_SESSION['keranjang'] as $id => $item) {
                        $sub = $item['harga'] * $item['qty'];
                        $total += $sub;
                        
                        // Handle image path
                        $img_src = "https://via.placeholder.com/80?text=Produk";
                        if (!empty($item['gambar'])) {
                            $path = "../assets/images/produk/" . $item['gambar'];
                            if (file_exists($path)) {
                                $img_src = $path;
                            }
                        }
                    ?>
                    <div class="cart-item p-3 border-bottom" data-id="<?= $id ?>">
                        <div class="row align-items-center">
                            <div class="col-3 col-md-2">
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="product-img-cart">
                            </div>
                            <div class="col-9 col-md-4">
                                <h6 class="mb-1 fw-bold text-dark text-decoration-none"><?= htmlspecialchars($item['nama']) ?></h6>
                                <div class="text-success fw-bold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                            </div>
                            <div class="col-6 col-md-3 mt-3 mt-md-0">
                                <div class="input-group input-group-sm flex-nowrap" style="width: 100px;">
                                    <button type="button" class="btn btn-qty btn-minus">-</button>
                                    <input type="number" name="qty[<?= $id ?>]" value="<?= $item['qty'] ?>" class="form-control qty-input" min="1">
                                    <button type="button" class="btn btn-qty btn-plus">+</button>
                                </div>
                            </div>
                            <div class="col-4 col-md-2 mt-3 mt-md-0 text-end">
                                <span class="fw-bold text-dark">Rp <?= number_format($sub, 0, ',', '.') ?></span>
                            </div>
                            <div class="col-2 col-md-1 mt-3 mt-md-0 text-end">
                                <button type="button" class="btn btn-link text-danger p-0 btn-hapus" data-id="<?= $id ?>" title="Hapus">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="home.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
            </div>
        </div>

        <!-- Summary Column -->
        <div class="col-lg-4">
            <div class="cart-card bg-white p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-4">Ringkasan Belanja</h5>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Harga (<?= count($_SESSION['keranjang']) ?> barang)</span>
                    <span class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>
                
                <hr class="my-3">
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">Total Tagihan</span>
                    <span class="fw-bold fs-5 text-success">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="update" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-arrow-repeat"></i> Update Keranjang
                    </button>
                    <a href="checkout.php" class="btn btn-success fw-bold py-2 shadow-sm">
                        Checkout Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</form>
<?php } ?>

</div>

<script>
    // Handle delete button click
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                const id = this.dataset.id;
                
                fetch('keranjang.php?hapus=' + id + '&ajax=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove row from table
                            const row = document.querySelector(`[data-id="${id}"]`);
                            if (row) {
                                row.remove();
                            }
                            
                            // Update cart badge in parent window/home page
                            if (window.opener) {
                                window.opener.updateCartBadge(data.count);
                            }
                            
                            // Refresh page to update total
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan');
                    });
            }
        });
    });

    // Handle Quantity Buttons
    document.querySelectorAll('.btn-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
        });
    });

    document.querySelectorAll('.btn-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
</script>

</body>
</html>
