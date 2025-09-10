-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th9 10, 2025 lúc 12:53 PM
-- Phiên bản máy phục vụ: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- Phiên bản PHP: 8.1.2-1ubuntu2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlkh`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inbound_orders`
--

CREATE TABLE `inbound_orders` (
  `id` int(11) NOT NULL,
  `supplier_info` text NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `invoice_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `inbound_orders`
--

INSERT INTO `inbound_orders` (`id`, `supplier_info`, `total_amount`, `purchase_date`, `invoice_file`) VALUES
(17, 'Tên: Kho Sỉ MP HN\r\nSĐT: \r\nLink: \r\n', 3538000.00, '2025-07-31', 'hoadon_nhap_20250731_17.pdf'),
(18, 'Tên: Kho Sỉ MP HN\r\nSĐT:\r\nLink:\r\n', 2414000.00, '2025-08-18', 'hoadon_nhap_20250818_18.pdf'),
(19, 'Tên: Nana Beauty\r\nSĐT: \r\nLink: \r\n', 2490000.00, '2025-08-28', 'hoadon_nhap_20250828_19.pdf');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inbound_order_details`
--

CREATE TABLE `inbound_order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `quantity_purchased` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `inbound_order_details`
--

INSERT INTO `inbound_order_details` (`id`, `order_id`, `product_id`, `product_name`, `sku`, `quantity_purchased`) VALUES
(24, 17, 37, 'Mặt Nạ Colorkey B5 Dưỡng Sáng & Căng Bóng Da - Luminous Brightening Facial Mask', 'MND-COL-001-10', 2),
(25, 17, 38, 'Mặt Nạ Colorkey B5 Luminous Hỗ Trợ Cấp Ẩm, Dưỡng Da Căng Mịn - Regenerating Facial Mask', 'MND-COL-002-10', 2),
(26, 17, 39, 'Mặt Nạ Colorkey B5 Luminous Hỗ Trợ Làm Sáng Da - Hydrating Facial Mask', 'MND-COL-003-10', 2),
(27, 17, 40, 'Mặt Nạ Colorkey B5 Luminous Hồi Phục Làn Da - Nourishing Facial Mask', 'MND-COL-004-10', 2),
(28, 17, 41, 'Mặt Nạ Colorkey B5 Luminous Thảo Dược Đông Y - Replenishing Facial Mask', 'MND-COL-005-10', 2),
(29, 17, 33, 'Nước Tẩy Trang Garnier Dành Cho Da Nhạy Cảm', 'GAR-NTT-001-400', 6),
(30, 17, 34, 'Nước Tẩy Trang Garnier Cho Da Dầu & Mụn', 'GAR-NTT-002-400', 6),
(31, 17, 35, 'Nước Tẩy Trang Garnier Dành Cho Da Sạm & Xỉn Màu', 'GAR-NTT-003-400', 6),
(32, 17, 36, 'Nước Tẩy Trang Garnier Dành Cho Da Khô & Trang Điểm', 'GAR-NTT-004-400', 6),
(86, 18, 42, 'Tẩy Tế Bào Chết Arrahan Táo Đỏ – Apple Peeling Gel', 'ARR-TDM-001-180', 2),
(87, 18, 43, 'Tẩy Tế Bào Chết Arrahan Chanh Vàng – Lemon Peeling Gel', 'ARR-TDM-002-180', 2),
(88, 18, 44, 'Tẩy Tế Bào Chết Arrahan Than Hoạt Tính – Charcoal Peeling Gel', 'ARR-TDM-003-180', 2),
(89, 18, 45, 'Tẩy Tế Bào Chết Arrahan Hoa Oải Hương – Aroma Peeling Gel', 'ARR-TDM-004-180', 2),
(90, 18, 46, 'Tẩy Tế Bào Chết Arrahan Nha Đam – Aloe Vera Peeling Gel', 'ARR-TDM-005-180', 2),
(91, 18, 47, 'Tẩy Tế Bào Chết Arrahan Thảo Mộc – Hanbang Arrahan Soo Peeling Gel', 'ARR-TDM-006-180', 2),
(92, 18, 51, 'Kem Dưỡng Ẩm Naturie – Hatomugi Skin Conditioning Gel', 'NAT-KDA-001-180', 2),
(93, 18, 52, 'Nước Tẩy Trang Simple – Micellar Cleansing Water', 'SIM-NTT-001-400', 2),
(94, 18, 53, 'Nước Hoa Hồng Simple – Smoothing Facial Toner', 'SIM-NHH-001-200', 2),
(95, 18, 54, 'Nước Tẩy Trang Byphasse Phù Hợp Mọi Loại Da', 'BYP-NTT-001-500', 2),
(96, 18, 55, 'Nước Tẩy Trang Byphasse Dành Cho Da Dầu', 'BYP-NTT-002-500', 2),
(97, 18, 56, 'Sữa Rửa Mặt Hada Labo Cải Thiện Dấu Hiệu Lão Hóa Dạng Kem', 'HAD-SRM-001-80', 2),
(98, 18, 57, 'Sữa Rửa Mặt Hada Labo Dưỡng Ẩm Dạng Kem', 'HAD-SRM-002-80', 2),
(99, 18, 50, 'Nước Hoa Hồng Cocoon Hoa Sen - Nước Sen Hậu Giang', 'COC-NHH-001-310', 2),
(100, 18, 49, 'Nước Hoa Hồng Chinoshio Diếp Cá – Dokudami Natural Skin Lotion', 'CHI-NHH-002-500', 2),
(101, 18, 48, 'Nước Hoa Hồng Chinoshio Tía Tô – Perilla Natural Skin Lotion', 'CHI-NHH-001-500', 2),
(102, 19, 58, 'Sữa Rửa Mặt Hada Labo Dưỡng Trắng Dạng Kem', 'HAD-SRM-003-80', 2),
(103, 19, 59, 'Sữa Rửa Mặt Hada Labo Dành Cho Da Mụn & Nhạy Cảm Dạng Kem', 'HAD-SRM-004-80', 2),
(104, 19, 60, 'Nước Tẩy Trang Hada Labo Trắng', 'HAD-NTT-001-240', 2),
(105, 19, 61, 'Nước Tẩy Trang Hada Labo Xanh Dương', 'HAD-NTT-002-240', 2),
(106, 19, 62, 'Nước Tẩy Trang Hada Labo Xanh Ngọc', 'HAD-NTT-003-240', 2),
(107, 19, 63, 'Nước Tẩy Trang L’Oréal Paris Sạch Sâu – Loreal', 'LOR-NTT-001-400', 2),
(108, 19, 64, 'Nước Tẩy Trang L’Oréal Paris Mềm Mịn – Loreal', 'LOR-NTT-002-400', 2),
(109, 19, 65, 'Nước Tẩy Trang L’Oréal Paris Dành Cho Da Khô – Loreal', 'LOR-NTT-003-400', 2),
(110, 19, 66, 'Nước Tẩy Trang L’Oréal Paris Kiềm Dầu – Loreal', 'LOR-NTT-004-400', 2),
(111, 19, 67, 'Nước Tẩy Trang L’Oréal Paris Tươi Mát – Loreal', 'LOR-NTT-005-400', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `outbound_orders`
--

CREATE TABLE `outbound_orders` (
  `id` int(11) NOT NULL,
  `customer_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `invoice_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `outbound_order_details`
--

CREATE TABLE `outbound_order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `quantity_sold` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `purchase_price` decimal(10,0) NOT NULL,
  `sale_price` decimal(10,0) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `purchase_price`, `sale_price`, `quantity`) VALUES
