<?php
include "../config/database.php";
include "layout/header.php";
include "layout/sidebar.php";

/* Ambil data untuk summary */
$produk = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM produk"));
$kategori = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM kategori"));
$user = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users"));
$pesanan = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pesanan"));

// Ambil data status pesanan
$status_count = [];
$status_pesanan = mysqli_query($conn,"SELECT status_pesanan, COUNT(*) as total FROM pesanan GROUP BY status_pesanan");
if($status_pesanan) {
    while($s = mysqli_fetch_assoc($status_pesanan)){
        $status_count[$s['status_pesanan']] = $s['total'];
    }
}

// Pesanan berhasil (selesai)
$pesanan_selesai = $status_count['selesai'] ?? 0;

// Total revenue
$revenue_result = mysqli_query($conn,"SELECT SUM(pd.harga * pd.qty) as total_revenue FROM pesanan p JOIN pesanan_detail pd ON p.id_pesanan = pd.id_pesanan WHERE p.status_pesanan='selesai'");
$revenue_row = mysqli_fetch_assoc($revenue_result);
$total_revenue = ($revenue_row && isset($revenue_row['total_revenue'])) ? intval($revenue_row['total_revenue']) : 0;

/* Ambil data untuk grafik bulanan pesanan */
$bulan = [];
$jumlah_pesanan = [];
$jumlah_pendapatan = [];
for($m=1;$m<=12;$m++){
    $bulan[] = date('M', mktime(0,0,0,$m,1));
    
    // Jumlah pesanan
    $q = mysqli_query($conn,"SELECT COUNT(*) as total FROM pesanan WHERE MONTH(tanggal)=$m");
    $r = ($q) ? mysqli_fetch_assoc($q) : null;
    $jumlah_pesanan[] = ($r && isset($r['total'])) ? intval($r['total']) : 0;
    
    // Pendapatan
    $q2 = mysqli_query($conn,"SELECT SUM(pd.harga * pd.qty) as revenue FROM pesanan p JOIN pesanan_detail pd ON p.id_pesanan = pd.id_pesanan WHERE p.status_pesanan='selesai' AND MONTH(p.tanggal)=$m");
    $r2 = ($q2) ? mysqli_fetch_assoc($q2) : null;
    $jumlah_pendapatan[] = ($r2 && isset($r2['revenue'])) ? intval($r2['revenue']) : 0;
}

// Kategori populer
$kategori_data = [];
$kategori_query = mysqli_query($conn,"SELECT k.nama_kategori, COUNT(p.id_produk) as total FROM kategori k LEFT JOIN produk p ON k.id_kategori = p.id_kategori GROUP BY k.id_kategori LIMIT 6");
$kat_names = [];
$kat_counts = [];
if($kategori_query) {
    while($k = mysqli_fetch_assoc($kategori_query)){
        $kat_names[] = $k['nama_kategori'];
        $kat_counts[] = (int)$k['total'];
    }
}
?>

<h4 class="mb-4"><i class="bi bi-bar-chart me-2"></i>Dashboard Admin</h4>

<!-- STAT CARDS dengan Gradien -->
<div class="row g-3 mb-4">
<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">Produk</h6>
      <h2 class="mb-0"><?= $produk ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 6.5l6-3 6 3v5l-6 3-6-3v-5z"></path><path d="M8 3.5v9"></path></svg></div>
  </div>
</div>

<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">Kategori</h6>
      <h2 class="mb-0"><?= $kategori ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 9.5L9 3l4 4-6.5 6.5L2.5 9.5z"></path><circle cx="11.5" cy="5" r="0.6"/></svg></div>
  </div>
</div>

<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">User</h6>
      <h2 class="mb-0"><?= $user ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="5" cy="6" r="1.6"></circle><circle cx="11" cy="6" r="1.6"></circle><path d="M2 13c1.6-2 4-3 6-3s4.5 1 6 3"></path></svg></div>
  </div>
</div>

