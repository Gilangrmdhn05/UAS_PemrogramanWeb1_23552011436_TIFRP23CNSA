-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Feb 2026 pada 15.16
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warungku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `flash_sale`
--

CREATE TABLE `flash_sale` (
  `id_flash` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `harga_diskon` int(11) NOT NULL,
  `diskon_persen` int(11) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `status` enum('aktif','nonaktif','selesai') DEFAULT 'nonaktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(6, 'Minuman'),
(7, 'Pakaian Pria'),
(8, 'Elektronik'),
(9, 'Makanan'),
(10, 'Kecantikan'),
(11, 'Rumah'),
(12, 'Olahraga'),
(13, 'Aksesoris Pria'),
(14, 'Aksesoris Wanita'),
(15, 'Pakaian Wanita'),
(16, 'Sepatu Pria'),
(17, 'Sepatu Wanita');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `no_telepon_penerima` varchar(20) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `total_produk` int(11) DEFAULT NULL,
  `biaya_pengiriman` int(11) NOT NULL DEFAULT 0,
  `total_bayar` int(11) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status_pesanan` enum('menunggu_pembayaran','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `status_pembayaran` enum('menunggu','lunas','gagal') NOT NULL DEFAULT 'menunggu',
  `kode_pembayaran` varchar(100) DEFAULT NULL,
  `nomor_resi` varchar(50) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT 0,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_user`, `nama_penerima`, `no_telepon_penerima`, `alamat_pengiriman`, `total_produk`, `biaya_pengiriman`, `total_bayar`, `metode_pembayaran`, `status_pesanan`, `status_pembayaran`, `kode_pembayaran`, `nomor_resi`, `is_hidden`, `tanggal`) VALUES
