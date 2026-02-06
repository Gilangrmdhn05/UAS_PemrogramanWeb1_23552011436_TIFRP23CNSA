# Rancangan API untuk Aplikasi Mobile Warungku

Berikut adalah daftar endpoint API yang akan digunakan untuk menjembatani aplikasi mobile Flutter dengan backend PHP.

## 1. Autentikasi

### `POST /api/auth.php?action=register`
- **Deskripsi:** Mendaftarkan user baru.
- **Request Body:**
  ```json
  {
    "nama": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "no_hp": "081234567890",
    "alamat": "Jalan Raya No. 123"
  }
  ```
- **Response (Success):**
  ```json
  {
    "status": "success",
    "message": "Registrasi berhasil."
  }
  ```

### `POST /api/auth.php?action=login`
- **Deskripsi:** Login user.
- **Request Body:**
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Response (Success):**
  ```json
  {
    "status": "success",
    "message": "Login berhasil.",
    "data": {
      "user_id": 1,
      "nama": "John Doe",
      "email": "john@example.com",
      "token": "xxxxxxxxxxxxx"
    }
  }
  ```

## 2. Produk

### `GET /api/produk.php`
- **Deskripsi:** Mendapatkan semua daftar produk.
- **Query Params:** `kategori={id_kategori}` (opsional), `search={keyword}` (opsional).
- **Response (Success):**
  ```json
  {
    "status": "success",
    "data": [
      {
        "id_produk": 1,
        "nama_produk": "Produk A",
        "harga": 15000,
        "stok": 100,
        "gambar": "url/to/image.jpg"
      }
    ]
  }
  ```

### `GET /api/produk.php?id={id_produk}`
- **Deskripsi:** Mendapatkan detail satu produk.
- **Response (Success):**
  ```json
  {
    "status": "success",
    "data": {
      "id_produk": 1,
      "nama_produk": "Produk A",
      "harga": 15000,
      "deskripsi": "Deskripsi lengkap produk A.",
      "stok": 100,
      "gambar": "url/to/image.jpg"
    }
  }
  ```

## 3. Keranjang Belanja (Cart)

### `GET /api/keranjang.php?user_id={user_id}`
- **Deskripsi:** Melihat isi keranjang user.
- **Response (Success):**
  ```json
  {
    "status": "success",
    "data": [
      {
        "id_produk": 1,
        "nama_produk": "Produk A",
        "harga": 15000,
        "jumlah": 2,
        "subtotal": 30000
      }
    ]
  }
  ```

### `POST /api/keranjang.php`
- **Deskripsi:** Menambah/update produk di keranjang.
- **Request Body:**
  ```json
  {
    "user_id": 1,
    "produk_id": 1,
    "jumlah": 1
  }
  ```
- **Response (Success):**
  ```json
  {
    "status": "success",
    "message": "Produk ditambahkan ke keranjang."
  }
  ```

### `DELETE /api/keranjang.php`
- **Deskripsi:** Menghapus produk dari keranjang.
- **Request Body:**
  ```json
  {
    "user_id": 1,
    "produk_id": 1
  }
  ```
- **Response (Success):**
  ```json
  {
    "status": "success",
    "message": "Produk dihapus dari keranjang."
  }
  ```

## 4. Pesanan (Order)

### `POST /api/order.php`
- **Deskripsi:** Membuat pesanan baru dari item di keranjang.
- **Request Body:**
  ```json
  {
    "user_id": 1,
    "metode_pembayaran": "COD"
    "catatan": "Catatan untuk penjual"
  }
  ```
- **Response (Success):**
  ```json
  {
    "status": "success",
    "message": "Pesanan berhasil dibuat.",
    "data": {
      "order_id": "INV123456"
    }
  }
  ```

### `GET /api/order.php?user_id={user_id}`
- **Deskripsi:** Mendapatkan riwayat pesanan user.
- **Response (Success):**
  ```json
  {
    "status": "success",
    "data": [
      {
        "id_pesanan": "INV123456",
        "tanggal_pesanan": "2024-08-01 10:00:00",
        "total_bayar": 50000,
        "status_pesanan": "Sedang diproses"
      }
    ]
  }
  ```

### `GET /api/order.php?id={id_pesanan}`
- **Deskripsi:** Mendapatkan detail pesanan.
- **Response (Success):**
  ```json
  {
    "status": "success",
    "data": {
      "id_pesanan": "INV123456",
      "tanggal_pesanan": "2024-08-01 10:00:00",
      "total_bayar": 50000,
      "status_pesanan": "Sedang diproses",
      "produk": [
        {
          "nama_produk": "Produk A",
          "jumlah": 2,
          "harga": 15000
        }
      ]
    }
  }
  ```
