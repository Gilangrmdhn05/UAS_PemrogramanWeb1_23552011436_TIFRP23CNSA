<?php
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id_produk = intval($_GET['id']);
        $stmt = $conn->prepare("
            SELECT
                p.id_produk,
                p.id_kategori,
                p.nama_produk,
                p.stok,
                p.deskripsi,
                p.gambar,
                p.harga AS harga_normal,
                CASE
                    WHEN fs.id_flash IS NOT NULL
                         AND fs.status = 'aktif'
                         AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai
                    THEN fs.harga_diskon
                    ELSE p.harga
                END AS harga_final,
                fs.id_flash,
                fs.harga_diskon,
                fs.diskon_persen,
                fs.waktu_mulai,
                fs.waktu_selesai
            FROM
                produk p
            LEFT JOIN
                flash_sale fs ON p.id_produk = fs.id_produk
            WHERE
                p.id_produk = ?
                AND (fs.id_flash IS NULL OR (fs.status = 'aktif' AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai))
        ");
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $produk = $result->fetch_assoc();
            $response = ["status" => true, "data" => $produk];
        } else {
            http_response_code(404);
            throw new Exception("Produk tidak ditemukan.");
        }
        $stmt->close();
    } else {
        $query = "
            SELECT
                p.id_produk,
                p.id_kategori,
                p.nama_produk,
                p.stok,
                p.deskripsi,
                p.gambar,
                p.harga AS harga_normal,
                CASE
                    WHEN fs.id_flash IS NOT NULL
                         AND fs.status = 'aktif'
                         AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai
                    THEN fs.harga_diskon
                    ELSE p.harga
                END AS harga_final,
                fs.id_flash,
                fs.harga_diskon,
                fs.diskon_persen,
                fs.waktu_mulai,
                fs.waktu_selesai
            FROM
                produk p
            LEFT JOIN
                flash_sale fs ON p.id_produk = fs.id_produk
            WHERE
                fs.id_flash IS NULL OR (fs.status = 'aktif' AND NOW() BETWEEN fs.waktu_mulai AND fs.waktu_selesai)
            ORDER BY p.id_produk DESC
        ";
        $q = mysqli_query($conn, $query);

        if (!$q) {
            throw new Exception("Query to fetch all products failed: " . mysqli_error($conn));
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($q)) {
            $data[] = $row;
        }
        $response = ["status" => true, "data" => $data];
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
