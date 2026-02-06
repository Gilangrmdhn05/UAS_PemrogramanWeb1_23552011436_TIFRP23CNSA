# 🔧 FIX DATABASE ISSUE - Nama Produk dan Gambar Tidak Muncul

## 📋 Masalah yang Ditemukan:

1. **Tabel `pesanan` tidak memiliki PRIMARY KEY** - Ini menyebabkan id_pesanan bisa bernilai 0 (invalid)
2. **Ada data pesanan dengan id_pesanan=0** - Data invalid ini tidak bisa di-join dengan produk
3. **Ada pesanan_detail dengan id_produk=0** - Produk tidak ada di database
4. **Query menggunakan LEFT JOIN tanpa kondisi filter id_produk > 0** - Menghasilkan banyak NULL values

## ✅ Solusi yang Dilakukan:

### 1. **Kode PHP Diperbaiki** (`user/pesanan.php` dan `admin/pesanan.php`):
   - ✅ Filter `id_pesanan > 0` untuk menghindari record invalid
   - ✅ Filter `id_produk > 0` di query untuk menghindari produk yang tidak ada
   - ✅ Better error handling dengan `mysqli_real_escape_string()`
   - ✅ Improved image path handling dengan `htmlspecialchars()`
   - ✅ Added `onerror` fallback untuk gambar yang tidak ditemukan

### 2. **Database Perlu Diperbaiki** - PENTING!

Jalankan query SQL berikut di phpMyAdmin:

```sql
-- 1. Tambahkan PRIMARY KEY ke tabel pesanan
ALTER TABLE `pesanan` ADD PRIMARY KEY IF NOT EXISTS (`id_pesanan`);

-- 2. Set AUTO_INCREMENT untuk pesanan agar dimulai dari 1
ALTER TABLE `pesanan` MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- 3. Hapus data pesanan dengan id_pesanan=0 (data invalid)
DELETE FROM `pesanan_detail` WHERE `id_pesanan` = 0;
DELETE FROM `pesanan` WHERE `id_pesanan` = 0 OR `id_pesanan` <= 0;

-- 4. Hapus data pesanan_detail dengan id_produk=0 (produk tidak valid)
DELETE FROM `pesanan_detail` WHERE `id_produk` = 0 OR `id_produk` IS NULL;
```

## 📖 Langkah-Langkah Menjalankan Fix:

### **Cara 1: Menggunakan phpMyAdmin**

1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Pilih database `warungku`
3. Klik tab **SQL**
4. Copy-paste query dari `fix_database.sql`
5. Klik **Go/Execute**

### **Cara 2: Menggunakan Command Line**

```bash
cd c:\xampp\mysql\bin
mysql -u root -p warungku < c:\xampp\htdocs\warungku\fix_database.sql
```

## ✨ Hasil Setelah Fix:

✅ Nama produk akan **tampil dengan benar**
✅ Gambar produk akan **tampil dengan benar**
✅ Tidak ada lagi data invalid di database
✅ Query JOIN akan berfungsi dengan optimal
✅ Error handling lebih baik

## 🧪 Cara Testing:

1. Buat pesanan baru dengan checkout produk
2. Buka halaman **Pesanan** di user panel
3. Verifikasi nama produk dan gambar muncul
4. Cek halaman **Pesanan** di admin panel
5. Semua produk harus tampil dengan nama dan gambar

## ⚠️ Penting:

- **Backup database** sebelum menjalankan query!
- Query ini akan **menghapus data invalid** yang sudah ada
- Setelah fix, pesanan baru akan muncul dengan benar
