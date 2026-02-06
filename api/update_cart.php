<?php
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
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
