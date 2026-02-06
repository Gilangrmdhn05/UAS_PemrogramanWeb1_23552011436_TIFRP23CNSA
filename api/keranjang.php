<?php
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
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
        case 'GET':
            if (!isset($_GET['user_id'])) {
                throw new Exception('user_id tidak ditemukan.');
            }
            $user_id = intval($_GET['user_id']);
            $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/warungku/assets/images/produk/";

            $stmt = $conn->prepare("
                SELECT
                    p.id_produk,
                    p.nama_produk,
                    p.gambar,
                    k.qty,
                    p.harga AS harga_normal,
                    CASE
                        WHEN fs.id_flash IS NOT NULL
                             AND fs.status = 'aktif'
                             AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai
                        THEN fs.harga_diskon
                        ELSE p.harga
                    END AS harga_final
                FROM
                    keranjang k
                JOIN
                    produk p ON k.id_produk = p.id_produk
                LEFT JOIN
                    flash_sale fs ON p.id_produk = fs.id_produk
                WHERE
                    k.id_user = ?
                    AND (fs.id_flash IS NULL OR (fs.status = 'aktif' AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai))
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $keranjang_items = [];
            while ($row = $result->fetch_assoc()) {
                $row['gambar'] = $baseUrl . $row['gambar'];
                $row['harga'] = $row['harga_final']; // Gunakan harga_final untuk kompatibilitas
                $row['subtotal'] = $row['harga_final'] * $row['qty'];
                $keranjang_items[] = $row;
            }
            $response = ['status' => true, 'data' => $keranjang_items];
            $stmt->close();
            break;

        case 'POST':
        case 'DELETE':
            $input = file_get_contents("php://input");
            if (empty($input)) throw new Exception("Input data is empty.");
            $data = json_decode($input);
            if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Invalid JSON format.");

            if ($request_method === 'POST') {
                if (!isset($data->user_id) || !isset($data->produk_id) || !isset($data->qty)) throw new Exception('Data tidak lengkap.');
                
                $qty = intval($data->qty);
                if ($qty <= 0) {
                    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_user = ? AND id_produk = ?");
                    $stmt->bind_param("ii", $data->user_id, $data->produk_id);
                    $stmt->execute();
                    $response = ['status' => true, 'message' => 'Produk dihapus dari keranjang.'];
                } else {
                    $stmt = $conn->prepare("INSERT INTO keranjang (id_user, id_produk, qty) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE qty = ?");
                    $stmt->bind_param("iiii", $data->user_id, $data->produk_id, $qty, $qty);
                    $stmt->execute();
                    $response = ['status' => true, 'message' => 'Keranjang berhasil diperbarui.'];
                }
            } else { // DELETE
                if (!isset($data->user_id) || !isset($data->produk_id)) throw new Exception('user_id atau produk_id tidak ditemukan.');
                
                $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_user = ? AND id_produk = ?");
                $stmt->bind_param("ii", $data->user_id, $data->produk_id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $response = ['status' => true, 'message' => 'Produk berhasil dihapus dari keranjang.'];
                } else {
                    throw new Exception('Produk tidak ditemukan di keranjang.');
                }
            }
            $stmt->close();
            break;

        default:
            throw new Exception('Metode tidak diizinkan.');
            break;
    }
} catch (Throwable $e) {
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
