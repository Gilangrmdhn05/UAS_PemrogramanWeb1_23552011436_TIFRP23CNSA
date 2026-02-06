<?php
// Set headers and error handling at the very top
ini_set('display_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Prepare a default response
$response = ['status' => 'error', 'message' => 'An unexpected server error occurred.'];
$conn = null;

try {
    require '../config/database.php';

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $action = $_GET['action'] ?? '';
    $input = file_get_contents("php://input");
    
    if (empty($input)) {
        throw new Exception("Input data is empty.");
    }
    $data = json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format.");
    }

    switch ($action) {
        case 'register':
            if (empty($data->nama) || empty($data->email) || empty($data->password) || empty($data->no_hp) || empty($data->alamat)) {
                throw new Exception('Data tidak lengkap.');
            }
            
            $email = $data->email;
            $stmt_check = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                throw new Exception('Email sudah terdaftar.');
            }
            $stmt_check->close();

            $password = password_hash($data->password, PASSWORD_BCRYPT);
            $stmt_insert = $conn->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt_insert->bind_param("sssss", $data->nama, $data->email, $password, $data->no_hp, $data->alamat);

            if ($stmt_insert->execute()) {
                $response = ['status' => 'success', 'message' => 'Registrasi berhasil.'];
            } else {
                throw new Exception('Registrasi gagal.');
            }
            $stmt_insert->close();
            break;

        case 'login':
            if (empty($data->email) || empty($data->password)) {
                throw new Exception('Email dan password tidak boleh kosong.');
            }

            $stmt = $conn->prepare("SELECT id_user, nama, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $data->email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($data->password, $user['password'])) {
                    $token = bin2hex(random_bytes(32));
                    $response = [
                        'status' => 'success',
                        'message' => 'Login berhasil.',
                        'data' => [
                            'user_id' => $user['id_user'],
                            'nama' => $user['nama'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'token' => $token
                        ]
                    ];
                } else {
                    throw new Exception('Password salah.');
                }
            } else {
                throw new Exception('Email tidak ditemukan.');
            }
            $stmt->close();
            break;

        default:
            throw new Exception('Aksi tidak valid.');
            break;
    }

} catch (Throwable $e) {
    http_response_code(400); // Bad Request or other appropriate error code
    $response = ['status' => 'error', 'message' => $e->getMessage()];
} finally {
    if ($conn) {
        $conn->close();
    }
    echo json_encode($response);
}
?>