(33, 'Nước Tẩy Trang Garnier Dành Cho Da Nhạy Cảm', 'GAR-NTT-001-400', 117000, 146000, 6),
(34, 'Nước Tẩy Trang Garnier Cho Da Dầu & Mụn', 'GAR-NTT-002-400', 117000, 146000, 6),
(35, 'Nước Tẩy Trang Garnier Dành Cho Da Sạm & Xỉn Màu', 'GAR-NTT-003-400', 117000, 146000, 6),
(36, 'Nước Tẩy Trang Garnier Dành Cho Da Khô & Trang Điểm', 'GAR-NTT-004-400', 117000, 146000, 6),
(37, 'Mặt Nạ Colorkey B5 Dưỡng Sáng & Căng Bóng Da - Luminous Brightening Facial Mask', 'COL-MND-005-10', 73000, 91000, 2),
(38, 'Mặt Nạ Colorkey B5 Luminous Hỗ Trợ Cấp Ẩm, Dưỡng Da Căng Mịn - Regenerating Facial Mask', 'COL-MND-004-10', 73000, 91000, 2),
(39, 'Mặt Nạ Colorkey B5 Luminous Hỗ Trợ Làm Sáng Da - Hydrating Facial Mask', 'MND-COL-003-10', 73000, 91000, 2),
(40, 'Mặt Nạ Colorkey B5 Luminous Hồi Phục Làn Da - Nourishing Facial Mask', 'MND-COL-004-10', 73000, 91000, 2),
(41, 'Mặt Nạ Colorkey B5 Luminous Thảo Dược Đông Y - Replenishing Facial Mask', 'MND-COL-005-10', 73000, 91000, 2),
(42, 'Tẩy Tế Bào Chết Arrahan Táo Đỏ – Apple Peeling Gel', 'ARR-TDM-001-180', 54000, 67000, 2),
(43, 'Tẩy Tế Bào Chết Arrahan Chanh Vàng – Lemon Peeling Gel', 'ARR-TDM-002-180', 54000, 67000, 2),
(44, 'Tẩy Tế Bào Chết Arrahan Than Hoạt Tính – Charcoal Peeling Gel', 'ARR-TDM-003-180', 54000, 67000, 2),
(45, 'Tẩy Tế Bào Chết Arrahan Hoa Oải Hương – Aroma Peeling Gel', 'ARR-TDM-004-180', 54000, 67000, 2),
(46, 'Tẩy Tế Bào Chết Arrahan Nha Đam – Aloe Vera Peeling Gel', 'ARR-TDM-005-180', 54000, 67000, 2),
(47, 'Tẩy Tế Bào Chết Arrahan Thảo Mộc – Hanbang Arrahan Soo Peeling Gel', 'ARR-TDM-006-180', 54000, 67000, 2),
(48, 'Nước Hoa Hồng Chinoshio Tía Tô – Perilla Natural Skin Lotion', 'CHI-NHH-001-500', 66000, 82000, 2),
(49, 'Nước Hoa Hồng Chinoshio Diếp Cá – Dokudami Natural Skin Lotion', 'CHI-NHH-002-500', 66000, 82000, 2),
(50, 'Nước Hoa Hồng Cocoon Hoa Sen - Nước Sen Hậu Giang', 'COC-NHH-001-310', 140000, 175000, 2),
(51, 'Kem Dưỡng Ẩm Naturie – Hatomugi Skin Conditioning Gel', 'NAT-KDA-001-180', 122000, 152000, 2),
(52, 'Nước Tẩy Trang Simple – Micellar Cleansing Water', 'SIM-NTT-001-400', 115000, 143000, 2),
(53, 'Nước Hoa Hồng Simple – Smoothing Facial Toner', 'SIM-NHH-001-200', 78000, 97000, 2),
(54, 'Nước Tẩy Trang Byphasse Phù Hợp Mọi Loại Da', 'BYP-NTT-001-500', 80000, 100000, 2),
(55, 'Nước Tẩy Trang Byphasse Dành Cho Da Dầu', 'BYP-NTT-002-500', 80000, 100000, 2),
(56, 'Sữa Rửa Mặt Hada Labo Cải Thiện Dấu Hiệu Lão Hóa Dạng Kem', 'HAD-SRM-001-80', 73000, 91000, 2),
(57, 'Sữa Rửa Mặt Hada Labo Dưỡng Ẩm Dạng Kem', 'HAD-SRM-002-80', 63000, 78000, 2),
(58, 'Sữa Rửa Mặt Hada Labo Dưỡng Trắng Dạng Kem', 'HAD-SRM-003-80', 82000, 102000, 2),
(59, 'Sữa Rửa Mặt Hada Labo Dành Cho Da Mụn & Nhạy Cảm Dạng Kem', 'HAD-SRM-004-80', 82000, 102000, 2),
(60, 'Nước Tẩy Trang Hada Labo Trắng', 'HAD-NTT-001-240', 106000, 132000, 2),
(61, 'Nước Tẩy Trang Hada Labo Xanh Dương', 'HAD-NTT-002-240', 106000, 132000, 2),
(62, 'Nước Tẩy Trang Hada Labo Xanh Ngọc', 'HAD-NTT-003-240', 112000, 140000, 2),
(63, 'Nước Tẩy Trang L’Oréal Paris Sạch Sâu – Loreal', 'LOR-NTT-001-400', 155000, 193000, 2),
(64, 'Nước Tẩy Trang L’Oréal Paris Mềm Mịn – Loreal', 'LOR-NTT-002-400', 134000, 167000, 2),
(65, 'Nước Tẩy Trang L’Oréal Paris Dành Cho Da Khô – Loreal', 'LOR-NTT-003-400', 167000, 208000, 2),
(66, 'Nước Tẩy Trang L’Oréal Paris Kiềm Dầu – Loreal', 'LOR-NTT-004-400', 167000, 208000, 2),
(67, 'Nước Tẩy Trang L’Oréal Paris Tươi Mát – Loreal', 'LOR-NTT-005-400', 134000, 167000, 2);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Chỉ mục cho bảng `inbound_orders`
--
ALTER TABLE `inbound_orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `inbound_order_details`
--
ALTER TABLE `inbound_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `outbound_orders`
--
ALTER TABLE `outbound_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Chỉ mục cho bảng `outbound_order_details`
--
ALTER TABLE `outbound_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `unique_sku` (`sku`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `inbound_orders`
--
ALTER TABLE `inbound_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `inbound_order_details`
--
ALTER TABLE `inbound_order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT cho bảng `outbound_orders`
--
ALTER TABLE `outbound_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `outbound_order_details`
--
ALTER TABLE `outbound_order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `inbound_order_details`
--
ALTER TABLE `inbound_order_details`
  ADD CONSTRAINT `inbound_order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `inbound_orders` (`id`),
  ADD CONSTRAINT `inbound_order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `outbound_orders`
--
ALTER TABLE `outbound_orders`
  ADD CONSTRAINT `outbound_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Các ràng buộc cho bảng `outbound_order_details`
--
ALTER TABLE `outbound_order_details`
  ADD CONSTRAINT `outbound_order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `outbound_orders` (`id`),
  ADD CONSTRAINT `outbound_order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
