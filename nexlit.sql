-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2024 at 01:07 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nexlit`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int NOT NULL,
  `no_meteran` bigint NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `kontak_pelanggan` varchar(15) NOT NULL,
  `tipe_rumah` enum('1BR','2BR','3BR','4BR') CHARACTER SET utf8mb4 NOT NULL,
  `rukun_tetangga` varchar(3) NOT NULL,
  `rukun_warga` varchar(3) NOT NULL,
  `kode_pos` varchar(5) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `id_tarif` int DEFAULT NULL,
  `daya` int DEFAULT NULL,
  `created_by` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `no_meteran`, `nama_pelanggan`, `kontak_pelanggan`, `tipe_rumah`, `rukun_tetangga`, `rukun_warga`, `kode_pos`, `alamat`, `id_tarif`, `daya`, `created_by`) VALUES
(505, 231011400278, 'AHMAD DZAKY', '081211720925', '3BR', '3', '01', '15417', 'UNPAM', 3, 2200, 'admin'),
(506, 231011400300, 'ISNA FITRI ARLINA', '081211720999', '2BR', '2', '01', '15417', 'UNPAM', 3, 2200, 'admin'),
(507, 231011400500, 'LANDIE AZIZ NUGROHO', '081211722224', '1BR', '2', '01', '15417', 'UNPAM', 3, 2200, 'admin'),
(508, 231011400501, 'YUMADISA AZHARI', '081211720926', '2BR', '1', '01', '15417', 'UNPAM', 1, 900, 'admin'),
(510, 213199292929, 'WIJAYYYY', '081018208102', '3BR', '3', '01', '15417', 'jl. Unpam victor no.12222', 4, 3500, 'admin'),
(511, 123123123123, 'A', '123123123123', '2BR', '4', '01', '15417', 'UNPAM', 2, 1300, 'admin'),
(512, 123456123456, 'UN', '123456123456', '4BR', '2', '01', '15417', 'UNPAM', 2, 1300, 'admin');

--
-- Triggers `customer`
--
DELIMITER $$
CREATE TRIGGER `after_customer_insert` AFTER INSERT ON `customer` FOR EACH ROW BEGIN
    DECLARE hashed_password VARCHAR(64);
    SET hashed_password = SHA2(NEW.no_meteran, 256);
    INSERT INTO user_account (no_meteran, nama_pelanggan, username, password, confirm_password)
    VALUES (NEW.no_meteran, NEW.nama_pelanggan, NEW.no_meteran, hashed_password, hashed_password);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `hunian`
--

CREATE TABLE `hunian` (
  `id` int NOT NULL,
  `tipe_rumah` varchar(100) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `hunian`
--

INSERT INTO `hunian` (`id`, `tipe_rumah`) VALUES
(1, '1BR'),
(2, '2BR'),
(3, '3BR'),
(4, '4BR');

-- --------------------------------------------------------

--
-- Table structure for table `nominal`
--

CREATE TABLE `nominal` (
  `id` int NOT NULL,
  `nominal` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `nominal`
--

INSERT INTO `nominal` (`id`, `nominal`) VALUES
(1, '50000'),
(2, '100000'),
(3, '200000'),
(4, '500000'),
(5, '1000000'),
(6, '1500000'),
(7, '2000000'),
(8, '5000000'),
(9, '10000000');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int NOT NULL,
  `tipe_pembayaran` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `nama_bank` varchar(100) NOT NULL,
  `va` varchar(15) DEFAULT NULL,
  `atas_nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `tipe_pembayaran`, `nama_bank`, `va`, `atas_nama`) VALUES