<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">Pesanan</h6>
      <h2 class="mb-0"><?= $pesanan ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="12" height="10" rx="1"></rect><path d="M6 6h6"></path><path d="M6 9h6"></path></svg></div>
  </div>
</div>
</div>

<!-- ROW 2: Revenue & Selesai -->
<div class="row g-3 mb-4">
<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">Total Pendapatan</h6>
      <h2 class="mb-0">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1.5" y="4" width="13" height="8" rx="1.2"></rect><circle cx="12" cy="8" r="0.7"></circle></svg></div>
  </div>
</div>

<div class="col-md-3">
  <div class="stat-card light">
    <div>
      <h6 class="mb-2">Pesanan Selesai</h6>
      <h2 class="mb-0"><?= $pesanan_selesai ?></h2>
    </div>
    <div class="icon"><svg class="stat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="6"></circle><path d="M6 8.5l1.5 1.6L10 6.8"></path></svg></div>
  </div>
</div>
</div>

<!-- CHARTS SECTION -->
<div class="row g-3 mb-4">
<!-- Grafik Pesanan & Revenue -->
<div class="col-lg-8">
<div class="card shadow border-0">
<div class="card-header bg-gradient green">
<h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Pesanan & Pendapatan Bulanan</h5>
</div>
<div class="card-body">
<canvas id="grafikPesananRevenue" height="80"></canvas>
</div>
</div>
</div>

<!-- Status Pesanan Pie -->
<div class="col-lg-4">
<div class="card shadow border-0">
<div class="card-header bg-gradient teal">
<h5 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Status Pesanan</h5>
</div>
<div class="card-body">
<canvas id="statusChart" height="100"></canvas>
</div>
</div>
</div>
</div>

<!-- Kategori -->
<div class="row g-3">
<div class="col-lg-6">
<div class="card shadow border-0">
<div class="card-header bg-gradient teal">
<h5 class="mb-0"><i class="bi bi-tags me-2"></i>Produk per Kategori</h5>
</div>
<div class="card-body">
<canvas id="kategoriChart" height="60"></canvas>
</div>
</div>
</div>

<!-- Placeholder untuk perluasan -->
<div class="col-lg-6">
<div class="card shadow border-0">
<div class="card-header bg-gradient green">
<h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Flash Sale Aktif</h5>
</div>
<div class="card-body">
<?php
$flash_sale = mysqli_query($conn,"SELECT id_flash_sale, p.nama_produk, fs.harga_flash, p.harga FROM flash_sale fs JOIN produk p ON fs.id_produk = p.id_produk WHERE fs.is_active=1 AND fs.selesai >= NOW()");
if($flash_sale && mysqli_num_rows($flash_sale) > 0){
    echo '<div class="list-group">';
    while($fs = mysqli_fetch_assoc($flash_sale)){
        $diskon = round(((int)$fs['harga'] - (int)$fs['harga_flash']) / (int)$fs['harga'] * 100);
        echo '<div class="list-group-item border-0 d-flex justify-content-between align-items-center">
            <span>'.$fs['nama_produk'].'</span>
            <span class="badge bg-danger rounded-pill">'.$diskon.'%</span>
        </div>';
    }
    echo '</div>';
} else {
    echo '<p class="text-muted text-center">Tidak ada flash sale aktif</p>';
}
?>
</div>
</div>
</div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Default color scheme yang keren
Chart.defaults.color = '#053024';
Chart.defaults.borderColor = '#e6eef2';
Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Helvetica Neue', sans-serif";

