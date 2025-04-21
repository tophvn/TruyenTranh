-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 21, 2025 at 08:14 AM
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
-- Database: `truyentranh`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `truyen_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lichsudoc`
--

CREATE TABLE `lichsudoc` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `truyen_id` int NOT NULL,
  `read_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `truyen_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `reply_id` int NOT NULL,
  `comment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reply_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slides`
--

CREATE TABLE `slides` (
  `id` int NOT NULL,
  `story_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `truyen`
--

CREATE TABLE `truyen` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `views` int NOT NULL DEFAULT '0',
  `likes` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `daily_views` int DEFAULT '0',
  `last_view_date` date DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genres` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chapters` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `truyen`
--

INSERT INTO `truyen` (`id`, `name`, `slug`, `thumb_url`, `origin_name`, `status`, `updated_at`, `views`, `likes`, `created_at`, `daily_views`, `last_view_date`, `author`, `genres`, `description`, `chapters`) VALUES
(1, 'Wind Breaker', 'wind-breaker', 'wind-breaker-thumb.jpg', '', 'ongoing', '2024-12-16 04:18:20', 573, 0, '2024-12-17 02:44:53', 0, NULL, NULL, NULL, NULL, NULL),
(2, 'Zannen Jokanbu Black General-san', 'zannen-jokanbu-black-general-san', 'zannen-jokanbu-black-general-san-thumb.jpg', '', 'ongoing', '2024-12-16 04:19:01', 33, 0, '2024-12-17 02:45:26', 0, NULL, NULL, NULL, NULL, NULL),
(3, 'Xác sống cuối cùng', 'xac-song-cuoi-cung', 'xac-song-cuoi-cung-thumb.jpg', '', 'ongoing', '2024-12-16 04:18:42', 1, 0, '2024-12-17 02:45:38', 0, NULL, NULL, NULL, NULL, NULL),
(4, 'Trụ Vương Tái Sinh Không Muốn Làm Đại Phản Diện', 'tru-vuong-tai-sinh-khong-muon-lam-dai-phan-dien', 'tru-vuong-tai-sinh-khong-muon-lam-dai-phan-dien-thumb.jpg', '', 'ongoing', '2024-12-16 04:15:55', 20, 0, '2024-12-17 02:45:56', 0, NULL, NULL, NULL, NULL, NULL),
(5, 'Thiên Quỷ Huyệt Đạo', 'thien-quy-huyet-dao', 'thien-quy-huyet-dao-thumb.jpg', '', 'ongoing', '2024-12-16 04:14:12', 0, 0, '2024-12-17 02:49:52', 0, NULL, NULL, NULL, NULL, NULL),
(6, 'Worst Ấn Bản Mới', 'worst-an-ban-moi', 'worst-an-ban-moi-thumb.jpg', '', 'ongoing', '2024-12-16 04:18:32', 37, 0, '2024-12-17 02:57:03', 0, NULL, NULL, NULL, NULL, NULL),
(7, 'Vương Quốc Huyết Mạch', 'vuong-quoc-huyet-mach', 'vuong-quoc-huyet-mach-thumb.jpg', '', 'ongoing', '2024-12-16 04:17:22', 0, 0, '2024-12-17 03:00:28', 0, NULL, NULL, NULL, NULL, NULL),
(8, 'We-On: Be The Shield', 'we-on-be-the-shield', 'we-on-be-the-shield-thumb.jpg', '', 'ongoing', '2024-12-16 04:17:57', 4, 0, '2024-12-17 03:09:54', 0, NULL, NULL, NULL, NULL, NULL),
(9, 'Vạn Tra Triêu Hoàng', 'van-tra-trieu-hoang', 'van-tra-trieu-hoang-thumb.jpg', '', 'ongoing', '2024-12-16 04:16:39', 0, 1, '2024-12-17 03:48:30', 0, NULL, NULL, NULL, NULL, NULL),
(10, 'Xuyên Nhanh Ký Chủ Cô Ấy Một Lòng Muốn Chết', 'xuyen-nhanh-ky-chu-co-ay-mot-long-muon-chet', 'xuyen-nhanh-ky-chu-co-ay-mot-long-muon-chet-thumb.jpg', 'Xuyên Nhanh: Kí Chủ Muốn Chết', 'ongoing', '2024-12-17 09:06:28', 1, 0, '2024-12-17 09:59:05', 0, NULL, NULL, NULL, NULL, NULL),
(11, 'Tuyệt Mỹ Bạch Liên Online Dạy Học', 'tuyet-my-bach-lien-online-day-hoc', 'tuyet-my-bach-lien-online-day-hoc-thumb.jpg', '', 'ongoing', '2024-12-17 09:05:27', 0, 0, '2024-12-17 10:04:40', 0, NULL, NULL, NULL, NULL, NULL),
(12, 'The Kurosagi corpse delivery service', 'the-kurosagi-corpse-delivery-service', 'the-kurosagi-corpse-delivery-service-thumb.jpg', '', 'ongoing', '2024-12-17 09:03:11', 0, 0, '2024-12-17 10:06:34', 0, NULL, NULL, NULL, NULL, NULL),
(13, 'Unmei No Makimodoshi', 'unmei-no-makimodoshi', 'unmei-no-makimodoshi-thumb.jpg', '', 'ongoing', '2024-12-17 09:05:44', 0, 0, '2024-12-17 10:09:35', 0, NULL, NULL, NULL, NULL, NULL),
(14, 'TÃ´i Chiáº¿n Äáº¥u Má»™t MÃ¬nh', 'toi-chien-dau-mot-minh', 'toi-chien-dau-mot-minh-thumb.jpg', '', 'ongoing', '2024-12-17 04:04:36', 0, 0, '2024-12-17 13:55:57', 0, NULL, NULL, NULL, NULL, NULL),
(15, 'Thá»‰nh CÃ¹ng Ta Äá»“ng MiÃªn-Xin HÃ£y Ngá»§ CÃ¹ng Ta', 'thinh-cung-ta-dong-mien-xin-hay-ngu-cung-ta', 'thinh-cung-ta-dong-mien-xin-hay-ngu-cung-ta-thumb.jpg', '', 'ongoing', '2024-12-17 04:03:45', 0, 0, '2024-12-17 14:11:11', 0, NULL, NULL, NULL, NULL, NULL),
(16, 'Sát Thủ Peter', 'sat-thu-peter', 'sat-thu-peter-thumb.jpg', 'SÁT THỦ PETER 1', 'ongoing', '2024-12-17 04:01:49', 0, 0, '2024-12-17 15:14:06', 0, NULL, NULL, NULL, NULL, NULL),
(17, 'Tóm Lại Là Em Dễ Thương Được Chưa ?', 'tom-lai-la-em-de-thuong-duoc-chua', 'tom-lai-la-em-de-thuong-duoc-chua-thumb.jpg', '', 'coming_soon', '2024-12-17 04:05:17', 1, 0, '2024-12-17 15:21:19', 0, NULL, NULL, NULL, NULL, NULL),
(18, 'Solo Leveling Arise: Nguồn Gốc Của Thợ Săn', 'solo-leveling-arise-nguon-goc-cua-tho-san', 'solo-leveling-arise-nguon-goc-cua-tho-san-thumb.jpg', '', 'ongoing', '2024-12-05 22:24:10', 112, 0, '2024-12-17 15:42:34', 0, NULL, NULL, NULL, NULL, NULL),
(19, 'Xin Chào! Bác Sĩ Thú Y', 'xin-chao-bac-si-thu-y', 'xin-chao-bac-si-thu-y-thumb.jpg', '', 'ongoing', '2024-12-17 04:06:15', 249, 0, '2024-12-18 01:53:07', 0, '2025-03-14', NULL, NULL, NULL, NULL),
(20, 'Ane no yuujin', 'ane-no-yuujin', 'ane-no-yuujin-thumb.jpg', 'Bạn của chị gái tôi', 'completed', '2024-10-22 00:14:14', 104, 0, '2024-12-18 01:55:43', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(21, 'No. 5', 'no-5', 'no-5-thumb.jpg', '', 'ongoing', '2023-10-11 13:38:38', 0, 0, '2024-12-18 01:58:27', 0, NULL, NULL, NULL, NULL, NULL),
(22, 'Rosen Garten Saga', 'rosen-garten-saga', 'rosen-garten-saga-thumb.jpg', '', 'ongoing', '2024-07-22 07:04:24', 0, 0, '2024-12-18 02:00:22', 0, NULL, NULL, NULL, NULL, NULL),
(23, 'Em Cho Cô Mượn Chút Lửa Nhé?', 'em-cho-co-muon-chut-lua-nhe', 'em-cho-co-muon-chut-lua-nhe-thumb.jpg', '', 'ongoing', '2024-05-10 03:14:52', 0, 0, '2024-12-18 02:01:07', 0, NULL, NULL, NULL, NULL, NULL),
(24, 'Đại Chiến Người Khổng Lồ', 'dai-chien-nguoi-khong-lo', 'dai-chien-nguoi-khong-lo-thumb.jpg', '', 'completed', '2023-12-10 03:49:57', 2683, 0, '2024-12-18 02:40:56', 166, '2025-03-24', NULL, NULL, NULL, NULL),
(25, 'Nhà Tôi Có Một Con Chuột', 'nha-toi-co-mot-con-chuot', 'nha-toi-co-mot-con-chuot-thumb.jpg', '', 'ongoing', '2024-04-27 01:38:38', 902, 0, '2024-12-18 02:44:55', 0, NULL, NULL, NULL, NULL, NULL),
(26, 'The Fragrant Flower Blooms With Dignity - Kaoru Hana Wa Rin To Saku', 'the-fragrant-flower-blooms-with-dignity-kaoru-hana-wa-rin-to-saku', 'the-fragrant-flower-blooms-with-dignity-kaoru-hana-wa-rin-to-saku-thumb.jpg', 'Những đóa hoa thơm nở diễm kiều', 'coming_soon', '2024-12-04 21:48:43', 0, 0, '2024-12-18 04:38:45', 0, NULL, NULL, NULL, NULL, NULL),
(27, 'Tôi Là Vị Hôn Thê Của Nam Phụ Phản Diện', 'toi-la-vi-hon-the-cua-nam-phu-phan-dien', 'toi-la-vi-hon-the-cua-nam-phu-phan-dien-thumb.jpg', '', 'ongoing', '2024-12-17 04:05:01', 0, 0, '2024-12-18 04:44:02', 0, NULL, NULL, NULL, NULL, NULL),
(28, 'Thứ mà đôi ta mong muốn', 'thu-ma-doi-ta-mong-muon', 'thu-ma-doi-ta-mong-muon-thumb.jpg', 'Fechippuru ~ bokura no junsuina koi', 'ongoing', '2024-12-17 04:04:00', 0, 0, '2024-12-18 04:44:33', 0, NULL, NULL, NULL, NULL, NULL),
(29, 'Tôi Bị Hiểu Lầm Là Diễn Viên Thiên Tài Quái Vật', 'toi-bi-hieu-lam-la-dien-vien-thien-tai-quai-vat', 'toi-bi-hieu-lam-la-dien-vien-thien-tai-quai-vat-thumb.jpg', '', 'ongoing', '2024-12-17 04:04:25', 0, 0, '2024-12-18 06:19:15', 0, NULL, NULL, NULL, NULL, NULL),
(30, 'Thì Ra Thư Ký Chu Là Người Như Vậy', 'thi-ra-thu-ky-chu-la-nguoi-nhu-vay', 'thi-ra-thu-ky-chu-la-nguoi-nhu-vay-thumb.jpg', '', 'ongoing', '2024-12-17 04:03:23', 0, 0, '2024-12-18 06:20:59', 0, NULL, NULL, NULL, NULL, NULL),
(31, 'Fantasy Bishoujo Juniku Ojisan To', 'fantasy-bishoujo-juniku-ojisan-to', 'fantasy-bishoujo-juniku-ojisan-to-thumb.jpg', '', 'ongoing', '2024-12-18 02:17:31', 0, 0, '2024-12-18 07:19:09', 0, NULL, NULL, NULL, NULL, NULL),
(32, 'Solo Leveling Ragnarok', 'solo-leveling-ragnarok', 'solo-leveling-ragnarok-thumb.jpg', '', 'ongoing', '2024-12-15 23:09:29', 329, 0, '2024-12-18 07:48:01', 0, NULL, NULL, NULL, NULL, NULL),
(33, 'Solo Leveling SS3', 'solo-leveling-ss3', 'solo-leveling-ss3-thumb.jpg', 'Tôi Thăng Cấp Một Mình SS3', 'ongoing', '2024-01-17 00:59:11', 241, 0, '2024-12-18 07:48:09', 0, NULL, NULL, NULL, NULL, NULL),
(34, 'Sống Chung Chỉ Là Để Chinh Phục Em', 'song-chung-chi-la-de-chinh-phuc-em', 'song-chung-chi-la-de-chinh-phuc-em-thumb.jpg', '', 'ongoing', '2024-12-18 02:28:45', 1, 0, '2024-12-18 08:15:31', 0, NULL, NULL, NULL, NULL, NULL),
(35, 'Vết Trăng', 'vet-trang', 'vet-trang-thumb.jpg', '', 'ongoing', '2024-12-18 02:33:07', 7, 0, '2024-12-18 08:21:55', 0, NULL, NULL, NULL, NULL, NULL),
(36, 'Thống Lĩnh Học Viện Chỉ Bằng Dao Sashimi', 'thong-linh-hoc-vien-chi-bang-dao-sashimi', 'thong-linh-hoc-vien-chi-bang-dao-sashimi-thumb.jpg', '', 'ongoing', '2024-12-18 02:30:20', 4, 0, '2024-12-18 08:21:58', 0, NULL, NULL, NULL, NULL, NULL),
(37, 'Quỷ dị khôi phục ta có thể hóa thân thành đại yêu', 'quy-di-khoi-phuc-ta-co-the-hoa-than-thanh-dai-yeu', 'quy-di-khoi-phuc-ta-co-the-hoa-than-thanh-dai-yeu-thumb.jpg', '', 'ongoing', '2024-12-18 02:28:15', 1, 0, '2024-12-18 08:23:53', 0, NULL, NULL, NULL, NULL, NULL),
(38, 'Vạn Cổ Tối Cường Tông', 'van-co-toi-cuong-tong', 'van-co-toi-cuong-tong-thumb.jpg', '', 'ongoing', '2024-12-18 02:32:53', 7, 0, '2024-12-18 08:24:19', 0, NULL, NULL, NULL, NULL, NULL),
(39, 'Kiêm Chức Thần Tiên', 'kiem-chuc-than-tien', 'kiem-chuc-than-tien-thumb.jpg', '', 'ongoing', '2024-12-17 03:46:29', 19, 0, '2024-12-18 08:36:53', 0, NULL, NULL, NULL, NULL, NULL),
(40, 'Sống Sót Như Một Hầu Gái Trong Trò Chơi Kinh Dị', 'song-sot-nhu-mot-hau-gai-trong-tro-choi-kinh-di', 'song-sot-nhu-mot-hau-gai-trong-tro-choi-kinh-di-thumb.jpg', 'Tồn Tại Với Tư Cách Hầu Gái Trong Game Kinh Dị', 'coming_soon', '2024-12-17 04:02:13', 1, 0, '2024-12-18 08:44:06', 0, NULL, NULL, NULL, NULL, NULL),
(41, 'Bên bếp lửa nhà Alice-san', 'ben-bep-lua-nha-alice-san', 'ben-bep-lua-nha-alice-san-thumb.jpg', 'Alice', 'ongoing', '2024-11-17 22:26:34', 1, 0, '2024-12-18 08:53:22', 0, NULL, NULL, NULL, NULL, NULL),
(42, 'Yasei No Last Boss Ga Arawareta', 'yasei-no-last-boss-ga-arawareta', 'yasei-no-last-boss-ga-arawareta-thumb.jpg', 'A Wild Last Boss Appeared', 'ongoing', '2024-12-18 02:33:30', 53, 0, '2024-12-18 09:31:06', 0, NULL, NULL, NULL, NULL, NULL),
(43, 'Thực Ra Tôi Mới Là Thật', 'thuc-ra-toi-moi-la-that', 'thuc-ra-toi-moi-la-that-thumb.jpg', 'Tôi Là Minh Chứng Của Sự Thật', 'coming_soon', '2024-12-18 02:30:59', 1, 0, '2024-12-18 09:50:53', 0, NULL, NULL, NULL, NULL, NULL),
(44, 'Trở Thành Thiên Tài Tốc Biến Của Học Viện Ma Pháp', 'tro-thanh-thien-tai-toc-bien-cua-hoc-vien-ma-phap', 'tro-thanh-thien-tai-toc-bien-cua-hoc-vien-ma-phap-thumb.jpg', '', 'ongoing', '2024-12-18 02:32:41', 9, 0, '2024-12-18 09:55:30', 0, NULL, NULL, NULL, NULL, NULL),
(45, 'Tôi Trở Thành Vợ Nam Chính', 'toi-tro-thanh-vo-nam-chinh', 'toi-tro-thanh-vo-nam-chinh-thumb.jpg', '', 'ongoing', '2024-12-18 02:32:26', 65, 0, '2024-12-18 10:08:17', 0, NULL, NULL, NULL, NULL, NULL),
(46, 'Thanh Gươm Diệt Quỷ', 'thanh-guom-diet-quy', 'thanh-guom-diet-quy-thumb.jpg', 'Kimetsu no Yaiba', 'completed', '2024-05-15 01:24:07', 366, 0, '2024-12-18 10:14:31', 0, NULL, NULL, NULL, NULL, NULL),
(47, 'Thiên Hạ Đệ Nhất Đại Sư Huynh', 'thien-ha-de-nhat-dai-su-huynh', 'thien-ha-de-nhat-dai-su-huynh-thumb.jpg', 'Thiên Hạ Đệ Nhất Đại Huynh', 'ongoing', '2024-12-18 02:29:56', 3, 0, '2024-12-18 10:57:56', 0, NULL, NULL, NULL, NULL, NULL),
(48, 'Phương Pháp Che Giấu Đứa Con Của Hoàng Đế', 'phuong-phap-che-giau-dua-con-cua-hoang-de', 'phuong-phap-che-giau-dua-con-cua-hoang-de-thumb.jpg', 'Cách Che Giấu Đứa Con Của Hoàng Đế', 'ongoing', '2024-12-18 02:27:55', 1, 0, '2024-12-18 11:40:29', 0, NULL, NULL, NULL, NULL, NULL),
(49, 'Senryuu Shoujo', 'senryuu-shoujo', 'senryuu-shoujo-thumb.jpg', 'Cô nàng làm thơ', 'ongoing', '2024-12-18 02:28:29', 1, 0, '2024-12-18 11:42:23', 0, NULL, NULL, NULL, NULL, NULL),
(50, 'Trở Thành Cô Cháu Gái Bị Khinh Miệt Của Gia Tộc Võ Lâm', 'tro-thanh-co-chau-gai-bi-khinh-miet-cua-gia-toc-vo-lam', 'tro-thanh-co-chau-gai-bi-khinh-miet-cua-gia-toc-vo-lam-thumb.jpg', 'Trở thành cô cháu gái bị khinh miệt của nhà quyền quý', 'completed', '2024-11-06 22:27:35', 227, 0, '2024-12-18 11:50:11', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(51, 'Sống Trong Ngôi Nhà Cấp 4', 'song-trong-ngoi-nha-cap-4', 'song-trong-ngoi-nha-cap-4-thumb.jpg', 'Hirayasumi', 'ongoing', '2024-12-18 02:28:56', 1, 0, '2024-12-18 12:41:39', 0, NULL, NULL, NULL, NULL, NULL),
(52, 'Cylcia = Code', 'cylcia-code-123', 'cylcia--code-123-thumb.jpg', '', 'ongoing', '2024-01-17 22:42:46', 1, 0, '2024-12-18 13:06:11', 0, NULL, NULL, NULL, NULL, NULL),
(53, 'Chú Tôi Ở Dị Giới', 'chu-toi-o-di-gioi', 'chu-toi-o-di-gioi-thumb.jpg', '', 'ongoing', '2024-12-18 07:48:53', 2, 0, '2024-12-18 13:13:25', 0, NULL, NULL, NULL, NULL, NULL),
(54, 'Có Nhỏ Vợ Cũ Hồi Xuân Trong Lớp Tôi', 'co-nho-vo-cu-hoi-xuan-trong-lop-toi', 'co-nho-vo-cu-hoi-xuan-trong-lop-toi-thumb.jpg', 'Ore no Kurasu ni Wakagaetta Moto Yome ga Iru', 'ongoing', '2024-12-03 03:43:38', 136, 0, '2024-12-18 13:45:01', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(55, 'Trở Lại Ngày Tận Thế', 'tro-lai-ngay-tan-the', 'tro-lai-ngay-tan-the-thumb.jpg', '', 'ongoing', '2024-01-12 00:52:05', 1, 0, '2024-12-18 13:46:16', 0, NULL, NULL, NULL, NULL, NULL),
(56, 'Thế Hệ Bất Hảo', 'the-he-bat-hao', 'the-he-bat-hao-thumb.jpg', 'Thế Hệ Bất Hảo', 'ongoing', '2024-12-18 07:48:49', 3, 0, '2024-12-18 13:49:14', 0, NULL, NULL, NULL, NULL, NULL),
(57, 'Đại Pháp Sư Thần Thoại Tái Lâm', 'dai-phap-su-than-thoai-tai-lam', 'dai-phap-su-than-thoai-tai-lam-thumb.jpg', 'Đại Pháp Sư Thần Thoại Tái Lâm', 'ongoing', '2024-12-18 07:47:20', 1, 0, '2024-12-18 13:51:44', 0, NULL, NULL, NULL, NULL, NULL),
(58, 'Hầu Gái Trong Trò Chơi Harem Ngược Muốn Nghỉ Việc', 'hau-gai-trong-tro-choi-harem-nguoc-muon-nghi-viec', 'hau-gai-trong-tro-choi-harem-nguoc-muon-nghi-viec-thumb.jpg', '', 'ongoing', '2024-12-18 07:48:45', 20, 0, '2024-12-18 13:52:07', 0, NULL, NULL, NULL, NULL, NULL),
(59, 'Kiếm Thần: Thần Chi Tử', 'kiem-than-than-chi-tu', 'kiem-than-than-chi-tu-thumb.jpg', '', 'ongoing', '2024-12-18 07:48:57', 1, 0, '2024-12-18 13:52:14', 0, NULL, NULL, NULL, NULL, NULL),
(60, 'Trợ lí pháp sư vô dụng bắt đầu cuộc sống mới', 'tro-li-phap-su-vo-dung-bat-dau-cuoc-song-moi', 'tro-li-phap-su-vo-dung-bat-dau-cuoc-song-moi-thumb.jpg', 'Ore Igai Dare mo Saishu Dekinai Sozai na no ni \"Sozai Saishuritsu ga Hikui\" to Pawahara suru Osananajimi Renkinjutsushi to Zetsuen shita Senzoku Madoushi', 'ongoing', '2024-07-14 02:56:54', 2, 0, '2024-12-18 13:52:27', 0, NULL, NULL, NULL, NULL, NULL),
(61, 'Cô Dâu Hiến Tế Của Thủy Thần', 'co-dau-hien-te-cua-thuy-than', 'co-dau-hien-te-cua-thuy-than-thumb.jpg', '', 'ongoing', '2024-12-18 07:47:29', 1, 0, '2024-12-18 13:55:11', 0, NULL, NULL, NULL, NULL, NULL),
(62, 'Lượng Mana Đáy Xã Hội! Ta Vô Địch Nhờ Kỹ Năng Của Mình', 'luong-mana-day-xa-hoi-ta-vo-dich-nho-ky-nang-cua-minh', 'luong-mana-day-xa-hoi-ta-vo-dich-nho-ky-nang-cua-minh-thumb.jpg', 'Lượng Mana Đáy Xã Hội! Ta Vô Địch Nhờ Kỹ Năng Của Mình', 'ongoing', '2024-12-18 07:48:14', 3, 0, '2024-12-18 13:55:31', 0, NULL, NULL, NULL, NULL, NULL),
(63, 'Khi Điện Thoại Đổ Chuông', 'khi-dien-thoai-do-chuong', 'khi-dien-thoai-do-chuong-thumb.jpg', 'Khi Điện Thoại Đổ Chuông', 'ongoing', '2024-12-18 07:49:01', 1, 0, '2024-12-19 02:38:43', 0, NULL, NULL, NULL, NULL, NULL),
(64, 'Nữ Hiệp Sĩ Goblin', 'nu-hiep-si-goblin', 'nu-hiep-si-goblin-thumb.jpg', 'Felmale Knight Gobin', 'ongoing', '2024-05-24 08:27:40', 1, 0, '2024-12-19 05:29:37', 0, NULL, NULL, NULL, NULL, NULL),
(65, 'Album Natural Wallpapers', 'album-natural-wallpapers', 'album-natural-wallpapers-thumb.jpg', '', 'ongoing', '2023-12-16 22:51:18', 1, 0, '2024-12-19 05:29:57', 0, NULL, NULL, NULL, NULL, NULL),
(66, 'Cả Nhà Bạo Quân Đều Dựa Vào Việc Đọc Tiếng Lòng Của Cô Ấy Để Giữ Mạng', 'ca-nha-bao-quan-deu-dua-vao-viec-doc-tieng-long-cua-co-ay-de-giu-mang', 'ca-nha-bao-quan-deu-dua-vao-viec-doc-tieng-long-cua-co-ay-de-giu-mang-thumb.jpg', '', 'ongoing', '2024-12-18 07:48:24', 2, 0, '2024-12-19 05:33:38', 0, NULL, NULL, NULL, NULL, NULL),
(67, 'The Ride On King', 'the-ride-on-king', 'the-ride-on-king-thumb.jpg', 'Hành Trình Của Đại Đế', 'ongoing', '2024-12-18 07:48:05', 1, 0, '2024-12-19 05:34:11', 0, NULL, NULL, NULL, NULL, NULL),
(68, 'Tình Yêu Màu Lam Nhà Wakaba', 'tinh-yeu-mau-lam-nha-wakaba', 'tinh-yeu-mau-lam-nha-wakaba-thumb.jpg', 'Tình Yêu Màu Lam Nhà Wakaba', 'ongoing', '2024-12-18 07:48:41', 1, 0, '2024-12-19 05:40:45', 0, NULL, NULL, NULL, NULL, NULL),
(69, 'Coffee wo Shizuka ni', 'coffee-wo-shizuka-ni', 'coffee-wo-shizuka-ni-thumb.jpg', '', 'completed', '2024-10-13 05:02:16', 1, 0, '2024-12-19 07:43:55', 0, NULL, NULL, NULL, NULL, NULL),
(70, 'Ánh Trăng Vì Tôi Mà Đến', 'anh-trang-vi-toi-ma-den', 'anh-trang-vi-toi-ma-den-thumb.jpg', '', 'ongoing', '2024-12-19 05:48:05', 2, 0, '2024-12-19 10:53:51', 0, NULL, NULL, NULL, NULL, NULL),
(71, 'Xạ thủ đạn ma', 'xa-thu-dan-ma', 'xa-thu-dan-ma-thumb.jpg', 'Arcane Sniper', 'ongoing', '2024-12-19 06:22:44', 1, 0, '2024-12-19 15:38:46', 0, NULL, NULL, NULL, NULL, NULL),
(72, 'Trở Thành Con Gái Nhà Tài Phiệt', 'tro-thanh-con-gai-nha-tai-phiet', 'tro-thanh-con-gai-nha-tai-phiet-thumb.jpg', 'A Nào', 'ongoing', '2024-12-19 22:59:22', 1, 0, '2024-12-20 12:20:25', 0, NULL, NULL, NULL, NULL, NULL),
(73, 'Từ Phù Thủy Mạnh Nhất Khu Ổ Chuột Đến Vô Song Tại Học Viện Pháp Thuật Hoàng Gia', 'tu-phu-thuy-manh-nhat-khu-o-chuot-den-vo-song-tai-hoc-vien-phap-thuat-hoang-gia', 'tu-phu-thuy-manh-nhat-khu-o-chuot-den-vo-song-tai-hoc-vien-phap-thuat-hoang-gia-thumb.jpg', 'The Irregular of the Royal Academy of Magic ~The Strongest Sorcerer From the Slums is Unrivaled in the School of Royals ~', 'ongoing', '2024-12-22 00:13:20', 2, 0, '2024-12-22 22:25:25', 0, NULL, NULL, NULL, NULL, NULL),
(74, 'Thợ Săn Huyền Thoại Trẻ Hóa', 'tho-san-huyen-thoai-tre-hoa', 'tho-san-huyen-thoai-tre-hoa-thumb.jpg', '', 'ongoing', '2024-12-25 03:05:42', 1, 0, '2024-12-26 01:33:32', 0, NULL, NULL, NULL, NULL, NULL),
(75, 'Vô Hạn Thôi Diễn', 'vo-han-thoi-dien', 'vo-han-thoi-dien-thumb.jpg', '', 'ongoing', '2024-12-25 03:10:22', 289, 0, '2024-12-26 08:59:40', 0, '2025-03-24', NULL, NULL, NULL, NULL),
(76, 'Thăng Cấp Trong Ngục Tối Độc Quyền', 'thang-cap-trong-nguc-toi-doc-quyen', 'thang-cap-trong-nguc-toi-doc-quyen-thumb.jpg', '', 'ongoing', '2024-12-25 03:04:26', 1, 0, '2024-12-26 09:08:33', 0, NULL, NULL, NULL, NULL, NULL),
(77, 'Vị Thần Trở Lại', 'vi-than-tro-lai', 'vi-than-tro-lai-thumb.jpg', 'Sự Trở Lại Của Thần', 'ongoing', '2024-12-25 03:08:22', 1, 0, '2024-12-26 09:14:19', 0, NULL, NULL, NULL, NULL, NULL),
(78, 'Nàng Dâu nhà họ Kyougane', 'nang-dau-nha-ho-kyougane', 'nang-dau-nha-ho-kyougane-thumb.jpg', 'Kyouganeke no Hanayome', 'ongoing', '2024-06-09 06:32:42', 341, 0, '2024-12-26 09:14:56', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(79, 'Thiết Huyết Kiếm Sĩ Hồi Quy', 'thiet-huyet-kiem-si-hoi-quy', 'thiet-huyet-kiem-si-hoi-quy-thumb.jpg', 'Thiết Huyết Cẩu Kiếm Sĩ Báo Thù', 'ongoing', '2024-12-25 03:05:21', 1, 0, '2024-12-26 09:16:02', 0, NULL, NULL, NULL, NULL, NULL),
(80, 'Thể Thao Cực Hạn', 'the-thao-cuc-han', 'the-thao-cuc-han-thumb.jpg', '', 'ongoing', '2024-12-25 03:05:00', 1, 0, '2024-12-26 09:17:50', 0, NULL, NULL, NULL, NULL, NULL),
(81, 'Hoa Hồng Ẩn Giấu Sao Băng', 'hoa-hong-an-giau-sao-bang', 'hoa-hong-an-giau-sao-bang-thumb.jpg', '', 'ongoing', '2024-12-26 23:54:56', 1, 0, '2024-12-28 03:28:08', 0, NULL, NULL, NULL, NULL, NULL),
(82, 'Thần Võ Thiên Tôn', 'than-vo-thien-ton', 'than-vo-thien-ton-thumb.jpg', '', 'ongoing', '2024-12-31 04:06:33', 1, 0, '2024-12-31 19:11:32', 0, NULL, NULL, NULL, NULL, NULL),
(83, 'Vô Địch Học Bạ Hệ Thống', 'vo-dich-hoc-ba-he-thong', 'vo-dich-hoc-ba-he-thong-thumb.jpg', '', 'ongoing', '2025-01-06 00:03:42', 1, 0, '2025-01-07 10:11:26', 0, NULL, NULL, NULL, NULL, NULL),
(84, 'Vưu Vật', 'vuu-vat', 'vuu-vat-thumb.jpg', '', 'coming_soon', '2025-01-09 00:35:47', 1, 0, '2025-01-09 07:21:08', 0, NULL, NULL, NULL, NULL, NULL),
(85, 'Sau Khi Kết Thúc, Tôi Đã Cứu Rỗi Vai Phản Diện Bằng Tiền', 'sau-khi-ket-thuc-toi-da-cuu-roi-vai-phan-dien-bang-tien', 'sau-khi-ket-thuc-toi-da-cuu-roi-vai-phan-dien-bang-tien-thumb.jpg', 'Sau Khi Kết Thúc', 'ongoing', '2025-01-17 22:53:26', 2, 0, '2025-01-18 05:20:37', 0, NULL, NULL, NULL, NULL, NULL),
(86, 'Thám Tử Conan', 'tham-tu-conan', 'tham-tu-conan-thumb.jpg', '', 'ongoing', '2024-12-09 01:55:17', 2, 0, '2025-01-19 14:10:38', 0, NULL, NULL, NULL, NULL, NULL),
(87, 'Nữ Hầu Gái Ác Ma Chỉ Muốn Bị Tiểu Thư Hành Hạ', 'nu-hau-gai-ac-ma-chi-muon-bi-tieu-thu-hanh-ha', 'nu-hau-gai-ac-ma-chi-muon-bi-tieu-thu-hanh-ha-thumb.jpg', 'Nữ Hầu Gái Ác Ma Chỉ Muốn Bị Tiểu Thư Hành Hạ', 'ongoing', '2025-01-24 22:16:23', 1, 0, '2025-01-26 02:47:09', 0, NULL, NULL, NULL, NULL, NULL),
(88, 'Tiểu Gia Chủ của Tứ Xuyên Đường Gia trở thành Kiếm Thần', 'tieu-gia-chu-cua-tu-xuyen-duong-gia-tro-thanh-kiem-than', 'tieu-gia-chu-cua-tu-xuyen-duong-gia-tro-thanh-kiem-than-thumb.jpg', '', 'ongoing', '2025-02-03 23:07:36', 1, 0, '2025-02-04 04:49:57', 0, NULL, NULL, NULL, NULL, NULL),
(89, 'Xuyên Vào Tiểu Thuyết Làm Nữ Hoàng Tàn Độc', 'xuyen-vao-tieu-thuyet-lam-nu-hoang-tan-doc', 'xuyen-vao-tieu-thuyet-lam-nu-hoang-tan-doc-thumb.jpg', '', 'coming_soon', '2025-02-03 23:09:31', 2, 0, '2025-02-04 04:50:10', 0, NULL, NULL, NULL, NULL, NULL),
(90, 'Đến Giờ', 'den-gio', 'den-gio-thumb.jpg', 'Đến giờ Thẩm vấn rồi', 'ongoing', '2025-02-13 00:43:22', 2, 0, '2025-02-13 15:41:10', 0, NULL, NULL, NULL, NULL, NULL),
(91, 'Nội Gián', 'noi-gian', 'noi-gian-thumb.jpg', 'The life of a spy in the cult', 'ongoing', '2025-02-13 00:38:55', 2, 0, '2025-02-13 15:43:13', 0, NULL, NULL, NULL, NULL, NULL),
(92, 'Được Yêu Thương Mà Còn Ngại Ngùng Sao!', 'duoc-yeu-thuong-ma-con-ngai-ngung-sao', 'duoc-yeu-thuong-ma-con-ngai-ngung-sao-thumb.jpg', 'Khi ác nữ phản diện được yêu', 'completed', '2025-02-06 04:12:26', 2, 0, '2025-02-13 15:43:40', 0, NULL, NULL, NULL, NULL, NULL),
(93, 'Tiên Làm Nô Thần Là Bộc, Đại Đế Làm Chó Giữ Nhà', 'tien-lam-no-than-la-boc-dai-de-lam-cho-giu-nha', 'tien-lam-no-than-la-boc-dai-de-lam-cho-giu-nha-thumb.jpg', '', 'ongoing', '2025-02-12 22:40:48', 3, 0, '2025-02-13 15:45:20', 0, NULL, NULL, NULL, NULL, NULL),
(94, 'Lục nhân thập tự giá', 'luc-nhan-thap-tu-gia', 'luc-nhan-thap-tu-gia-thumb.jpg', '', 'ongoing', '2025-02-13 23:56:40', 3, 0, '2025-02-15 04:36:39', 0, NULL, NULL, NULL, NULL, NULL),
(95, 'Trở Lại Cổ Đại Làm Hoàng Đế', 'tro-lai-co-dai-lam-hoang-de', 'tro-lai-co-dai-lam-hoang-de-thumb.jpg', 'Trở Lại Cổ Đại Làm Hoàng Đế', 'ongoing', '2025-02-19 01:13:16', 15, 0, '2025-02-19 07:57:23', 0, NULL, NULL, NULL, NULL, NULL),
(96, 'Học Nhóm', 'hoc-nhom', 'hoc-nhom-thumb.jpg', '', 'ongoing', '2025-02-19 22:38:13', 1, 0, '2025-02-20 12:25:18', 0, NULL, NULL, NULL, NULL, NULL),
(97, 'Kỷ Nguyên Kỳ Lạ', 'ky-nguyen-ky-la', 'ky-nguyen-ky-la-thumb.jpg', 'Toàn Cầu Quỷ Dị Thời Đại', 'ongoing', '2025-02-21 23:02:23', 7, 0, '2025-02-22 10:51:34', 0, NULL, NULL, NULL, NULL, NULL),
(98, 'Tôi Đã Nuôi Dưỡng Nam Phụ Phản Diện', 'toi-da-nuoi-duong-nam-phu-phan-dien', 'toi-da-nuoi-duong-nam-phu-phan-dien-thumb.jpg', 'Tôi Đã Nuôi Dưỡng Nam Phụ Phản Diện', 'ongoing', '2025-02-24 23:50:47', 2, 0, '2025-02-25 10:55:11', 0, NULL, NULL, NULL, NULL, NULL),
(99, 'Con Trai Út Huyền Thoại Nhà Hầu Tước', 'con-trai-ut-huyen-thoai-nha-hau-tuoc', 'con-trai-ut-huyen-thoai-nha-hau-tuoc-thumb.jpg', 'Legendary Youngest Son of the Marquis House', 'coming_soon', '2025-02-17 23:30:17', 1, 0, '2025-03-01 04:47:29', 0, NULL, NULL, NULL, NULL, NULL),
(100, 'Vô Tình Ghi Danh', 'vo-tinh-ghi-danh', 'vo-tinh-ghi-danh-thumb.jpg', '', 'ongoing', '2025-02-28 23:34:24', 1, 0, '2025-03-02 01:45:11', 0, NULL, NULL, NULL, NULL, NULL),
(101, 'Thất Tinh', 'that-tinh', 'that-tinh-thumb.jpg', '', 'ongoing', '2025-03-03 07:42:17', 3, 0, '2025-03-03 14:20:01', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(102, 'Vận May Không Ngờ', 'van-may-khong-ngo', 'van-may-khong-ngo-thumb.jpg', 'Vận May Bất Ngờ', 'ongoing', '2025-03-03 04:31:52', 3, 0, '2025-03-03 14:20:08', 0, NULL, NULL, NULL, NULL, NULL),
(103, 'Majo Taisen - The War of Greedy Witches', 'majo-taisen-the-war-of-greedy-witches', 'majo-taisen-the-war-of-greedy-witches-thumb.jpg', 'Majo Taisen', 'ongoing', '2025-03-04 00:07:25', 30, 0, '2025-03-04 20:45:50', 0, NULL, NULL, NULL, NULL, NULL),
(104, 'Đại Phụng Đả Canh Nhân', 'dai-phung-da-canh-nhan', 'dai-phung-da-canh-nhan-thumb.jpg', '', 'ongoing', '2025-02-23 23:46:36', 1586, 0, '2025-03-04 20:51:55', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(105, 'Toàn Chức Pháp Sư', 'toan-chuc-phap-su', 'toan-chuc-phap-su-thumb.jpg', '', 'ongoing', '2024-04-14 04:23:00', 2, 0, '2025-03-04 20:52:14', 0, NULL, NULL, NULL, NULL, NULL),
(106, 'Ma Đạo Chuyển Sinh Ký', 'ma-dao-chuyen-sinh-ky', 'ma-dao-chuyen-sinh-ky-thumb.jpg', 'Ma Đạo Luân Hồi Ký', 'ongoing', '2025-03-04 00:07:16', 28, 0, '2025-03-04 21:10:23', 0, NULL, NULL, NULL, NULL, NULL),
(107, 'Lý Do Tôi Rời Bỏ Quỷ Vương', 'ly-do-toi-roi-bo-quy-vuong', 'ly-do-toi-roi-bo-quy-vuong-thumb.jpg', '', 'ongoing', '2025-03-04 00:07:04', 59, 0, '2025-03-04 21:20:55', 0, NULL, NULL, NULL, NULL, NULL),
(108, 'Kuhime', 'kuhime', 'kuhime-thumb.jpg', '', 'ongoing', '2025-03-04 00:05:46', 11, 0, '2025-03-04 21:22:45', 0, NULL, NULL, NULL, NULL, NULL),
(109, 'Kuroiwa Medaka Ni Watashi No Kawaii Ga Tsuujinai', 'kuroiwa-medaka-ni-watashi-no-kawaii-ga-tsuujinai', 'kuroiwa-medaka-ni-watashi-no-kawaii-ga-tsuujinai-thumb.jpg', '', 'coming_soon', '2025-03-04 00:06:01', 103, 0, '2025-03-05 04:36:56', 0, '2025-03-18', NULL, NULL, NULL, NULL),
(110, 'Yuragi-Sou No Yuuna-San', 'yuragi-sou-no-yuuna-san', 'yuragi-sou-no-yuuna-san-thumb.jpg', 'Ma nữ cứng đầu', 'completed', '2025-01-16 12:17:33', 3, 0, '2025-03-05 07:00:42', 0, NULL, NULL, NULL, NULL, NULL),
(111, 'Đặc Nhiệm Thám Tử', 'dac-nhiem-tham-tu', 'dac-nhiem-tham-tu-thumb.jpg', '', 'coming_soon', '2025-02-06 09:09:20', 1, 0, '2025-03-05 07:12:20', 0, NULL, NULL, NULL, NULL, NULL),
(112, 'NOW', 'now', 'now-thumb.jpg', '', 'completed', '2024-05-12 08:23:39', 1, 0, '2025-03-05 07:17:54', 0, NULL, NULL, NULL, NULL, NULL),
(113, 'Bạn Thời Thơ Ấu', 'ban-thoi-tho-au', 'ban-thoi-tho-au-thumb.jpg', 'My Childhood Friend', 'completed', '2024-05-12 08:15:24', 1, 0, '2025-03-05 07:19:02', 0, NULL, NULL, NULL, NULL, NULL),
(114, 'Rainbow', 'rainbow', 'rainbow-thumb.jpg', '', 'completed', '2024-05-13 06:49:26', 1, 0, '2025-03-05 07:21:28', 0, NULL, NULL, NULL, NULL, NULL),
(115, 'Phong Vân', 'phong-van', 'phong-van-thumb.jpg', '', 'completed', '2024-01-27 07:08:49', 1, 0, '2025-03-05 07:23:56', 0, NULL, NULL, NULL, NULL, NULL),
(116, 'Mối Quan Hệ Đặc Biệt', 'moi-quan-he-dac-biet', 'moi-quan-he-dac-biet-thumb.jpg', '', 'ongoing', '2025-03-03 09:21:58', 1, 0, '2025-03-05 07:25:29', 0, NULL, NULL, NULL, NULL, NULL),
(117, 'Yumizuka Iroha wa Tejun ga Daiji!', 'yumizuka-iroha-wa-tejun-ga-daiji', 'yumizuka-iroha-wa-tejun-ga-daiji-thumb.jpg', 'Yumizuka Iroha\'s No Good Without Her Procedure!', 'ongoing', '2025-03-03 09:33:30', 1, 0, '2025-03-05 07:28:13', 0, NULL, NULL, NULL, NULL, NULL),
(118, 'Hồi ức Của Chiến Thần', 'hoi-uc-cua-chien-than', 'hoi-uc-cua-chien-than-thumb.jpg', 'Chiến thần bí sử', 'ongoing', '2025-03-04 05:02:22', 18, 0, '2025-03-05 08:26:41', 0, NULL, NULL, NULL, NULL, NULL),
(119, 'Không Chết Được Ta Đành Thống Trị Ma Giới', 'khong-chet-duoc-ta-danh-thong-tri-ma-gioi', 'khong-chet-duoc-ta-danh-thong-tri-ma-gioi-thumb.jpg', '', 'ongoing', '2025-03-04 05:05:23', 9, 0, '2025-03-05 09:06:40', 0, NULL, NULL, NULL, NULL, NULL),
(120, 'Kiếm Sĩ Thiên Tài Của Học Viện', 'kiem-si-thien-tai-cua-hoc-vien', 'kiem-si-thien-tai-cua-hoc-vien-thumb.jpg', 'Kiếm Thánh Thiên Tài Của Học Viện', 'ongoing', '2025-03-04 05:05:34', 2951, 0, '2025-03-05 09:52:38', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(121, 'Tsuma, Shougakusei Ni Naru.', 'tsuma-shougakusei-ni-naru', 'tsuma-shougakusei-ni-naru-thumb.jpg', 'If My Wife Became an Elementary School Student', 'completed', '2025-02-09 05:22:43', 424, 0, '2025-03-05 10:12:12', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(122, 'Câu Lạc Bộ Siêu Cấp Về Nhà', 'cau-lac-bo-sieu-cap-ve-nha', 'cau-lac-bo-sieu-cap-ve-nha-thumb.jpg', 'Journey Home After School Houkago Kitaku Biyori', 'ongoing', '2025-02-20 03:27:12', 1699, 0, '2025-03-05 10:35:32', 0, '2025-03-18', NULL, NULL, NULL, NULL),
(123, 'Đại chiến Titan - Before the fall', 'dai-chien-titan-before-the-fall', 'dai-chien-titan-before-the-fall-thumb.jpg', 'Attack on Titans', 'ongoing', '2023-11-30 05:19:09', 1, 0, '2025-03-05 10:44:29', 0, NULL, NULL, NULL, NULL, NULL),
(124, 'Chainsaw man - Thợ Săn Quỷ', 'chainsaw-man-tho-san-quy', 'chainsaw-man-tho-san-quy-thumb.jpg', '', 'ongoing', '2024-05-01 13:51:51', 1, 0, '2025-03-05 10:45:41', 0, NULL, NULL, NULL, NULL, NULL),
(125, 'Chainsawman Phần 2', 'chainsawman-phan-2', 'chainsawman-phan-2-thumb.jpg', 'Chainsawman', 'ongoing', '2025-03-03 09:12:16', 479, 0, '2025-03-05 10:46:13', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(126, 'Ruri Dragon', 'ruri-dragon', 'ruri-dragon-thumb.jpg', 'Long Nữ Ruri', 'ongoing', '2025-02-27 03:51:35', 9, 0, '2025-03-05 10:52:59', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(127, 'Yêu Túc Sơn', 'yeu-tuc-son', 'yeu-tuc-son-thumb.jpg', '', 'ongoing', '2025-03-05 10:59:49', 3, 0, '2025-03-05 11:40:19', 0, NULL, NULL, NULL, NULL, NULL),
(128, 'Tôi Hoài Nghi Ảnh Đế Đang Theo Đuổi Tôi', 'toi-hoai-nghi-anh-de-dang-theo-duoi-toi', 'toi-hoai-nghi-anh-de-dang-theo-duoi-toi-thumb.jpg', '', 'ongoing', '2025-03-05 10:56:32', 2, 0, '2025-03-05 12:02:28', 0, NULL, NULL, NULL, NULL, NULL),
(129, 'Thanh Mai Trúc Mã Của Đệ Nhất Thiên Hạ', 'thanh-mai-truc-ma-cua-de-nhat-thien-ha', 'thanh-mai-truc-ma-cua-de-nhat-thien-ha-thumb.jpg', '', 'ongoing', '2025-03-05 10:52:40', 1, 0, '2025-03-05 12:03:16', 0, NULL, NULL, NULL, NULL, NULL),
(130, 'Thành Thần Bắt Đầu Từ Thủy Hầu Tử', 'thanh-than-bat-dau-tu-thuy-hau-tu', 'thanh-than-bat-dau-tu-thuy-hau-tu-thumb.jpg', 'Thành Thần Bắt Đầu Từ Thủy Hầu Tử', 'ongoing', '2025-03-05 10:52:50', 1, 0, '2025-03-05 12:03:22', 0, NULL, NULL, NULL, NULL, NULL),
(131, 'Thợ Rèn Huyền Thoại', 'tho-ren-huyen-thoai', 'tho-ren-huyen-thoai-thumb.jpg', '', 'ongoing', '2025-03-05 10:54:03', 1, 0, '2025-03-05 12:03:37', 0, NULL, NULL, NULL, NULL, NULL),
(132, 'Tôi Đang Được Nuôi Dưỡng Bởi Những Kẻ Phản Diện', 'toi-dang-duoc-nuoi-duong-boi-nhung-ke-phan-dien', 'toi-dang-duoc-nuoi-duong-boi-nhung-ke-phan-dien-thumb.jpg', '', 'ongoing', '2025-03-05 10:56:19', 2, 0, '2025-03-05 12:30:05', 0, NULL, NULL, NULL, NULL, NULL),
(133, 'Chuyển Sinh Thành Liễu Đột Biến', 'chuyen-sinh-thanh-lieu-dot-bien', 'chuyen-sinh-thanh-lieu-dot-bien-thumb.jpg', 'Từ Đại Thụ Tiến Hóa', 'ongoing', '2025-03-04 04:50:57', 1, 0, '2025-03-05 12:36:29', 0, NULL, NULL, NULL, NULL, NULL),
(134, 'The Fairytale-like You Goes On The Assault', 'the-fairytale-like-you-goes-on-the-assault', 'the-fairytale-like-you-goes-on-the-assault-thumb.jpg', '', 'ongoing', '2025-03-05 10:53:15', 40, 0, '2025-03-05 12:39:57', 0, NULL, NULL, NULL, NULL, NULL),
(135, 'Kougekiryoku Zero Kara Hajimeru Kenseitan', 'kougekiryoku-zero-kara-hajimeru-kenseitan', 'kougekiryoku-zero-kara-hajimeru-kenseitan-thumb.jpg', 'Câu chuyện của Kiếm sĩ vô năng trên con đường trở thành Thánh Kiếm', 'ongoing', '2024-08-15 12:44:59', 1, 0, '2025-03-05 12:40:27', 0, NULL, NULL, NULL, NULL, NULL),
(136, 'Yuricam', 'yuricam', 'yuricam-thumb.jpg', 'Yurika no campus life', 'completed', '2024-01-20 03:13:48', 300, 0, '2025-03-05 12:41:17', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(137, 'Lý Do Kết Hôn', 'ly-do-ket-hon', 'ly-do-ket-hon-thumb.jpg', '', 'ongoing', '2024-03-27 07:12:58', 1, 0, '2025-03-05 12:45:38', 0, NULL, NULL, NULL, NULL, NULL),
(138, 'Massacre Happy End', 'massacre-happy-end', 'massacre-happy-end-thumb.jpg', 'Gyakusatsu Happiendo', 'ongoing', '2024-01-28 04:00:21', 14, 0, '2025-03-05 12:52:50', 0, NULL, NULL, NULL, NULL, NULL),
(139, 'Cường Giả Đến Từ Trại Tâm Thần', 'cuong-gia-den-tu-trai-tam-than', 'cuong-gia-den-tu-trai-tam-than-thumb.jpg', '', 'coming_soon', '2025-03-04 04:52:41', 0, 0, '2025-03-06 03:12:24', 0, NULL, NULL, NULL, NULL, NULL),
(140, 'Amagami-san Chi no Enmusubi!', 'amagami-san-chi-no-enmusubi', 'amagami-san-chi-no-enmusubi-thumb.jpg', 'Marriage Ties of a Sweet God’s House', 'ongoing', '2025-03-06 03:10:48', 0, 0, '2025-03-06 03:14:29', 0, NULL, NULL, NULL, NULL, NULL),
(141, 'Tuyệt Thế Võ Thần', 'tuyet-the-vo-than', 'tuyet-the-vo-than-thumb.jpg', '', 'ongoing', '2025-03-05 10:58:09', 0, 0, '2025-03-06 03:14:32', 0, NULL, NULL, NULL, NULL, NULL),
(142, 'Final Fantasy: Lost Stranger', 'final-fantasy-lost-stranger', 'final-fantasy-lost-stranger-thumb.jpg', '', 'ongoing', '2025-03-06 03:16:26', 0, 0, '2025-03-06 03:20:32', 0, NULL, NULL, NULL, NULL, NULL),
(143, 'Giáo Sư Gián Điệp', 'giao-su-gian-diep', 'giao-su-gian-diep-thumb.jpg', 'Giáo Sư Bí Mật Của Học Viện', 'ongoing', '2025-03-06 03:20:26', 0, 0, '2025-03-06 03:21:46', 0, NULL, NULL, NULL, NULL, NULL),
(144, 'Flying Witch', 'flying-witch', 'flying-witch-thumb.jpg', '', 'coming_soon', '2025-03-03 09:16:32', 0, 0, '2025-03-06 03:40:56', 0, NULL, NULL, NULL, NULL, NULL),
(145, 'Majime Succubus Hiragi-san', 'majime-succubus-hiragi-san', 'majime-succubus-hiragi-san-thumb.jpg', 'The Serious Succubus Hiragi', 'completed', '2024-12-17 08:47:42', 474, 0, '2025-03-06 03:49:44', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(146, 'Nhất Lực Phá Chư Thiên Vạn Giới', 'nhat-luc-pha-chu-thien-van-gioi', 'nhat-luc-pha-chu-thien-van-gioi-thumb.jpg', '', 'ongoing', '2025-03-06 03:29:39', 275, 0, '2025-03-06 03:50:04', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(147, 'Nihon e Youkoso Elf-san', 'nihon-e-youkoso-elf-san', 'nihon-e-youkoso-elf-san-thumb.jpg', 'Xuyên Không Mang Elf Về Nhà', 'ongoing', '2025-03-06 03:29:49', 0, 0, '2025-03-06 03:51:02', 0, NULL, NULL, NULL, NULL, NULL),
(148, 'Ông Chú Ma Pháp Thiếu Nữ', 'ong-chu-ma-phap-thieu-nu', 'ong-chu-ma-phap-thieu-nu-thumb.jpg', '', 'ongoing', '2025-03-06 03:30:15', 0, 0, '2025-03-06 04:01:07', 0, NULL, NULL, NULL, NULL, NULL),
(149, 'Nàng Công Chúa Tiên Tri', 'nang-cong-chua-tien-tri', 'nang-cong-chua-tien-tri-thumb.jpg', '', 'ongoing', '2025-03-06 03:27:52', 0, 0, '2025-03-06 11:05:41', 0, NULL, NULL, NULL, NULL, NULL),
(150, 'Nữ Tước Trong Sự Lụi Tàn', 'nu-tuoc-trong-su-lui-tan', 'nu-tuoc-trong-su-lui-tan-thumb.jpg', '', 'ongoing', '2025-03-06 03:30:02', 0, 0, '2025-03-07 03:34:33', 0, NULL, NULL, NULL, NULL, NULL),
(151, 'Misoshiru De Kanpai!', 'misoshiru-de-kanpai', 'misoshiru-de-kanpai-thumb.jpg', '味噌汁でカンパイ！', 'ongoing', '2025-03-06 03:25:00', 0, 0, '2025-03-07 03:36:40', 0, NULL, NULL, NULL, NULL, NULL),
(152, 'Hand Jumper', 'hand-jumper', 'hand-jumper-thumb.jpg', 'Hand Jumper', 'ongoing', '2025-03-06 03:21:10', 0, 0, '2025-03-07 03:39:31', 0, NULL, NULL, NULL, NULL, NULL),
(153, 'Nhân Vật Chính Chỉ Muốn Yêu Đương', 'nhan-vat-chinh-chi-muon-yeu-duong', 'nhan-vat-chinh-chi-muon-yeu-duong-thumb.jpg', '', 'ongoing', '2025-03-06 03:29:14', 0, 0, '2025-03-07 03:46:29', 0, NULL, NULL, NULL, NULL, NULL),
(154, 'Sát Long Nhân Hồi Quy Siêu Việt', 'sat-long-nhan-hoi-quy-sieu-viet', 'sat-long-nhan-hoi-quy-sieu-viet-thumb.jpg', 'Sát Long Nhân Hồi Quy Siêu Việt', 'ongoing', '2025-03-07 03:45:22', 0, 0, '2025-03-07 03:49:02', 0, NULL, NULL, NULL, NULL, NULL),
(155, 'She Was Actually My Stepsister ~recently The Sense Of Distance Between Me And My New Stepbrother Is Incredibly Close~', 'she-was-actually-my-stepsister-recently-the-sense-of-distance-between-me-and-my-new-stepbrother-is-incredibly-close', 'she-was-actually-my-stepsister-recently-the-sense-of-distance-between-me-and-my-new-stepbrother-is-incredibly-close-thumb.jpg', '', 'ongoing', '2025-03-07 03:46:01', 0, 0, '2025-03-07 03:49:03', 0, NULL, NULL, NULL, NULL, NULL),
(156, 'Thiên Tài Stream Game', 'thien-tai-stream-game', 'thien-tai-stream-game-thumb.jpg', '', 'ongoing', '2025-03-07 03:51:24', 0, 0, '2025-03-07 03:53:47', 0, NULL, NULL, NULL, NULL, NULL),
(157, 'Khóc Đi, Hay Cầu Xin Tôi Cũng Được', 'khoc-di-hay-cau-xin-toi-cung-duoc', 'khoc-di-hay-cau-xin-toi-cung-duoc-thumb.jpg', '', 'ongoing', '2024-02-10 08:17:12', 0, 1, '2025-03-07 04:02:23', 0, NULL, NULL, NULL, NULL, NULL),
(158, 'Tu Luyện Thành Tiên Ta Chỉ Muốn Nuôi Nữ Đồ Đệ', 'tu-luyen-thanh-tien-ta-chi-muon-nuoi-nu-do-de', 'tu-luyen-thanh-tien-ta-chi-muon-nuoi-nu-do-de-thumb.jpg', '', 'ongoing', '2025-03-07 03:54:16', 0, 19, '2025-03-07 04:04:43', 0, NULL, NULL, NULL, NULL, NULL),
(159, 'Wistoria Bản Hùng Ca Kiếm Và Pháp Trượng', 'wistoria-ban-hung-ca-kiem-va-phap-truong', 'wistoria-ban-hung-ca-kiem-va-phap-truong-thumb.jpg', '', 'ongoing', '2025-03-07 03:56:06', 0, 0, '2025-03-07 05:11:35', 0, NULL, NULL, NULL, NULL, NULL),
(160, 'Mối Tình Đầu Đầy Trắc Trở Của Momose Akira', 'moi-tinh-dau-day-trac-tro-cua-momose-akira', 'moi-tinh-dau-day-trac-tro-cua-momose-akira-thumb.jpg', '', 'ongoing', '2024-11-21 11:35:39', 0, 0, '2025-03-07 07:25:56', 0, NULL, NULL, NULL, NULL, NULL),
(161, 'Tuy Là Hoàng Hậu, Nhưng Tôi Muốn Né Hoàng Đế', 'tuy-la-hoang-hau-nhung-toi-muon-ne-hoang-de', 'tuy-la-hoang-hau-nhung-toi-muon-ne-hoang-de-thumb.jpg', 'Tuy Là Hoàng Hậu', 'ongoing', '2025-03-07 03:54:28', 0, 0, '2025-03-07 07:29:47', 0, NULL, NULL, NULL, NULL, NULL),
(162, 'Vạn Cổ Chí Tôn', 'van-co-chi-ton', 'van-co-chi-ton-thumb.jpg', '', 'ongoing', '2025-03-07 03:55:09', 0, 0, '2025-03-07 07:33:32', 0, NULL, NULL, NULL, NULL, NULL),
(163, 'Vua sáng chế', 'vua-sang-che', 'vua-sang-che-thumb.jpg', '', 'ongoing', '2025-03-07 03:55:39', 0, 0, '2025-03-07 08:21:13', 0, NULL, NULL, NULL, NULL, NULL),
(164, 'Beastly Things', 'beastly-things', 'beastly-things-thumb.jpg', 'Beastly Things', 'ongoing', '2025-03-07 07:50:22', 0, 0, '2025-03-07 08:33:58', 0, NULL, NULL, NULL, NULL, NULL),
(165, 'Coffee Shop Anemone', 'coffee-shop-anemone', 'coffee-shop-anemone-thumb.jpg', 'Houkago wa Kissaten de', 'ongoing', '2025-03-07 07:49:59', 73, 0, '2025-03-07 08:48:14', 0, NULL, NULL, NULL, NULL, NULL),
(166, 'Trở Thành Con Gái Nuôi Của Một Gia Đình Sắp Bị Phá Hủy', 'tro-thanh-con-gai-nuoi-cua-mot-gia-dinh-sap-bi-pha-huy', 'tro-thanh-con-gai-nuoi-cua-mot-gia-dinh-sap-bi-pha-huy-thumb.jpg', '', 'ongoing', '2025-03-07 03:53:21', 128, 0, '2025-03-07 09:00:33', 0, NULL, NULL, NULL, NULL, NULL),
(167, 'Thiên Ma Quy Hoàn', 'thien-ma-quy-hoan', 'thien-ma-quy-hoan-thumb.jpg', '', 'ongoing', '2025-03-07 03:51:07', 193, 0, '2025-03-07 09:21:35', 0, NULL, NULL, NULL, NULL, NULL),
(168, 'Mối Tình Đầu Đến Từ Tương Lai', 'moi-tinh-dau-den-tu-tuong-lai', 'moi-tinh-dau-den-tu-tuong-lai-thumb.jpg', 'Mối Tình Đầu Đến Từ Tương Lai', 'ongoing', '2025-03-08 05:04:55', 95, 0, '2025-03-08 12:56:58', 0, NULL, NULL, NULL, NULL, NULL),
(169, 'Giang Hồ Thực Thi Công Lý', 'giang-ho-thuc-thi-cong-ly', 'giang-ho-thuc-thi-cong-ly-thumb.jpg', '', 'ongoing', '2025-03-08 05:01:49', 96, 0, '2025-03-09 04:27:50', 0, NULL, NULL, NULL, NULL, NULL),
(170, 'Kẻ Phản Diện Là Một Con Rối', 'ke-phan-dien-la-mot-con-roi', 'ke-phan-dien-la-mot-con-roi-thumb.jpg', 'Ác Nữ Chỉ Là Một Con Rối', 'ongoing', '2025-03-08 05:03:45', 54, 0, '2025-03-09 05:03:03', 0, NULL, NULL, NULL, NULL, NULL),
(171, 'Te Ni Ireta Saimin Appli De Yume No Harem Seikatsu O Okuritai', 'te-ni-ireta-saimin-appli-de-yume-no-harem-seikatsu-o-okuritai', 'te-ni-ireta-saimin-appli-de-yume-no-harem-seikatsu-o-okuritai-thumb.jpg', 'I Want to Live the Harem Life of My Dreams With the Hypnosis App I Got', 'ongoing', '2025-03-11 05:09:46', 135, 0, '2025-03-12 03:00:13', 0, NULL, NULL, NULL, NULL, NULL),
(172, 'Bức Thư Tình Đến Từ Tương Lai', 'buc-thu-tinh-den-tu-tuong-lai', 'buc-thu-tinh-den-tu-tuong-lai-thumb.jpg', 'Bức Thư Tình Đến Từ Tương Lai', 'ongoing', '2025-03-11 08:30:31', 107, 0, '2025-03-12 03:06:24', 0, NULL, NULL, NULL, NULL, NULL),
(173, 'Khi Tôi Bị Chú Chó Tôi Bỏ Rơi Cắn', 'khi-toi-bi-chu-cho-toi-bo-roi-can', 'khi-toi-bi-chu-cho-toi-bo-roi-can-thumb.jpg', '', 'ongoing', '2025-03-03 09:19:17', 44, 0, '2025-03-12 03:06:38', 0, NULL, NULL, NULL, NULL, NULL),
(174, 'Đánh thức kĩ năng ngủ, tôi xây dựng dàn harem mạnh nhất', 'danh-thuc-ki-nang-ngu-toi-xay-dung-dan-harem-manh-nhat', 'danh-thuc-ki-nang-ngu-toi-xay-dung-dan-harem-manh-nhat-thumb.jpg', 'Hazure Skill \"Soine\" ga Kakuseishi', 'ongoing', '2024-05-02 04:52:44', 219, 0, '2025-03-12 03:08:17', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(175, 'Corpse Party Another Child', 'corpse-party-another-child', 'corpse-party-another-child-thumb.jpg', '', 'ongoing', '2024-01-20 03:14:28', 49, 0, '2025-03-12 03:11:15', 0, NULL, NULL, NULL, NULL, NULL),
(176, 'Thưa Ngài, Tôi Cảm Thấy Khó Chịu', 'thua-ngai-toi-cam-thay-kho-chiu', 'thua-ngai-toi-cam-thay-kho-chiu-thumb.jpg', 'Thưa Ngài', 'ongoing', '2025-03-11 05:10:40', 97, 0, '2025-03-12 03:11:26', 0, NULL, NULL, NULL, NULL, NULL),
(177, 'Vật Lý Tu Tiên Hai Vạn Năm', 'vat-ly-tu-tien-hai-van-nam', 'vat-ly-tu-tien-hai-van-nam-thumb.jpg', '', 'ongoing', '2025-03-11 05:12:47', 48, 0, '2025-03-12 03:26:00', 0, NULL, NULL, NULL, NULL, NULL),
(178, 'Đồ Nhi Phản Diện Ngươi Hãy Bỏ Qua Sư Tôn Đi', 'do-nhi-phan-dien-nguoi-hay-bo-qua-su-ton-di', 'do-nhi-phan-dien-nguoi-hay-bo-qua-su-ton-di-thumb.jpg', '', 'ongoing', '2025-03-12 05:11:03', 465, 0, '2025-03-12 05:20:29', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(179, 'Kể Từ Bây Giờ Tôi Là Một Người Chơi', 'ke-tu-bay-gio-toi-la-mot-nguoi-choi', 'ke-tu-bay-gio-toi-la-mot-nguoi-choi-thumb.jpg', '', 'ongoing', '2025-03-12 05:13:19', 1107, 0, '2025-03-12 09:54:06', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(180, 'Multiverse No Watashi, Koishite Ii Desu Ka', 'multiverse-no-watashi-koishite-ii-desu-ka', 'multiverse-no-watashi-koishite-ii-desu-ka-thumb.jpg', '', 'completed', '2025-02-19 06:03:51', 76, 0, '2025-03-12 11:19:21', 0, NULL, NULL, NULL, NULL, NULL),
(181, 'Ma Kể Chuyện 2', 'ma-ke-chuyen-2', 'ma-ke-chuyen-2-thumb.jpg', 'Ghost Teller', 'completed', '2024-06-06 12:59:21', 96, 0, '2025-03-12 11:23:19', 0, NULL, NULL, NULL, NULL, NULL),
(182, 'Huyền Thoại Game Thủ - Tái Xuất', 'huyen-thoai-game-thu-tai-xuat', 'huyen-thoai-game-thu-tai-xuat-thumb.jpg', 'Ranker Tái Xuất', 'ongoing', '2025-03-12 05:12:49', 1061, 0, '2025-03-12 11:55:11', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(183, 'Lời Thú Nhận Của Chúa Tể Bóng Tối', 'loi-thu-nhan-cua-chua-te-bong-toi', 'loi-thu-nhan-cua-chua-te-bong-toi-thumb.jpg', 'The Dark Lord\'s Confession', 'ongoing', '2025-03-12 05:13:53', 929, 0, '2025-03-12 12:21:00', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(184, 'Con Trai Út Của Bá Tước Là Một Người Chơi', 'con-trai-ut-cua-ba-tuoc-la-mot-nguoi-choi', 'con-trai-ut-cua-ba-tuoc-la-mot-nguoi-choi-thumb.jpg', 'Con Trai Út Của Bá Tước Là Người Chơi', 'ongoing', '2025-03-12 05:09:17', 1294, 0, '2025-03-12 12:52:24', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(185, 'Phệ Kiếm', 'phe-kiem', 'phe-kiem-thumb.jpg', 'Weapon eating bastard', 'ongoing', '2025-03-11 08:29:35', 17, 0, '2025-03-12 13:56:15', 0, NULL, NULL, NULL, NULL, NULL),
(186, 'Đại Phản Diện', 'dai-phan-dien', 'dai-phan-dien-thumb.jpg', '', 'ongoing', '2025-03-12 05:10:09', 516, 0, '2025-03-12 14:38:28', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(187, 'Coin Báo Thù', 'coin-bao-thu', 'coin-bao-thu-thumb.jpg', '', 'ongoing', '2025-03-12 05:09:03', 521, 0, '2025-03-12 14:38:35', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(188, 'Cửa Hàng Diệu Kỳ', 'cua-hang-dieu-ky', 'cua-hang-dieu-ky-thumb.jpg', '', 'ongoing', '2025-03-12 05:09:44', 267, 0, '2025-03-12 14:38:43', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(189, 'Luật Thanh Niên', 'luat-thanh-nien', 'luat-thanh-nien-thumb.jpg', '', 'ongoing', '2025-03-12 05:14:06', 1765, 0, '2025-03-12 14:38:53', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(190, 'Kí Sự Hồi Quy', 'ki-su-hoi-quy', 'ki-su-hoi-quy-thumb.jpg', 'Regressor Instruction Manual', 'ongoing', '2025-03-12 05:13:29', 4348, 0, '2025-03-13 01:32:56', 0, '2025-03-14', NULL, NULL, NULL, NULL),
(191, 'Nữ Phụ Pháo Hôi Không Muốn Để Nam Nữ Chính Chia Tay', 'nu-phu-phao-hoi-khong-muon-de-nam-nu-chinh-chia-tay', 'nu-phu-phao-hoi-khong-muon-de-nam-nu-chinh-chia-tay-thumb.jpg', '', 'ongoing', '2024-12-21 04:29:43', 52, 0, '2025-03-13 01:40:01', 0, NULL, NULL, NULL, NULL, NULL),
(192, '1', '1', '1-thumb.jpg', '', 'ongoing', '2023-09-22 06:02:50', 3, 0, '2025-03-13 03:03:57', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(193, 'Huyễn Thú Của Ta Có Thể Tiến Hoá Vô Hạn', 'huyen-thu-cua-ta-co-the-tien-hoa-vo-han', 'huyen-thu-cua-ta-co-the-tien-hoa-vo-han-thumb.jpg', 'Huyễn Thú Của Ta Có Thể Tiến Hoá Vô Hạn', 'ongoing', '2025-03-11 08:30:19', 9, 0, '2025-03-13 03:21:41', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(194, 'Ngủ Say Vạn Cổ: Xuất Thế Quét Ngang Chư Thiên', 'ngu-say-van-co-xuat-the-quet-ngang-chu-thien', 'ngu-say-van-co-xuat-the-quet-ngang-chu-thien-thumb.jpg', 'Ngủ Say Vạn Cổ: Xuất Thế Quét Ngang Chư Thiên', 'ongoing', '2025-03-03 12:37:55', 18, 0, '2025-03-13 03:21:53', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(195, 'Goo Sera', 'goo-sera', 'goo-sera-thumb.jpg', 'Goo Sera', 'ongoing', '2025-03-12 05:11:32', 400, 0, '2025-03-13 03:25:53', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(196, 'Hiệp Sĩ Sống Vì Ngày Hôm Nay', 'hiep-si-song-vi-ngay-hom-nay', 'hiep-si-song-vi-ngay-hom-nay-thumb.jpg', '', 'ongoing', '2025-03-12 05:11:41', 10, 0, '2025-03-13 03:27:13', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(197, 'Người Cha Che Giấu Sức Mạnh', 'nguoi-cha-che-giau-suc-manh', 'nguoi-cha-che-giau-suc-manh-thumb.jpg', 'Người Cha Che Giấu Sức Mạnh', 'ongoing', '2025-03-03 09:22:22', 3, 0, '2025-03-13 03:30:26', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(198, 'Bạo Lực Vương', 'bao-luc-vuong', 'bao-luc-vuong-thumb.jpg', '', 'ongoing', '2025-02-20 03:25:36', 278, 0, '2025-03-13 03:32:49', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(199, 'Cực Hàn Chiến Kỷ', 'cuc-han-chien-ky', 'cuc-han-chien-ky-thumb.jpg', '', 'ongoing', '2025-03-12 05:09:57', 651, 0, '2025-03-13 03:35:23', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(200, 'Đừng Đùa Với Cún Con', 'dung-dua-voi-cun-con', 'dung-dua-voi-cun-con-thumb.jpg', '', 'ongoing', '2025-03-12 05:11:21', 410, 0, '2025-03-13 03:36:23', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(201, 'Hồi Quy Giả Về Hưu', 'hoi-quy-gia-ve-huu', 'hoi-quy-gia-ve-huu-thumb.jpg', 'Hồi Quy Sau Khi Vừa Nghỉ Hưu', 'ongoing', '2025-03-12 05:12:17', 2090, 1, '2025-03-13 03:46:22', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(202, 'Hồi Ức Trong Ngục Tối', 'hoi-uc-trong-nguc-toi', 'hoi-uc-trong-nguc-toi-thumb.jpg', '', 'ongoing', '2025-03-12 05:12:28', 345, 0, '2025-03-13 04:10:30', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(203, 'Vĩnh Kiếp Vô Gián - Tiền Trần Kiếp', 'vinh-kiep-vo-gian-tien-tran-kiep', 'vinh-kiep-vo-gian-tien-tran-kiep-thumb.jpg', 'Naraka: Bladepoint Manhua', 'ongoing', '2024-03-23 07:39:34', 487, 0, '2025-03-13 04:12:25', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(204, 'Đệ Nhất Võ Sư, Baek Cao Thủ', 'de-nhat-vo-su-baek-cao-thu', 'de-nhat-vo-su-baek-cao-thu-thumb.jpg', 'Giảng Sư Đứng Đầu: Baek Sư phụ', 'ongoing', '2025-03-12 05:10:51', 483, 0, '2025-03-13 04:15:41', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(205, 'Ta Là Chúa Tể Tùng Lâm', 'ta-la-chua-te-tung-lam', 'ta-la-chua-te-tung-lam-thumb.jpg', '', 'ongoing', '2024-12-31 09:04:55', 173, 0, '2025-03-13 04:32:58', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(206, 'Cách Mạng Bắt Nạt', 'cach-mang-bat-nat', 'cach-mang-bat-nat-thumb.jpg', 'Cách Mạng Bắt Nạt', 'ongoing', '2025-03-11 08:29:38', 236, 0, '2025-03-13 04:58:16', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(207, 'Trưởng Giám Ngục Trông Coi Các Ma Nữ', 'truong-giam-nguc-trong-coi-cac-ma-nu', 'truong-giam-nguc-trong-coi-cac-ma-nu-thumb.jpg', 'Quản Giáo Cai Quản Các Ma Nữ', 'ongoing', '2025-03-11 05:11:40', 111, 0, '2025-03-13 05:04:26', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(208, 'Xác Ướp', 'xac-uop', 'xac-uop-thumb.jpg', '', 'ongoing', '2023-11-09 10:48:29', 483, 0, '2025-03-13 05:04:55', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(209, 'Tầm Mộng Hồn', 'tam-mong-hon', 'tam-mong-hon-thumb.jpg', '', 'ongoing', '2024-04-07 05:29:59', 281, 0, '2025-03-13 05:05:54', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(210, 'Con Trai Út Của Gia Đình Kiếm Thuật Danh Tiếng', 'con-trai-ut-cua-gia-dinh-kiem-thuat-danh-tieng', 'con-trai-ut-cua-gia-dinh-kiem-thuat-danh-tieng-thumb.jpg', 'Con Trai Út Của Kiếm Thánh', 'ongoing', '2025-03-12 05:09:29', 140, 0, '2025-03-13 05:07:18', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(211, 'Saeism', 'saeism', 'saeism-thumb.jpg', 'Saeism', 'completed', '2023-12-10 06:37:49', 288, 0, '2025-03-13 05:07:44', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(212, 'Centuria', 'centuria', 'centuria-thumb.jpg', '', 'ongoing', '2025-03-08 04:56:21', 174, 0, '2025-03-13 05:09:42', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(213, 'Thế Giới Sau Tận Thế', 'the-gioi-sau-tan-the', 'the-gioi-sau-tan-the-thumb.jpg', '', 'ongoing', '2024-05-22 13:28:16', 190, 0, '2025-03-13 05:18:30', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(214, 'Nanashi - Nakushita Nani ka no Sagashikata', 'nanashi-nakushita-nani-ka-no-sagashikata', 'nanashi-nakushita-nani-ka-no-sagashikata-thumb.jpg', 'How to Look for Something Lost', 'ongoing', '2024-04-20 11:46:10', 296, 0, '2025-03-13 05:22:07', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(215, 'Bất Khả Chiến Bại Ở Mạt Thế Tôi Là Người Chơi Beta Duy Nhất', 'bat-kha-chien-bai-o-mat-the-toi-la-nguoi-choi-beta-duy-nhat', 'bat-kha-chien-bai-o-mat-the-toi-la-nguoi-choi-beta-duy-nhat-thumb.jpg', 'Bất Khả Chiến Bại Trong Ngày Tận Thế: Tôi Là Người Chơi Beta Duy Nhất', 'ongoing', '2024-01-16 03:38:56', 61, 0, '2025-03-13 05:25:55', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(216, 'Linh Khí Khôi Phục: Từ Cá Chép Tiến Hoá Thành Thần Long', 'linh-khi-khoi-phuc-tu-ca-chep-tien-hoa-thanh-than-long', 'linh-khi-khoi-phuc-tu-ca-chep-tien-hoa-thanh-than-long-thumb.jpg', '', 'ongoing', '2025-01-20 03:03:18', 189, 0, '2025-03-13 05:33:02', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(217, 'Chữa Lành Cuộc Sống Thông Qua Cắm Trại Ở Thế Giới Khác', 'chua-lanh-cuoc-song-thong-qua-cam-trai-o-the-gioi-khac', 'chua-lanh-cuoc-song-thong-qua-cam-trai-o-the-gioi-khac-thumb.jpg', '', 'ongoing', '2025-03-08 04:57:43', 131, 0, '2025-03-13 05:33:12', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(218, 'Caramal Kỳ Quái', 'caramal-ky-quai', 'caramal-ky-quai-thumb.jpg', '', 'ongoing', '2024-05-20 09:05:30', 83, 0, '2025-03-13 05:34:15', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(219, 'Sam Yi Tái Sinh', 'sam-yi-tai-sinh', 'sam-yi-tai-sinh-thumb.jpg', '', 'ongoing', '2024-05-05 08:29:15', 117, 0, '2025-03-13 05:40:42', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(220, 'Võ Lâm đệ nhất đầu bếp', 'vo-lam-de-nhat-dau-bep', 'vo-lam-de-nhat-dau-bep-thumb.jpg', '', 'ongoing', '2023-12-16 04:12:14', 250, 0, '2025-03-13 05:45:10', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(221, 'Ngao du tại chốn dị giới', 'ngao-du-tai-chon-di-gioi', 'ngao-du-tai-chon-di-gioi-thumb.jpg', 'Isekai Walking • Walking in Another World', 'ongoing', '2025-01-26 11:14:48', 190, 0, '2025-03-13 05:55:54', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(222, 'Huyền Thoại Giáo Sĩ Trở Lại', 'huyen-thoai-giao-si-tro-lai', 'huyen-thoai-giao-si-tro-lai-thumb.jpg', 'Sự trở lại của vị hiệp sĩ dùng thương', 'ongoing', '2025-03-12 05:13:05', 232, 0, '2025-03-13 06:01:15', 0, '2025-03-13', NULL, NULL, NULL, NULL);
INSERT INTO `truyen` (`id`, `name`, `slug`, `thumb_url`, `origin_name`, `status`, `updated_at`, `views`, `likes`, `created_at`, `daily_views`, `last_view_date`, `author`, `genres`, `description`, `chapters`) VALUES
(223, 'Hồi Quy Giả Của Gia Tộc Suy Vong', 'hoi-quy-gia-cua-gia-toc-suy-vong', 'hoi-quy-gia-cua-gia-toc-suy-vong-thumb.jpg', 'Kẻ Hồi Quy Của Gia Tộc Suy Vong', 'ongoing', '2025-03-12 05:12:02', 274, 0, '2025-03-13 06:03:03', 0, '2025-03-13', NULL, NULL, NULL, NULL),
(227, 'Võ Đang Kỳ Hiệp', 'vo-dang-ky-hiep', 'vo-dang-ky-hiep-thumb.jpg', '', 'ongoing', '2025-03-13 07:05:32', 342, 0, '2025-03-14 02:26:38', 0, '2025-03-14', NULL, NULL, NULL, NULL),
(230, 'D4Dj -The Story Of Happy Around!', 'D4Dj-The-Story-Of-Happy-Around', 'https://i.ibb.co/t7w8bwW/3cd3cc741110.jpg', '', 'ongoing', '2025-03-14 09:42:08', 204, 0, '2025-03-14 02:42:08', 0, '2025-03-14', 'Đang Cập Nhật', 'Comedy, Anime', '“Khung cảnh đó, chúng tôi sẽ không bao giờ quên.”\r\n Rinku mỗi khi vui vẻ đều hay nói “Happy Around!”. Sau khi trở về Nhật Bản, cô nhập học vào học viện Yoba, nơi mà những hoạt động DJ luôn diễn ra sôi nổi. Ấn tượng bởi buổi live DJ của Peaky P-Key, Rinku quyết định sẽ cùng Maho, Muni và Rei lập một nhóm DJ tên là “Happy Around!”. Trong khi đó, cô cũng sẽ giao lưu với các nhóm DJ khác như “Peaky P-Key” và “Photon Maiden”.\r\n\r\n Câu chuyện kể về hành trình Rinku và những người bạn với mong muốn được biểu diễn tại một Stage lớn!   Manga này tập trung câu chuyện về Happy Around!, cũng có bộ anime D4DJ tên là \"D4DJ First Mix\", mọi người nhớ xem nha!', '[{\"link\": \"https://truyenqqto.com/truyen-tranh/d4dj-the-story-of-happy-around-11286-chap-3.html\", \"name\": \"Chương 3\"}, {\"link\": \"https://truyenqqto.com/truyen-tranh/d4dj-the-story-of-happy-around-11286-chap-2.html\", \"name\": \"Chương 2\"}, {\"link\": \"https://truyenqqto.com/truyen-tranh/d4dj-the-story-of-happy-around-11286-chap-1.html\", \"name\": \"Chương 1\"}]'),
(231, 'Yêu Thần Ký', 'yeu-than-ky', 'yeu-than-ky-thumb.jpg', 'Tales of Demons And Gods', 'ongoing', '2025-03-13 07:06:20', 111, 0, '2025-03-14 02:51:29', 0, '2025-03-14', NULL, NULL, NULL, NULL),
(232, 'Diễn Viên Gangster', 'dien-vien-gangster', 'dien-vien-gangster-thumb.jpg', 'Gangster Actor', 'ongoing', '2025-03-18 05:05:36', 274, 0, '2025-03-18 07:52:54', 0, '2025-03-18', NULL, NULL, NULL, NULL),
(233, 'Xin Hãy Để Ý Tới Jasmine', 'xin-hay-de-y-toi-jasmine', 'xin-hay-de-y-toi-jasmine-thumb.jpg', 'Xin Hãy Để Ý Tới Jasmine', 'ongoing', '2025-03-21 05:00:54', 213, 0, '2025-03-21 06:25:25', 0, '2025-03-21', NULL, NULL, NULL, NULL),
(234, ' ZINGNIZE', 'zingnize', 'zingnize-thumb.jpg', 'ジンナイズ', 'ongoing', '2025-03-21 05:01:07', 135, 0, '2025-03-21 06:25:33', 0, '2025-03-21', NULL, NULL, NULL, NULL),
(235, 'Thánh Chiến Ký Elna Saga', 'thanh-chien-ky-elna-saga', 'thanh-chien-ky-elna-saga-thumb.jpg', '', 'ongoing', '2025-03-23 02:47:24', 186, 0, '2025-03-24 07:52:48', 0, '2025-03-24', NULL, NULL, NULL, NULL),
(236, 'Yukionna to Kani wo Kuu: Okinawa-hen', 'yukionna-to-kani-wo-kuu-okinawa-hen', 'yukionna-to-kani-wo-kuu-okinawa-hen-thumb.jpg', '', 'ongoing', '2024-05-15 06:29:25', 188, 0, '2025-03-24 07:53:04', 0, '2025-03-24', NULL, NULL, NULL, NULL),
(237, 'Tonari No Seki No Yatsu Ga Souiu Me De Mitekuru', 'tonari-no-seki-no-yatsu-ga-souiu-me-de-mitekuru', 'tonari-no-seki-no-yatsu-ga-souiu-me-de-mitekuru-thumb.jpg', 'Tonari No Seki No Yatsu Ga Souiu Me De Mitekuru', 'ongoing', '2025-03-23 02:50:20', 109, 0, '2025-03-24 08:02:37', 109, '2025-03-24', NULL, NULL, NULL, NULL),
(238, 'Nam Chủ Vì Sao Quyến Rũ Ta', 'nam-chu-vi-sao-quyen-ru-ta', 'nam-chu-vi-sao-quyen-ru-ta-thumb.jpg', '', 'ongoing', '2025-04-19 03:56:05', 396, 0, '2025-04-19 06:34:46', 396, '2025-04-19', NULL, NULL, NULL, NULL),
(239, 'Mạt Thế Vi Vương', 'mat-the-vi-vuong', 'mat-the-vi-vuong-thumb.jpg', 'King Of Doom', 'ongoing', '2025-04-19 03:55:00', 238, 0, '2025-04-19 09:06:34', 238, '2025-04-19', NULL, NULL, NULL, NULL),
(240, 'Mayonaka Heart Tune', 'mayonaka-heart-tune', 'mayonaka-heart-tune-thumb.jpg', '', 'ongoing', '2025-04-19 03:55:19', 113, 0, '2025-04-19 09:06:43', 113, '2025-04-19', NULL, NULL, NULL, NULL),
(241, 'Kỷ Nguyên Siêu Anh Hùng', 'ky-nguyen-sieu-anh-hung', 'ky-nguyen-sieu-anh-hung-thumb.jpg', 'Kỷ Nguyên Siêu Anh Hùng', 'ongoing', '2025-04-17 06:09:28', 72, 0, '2025-04-19 09:12:38', 72, '2025-04-19', NULL, NULL, NULL, NULL),
(242, 'Mọi Người Đều Yêu Cô Ấy', 'moi-nguoi-deu-yeu-co-ay', 'moi-nguoi-deu-yeu-co-ay-thumb.jpg', 'Everyone loves her', 'ongoing', '2025-04-19 03:55:32', 78, 0, '2025-04-19 11:29:20', 78, '2025-04-19', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `roles` enum('admin','user') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `google_auth_secret` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `score` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yeuthich`
--

CREATE TABLE `yeuthich` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `truyen_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comic_id` (`truyen_id`);

--
-- Indexes for table `lichsudoc`
--
ALTER TABLE `lichsudoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `truyen_id` (`truyen_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`truyen_id`),
  ADD KEY `truyen_id` (`truyen_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `slides`
--
ALTER TABLE `slides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_position` (`position`);

--
-- Indexes for table `truyen`
--
ALTER TABLE `truyen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `unique_slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username_unique` (`username`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- Indexes for table `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lichsudoc`
--
ALTER TABLE `lichsudoc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `reply_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slides`
--
ALTER TABLE `slides`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `truyen`
--
ALTER TABLE `truyen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `yeuthich`
--
ALTER TABLE `yeuthich`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`truyen_id`) REFERENCES `truyen` (`id`);

--
-- Constraints for table `lichsudoc`
--
ALTER TABLE `lichsudoc`
  ADD CONSTRAINT `lichsudoc_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `lichsudoc_ibfk_2` FOREIGN KEY (`truyen_id`) REFERENCES `truyen` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`truyen_id`) REFERENCES `truyen` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`),
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD CONSTRAINT `yeuthich_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
