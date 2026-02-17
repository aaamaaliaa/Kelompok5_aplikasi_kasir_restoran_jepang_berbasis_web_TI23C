-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 12:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resto_jepang`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `nomor_bukti` varchar(30) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `catatan_item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `nomor_bukti`, `id_menu`, `jumlah`, `harga`, `subtotal`, `catatan_item`) VALUES
(45, 'TRX-20260128-006', 7, 1, 58000.00, 58000.00, ''),
(46, 'TRX-20260128-006', 11, 1, 15000.00, 15000.00, ''),
(47, 'TRX-20260128-007', 10, 1, 55000.00, 55000.00, ''),
(48, 'TRX-20260128-007', 8, 1, 35000.00, 35000.00, ''),
(49, 'TRX-20260128-007', 1, 1, 35000.00, 35000.00, ''),
(50, 'TRX-20260128-007', 2, 1, 38000.00, 38000.00, ''),
(51, 'TRX-20260128-008', 13, 1, 25000.00, 25000.00, ''),
(52, 'TRX-20260128-009', 16, 1, 20000.00, 20000.00, ''),
(53, 'TRX-20260128-009', 18, 1, 15000.00, 15000.00, ''),
(54, 'TRX-20260128-009', 2, 1, 38000.00, 38000.00, ''),
(55, 'TRX-20260128-009', 19, 1, 50000.00, 50000.00, ''),
(56, 'TRX-20260128-010', 9, 1, 40000.00, 40000.00, ''),
(57, 'TRX-20260128-010', 1, 1, 35000.00, 35000.00, ''),
(58, 'TRX-20260128-010', 1, 1, 35000.00, 35000.00, ''),
(59, 'TRX-20260128-012', 4, 1, 48000.00, 48000.00, ''),
(60, 'TRX-20260128-012', 1, 1, 35000.00, 35000.00, ''),
(61, 'TRX-20260128-012', 3, 1, 45000.00, 45000.00, ''),
(62, 'TRX-20260128-011', 2, 1, 38000.00, 38000.00, ''),
(63, 'TRX-20260128-011', 19, 1, 50000.00, 50000.00, ''),
(64, 'TRX-20260128-011', 12, 1, 30000.00, 30000.00, ''),
(65, 'TRX-20260128-013', 7, 1, 58000.00, 58000.00, ''),
(66, 'TRX-20260128-013', 4, 1, 48000.00, 48000.00, ''),
(67, 'TRX-20260128-013', 12, 1, 30000.00, 30000.00, ''),
(70, 'TRX-20260128-016', 16, 1, 20000.00, 20000.00, ''),
(71, 'TRX-20260129-001', 8, 2, 35000.00, 70000.00, ''),
(72, 'TRX-20260129-002', 7, 2, 58000.00, 116000.00, ''),
(73, 'TRX-20260129-002', 12, 1, 30000.00, 30000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL,
  `nomor_meja` varchar(50) NOT NULL COMMENT 'Nomor/nama meja (contoh: Meja 1, VIP-A)',
  `kapasitas` int(11) NOT NULL DEFAULT 4 COMMENT 'Jumlah kursi/orang yang bisa duduk',
  `status` enum('kosong','terisi','reserved') NOT NULL DEFAULT 'kosong' COMMENT 'Status ketersediaan meja',
  `lokasi` varchar(100) DEFAULT NULL COMMENT 'Lokasi meja (contoh: Lantai 1, Area Outdoor)',
  `deskripsi` text DEFAULT NULL COMMENT 'Keterangan tambahan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meja`
--

INSERT INTO `meja` (`id_meja`, `nomor_meja`, `kapasitas`, `status`, `lokasi`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Meja 1', 4, 'kosong', 'Lantai 1 - Indoor', 'Dekat jendela', '2026-01-28 05:54:18', '2026-01-29 06:11:23'),
(2, 'Meja 2', 2, 'kosong', 'Lantai 1 - Indoor', 'Meja untuk 2 orang', '2026-01-28 05:54:18', '2026-01-29 06:12:16'),
(3, 'Meja 3', 6, 'kosong', 'Lantai 1 - Indoor', 'Meja besar untuk keluarga', '2026-01-28 05:54:18', '2026-01-29 06:26:34'),
(4, 'Meja 4', 4, 'kosong', 'Lantai 1 - Indoor', 'Dekat pintu masuk', '2026-01-28 05:54:18', '2026-01-28 05:54:18'),
(5, 'Meja 5', 2, 'kosong', 'Lantai 2 - Indoor', 'Suasana tenang', '2026-01-28 05:54:18', '2026-01-28 06:50:35'),
(6, 'Meja 6', 4, 'reserved', 'Lantai 2 - Indoor', 'View kota', '2026-01-28 05:54:18', '2026-01-28 06:29:56'),
(7, 'VIP-A', 8, 'kosong', 'Lantai 2 - VIP Room', 'Ruang privat untuk 8 orang', '2026-01-28 05:54:18', '2026-01-28 11:44:34'),
(8, 'VIP-B', 10, 'kosong', 'Lantai 2 - VIP Room', 'Ruang privat untuk 10 orang', '2026-01-28 05:54:18', '2026-01-28 05:54:18'),
(9, 'Outdoor-1', 4, 'kosong', 'Area Outdoor', 'Teras outdoor', '2026-01-28 05:54:18', '2026-01-28 05:54:18'),
(10, 'Outdoor-2', 6, 'kosong', 'Area Outdoor', 'Garden area', '2026-01-28 05:54:18', '2026-01-28 06:41:14'),
(11, 'Meja 7', 3, 'reserved', 'Outdoor', '', '2026-01-29 06:13:52', '2026-01-29 06:14:11');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `kode_menu` varchar(20) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `kode_menu`, `nama_menu`, `kategori`, `harga`, `deskripsi`, `foto`, `status_aktif`) VALUES
(1, 'SUS-001', 'Salmon Nigiri', 'Sushi', 35000.00, '2 potong nigiri salmon segar', 'menu_69782b3756026.jpeg', 'aktif'),
(2, 'SUS-002', 'Tuna Nigiri', 'Sushi', 38000.00, '2 potong nigiri tuna', 'menu_69785f352624a.jpg', 'aktif'),
(3, 'SUS-003', 'California Roll', 'Sushi', 45000.00, '8 potong roll dengan avocado & crabstick', 'menu_69785f6a9ef2c.jpg', 'aktif'),
(4, 'SUS-004', 'Spicy Tuna Roll', 'Sushi', 48000.00, '8 potong roll tuna pedas', 'menu_69785f9593942.jpg', 'aktif'),
(5, 'RAM-001', 'Tonkotsu Ramen', 'Ramen', 65000.00, 'Ramen kuah kaldu tulang babi kental', 'menu_69785fcfdd3bb.jpg', 'aktif'),
(6, 'RAM-002', 'Miso Ramen', 'Ramen', 60000.00, 'Ramen kuah miso dengan topping lengkap', 'menu_69785fef3c3f5.jpg', 'aktif'),
(7, 'RAM-003', 'Shoyu Ramen', 'Ramen', 58000.00, 'Ramen klasik kuah kecap Jepang', 'menu_69786017956fb.jpg', 'aktif'),
(8, 'SID-001', 'Gyoza', 'Side Dish', 35000.00, '6 potong gyoza ayam goreng', 'menu_69786042be085.jpg', 'aktif'),
(9, 'SID-002', 'Takoyaki', 'Side Dish', 40000.00, '8 buah takoyaki dengan saus', 'menu_6978606783b31.jpg', 'aktif'),
(10, 'SID-003', 'Ebi Tempura', 'Side Dish', 55000.00, '5 udang tempura crispy', 'menu_6978608d773b2.jpg', 'aktif'),
(11, 'DRI-001', 'Ocha (Hot/Iced)', 'Drink', 15000.00, 'Teh hijau Jepang refill', 'menu_697860cdab24d.jpg', 'aktif'),
(12, 'DRI-002', 'Matcha Latte', 'Drink', 30000.00, 'Matcha dengan susu', 'menu_697860f42ff5d.jpg', 'aktif'),
(13, 'DRI-003', 'Calpis Soda', 'Drink', 25000.00, 'Minuman yogurt soda', 'menu_69782ca82c85b.jpg', 'aktif'),
(14, 'DES-001', 'Mochi Ice Cream Matcha', 'Dessert', 28000.00, '3 buah mochi isi es krim', 'menu_6978611c4cce0.jpg', 'aktif'),
(16, 'DES-002', 'Taiyaki', 'Dessert', 20000.00, 'Kue berbentuk ikan dengan isian kacang merah, custard, atau cokelat.', 'menu_69786152691e6.jpg', 'aktif'),
(18, 'DES-003', 'Dorayaki', 'dessert', 15000.00, 'Kue lembut berbentuk bulat, diisi anko, cokelat, atau krim.', 'menu_697861a4aec42.jpg', 'aktif'),
(19, 'SUS-005', 'Tamago Nigiri', 'sushi', 50000.00, 'Telur dadar Jepang yang manis dan lembut.', 'menu_20260127135836_3585.jpg', 'nonaktif');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `nomor_bukti` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL DEFAULT curtime(),
  `id_meja` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `kasir` varchar(100) DEFAULT NULL COMMENT 'Nama kasir yang membuat transaksi',
  `kasir_bayar` varchar(100) DEFAULT NULL COMMENT 'Nama kasir yang memproses pembayaran',
  `total_bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah pajak',
  `service_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya service',
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total akhir (sudah include tax & service)',
  `status_bayar` enum('lunas','belum_lunas','pending') NOT NULL DEFAULT 'belum_lunas',
  `metode_pembayaran` enum('tunai','debit_card','qris') DEFAULT NULL COMMENT 'Metode pembayaran yang digunakan',
  `jumlah_bayar` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah uang yang dibayarkan (khusus tunai)',
  `kembalian` decimal(12,2) DEFAULT NULL COMMENT 'Uang kembalian (khusus tunai)',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`nomor_bukti`, `tanggal`, `waktu`, `id_meja`, `nama_pelanggan`, `kasir`, `kasir_bayar`, `total_bayar`, `tax_amount`, `service_amount`, `grand_total`, `status_bayar`, `metode_pembayaran`, `jumlah_bayar`, `kembalian`, `catatan`, `created_at`) VALUES
