<?php
session_start();
include "../config/database.php";

// Authenticate user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Redirect if payment method was not submitted
if (empty($_SESSION['keranjang']) || !isset($_POST['metode_pembayaran'])) {
    header("Location: payment.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$metode_pembayaran = $_POST['metode_pembayaran'];
$shipping_details = $_SESSION['shipping_details'];
$order_totals = $_SESSION['order_totals'];

// ===== REAL ORDER PROCESSING =====
// Start transaction
mysqli_query($conn, "START TRANSACTION");

try {
    // Store all order IDs for final display
    // $all_order_ids = []; // Kita ubah jadi single order ID
    
    // 1. Buat SATU pesanan untuk semua produk (Gabungkan)
    $nama_penerima = mysqli_real_escape_string($conn, $shipping_details['nama_penerima']);
    $no_telepon_penerima = mysqli_real_escape_string($conn, $shipping_details['no_telepon_penerima']);
    $alamat_pengiriman = mysqli_real_escape_string($conn, $shipping_details['alamat_pengiriman']);
    $metode_pembayaran_escaped = mysqli_real_escape_string($conn, $metode_pembayaran);
    
    // Ambil total dari session yang sudah dihitung di payment.php
    $total_produk_all = $order_totals['total_produk'];
    $biaya_pengiriman = $order_totals['biaya_pengiriman'];
    $total_bayar_all = $order_totals['total_bayar'];

    $insert_pesanan = "INSERT INTO pesanan (id_user, nama_penerima, no_telepon_penerima, alamat_pengiriman, total_produk, biaya_pengiriman, total_bayar, metode_pembayaran, status_pesanan) 
                      VALUES ('$id_user', '$nama_penerima', '$no_telepon_penerima', '$alamat_pengiriman', '$total_produk_all', '$biaya_pengiriman', '$total_bayar_all', '$metode_pembayaran_escaped', 'menunggu_pembayaran')";
    
    if (!mysqli_query($conn, $insert_pesanan)) {
        throw new Exception("Gagal menyimpan pesanan: " . mysqli_error($conn));
    }
    
    $id_pesanan = mysqli_insert_id($conn);
    $all_order_ids = [$id_pesanan]; // Simpan dalam array agar kompatibel dengan konfirmasi_order.php

    // 2. Insert detail untuk setiap produk dalam keranjang
    foreach ($_SESSION['keranjang'] as $key => $item) {
        $item_id_produk = isset($item['id_produk']) ? (int)$item['id_produk'] : (int)$key;
        $item_harga = (int)$item['harga'];
        $item_qty = (int)$item['qty'];
        $item_nama = mysqli_real_escape_string($conn, $item['nama']);
        $item_gambar = isset($item['gambar']) ? mysqli_real_escape_string($conn, $item['gambar']) : '';

        $insert_detail = "INSERT INTO pesanan_detail (id_pesanan, id_produk, nama_produk, gambar, harga, qty) 
                         VALUES ('$id_pesanan', '$item_id_produk', '$item_nama', '$item_gambar', '$item_harga', '$item_qty')";
        
        if (!mysqli_query($conn, $insert_detail)) {
            throw new Exception("Gagal menyimpan detail pesanan: " . mysqli_error($conn));
        }

        // 3. Update product stock
        $update_stok = "UPDATE produk SET stok = stok - $item_qty WHERE id_produk = '$item_id_produk'";
        
        if (!mysqli_query($conn, $update_stok)) {
            throw new Exception("Gagal mengupdate stok produk ID: " . $item_id_produk);
        }
    }

    // 4. Generate payment code based on method
    $kode_pembayaran = '';
    $instruksi_pembayaran = '';

    switch ($metode_pembayaran) {
        case 'Transfer Bank (Virtual Account)':
            $kode_pembayaran = '8808' . str_pad($id_pesanan, 10, "0", STR_PAD_LEFT);
            $instruksi_pembayaran = 'Silakan transfer ke nomor Virtual Account di atas sebelum batas waktu pembayaran.';
            break;
        case 'E-Wallet (QRIS)':
            $kode_pembayaran = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://gilang.com';
            $instruksi_pembayaran = 'Silakan pindai kode QR di atas menggunakan aplikasi E-Wallet Anda (GoPay, OVO, DANA, dll).';
            break;
        case 'Bayar di Tempat (COD)':
            $kode_pembayaran = 'Tidak ada kode pembayaran untuk COD.';
            $instruksi_pembayaran = 'Pesanan Anda akan segera diproses. Mohon siapkan uang pas sejumlah total tagihan saat kurir kami tiba.';
            break;
    }

    // 5. Store all relevant info in a final order session variable
    $_SESSION['final_order'] = [
        'order_ids' => $all_order_ids,
        'order_id' => "INV-" . time() . "-" . $id_user,
        'tanggal' => date('d F Y, H:i'),
        'items' => $_SESSION['keranjang'],
        'shipping_details' => $shipping_details,
        'order_totals' => $order_totals,
        'metode_pembayaran' => $metode_pembayaran,
        'kode_pembayaran' => $kode_pembayaran,
        'instruksi_pembayaran' => $instruksi_pembayaran,
        'status_pembayaran' => 'menunggu'
    ];

    // Commit the transaction
    mysqli_query($conn, "COMMIT");

    // 6. Clear the shopping cart session
    unset($_SESSION['keranjang']);
    unset($_SESSION['shipping_details']);
    unset($_SESSION['order_totals']);

    // 7. Redirect to confirmation page
    header("Location: konfirmasi_order.php");
    exit;

} catch (Exception $e) {
    // An error occurred; rollback the transaction
    mysqli_query($conn, "ROLLBACK");

    // Log the error
    error_log("Order processing failed: " . $e->getMessage());

    // Redirect to an error page or back to the cart
    $_SESSION['error_message'] = "Terjadi kesalahan saat memproses pesanan Anda: " . $e->getMessage();
    header("Location: keranjang.php");
    exit;
}

?>
