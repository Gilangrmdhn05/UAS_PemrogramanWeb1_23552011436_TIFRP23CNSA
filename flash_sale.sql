-- Tabel untuk Flash Sale
CREATE TABLE `flash_sale` (
  `id_flash` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NOT NULL,
  `harga_diskon` int(11) NOT NULL,
  `diskon_persen` int(11) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `status` enum('aktif','nonaktif','selesai') DEFAULT 'nonaktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_flash`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `flash_sale_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
