-- Fix Database Issues
-- 1. Tambahkan PRIMARY KEY ke tabel pesanan
ALTER TABLE `pesanan` ADD PRIMARY KEY IF NOT EXISTS (`id_pesanan`);

-- 2. Set AUTO_INCREMENT untuk pesanan agar dimulai dari 1
ALTER TABLE `pesanan` MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- 3. Hapus data pesanan dengan id_pesanan=0 (data invalid)
DELETE FROM `pesanan_detail` WHERE `id_pesanan` = 0;
DELETE FROM `pesanan` WHERE `id_pesanan` = 0 OR `id_pesanan` <= 0;

-- 4. Hapus data pesanan_detail dengan id_produk=0 (produk tidak valid)
DELETE FROM `pesanan_detail` WHERE `id_produk` = 0 OR `id_produk` IS NULL;

-- 5. Tambahkan FOREIGN KEY constraints jika belum ada
ALTER TABLE `pesanan` ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
ALTER TABLE `pesanan_detail` ADD CONSTRAINT `pesanan_detail_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE;
ALTER TABLE `pesanan_detail` ADD CONSTRAINT `pesanan_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

-- 6. Verifikasi struktur pesanan
SELECT * FROM `pesanan`;
SELECT * FROM `pesanan_detail` pd LEFT JOIN `produk` p ON pd.id_produk = p.id_produk;
