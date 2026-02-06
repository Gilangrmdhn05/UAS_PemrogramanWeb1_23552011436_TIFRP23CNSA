# 🔧 Troubleshooting: Pesanan Tidak Muncul di Riwayat User

## 📋 Informasi Masalah

**Gejala:**
- ✅ Pesanan ada di database
- ✅ Admin bisa melihat di halaman admin pesanan
- ❌ User tidak bisa melihat di halaman pesanan pribadi

---

## 🔍 Langkah-Langkah Debugging

### **Step 1: Cek Data di Database**

Buka halaman: **`http://localhost/warungku/admin_debug_pesanan.php`**

**Yang harus dilihat:**
1. Scroll ke bagian "Semua User dan Pesanan Mereka"
2. Catat ID user yang memiliki pesanan
3. Catat ID pesanannya

**Contoh:**
```
ID User 6 → Gilang Ramadhan → 3 pesanan
  - Pesanan #1
  - Pesanan #2
  - Pesanan #3
```

---

### **Step 2: Login dengan User dan Cek Session**

1. **Logout dari admin**
2. **Login dengan akun user yang membuat pesanan** (ex: Gilang)
3. Buka halaman: **`http://localhost/warungku/user/check_my_orders.php`**

**Hasil yang diharapkan:**
```
✅ DITEMUKAN 3 Pesanan
```

**Jika muncul:**
```
❌ TIDAK ADA PESANAN
```

Maka ID user saat ini **BERBEDA** dengan ID user di database!

---

### **Step 3: Identifikasi Masalah**

#### **Masalah A: ID User Berbeda**

Contoh:
- Pesanan di database punya `id_user=6`
- Tapi user yang login punya `id_user=9`

**Penyebab:**
- User membuat pesanan dengan akun lain
- Session corrupt

**Solusi:**
- Login dengan akun yang sama saat membuat pesanan
- Atau update database:
  ```sql
  UPDATE pesanan SET id_user=6 WHERE id_pesanan=1;
  ```

#### **Masalah B: Pesanan Ada, Tapi Tidak Muncul di User Panel**

Buka: **`http://localhost/warungku/user/pesanan.php?debug=1`**

**Perhatikan alert:**
- Error query?
- `id_user` berapa yang digunakan?

---

### **Step 4: Verifikasi pada Saat Checkout**

**SEBELUM** checkout, buka halaman ini di browser tab baru:
**`http://localhost/warungku/user/verify_order_data.php`**

**Pastikan:**
- ✅ ID User ada
- ✅ Keranjang tidak kosong
- ✅ Shipping details ada
- ✅ Order totals ada

Jika ada yang ❌, maka proses order akan gagal!

---

## 🛠️ Fix Cepat

### **Jika ID User Tertukar di Database:**

1. Buka phpMyAdmin
2. Masuk database `warungku`
3. Pilih tabel `pesanan`
4. Jalankan query:

```sql
-- Replace 6 dengan ID user yang benar
-- Replace 1,2,3 dengan ID pesanan yang salah
UPDATE pesanan SET id_user=6 WHERE id_pesanan IN (1,2,3);
```

### **Jika Pesanan Masih Tidak Muncul:**

1. Cek apakah `is_hidden=1`
2. Jalankan query:

```sql
UPDATE pesanan SET is_hidden=0 WHERE id_pesanan > 0;
```

---

## 📞 Debugging Checklist

- [ ] Cek database → admin_debug_pesanan.php
- [ ] Catat ID user yang punya pesanan
- [ ] Login dengan user tersebut
- [ ] Buka check_my_orders.php → Apakah pesanan muncul?
- [ ] Jika tidak muncul, buka pesanan.php?debug=1
- [ ] Lihat error message atau informasi di-highlight
- [ ] Fix sesuai error message

---

## 📝 Catatan

Setiap file debug bisa diakses dengan login user biasa:

| URL | Fungsi |
|-----|--------|
| `admin_debug_pesanan.php` | Lihat semua pesanan & user di database |
| `user/check_my_orders.php` | Cek pesanan user yang sedang login |
| `user/pesanan.php?debug=1` | Buka pesanan dengan mode debug |
| `user/verify_order_data.php` | Cek data sebelum checkout |

---

Silakan cek file-file debug ini dan report hasilnya! 🚀