// Grafik Pesanan & Revenue (Kombinasi Bar + Line)
const ctxPesanan = document.getElementById('grafikPesananRevenue').getContext('2d');
new Chart(ctxPesanan, {
    type: 'bar',
    data: {
        labels: <?= json_encode($bulan) ?>,
        datasets: [
            {
                label: 'Jumlah Pesanan',
                data: <?= json_encode($jumlah_pesanan) ?>,
                backgroundColor: 'rgba(0, 184, 148, 0.9)',
                borderColor: 'rgba(0, 120, 90, 1)',
                borderWidth: 2,
                borderRadius: 8,
                yAxisID: 'y'
            },
            {
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($jumlah_pendapatan) ?>,
                type: 'line',
                borderColor: 'rgba(124, 58, 237, 1)',
                backgroundColor: 'rgba(124, 58, 237, 0.12)',
                borderWidth: 3,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: 'rgba(124, 58, 237, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: 'Pesanan', color: '#00b894' },
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: { display: true, text: 'Pendapatan', color: '#7c3aed' },
                beginAtZero: true,
                grid: { drawOnChartArea: false }
            }
        },
        plugins: {
            legend: { 
                display: true,
                labels: { padding: 15, font: { size: 13, weight: '500' } }
            }
        }
    }
});

// Status Pesanan - Pie Chart
const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'],
        datasets: [{
            data: [
                <?= $status_count['menunggu_pembayaran'] ?? 0 ?>,
                <?= $status_count['diproses'] ?? 0 ?>,
                <?= $status_count['dikirim'] ?? 0 ?>,
                <?= $status_count['selesai'] ?? 0 ?>,
                <?= $status_count['dibatalkan'] ?? 0 ?>
            ],
            backgroundColor: [
                '#f59e0b',
                '#60a5fa',
                '#7c3aed',
                '#00b894',
                '#ef4444'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 15, font: { size: 12, weight: '500' } }
            }
        }
    }
});

// Kategori - Horizontal Bar Chart
const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
new Chart(ctxKategori, {
    type: 'bar',
    data: {
        labels: <?= json_encode($kat_names) ?>,
        datasets: [{
            label: 'Total Produk',
            data: <?= json_encode($kat_counts) ?>,
            backgroundColor: [
                'rgba(0, 184, 148, 0.85)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(124, 58, 237, 0.8)',
                'rgba(34, 197, 94, 0.7)',
                'rgba(6, 182, 212, 0.7)',
                'rgba(249, 115, 22, 0.8)'
            ],
            borderColor: [
                'rgba(0, 120, 90, 1)',
                'rgba(67, 56, 202, 1)',
                'rgba(99, 102, 241, 1)',
                'rgba(16, 163, 82, 1)',
                'rgba(6, 182, 212, 1)',
                'rgba(194, 65, 12, 1)'
            ],
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

<!-- NOTIFIKASI PESANAN BARU -->
<div id="notification-area" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

<script>
function checkNewOrders() {
    fetch('api_cek_pesanan.php')
        .then(response => response.json())
        .then(data => {
            if(data.status !== 'success') return;

            const lastKnownId = localStorage.getItem('admin_last_order_id');
            const currentId = parseInt(data.last_id);
            
            // Jika belum ada data di localStorage (pertama kali buka), simpan ID saat ini tanpa notif
            if (lastKnownId === null) {
                if (!isNaN(currentId)) {
                    localStorage.setItem('admin_last_order_id', currentId);
                }
                return;
            }

            // Jika ada ID baru yang lebih besar dari yang tersimpan -> Notifikasi Muncul
            if (!isNaN(currentId) && currentId > parseInt(lastKnownId)) {
                showNotification(data.total_pending);
                localStorage.setItem('admin_last_order_id', currentId);
            }
        })
        .catch(error => console.error('Error checking orders:', error));
}

function showNotification(totalPending) {
    const container = document.getElementById('notification-area');
    const toastHtml = `
        <div class="toast show shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto"><i class="bi bi-bell-fill"></i> Pesanan Baru!</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
            </div>
            <div class="toast-body bg-white">
                Ada pesanan baru masuk! <br>
                Total Menunggu: <strong>${totalPending}</strong>
                <div class="mt-2 pt-2 border-top">
                    <a href="pesanan.php" class="btn btn-sm btn-primary w-100">Lihat Pesanan</a>
                </div>
            </div>
        </div>
    `;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = toastHtml;
    container.appendChild(wrapper.firstElementChild);
}

// Cek setiap 10 detik
setInterval(checkNewOrders, 10000);
// Cek saat load halaman
checkNewOrders();
</script>

<?php include "layout/footer.php"; ?>