(23, 6, 'Gilang Ramadhan Herdian', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 52000, 15000, 67000, 'Bayar di Tempat (COD)', 'selesai', 'lunas', NULL, NULL, 1, '2026-01-20 11:04:45'),
(25, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 80000, 15000, 95000, 'Bayar di Tempat (COD)', 'selesai', 'lunas', NULL, NULL, 1, '2026-01-20 12:19:38'),
(26, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 51000, 15000, 66000, 'Transfer Bank (Virtual Account)', 'selesai', 'gagal', NULL, NULL, 1, '2026-01-20 12:20:31'),
(27, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 21000, 15000, 36000, 'Bayar di Tempat (COD)', 'menunggu_pembayaran', 'menunggu', NULL, NULL, 0, '2026-01-20 17:14:13'),
(28, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 58500, 15000, 73500, 'Bayar di Tempat (COD)', 'diproses', 'lunas', NULL, NULL, 0, '2026-01-21 12:38:21'),
(29, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 80000, 15000, 95000, 'Bayar di Tempat (COD)', 'diproses', 'lunas', NULL, NULL, 0, '2026-01-21 17:51:35'),
(30, 6, 'Gilang Rmdhn', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 80000, 30000, 110000, 'E-Wallet (QRIS)', 'dikirim', 'lunas', NULL, NULL, 0, '2026-01-29 12:58:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan_detail`
--

CREATE TABLE `pesanan_detail` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `nama_produk` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan_detail`
--

INSERT INTO `pesanan_detail` (`id_detail`, `id_pesanan`, `id_produk`, `harga`, `qty`, `nama_produk`, `gambar`) VALUES
(60, 23, 14, 52000, 1, 'Threesixty Merchandise \"Music\" Tshirt Hitam', 'sg-11134201-7rdy7-m0fv4q10dopaf0.webp'),
(62, 25, 12, 80000, 1, 'Kaos Futsal', '7a0e677b6eed0c2cf76bce85eba9ca6a.jpg'),
(63, 26, 18, 51000, 1, 'Sevan Kaos Wanita Retro Letters Tshirt Korean Style Baju Kaos Oversize Retro Lengan Pendek', 'sg-11134201-8260s-mjaw71t33v9hfb.webp'),
(64, 27, 13, 21000, 1, 'Kaos polos pria LENGAN PENDEK WARNA WARNI LOKAL ONECK UNISEX', 'id-11134207-7r98w-lwxo3p2zn19m70.webp'),
(65, 28, 16, 58500, 1, 'Nebula Tshirt Vintage BoyPablo Kaos lengan pendek Hitam', 'id-11134207-7qul1-ljmz94ox4qwa48.webp'),
(66, 29, 12, 80000, 1, 'Kaos Futsal', '7a0e677b6eed0c2cf76bce85eba9ca6a.jpg'),
(67, 30, 12, 80000, 1, 'Kaos Futsal', '7a0e677b6eed0c2cf76bce85eba9ca6a.jpg'),
(68, 31, 14, 52000, 1, 'Threesixty Merchandise \"Music\" Tshirt Hitam', 'sg-11134201-7rdy7-m0fv4q10dopaf0.webp');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(150) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `harga`, `stok`, `deskripsi`, `gambar`) VALUES
(12, 12, 'Kaos Futsal', 80000, 116, 'Kaos futsal dengan bahan ringan dan breathable yang nyaman dipakai saat latihan maupun pertandingan. Dibuat dari material berkualitas yang mampu menyerap keringat dengan baik, menjaga tubuh tetap kering dan sejuk selama bermain. Desain sporty dan modern memberikan tampilan keren serta mendukung pergerakan bebas di lapangan. Cocok untuk tim futsal, komunitas, maupun penggunaan harian.', '7a0e677b6eed0c2cf76bce85eba9ca6a.jpg'),
(13, 7, 'Kaos polos pria LENGAN PENDEK WARNA WARNI LOKAL ONECK UNISEX', 21000, 195, 'Cocok untuk Pria dan Wanita (Unisex)\r\nKaos Polos bahan Cotton Polyester 30s Reaktif, bahan langsung terasa adem begitu dipakai dan ga panas lebih tipis dibanding 20s/24s. Cocok digunakan di iklim tropis seperti Indonesia.\r\n\r\n Spesifikasi yang kami tawarkan : \r\n1. POLYESTER 30s Gramasi Kain 150-160, adem, soft, menyerap keringat lembut ga kaku \r\n2. Jahitan Leher : Double Stick \r\n3. Jahitan Pundak : Rantai \r\n4. Jahitan Tangan : Overdeck Kumis (3 Jarum)', 'id-11134207-7r98w-lwxo3p2zn19m70.webp'),
(14, 7, 'Threesixty Merchandise \"Music\" Tshirt Hitam', 52000, 167, 'DETAILS:\r\n- Regular Fit\r\n- Material 100% combed cotton 30s\r\n- Design Unisex\r\n- Dibuat dengan kualitas jahitan dan sablon terbaik\r\n- Sablon Plastisol DTF', 'sg-11134201-7rdy7-m0fv4q10dopaf0.webp'),
(15, 7, 'NEBULA Tshirt Vintage The Warriors Swan Dan Ajax Kaos lengan pendek Hitam', 58500, 265, 'Detail Produk:\r\n\r\n✅Bahan: cotton combed 24s Premium\r\n✅Size: Lokal\r\n✅Lebel \r\n✅Hanteg\r\n\r\nSpesifikasi ukuran kaos: \r\nM: Lebar 50 panjang 72cm \r\nL: Lebar 52× panjang 74cm\r\nXL: Lebar 54× panjang 76Cm\r\nXXL ; 56 Lebar X Tinggi 78 \r\n\r\n READY Silahkan langsung diorder tanpa harus konfirmasi dulu, stok selalu ready Kami hanya menggunakan Kaos Premium 100% cotton combad 24s halus adem lembut, ', 'id-11134207-7qukw-lhz0hgnvu8no50.webp'),
(16, 7, 'Nebula Tshirt Vintage BoyPablo Kaos lengan pendek Hitam', 58500, 198, 'Detail Produk: \r\n\r\n✅ Sablon DTF\r\n✅Bahan: cotton combed 24s \r\n✅Size: Premium \r\n✅Lebel \r\n✅Hanteg\r\n\r\nSpesifikasi ukuran kaos: \r\nM: Lebar 50 panjang 72cm \r\nL: Lebar 52× panjang 74cm\r\nXL: Lebar 54× panjang 76Cm\r\nXXL ; 56 Lebar X Tinggi 78 \r\n\r\nREADY Silahkan langsung diorder tanpa harus konfirmasi dulu, stok selalu ready Kami hanya menggunakan Kaos Premium 100% cotton combad 24s halus adem lembut, ', 'id-11134207-7qul1-ljmz94ox4qwa48.webp'),
(17, 7, 'Kemeja Pria Workshirt Top Man Lengan Pendek Casual Bordir Skena Hitz Tebal Kerja', 83000, 171, 'Kemeja Skena BORDIR /WORKSHIT PRIA \r\nSize M, L, XL XXL (Lokal/Indonesia) \r\nBahan= AMERICAN DRILL TEBAL\r\nJAHITAN RAPIH DAN BORDIR\r\nChart Size \r\n\r\n- M (49 Lebar x 72 Panjang+Lingkar dada 98 )\r\n- L  (51 Lebar x 74 Panjang+Lingkar dada 102 ) \r\n- XL (54 Lebar x 76 Panjang+Lingkar dada 108)\r\n- XXL (57 Lebar x 79 Panjang+Lingkar dada 114)', 'id-11134207-7rbk0-m8x7rm8szda366.webp'),
(18, 15, 'Sevan Kaos Wanita Retro Letters Tshirt Korean Style Baju Kaos Oversize Retro Lengan Pendek', 51000, 197, 'Sevan: Pilih kaos oversize untuk tampil percaya diri dan nyaman setiap saat!\r\nTersedia dalam berbagai warna dan ukuran.', 'sg-11134201-8260s-mjaw71t33v9hfb.webp'),
(19, 15, 'Melda crinkle kemeja kerja wanita atasan polos korean style premium blouse terlaris', 52900, 288, 'MELDA \r\nBahan : Crinkle premium\r\nRante di bisa di lepas pasang\r\nUKURAN\r\nS = p 70cm Lingkar Dada 92 cm (40kg-45kg)\r\nM = p 70cm Lingkar Dada 98cm (45kg-55kg)\r\nL = p 70cm Lingkar Dada 104 cm (55kg-65kg)\r\nXL = p 70cm Lingkar Dada 110 cm (65kg-75kg)', 'id-11134207-7r98p-lw367uieny2650.webp'),
(20, 15, 'Lexander Fashion Set Bare Bears Spandek All Size', 24000, 372, 'Terbuat dari Bahan Spandek\r\nUkuran all size Fit To L ( bisa dipakai untuk tubuh ukuran S - L)\r\nDetail Size Baju: \r\nLingkar Dada: 92 Cm\r\nPanjang Pakaian: 55 Cm\r\nDetail Size Celana:\r\nLingkar Pinggang: 100 Cm\r\nLingkar Paha: 56 Cm\r\nPanjang Celana: 25 Cm', 'id-11134207-7r992-lt0ikby2o9nd35.webp'),
(21, 6, 'Nutri & Beyond Fiber Drink dengan Buah dan Sayur Rasa Mixberry 1 Box isi 16 Sachet', 279000, 100, 'Nutrisi dari 70+ Buah dan Sayur Rasa Mix Berry\r\n1x minum setara dengan konsumsi 70+ buah dan sayur, serat tubuh lengkap, pencernaan lancar. Kontrol berat badan ideal. Tanpa pemanis buatan, Zero sugar with Stevia.\r\n\r\nFat Burner : Bakar lemak jahat jadi energi, BAB lebih lancar \r\nFat Blocker : Ikat lemak jahat, kerja usus lebih ringan\r\nCraving Control : Cerna makan perlahan, kenyang lebih lama\r\n\r\nHALAL INDONESIA', 'id-11134207-81zti-mei4pb9awbgg09.webp'),
(22, 6, 'MINUMAN TEH MANIS GELAS LE VONTEA 220 ML MINUMAN TEH MURAH', 1200, 1266, 'MINUMAN TEH MANIS GELAS LE VONTEA 220 ML MINUMAN TEH MURAH\r\n\r\nISI : 220 ML \r\nHARGA PER PCS', 'id-11134207-7rbk5-m736iljo2r7838.webp'),
(23, 8, 'Lampu LED Tbulb Jumbo Tabung Kapsul Super Terang Putih 5w 10w 15w 20w 25w 30w 45w 55w', 12999, 723, 'Spesifikasi:\r\n - Tipe : LED T bulb\r\n -Daya:5w/10w/15w/20w/30w\r\n - Voltase : 220-240V / 50-60Hz\r\n - Fitting : E27\r\n - Beam Angle : 180°\r\n - Warna : Daylight 6500K\r\n - Lifetime : 10.000 Hours', 'b3d0c0d3506f1b35b95f599c4c2ecaba.webp'),
(24, 8, 'LAMPU LED STRIP SELANG 5050 SMD AC 220V METERAN (1M-100M) OUTDOOR AND INDOOR LAMPU PLAFON LAMPU HIAS', 6500, 1141, 'Lampu LED Selang SMD 5050 High Quality\r\nWarna yang tersedia :PUTIH, BIRU, MERAH, HIJAU, WARM WHITE, PINK.\r\nLED Selang ini cocok diaplikasikan pada penggunaan dengan instalasi diatas puluhan meter tanpa pake ribet ', '70b0382f3c9fb107bb984a56bf0f39d8.webp'),
(25, 9, 'Gehel Mie Lidi 200gr - Makanan Ringan', 15000, 1855, 'Mie Lidi merupakan cemilan dan jajanan legendaris yang banyak digemari dan disukai semua usia terutama remaja.Dengan bumbu taburan khas Gehel yang bikin nagih dan nostalgia. Bisa dijadiin teman gabut, teman nonton,dan sangat recomended untuk cemilan sambil nongkrong bareng temen.\r\nMie Lidi Gehel dengan bumbu super gurih dan nempel dijamin bikin ketagihan!!\r\n\r\nPilihan Rasa :\r\n- Asin\r\n- Pedas Jeruk \r\n- Pedas Biasa\r\n- Pedas Ekstra\r\n- Jagung Bakar\r\n- Balado', 'id-11134207-7rbk8-m9zly9mlq6uo7f.webp'),
(26, 9, 'Snackamz | Cireng ayam suwir isi 10 pedas best seller', 10999, 172, 'CIRENG BASRENG SEMUA YANG DI KIRIM DALAM KEADAAN FRESS \r\n\r\nNikmati kelezatan camilan khas Sunda yang sudah terkenal: Cireng ( Aci Digoreng). Dibuat dari tepung tapioka berkualitas, dipadu dengan bumbu khas yang meresap hingga ke dalam, menghasilkan rasa gurih yang lezat dan tekstur renyah di luar, kenyal di dalam.\r\n\r\nCocok dinikmati sebagai teman minum teh, camilan keluarga, atau ide jualan kembali.', 'id-11134207-81ztq-mf17a9khvifd78.webp'),
(27, 10, 'PINKFLASH Matte Lipstik Lembut Tahan Lama Pelembab Pigmentasi Tinggi 21 Colors', 17900, 729, 'ALL DAY MATTE and MOIST LIPSTICK: This highly pigmented matte liquid lipstick goes on smooth. Water-proof, transfer-proof. It has Vitamin E which moisturizes the skin on the lips. Light and comfortable texture, make your lips feel nothing the whole day. 20 colors for choose, suitable for all skin tones. Ink your lips in gorgeous, matte lip color.', 'id-11134207-8224w-mj08yeg0purma2.webp'),
(28, 10, 'SKINTIFIC - Cover All Perfect Cushion|Flawless & Glow Finish Cocok untuk Kulit Berminyak Berjerawat & Semua High Coverage', 52900, 289, 'Cover All Perfect Cushion dengan hasil akhir sempurna dalam 1 kali tap, memberikan coverage tinggi dan hasil akhir yang flawless. Dapat membantu mengontrol minyak untuk mencegah kusam dan warna tidak merata. Dilengkapi serum ingredients seperti 5X Ceramide, Centella and Hyaluronic Acid yang menjaga kelembaban kulit serta SPF 35 PA++++ untuk melindungi kulit dari bahaya sinar UV. Rubycell Puff Technology dengan teardrop shape, lubang udara yang rapat untuk menciptakan hasil akhir halus, merata dan sempurna. Medium to full coverage, tekstur ringan dan memberikan efek segar  dan terhidrasi tahan lama pada kulit. Tersedia dalam 7 pilihan warna : Vanilla, Ivory, Petal, Almond, Beige, Sand, Honey dengan kemasan double mirrored yang travel friendly, dengan ketebalan kurang dari 2 cm ', 'id-11134207-82251-mj2763fisoaq63.webp'),
(29, 11, 'Eden - Wallpaper Dinding Stiker Wallfoam 3D Sticker Dekorasi Kamar Rumah Murah High Quality TERMURAH!!!', 10000, 1279, 'Dekorasi 3D untuk Kamar Anda\r\n• Bahan XPE - Ringan, tahan air, dan mudah dibersihkan.\r\n• Lem Super Lengket - Tahan lama dan ramah lingkungan.\r\n• Perlindungan Anak - Melindungi dari benturan dinding.\r\n• Instalasi Mudah - Pasang sendiri dalam 3 langkah.\r\n• Desain Realistis - Pola 3D yang jelas dan menarik.', 'id-11134207-7r98t-lv07y7um2tsn52.webp'),
(30, 11, 'WENIS PENGHARUM RUANGAN / PEWANGI RUANGAN 50gr', 4542, 1089, 'Aroma Segar untuk Setiap Sudut Rumah\r\n• VARIAN BERAGAM - Pilih dari 8 aroma menawan seperti orange, lavender, dan kopi.\r\n• PENGHARUM PRAKTIS - Mudah digunakan untuk menjaga ruangan tetap harum sepanjang hari.\r\n• KEMASAN PRAKTIS - Ukuran 50gr, ideal untuk dibawa ke mana saja.\r\n• PENGIRIMAN AMAN - Produk dicek sebelum dikirim untuk memastikan kepuasan pelanggan.', 'sg-11134201-7ravc-mb7omiverpeq35@resize_w450_nl.webp'),
(31, 12, 'VARDOUR BOLA SEPAK UKURAN 5 FIFA APPROVED - MATERIAL KARET PREMIUM ANTI SLIP, DESAIN RINGAN & TAHAN BANTING', 111999, 126, 'Product details:\r\n\r\n\r\n1. Imported non-slip gauze, seamless paste, better air tightness, wear-resistant non-slip soft leather, better wear resistance. 26 Panel Design \r\n2. 26 Panel design, perfect for favorite surfaces, more predictable trajectory and better touch air flight stability, non-slip football\r\n3. Circumference, weight, recovery rate and water absorption test qualified.Service life more than 5 years.\r\n4. The superior air retention of the butyl plate increases durability.\r\n\r\nVardour Football is prepared for daily play with high contrast graphics to help you track it and 26 panels designed for true and precise flight.', 'id-11134207-81zto-mf6tol6xbd3j8a@resize_w450_nl.webp'),
(32, 12, '( 3 PASANG ) KAOS KAKI OLAHRAGA PENDEK TEBAL / KAOS KAKI SPORT PRIA WANITA DEWASA TERBARU BERKWALITAS BAGUS', 11498, 1209, 'Kaos kaki yang diproduksi dengan TCM ( Technology Computer Machine ) berteknologi tinggi sehingga pola dan jahitan yang dihasilkan lebih konsisten dan nyaman digunakan sepanjang hari\r\n\r\n- HARGA YANG TERTERA UNTUK 3 PASANG KAOS KAKI\r\n- Beli 1 sudah otomatis dapat 3 Pasang', 'id-11134207-82250-mhasih1ddij362.webp'),
(33, 13, 'PROMO BEST SELLER!! IKAT PINGGANG PRIA CANVAS TACTICAL MEN BELT ANTI METAL DETECTOR-MC-NL-40/BD-20-4/AH-319-1', 7500, 1468, 'SPESIFIKASI:\r\n- Terbuat dari plastik\r\n- Buckle model jepit yang sangat praktis\r\n- Metal detector free - mempermudah saat traveling, tidak perlu lepas belt pada saat melewati gerbang sensor\r\n- Ringan namun kuat\r\n- Tetap terlihat kokoh/rigid seperti metal buckle', '52994aa1e79ca01c01ecf1b0acce6eab.webp'),
(34, 14, 'Scrunchie Kuncir Ikat Rambut Hijab Iner Hijab Cemol Cepol', 1999, 836, '✅ Scrunchie Kuncir Rambut Hijab Jumbo – Bikin Rambut Terlihat Lebih Penuh dan Rapi!\r\n✔ Ukuran jumbo, cocok untuk inner hijab, cepol, cemol, atau gaya non-hijab\r\n✔ Memberi efek volume alami dan rapi pada rambut\r\n✔ Bahan mix pilihan: Wolfis / Ceruty Babydoll / Diamond / Spandek – lembut dan nyaman dipakai\r\n✔ Tersedia dalam berbagai warna cantik, mudah dipadukan dengan outfit harian\r\n✔ Nyaman digunakan seharian, tidak mudah longgar\r\n✔ Cocok untuk anak-anak, remaja, hingga dewasa', 'b18218603f83b20c0ba442bbe2945b9c.webp'),
(35, 17, 'VIVI NICI - Bianca Sepatu Pantofel Wanita Hak 5 cm', 215900, 152, 'VIVI NICI dapat digunakan untuk bekerja dan bepergian, desainnya yang menarik serta bahan yang nyaman sangat cocok untuk dipakai setiap hari. VIVI NICI juga memiliki variasi warna yang up to date. Cocok untuk berbagai jenis warna kulit dan tersedia berbagai macam ukuran yang nyaman untuk dipakai. \r\n\r\nSemua produk kami merupakan hasil karya dari produksi lokal yang bekerjasama dengan UMKM yang telah diseleksi dengan baik agar menghasilkan produk yang berkualitas, nyaman serta harga bersaing. Dengan membeli produk VIVI NICI, maka Anda turut serta dalam membangun UMKM persepatuan Indonesia. ', 'id-11134207-7ra0i-mdveswelgmj998@resize_w450_nl.webp'),
(36, 17, 'Sepatu Oxford Wanita - Sepatu Docmart BTS Terlaris', 44000, 247, 'Ukuran sepatu \r\n36 = 23 cm \r\n37 = 23,5 cm\r\n38 = 24 cm \r\n39= 24.5 cm\r\n40= 25 cm ', 'id-11134207-7r98t-lsdpotqgdb9i5b@resize_w450_nl.webp'),
(37, 16, 'Sepatu Allstar Tinggi / Sepatu Convers Chuck Taylor Classic High / Sepatu High Black White 70s Klasik Pria & Wanita Sepatu Sekolah', 51500, 241, 'Model: Converse Classic 70s\r\nKualitas:\r\n- 100% Barang import dari luar, sangat presisi.\r\n- Double Insole\r\n- Insole Semi Latex (Nyaman Di kaki / Tidak keras).\r\n\r\nKelangkapan :\r\n- 1 Pasang Sepatu.\r\n- Box Polos', 'id-11134207-8224u-mjjcyjhgd5acba.webp'),
(38, 16, 'Sepatu Casual Pria Ivory Vault Stroom', 300000, 178, 'Nyaman serta keren untuk di gunakan berolahraga,gym,lari dan berpergian. \r\nFashion keren yang pernah ada, tunggu apa lagi, yuk buruan di order.!!\r\n\r\nSize : 38 - 43', 'sg-11134201-22120-ufxzz2ugdclvdb@resize_w450_nl.webp');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT 'default.png',
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `no_hp`, `alamat`, `kota`, `kode_pos`, `foto_profil`, `password`, `role`, `created_at`, `updated_at`) VALUES
(4, 'Admin', 'admin@warungku.com', NULL, NULL, NULL, NULL, 'default.png', '$2y$10$mvsQuR/h0CliobQ2UrmLJey./qFzmA3XGewlFzuz6rfC77YW/zejy', 'admin', '2026-01-14 10:48:33', '2026-01-18 15:32:35'),
(6, 'Gilang Rmdhn', 'gilangrmdhn05@gmail.com', '083131811023', 'Kp.Calengka Rt.01/Rw.01 Desa Bumiwangi Kec.Ciparay Kab.Bandung ', 'Bandung', '0523', 'profil_6_1768774777.jpg', '$2y$10$KzOK70vnZL0ucC6n7.iVxOW5Vzo.SoiTC601SYEq46shnlaEfiKDe', 'user', '2026-01-14 13:44:22', '2026-01-20 11:27:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

CREATE TABLE `wishlist` (
  `id_wishlist` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `tanggal_ditambahkan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `flash_sale`
--
ALTER TABLE `flash_sale`
  ADD PRIMARY KEY (`id_flash`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `user_produk` (`id_user`,`id_produk`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `pesanan_ibfk_1` (`id_user`);

--
-- Indeks untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_wishlist`),
  ADD UNIQUE KEY `user_product_unique` (`id_user`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `flash_sale`
--
ALTER TABLE `flash_sale`
  MODIFY `id_flash` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `flash_sale`
--
ALTER TABLE `flash_sale`
  ADD CONSTRAINT `flash_sale_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
