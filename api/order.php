<?php
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$response = ['status' => false, 'message' => 'An unexpected server error occurred.'];
$conn = null;

try {
    require '../config/database.php';
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $request_method = $_SERVER["REQUEST_METHOD"];

    switch ($request_method) {
        case 'POST':
    $input = file_get_contents("php://input");
    if (empty($input)) throw new Exception("Input data is empty.");
    $data = json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Invalid JSON format: " . json_last_error_msg());

    // --- VALIDATION ---
    if (!isset($data->user_id) || !isset($data->metode_pembayaran) || !isset($data->items) || !is_array($data->items) || empty($data->items)) {
        throw new Exception('user_id, metode_pembayaran, and a non-empty items array are required.');
    }
    // Use the address from the request, or fallback to the user's default address
    $shipping_address = $data->shipping_address ?? ''; 

    $conn->begin_transaction();
    
    $user_id = intval($data->user_id);
    $metode_pembayaran = $data->metode_pembayaran;

    /`/ --- DATA FETCHING ---`
    /`/ Fetch user details for name and phone number`
    $stmt_user = $conn->prepare("SELECT nama, no_hp, alamat FROM users WHERE id_user = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    if ($user_result->num_rows === 0) throw new Exception("User tidak ditemukan.");
    $user = $user_result->fetch_assoc();

    /`/ If shipping address from request is empty`, use user's default address
    if (empty($shipping_address)) {
        $shipping_address = $user['alamat'];
    }
    
    // The items are now from the request body
    $cart_items = $data->items;

    // --- CALCULATION ---
    // Recalculate total on the server to ensure price integrity
    $total_produk = 0;
    $product_ids = array_map(fn($item) => intval($item->product_id), $cart_items);
    $sql_product_prices = "SELECT id_produk, harga FROM produk WHERE id_produk IN (" . implode(',', $product_ids) . ")";
    $prices_result = $conn->query($sql_product_prices);
    $server_prices = [];
    while($row = $prices_result->fetch_assoc()) {
        $server_prices[$row['id_produk']] = $row['harga'];
    }

    $final_cart_items = [];
    foreach ($cart_items as $item) {
        $product_id = intval($item->product_id);
        $quantity = intval($item->quantity);
        if (!isset($server_prices[$product_id])) {
            throw new Exception("Produk dengan ID $product_id tidak ditemukan.");
        }
        $price = $server_prices[$product_id];
        $total_produk += $price * $quantity;
        $final_cart_items[] = [
            'product_id' => $product_id,
            'price' => $price,
            'quantity' => $quantity
        ];
    }

    // --- DATABASE INSERTION ---
    $stmt_order = $conn->prepare("INSERT INTO pesanan (id_user, nama_penerima, no_telepon_penerima, alamat_pengiriman, total_produk, total_bayar, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_order->bind_param("isssiis", $user_id, $user['nama'], $user['no_hp'], $shipping_address, $total_produk, $total_produk, $metode_pembayaran);
    $stmt_order->execute();
    $id_pesanan_baru = $conn->insert_id;
    if ($id_pesanan_baru === 0) {
        throw new Exception("Gagal membuat pesanan baru.");
    }

    $stmt_detail = $conn->prepare("INSERT INTO pesanan_detail (id_pesanan, id_produk, harga, qty) VALUES (?, ?, ?, ?)");
    foreach ($final_cart_items as $item) {
        $stmt_detail->bind_param("iiii", $id_pesanan_baru, $item['product_id'], $item['price'], $item['quantity']);
        $stmt_detail->execute();
    }

    // The logic to clear the server-side cart is no longer needed, as the cart is on the client.
    
    $conn->commit();
    $response = ['status' => true, 'message' => 'Pesanan berhasil dibuat.', 'data' => ['order_id' => $id_pesanan_baru]];
    http_response_code(201);
    break;

        case 'GET':
            if (isset($_GET['id_pesanan'])) {
                $id_pesanan = intval($_GET['id_pesanan']);
                $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
                $stmt->bind_param("i", $id_pesanan);
                $stmt->execute();
                $pesanan = $stmt->get_result()->fetch_assoc();

                if ($pesanan) {
                    $stmt_detail = $conn->prepare("SELECT pd.harga, pd.qty, p.nama_produk, p.gambar FROM pesanan_detail pd JOIN produk p ON pd.id_produk = p.id_produk WHERE pd.id_pesanan = ?");
                    $stmt_detail->bind_param("i", $id_pesanan);
                    $stmt_detail->execute();
                    $pesanan['produk'] = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
                    $response = ['status' => true, 'data' => $pesanan];
                } else {
                    throw new Exception('Pesanan tidak ditemukan.');
                }
            } elseif (isset($_GET['user_id'])) {
                $user_id = intval($_GET['user_id']);
                $stmt = $conn->prepare("SELECT id_pesanan, tanggal, total_bayar, status_pesanan FROM pesanan WHERE id_user = ? ORDER BY tanggal DESC");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $response = ['status' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
            } else {
                throw new Exception('Parameter id_pesanan atau user_id diperlukan.');
            }
            break;

        default:
            throw new Exception('Metode tidak diizinkan.');
            break;
    }

} catch (Throwable $e) {
    if ($conn && $conn->thread_id) { // Check if connection is active
        $conn->rollback(); // Rollback transaction on error
    }
    if (!headers_sent()) {
        http_response_code(400);
    }
    $response = ['status' => false, 'message' => $e->getMessage()];
} finally {
    if ($conn) {
        $conn->close();
    }
    echo json_encode($response);
}
?>