(1, 'Transfer BCA', 'BCA', '502021243035', 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(2, 'Transfer MANDIRI', 'MANDIRI', '552021244035', 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(3, 'Transfer BNI', 'BNI', '654565845235', 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(4, 'Transfer BRI', 'BRI', '857898456523', 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(5, 'Transfer Lainnya', 'LAINNYA', '552021243035', 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(6, 'QRIS', 'QR', NULL, 'PT. NEXT GENERATION ELECTRICITY INTEGRATION'),
(7, 'TUNAI', 'TELLER', NULL, 'PT. NEXT GENERATION ELECTRICITY INTEGRATION');

-- --------------------------------------------------------

--
-- Table structure for table `rukun_masyarakat`
--

CREATE TABLE `rukun_masyarakat` (
  `id` int NOT NULL,
  `rukun_tetangga` enum('001','002','003','004') CHARACTER SET utf8mb4 NOT NULL,
  `rukun_warga` varchar(2) NOT NULL DEFAULT '01',
  `kode_pos` int NOT NULL DEFAULT '15417'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `rukun_masyarakat`
--

INSERT INTO `rukun_masyarakat` (`id`, `rukun_tetangga`, `rukun_warga`, `kode_pos`) VALUES
(1, '001', '01', 15417),
(2, '002', '01', 15417),
(3, '003', '01', 15417),
(4, '004', '01', 15417);

-- --------------------------------------------------------

--
-- Table structure for table `tarif_daya`
--

CREATE TABLE `tarif_daya` (
  `id_tarif` int NOT NULL,
  `nama_tarif` varchar(50) DEFAULT NULL,
  `daya` int DEFAULT NULL,
  `rupiah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `tarif_daya`
--

INSERT INTO `tarif_daya` (`id_tarif`, `nama_tarif`, `daya`, `rupiah`) VALUES
(1, 'Tarif 01', 900, 1200),
(2, 'Tarif 02', 1300, 1500),
(3, 'Tarif 03', 2200, 1700),
(4, 'Tarif 04', 3500, 3000),
(5, 'Tarif 05', 5500, 4500);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `no_meteran` bigint NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `nominal` int NOT NULL,
  `token` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `pembayaran` varchar(50) NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `invoice_number` varchar(50) NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `nominal_real` decimal(10,2) DEFAULT NULL,
  `jumlah_kwh` decimal(10,2) DEFAULT NULL,
  `tarif` int DEFAULT '0',
  `daya` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `no_meteran`, `nama_pelanggan`, `nominal`, `token`, `pembayaran`, `tanggal`, `invoice_number`, `created_by`, `nominal_real`, `jumlah_kwh`, `tarif`, `daya`) VALUES
(148, 231011400300, 'AHMAD DZAKY', 1000000, '30915748707301982309', 'Transfer MANDIRI', '2024-12-06 23:27:21', 'NXLT/INV/0001/XII/XXIV', '231011400300', 975000.00, 650.00, 2, 1300),
(149, 231011400300, 'AHMAD DZAKY', 50000, '89561161730981326266', 'Transfer BRI', '2024-12-06 23:28:23', 'NXLT/INV/0002/XII/XXIV', '231011400300', 44000.00, 29.33, 2, 1300),
(150, 231011400300, 'ISNA FITRI ARLINA', 500000, '58088331631410710389', 'Transfer MANDIRI', '2024-12-06 23:40:26', 'NXLT/INV/0003/XII/XXIV', '231011400300', 485000.00, 323.33, 2, 1300),
(151, 231011400278, 'AHMAD DZAKY', 50000, '34476936330493652961', 'Transfer MANDIRI', '2024-12-06 23:41:34', 'NXLT/INV/0004/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(152, 231011400300, 'ISNA FITRI ARLINA', 50000, '24739554130532733194', 'Transfer BCA', '2024-12-06 23:42:02', 'NXLT/INV/0005/XII/XXIV', '231011400300', 44000.00, 29.33, 2, 1300),
(153, 231011400300, 'ISNA FITRI ARLINA', 200000, '00078809981108573049', 'Tranfer Lainnya', '2024-12-06 23:43:56', 'NXLT/INV/0006/XII/XXIV', '231011400278', 191000.00, 127.33, 2, 1300),
(154, 231011400500, 'LANDIE AZIZ NUGROHO', 10000000, '15178189315095248594', 'QRIS', '2024-12-07 12:30:19', 'NXLT/INV/0007/XII/XXIV', '231011400500', 9795000.00, 5761.76, 3, 2200),
(155, 231011400278, 'AHMAD DZAKY', 50000, '51390278395799304365', 'Transfer BCA', '2024-12-07 13:21:18', 'NXLT/INV/0008/XII/XXIV', '231011400500', 44000.00, 25.88, 3, 2200),
(156, 231011400278, 'AHMAD DZAKY', 500000, '90603311728705453643', 'Transfer BNI', '2024-12-07 13:33:48', 'NXLT/INV/0009/XII/XXIV', '231011400500', 485000.00, 285.29, 3, 2200),
(157, 231011400300, 'ISNA FITRI ARLINA', 1500000, '42932099376125523887', 'QRIS', '2024-12-07 14:11:38', 'NXLT/INV/0010/XII/XXIV', '231011400300', 1465000.00, 976.67, 2, 1300),
(158, 231011400278, 'AHMAD DZAKY', 50000, '65435718400250899034', 'Transfer BCA', '2024-12-07 14:19:27', 'NXLT/INV/0011/XII/XXIV', '231011400300', 44000.00, 25.88, 3, 2200),
(159, 231011400278, 'AHMAD DZAKY', 50000, '89169025561208227445', 'Transfer BNI', '2024-12-07 14:33:01', 'NXLT/INV/0012/XII/XXIV', '231011400300', 44000.00, 25.88, 3, 2200),
(160, 231011400278, 'AHMAD DZAKY', 500000, '86035775251076314904', 'Transfer MANDIRI', '2024-12-07 14:40:38', 'NXLT/INV/0013/XII/XXIV', '231011400300', 485000.00, 285.29, 3, 2200),
(161, 231011400278, 'AHMAD DZAKY', 10000000, '58117165133984805248', 'Transfer MANDIRI', '2024-12-07 14:43:12', 'NXLT/INV/0014/XII/XXIV', '231011400300', 9795000.00, 5761.76, 3, 2200),
(162, 231011400278, 'AHMAD DZAKY', 500000, '00895839206986411226', 'Transfer MANDIRI', '2024-12-07 14:45:04', 'NXLT/INV/0015/XII/XXIV', '231011400300', 485000.00, 285.29, 3, 2200),
(163, 231011400278, 'AHMAD DZAKY', 500000, '02399815493349609033', 'Transfer BCA', '2024-12-07 14:51:27', 'NXLT/INV/0016/XII/XXIV', '231011400300', 485000.00, 285.29, 3, 2200),
(164, 231011400278, 'AHMAD DZAKY', 50000, '48261457321939182186', 'Transfer BCA', '2024-12-07 19:46:48', 'NXLT/INV/0017/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(165, 231011400278, 'AHMAD DZAKY', 50000, '30338359418263968936', 'Transfer BCA', '2024-12-08 12:46:45', 'NXLT/INV/0018/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(166, 231011400278, 'AHMAD DZAKY', 1500000, '74771967065202125920', 'Transfer BNI', '2024-12-08 12:47:06', 'NXLT/INV/0019/XII/XXIV', '231011400278', 1465000.00, 861.76, 3, 2200),
(167, 231011400278, 'AHMAD DZAKY', 2000000, '20954892482448397452', 'QRIS', '2024-12-08 14:27:43', 'NXLT/INV/0020/XII/XXIV', 'admin', 1955000.00, 1150.00, 3, 2200),
(168, 231011400278, 'AHMAD DZAKY', 10000000, '92800264327568042822', 'Transfer MANDIRI', '2024-12-08 14:37:51', 'NXLT/INV/0021/XII/XXIV', '231011400278', 9795000.00, 5761.76, 3, 2200),
(169, 231011400278, 'AHMAD DZAKY', 50000, '31977141952811731085', 'Transfer BCA', '2024-12-09 20:58:44', 'NXLT/INV/0022/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(170, 231011400278, 'AHMAD DZAKY', 500000, '14013221095940552981', 'Transfer Lainnya', '2024-12-09 21:02:55', 'NXLT/INV/0023/XII/XXIV', '231011400278', 485000.00, 285.29, 3, 2200),
(171, 231011400278, 'AHMAD DZAKY', 50000, '44701383846282949507', 'Transfer BCA', '2024-12-10 22:24:58', 'NXLT/INV/0024/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(172, 231011400278, 'AHMAD DZAKY', 50000, '27611734911787370756', 'Transfer BCA', '2024-12-10 22:31:51', 'NXLT/INV/0025/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200),
(173, 231011400278, 'AHMAD DZAKY', 100000, '45788653452289803258', 'Transfer BCA', '2024-12-10 22:44:25', 'NXLT/INV/0026/XII/XXIV', '231011400278', 93000.00, 54.71, 3, 2200),
(174, 231011400278, 'AHMAD DZAKY', 5000000, '13797742370445092584', 'TUNAI', '2024-12-14 09:14:49', 'NXLT/INV/0027/XII/XXIV', 'admin', 4900000.00, 2882.35, 3, 2200),
(175, 231011400278, 'AHMAD DZAKY', 500000, '33745641699453826906', 'Transfer MANDIRI', '2024-12-14 09:17:33', 'NXLT/INV/0028/XII/XXIV', 'admin', 490000.00, 288.24, 3, 2200),
(176, 231011400278, 'AHMAD DZAKY', 5000000, '08638668964903901329', 'TUNAI', '2024-12-14 09:18:16', 'NXLT/INV/0029/XII/XXIV', 'admin', 4900000.00, 2882.35, 3, 2200),
(178, 213199292929, 'WIJAYYYY', 10000000, '96791360769471876437', 'Transfer Lainnya', '2024-12-14 09:55:34', 'NXLT/INV/0030/XII/XXIV', '213199292929', 9795000.00, 3265.00, 4, 3500),
(179, 231011400278, 'AHMAD DZAKY', 1500000, '68036918902501063403', 'Transfer BNI', '2024-12-14 13:23:39', 'NXLT/INV/0031/XII/XXIV', 'admin', 1465000.00, 861.76, 3, 2200),
(180, 231011400278, 'AHMAD DZAKY', 50000, '95916610634316550547', 'QRIS', '2024-12-18 19:55:20', 'NXLT/INV/0032/XII/XXIV', '231011400278', 44000.00, 25.88, 3, 2200);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `confirm_password` varchar(200) DEFAULT NULL,
  `status` enum('Active','Restric') NOT NULL,
  `create_on` date DEFAULT NULL,
  `profile_picture` varchar(200) NOT NULL DEFAULT 'profile/default.png',
  `role` enum('Administrator') NOT NULL,
  `about` varchar(254) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `confirm_password`, `status`, `create_on`, `profile_picture`, `role`, `about`, `last_login`, `last_ip`) VALUES
(1, 'Administrator', 'admin', 'admin@nexlit.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', NULL, 'Active', '2024-11-19', 'profile/default.png', 'Administrator', 'Administrator NexLit nih boyys', '2024-12-18 20:05:05', '::1'),
(5, 'Isna Fitri Arlina', 'isna', 'isna@nexlit.com', 'ec33bb8e8cb0f685f1a1caeea361572d6b5faa64bbbffa1919cb671b8dd99ddc', NULL, 'Restric', '2024-11-21', 'profile/default.png', 'Administrator', 'Isna Fitri Arlina', '2024-12-13 23:38:58', '::1'),
(6, 'Christian Nathanael', 'nael', 'christian@nexlit.com', 'c1c95bdafd974e36ee2ff80db94672e698e575a37ff7f92d2301d1a997659c0f', NULL, 'Restric', '2024-11-21', 'profile/default.png', 'Administrator', 'UNPAM BANGET NICHHHHHH', '2024-11-30 09:27:13', '::1'),
(10, 'Anel', 'anel', 'anel@gmail.com', '20f3765880a5c269b747e1e906054a4b4a3a991259f1e16b5dde4742cec2319a', NULL, 'Active', '2024-12-14', 'profile/default.png', 'Administrator', 'pesepakbola tampan', '2024-12-14 10:04:17', '::1'),
(11, 'Tamu Nexlit', 'tamu', 'tamu@nexlit.com', '3b8cd3da133887e38bcdaf4f098c701567f6e4e8460197bf8537ced9c3507f7f', NULL, 'Active', '2024-12-15', 'profile/default.png', 'Administrator', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `id` int NOT NULL,
  `no_meteran` bigint NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `confirm_password` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`id`, `no_meteran`, `nama_pelanggan`, `username`, `password`, `confirm_password`) VALUES
(3, 231011400278, 'AHMAD DZAKY', '231011400278', '469efe188d441b736b4ddf3c0e08db3a13b96c6fc91e55346a5472cf00d2994b', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3'),
(4, 231011400300, 'ISNA FITRI ARLINA', '231011400300', '97176aa275501212bd14af4a7ae8464cbccb53bbf4c353eeeaf0cfb25de918a7', '97176aa275501212bd14af4a7ae8464cbccb53bbf4c353eeeaf0cfb25de918a7'),
(5, 231011400500, 'LANDIE AZIZ NUGROHO', '231011400500', 'b3d1c0a3b7df724e53b77ca097aa29f2bae030cf7bc08081f9df02fbe79f6b5f', 'b3d1c0a3b7df724e53b77ca097aa29f2bae030cf7bc08081f9df02fbe79f6b5f'),
(6, 231011400501, 'YUMADISA AZHARI', '231011400501', '04cb8137f5aa5358c152868124a521af9c7ece66d1d28e0ba5ff65dcf28b1f31', '04cb8137f5aa5358c152868124a521af9c7ece66d1d28e0ba5ff65dcf28b1f31'),
(8, 213199292929, 'WIJAYYYY', '213199292929', 'f42458797e4b2c6ea7391f225d9ceb23c3735acaf2b704096cb92aa68557bc5e', 'f42458797e4b2c6ea7391f225d9ceb23c3735acaf2b704096cb92aa68557bc5e'),
(9, 123123123123, 'A', '123123123123', 'b822bb93905a9bd8b3a0c08168c427696436cf8bf37ed4ab8ebf41a07642ed1c', 'b822bb93905a9bd8b3a0c08168c427696436cf8bf37ed4ab8ebf41a07642ed1c'),
(10, 123456123456, 'UN', '123456123456', '958d51602bbfbd18b2a084ba848a827c29952bfef170c936419b0922994c0589', '958d51602bbfbd18b2a084ba848a827c29952bfef170c936419b0922994c0589');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tarif` (`id_tarif`),
  ADD KEY `idx_no_meteran` (`no_meteran`);

--
-- Indexes for table `hunian`
--
ALTER TABLE `hunian`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nominal`
--
ALTER TABLE `nominal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rukun_masyarakat`
--
ALTER TABLE `rukun_masyarakat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tarif_daya`
--
ALTER TABLE `tarif_daya`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no_meteran` (`no_meteran`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `no_meteran` (`no_meteran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=513;

--
-- AUTO_INCREMENT for table `hunian`
--
ALTER TABLE `hunian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nominal`
--
ALTER TABLE `nominal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rukun_masyarakat`
--
ALTER TABLE `rukun_masyarakat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`id_tarif`) REFERENCES `tarif_daya` (`id_tarif`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`no_meteran`) REFERENCES `customer` (`no_meteran`) ON DELETE CASCADE;

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `user_account_ibfk_1` FOREIGN KEY (`no_meteran`) REFERENCES `customer` (`no_meteran`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
