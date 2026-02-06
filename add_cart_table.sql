-- Perintah SQL untuk membuat tabel keranjang
-- Jalankan ini di database 'warungku' Anda, misalnya melalui phpMyAdmin.

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_keranjang`),
  UNIQUE KEY `user_produk` (`id_user`,`id_produk`),
  KEY `id_user` (`id_user`),
  KEY `id_produk` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menambahkan constraint foreign key untuk relasi
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;