('TRX-20260128-006', '2026-01-28', '13:28:00', 3, 'amalia', 'admin', NULL, 73000.00, 7300.00, 3650.00, 83950.00, 'lunas', 'qris', NULL, NULL, '', '2026-01-28 06:28:28'),
('TRX-20260128-007', '2026-01-28', '13:31:00', 10, 'yaya', 'admin', NULL, 163000.00, 16300.00, 8150.00, 187450.00, 'lunas', 'tunai', 190000.00, 2550.00, '', '2026-01-28 06:31:27'),
('TRX-20260128-008', '2026-01-28', '13:42:00', 1, 'dimas', 'admin', NULL, 25000.00, 2500.00, 1250.00, 28750.00, 'lunas', 'qris', NULL, NULL, '', '2026-01-28 06:42:25'),
('TRX-20260128-009', '2026-01-28', '13:42:00', 1, 'rania', 'admin', NULL, 123000.00, 12300.00, 6150.00, 141450.00, 'lunas', 'debit_card', NULL, NULL, '', '2026-01-28 06:43:02'),
('TRX-20260128-010', '2026-01-28', '13:44:00', 5, 'amalia', 'admin', NULL, 110000.00, 11000.00, 5500.00, 126500.00, 'lunas', 'debit_card', NULL, NULL, '', '2026-01-28 06:44:17'),
('TRX-20260128-011', '2026-01-28', '13:47:00', 5, 'vale', 'admin', NULL, 118000.00, 11800.00, 5900.00, 135700.00, 'lunas', 'qris', NULL, NULL, '', '2026-01-28 06:47:20'),
('TRX-20260128-012', '2026-01-28', '13:47:00', 7, 'caca', 'admin', NULL, 128000.00, 12800.00, 6400.00, 147200.00, 'lunas', 'tunai', 150000.00, 2800.00, '', '2026-01-28 06:47:45'),
('TRX-20260128-013', '2026-01-28', '18:39:00', 1, 'eca', 'admin', NULL, 136000.00, 13600.00, 6800.00, 156400.00, 'lunas', 'tunai', 200000.00, 43600.00, '', '2026-01-28 11:40:35'),
('TRX-20260128-016', '2026-01-28', '19:05:00', 3, 'amalia firdaus', 'admin', NULL, 20000.00, 2000.00, 1000.00, 23000.00, 'lunas', 'tunai', 30000.00, 7000.00, '', '2026-01-28 12:06:00'),
('TRX-20260129-001', '2026-01-29', '13:09:00', 2, 'Dicki', 'admin', NULL, 70000.00, 7000.00, 3500.00, 80500.00, 'lunas', 'tunai', 100000.00, 19500.00, '', '2026-01-29 06:10:21'),
('TRX-20260129-002', '2026-01-29', '13:24:00', 3, 'Dicki', 'Lala', NULL, 146000.00, 14600.00, 7300.00, 167900.00, 'lunas', 'qris', NULL, NULL, '', '2026-01-29 06:24:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2b$12$htx4N0dWygRB1LT2oqiExelbuyUuKhjZK54F/FNYpfKleloBuPnuC', 'admin', '2026-01-27 00:22:32'),
(4, 'amalia', '$2y$10$XbSqZ4yyqeLSZXB6csX4teQPo/DObCT6ZNPKfwymLYX/7ht8ZkfkO', '', '2026-01-29 04:08:02'),
(7, 'Lala', '$2y$10$YpErQgCI0S0a7FuHrEU7xu2YdSKSmnS1jD4U.0a53LzpWd3lzHuay', 'kasir', '2026-01-29 04:23:48'),
(8, 'Lili', '$2y$10$YZbK0/JMhAfutd.PxuLVneYmGDktg4SaRRt.UME.giBhtfbD3uYfO', '', '2026-01-29 05:41:00'),
(9, 'Lila', '$2y$10$PlcCuPtumbjYcc6SAJT0Y.LipwL0HFFB9jCbsk/gpiSjxUo6demtW', '', '2026-01-29 05:43:06'),
(10, 'Lulu', '$2y$10$PS78/5nrPC7aOkNhmj9c3eOMXHZsK57.ByH5xVJYc9HsrJzarqon6', '', '2026-01-29 05:46:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_id_menu` (`id_menu`),
  ADD KEY `idx_detail_nomor_bukti` (`nomor_bukti`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD UNIQUE KEY `kode_menu` (`kode_menu`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`nomor_bukti`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_id_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`),
  ADD CONSTRAINT `fk_detail_nomor_bukti` FOREIGN KEY (`nomor_bukti`) REFERENCES `transaksi` (`nomor_bukti`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
