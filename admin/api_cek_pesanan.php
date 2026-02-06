<?php
session_start();
include "../config/database.php";

// Cek akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Ambil jumlah pesanan pending dan ID terakhir
// Kita menggunakan ID terakhir untuk mendeteksi apakah ada pesanan yang benar-benar baru masuk
$query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_pending, 
        MAX(id_pesanan) as last_id 
    FROM pesanan 
    WHERE status_pesanan = 'menunggu_pembayaran'
");

$data = mysqli_fetch_assoc($query);

echo json_encode([
    'status' => 'success',
    'total_pending' => (int)$data['total_pending'],
    'last_id' => (int)$data['last_id']
]);
?>