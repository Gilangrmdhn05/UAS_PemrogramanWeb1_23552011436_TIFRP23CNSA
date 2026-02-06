<?php
/**
 * API: Update Cart Count
 * Fungsi: Menghitung total qty di keranjang dari session
 * Digunakan untuk AJAX request dari home.php dan detail.php
 */
session_start();
include "../config/database.php";

header('Content-Type: application/json');

if ($_SESSION['role'] != 'user') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

// Hitung total item di keranjang
$cart_count = 0;
if (isset($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $cart_count += $item['qty'];
    }
}

if ($action === 'get_count') {
    echo json_encode(['status' => 'success', 'count' => $cart_count]);
} 
elseif ($action === 'get_stock') {
    // Get current stock of a product
    $id_produk = $_POST['id_produk'] ?? 0;
    if ($id_produk) {
        $result = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk='$id_produk'");
        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            echo json_encode(['status' => 'success', 'stok' => $data['stok']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        }
    }
}
else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
