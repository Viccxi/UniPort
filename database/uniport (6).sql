-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 30 Jun 2025 pada 09.45
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uniport`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','moderator') DEFAULT 'moderator',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$H4Wp4LZB63o9dFHmk3w0C.j0HgdpqfgKsevMOv12tlYMzXJ749wDW', 'superadmin', '2025-06-25 20:01:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `edited_at`, `photo`, `parent_id`) VALUES
(1, 1, 1, 'wih mantap mas rado, itu kalau digoreng dengan sambal enak sekali pasti.', '2025-06-25 15:30:29', '2025-06-25 16:40:26', NULL, NULL),
(2, 1, 1, 'mending maen roblox bang', '2025-06-25 16:19:01', NULL, '1750843141_Mantap.webp', NULL),
(4, 1, 4, 'ikan apa itu bang', '2025-06-25 20:25:55', NULL, NULL, NULL),
(5, 1, 5, 'aku jadi lapar bang', '2025-06-26 13:33:10', NULL, NULL, NULL),
(6, 1, 7, 'wih rezeki', '2025-06-26 15:10:14', NULL, NULL, NULL),
(7, 3, 4, 'darimana itu mas aji', '2025-06-29 17:16:38', NULL, NULL, NULL),
(8, 2, 4, 'besok libur bang faris', '2025-06-29 18:32:36', NULL, NULL, NULL),
(10, 7, 4, 'gitar apa itu bang', '2025-06-29 20:39:04', NULL, NULL, NULL),
(15, 11, 23, 'mantab mas ', '2025-06-30 12:40:25', NULL, NULL, NULL),
(22, 8, 4, 'itu tabel apa mas', '2025-06-30 12:52:29', NULL, NULL, NULL),
(23, 8, 4, 'wow mantap mas', '2025-06-30 12:52:47', NULL, NULL, NULL),
(24, 8, 2, 'kuno banget bang', '2025-06-30 12:56:21', NULL, NULL, NULL),
(25, 11, 23, 'kerenn', '2025-06-30 13:21:28', NULL, NULL, NULL),
(26, 11, 5, 'anjay', '2025-06-30 13:27:15', NULL, NULL, NULL),
(27, 2, 4, 'wow', '2025-06-30 13:29:39', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `followers`
--

INSERT INTO `followers` (`id`, `user_id`, `follower_id`, `created_at`, `status`) VALUES
(4, 1, 2, '2025-06-25 17:54:38', 'accepted'),
(5, 2, 1, '2025-06-25 17:57:25', 'accepted'),
(7, 2, 4, '2025-06-25 20:25:36', 'accepted'),
(8, 2, 3, '2025-06-26 13:25:45', 'accepted'),
(9, 4, 2, '2025-06-26 13:26:11', 'accepted'),
(10, 3, 2, '2025-06-26 13:26:34', 'accepted'),
(12, 2, 5, '2025-06-26 13:32:07', 'accepted'),
(14, 4, 7, '2025-06-26 15:17:49', 'accepted'),
(15, 5, 7, '2025-06-26 15:17:54', 'accepted'),
(16, 2, 7, '2025-06-26 15:18:10', 'accepted'),
(17, 3, 6, '2025-06-26 15:18:43', 'accepted'),
(19, 3, 7, '2025-06-26 16:24:45', 'accepted'),
(20, 1, 7, '2025-06-26 16:26:09', 'accepted'),
(21, 5, 2, '2025-06-26 16:26:25', 'accepted'),
(22, 7, 2, '2025-06-26 16:26:41', 'accepted'),
(23, 3, 8, '2025-06-26 23:48:40', 'accepted'),
(25, 8, 2, '2025-06-27 00:02:45', 'accepted'),
(26, 8, 19, '2025-06-29 20:39:18', 'accepted'),
(27, 4, 19, '2025-06-29 20:39:36', 'accepted'),
(28, 19, 4, '2025-06-29 20:39:42', 'accepted'),
(31, 4, 23, '2025-06-30 12:51:55', 'accepted'),
(32, 23, 4, '2025-06-30 12:51:56', 'accepted'),
(33, 5, 4, '2025-06-30 13:23:52', 'accepted'),
(34, 4, 5, '2025-06-30 13:31:28', 'accepted');

-- --------------------------------------------------------

--
-- Struktur dari tabel `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `followed_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(13, 1, 1, '2025-06-25 17:49:11'),
(17, 5, 1, '2025-06-26 13:32:53'),
(20, 6, 1, '2025-06-26 15:18:23'),
(32, 7, 1, '2025-06-26 15:30:13'),
(33, 8, 2, '2025-06-26 23:59:34'),
(34, 8, 1, '2025-06-26 23:59:36'),
(35, 2, 3, '2025-06-27 00:02:29'),
(37, 4, 3, '2025-06-29 17:16:17'),
(39, 4, 8, '2025-06-30 12:04:30'),
(40, 23, 8, '2025-06-30 12:25:09'),
(41, 4, 11, '2025-06-30 12:39:49'),
(42, 23, 11, '2025-06-30 12:40:12'),
(62, 2, 8, '2025-06-30 13:19:53'),
(63, 2, 11, '2025-06-30 13:20:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `sent_at`, `created_at`, `content`) VALUES
(1, 4, 2, 'halo mas rado', 0, '2025-06-29 18:10:40', '2025-06-29 18:10:40', NULL),
(2, 2, 4, 'wah halo juga mas ovic', 0, '2025-06-29 18:11:10', '2025-06-29 18:11:10', NULL),
(3, 4, 2, 'kabarnya gimana mas rado', 0, '2025-06-29 18:14:02', '2025-06-29 18:14:02', NULL),
(4, 2, 4, 'baik mas ovic, mas ovic sendiri bagaimana', 0, '2025-06-29 18:26:19', '2025-06-29 18:26:19', NULL),
(5, 2, 1, 'mas apin', 0, '2025-06-29 19:53:33', '2025-06-29 19:53:33', NULL),
(6, 4, 19, 'pie su', 0, '2025-06-29 20:41:13', '2025-06-29 20:41:13', NULL),
(7, 19, 4, 'su', 0, '2025-06-29 20:41:13', '2025-06-29 20:41:13', NULL),
(8, 4, 19, 'baulan su', 0, '2025-06-29 20:41:23', '2025-06-29 20:41:23', NULL),
(9, 4, 19, 'radiasilit', 0, '2025-06-29 20:41:43', '2025-06-29 20:41:43', NULL),
(10, 23, 4, 'asu mas', 0, '2025-06-30 11:53:41', '2025-06-30 11:53:41', NULL),
(11, 4, 23, 'halo mas prabowo', 0, '2025-06-30 11:53:43', '2025-06-30 11:53:43', NULL),
(12, 5, 4, 'halo', 0, '2025-06-30 13:27:52', '2025-06-30 13:27:52', NULL),
(13, 4, 5, 'halo mas', 0, '2025-06-30 13:28:22', '2025-06-30 13:28:22', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `type` enum('comment') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `from_user_id`, `type`, `reference_id`, `message`, `is_read`, `created_at`, `post_id`, `comment_id`) VALUES
(5, 23, 4, 'comment', NULL, NULL, 0, '2025-06-30 12:52:29', 8, 22),
(6, 23, 4, 'comment', NULL, NULL, 0, '2025-06-30 12:52:47', 8, 23),
(7, 23, 2, 'comment', NULL, NULL, 0, '2025-06-30 12:56:21', 8, 24),
(8, 4, 23, 'comment', NULL, NULL, 0, '2025-06-30 13:21:28', 11, 25),
(9, 4, 5, 'comment', NULL, NULL, 0, '2025-06-30 13:27:15', 11, 26),
(10, 5, 4, 'comment', NULL, NULL, 0, '2025-06-30 13:29:39', 2, 27);

-- --------------------------------------------------------

--
-- Struktur dari tabel `portfolios`
--

CREATE TABLE `portfolios` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published',
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `portfolios`
--

INSERT INTO `portfolios` (`id`, `user_id`, `title`, `description`, `file`, `created_at`, `category`, `status`, `link`) VALUES
(1, 2, 'Flyer from a Reggae Concert event', 'Brosur Konser reggae konser kolektif', '1750852805_bn rege.jpg', '2025-06-25 19:00:05', 'Desain', 'published', 'https://www.instagram.com/bitternight.band/p/DBf1j-fzqiC/'),
(2, 2, 'Gorengan Silit', 'Enak dan mantap', '1750853200_WhatsApp Image 2025-06-04 at 19.11.15_785f9394.jpg', '2025-06-25 19:06:40', 'Pemrograman', 'published', 'https://www.instagram.com/'),
(3, 4, 'Seonggok Mayat, Tembus Pandang', 'Customers by: \r\n@Fata Kusuma (Stage Name: @Setsuko And Seita)\r\n\r\nGenre and Tags\r\n- Skramz\r\n- 5th Wave Emo\r\n- Experimental\r\n- Emo\r\n- Screamo\r\n- Spoken Word Poetry\r\n- Indonesia\r\n\r\nServices Provided:\r\n- Producer\r\n- Arranger\r\n- Mixing\r\n- Mastering\r\n\r\nMusic Platform:\r\n- Bandcamp\r\n- Spotify\r\n- etc', '1751259762_Screenshot 2025-06-30 at 11.55.38.png', '2025-06-30 12:02:42', 'Musik', 'published', 'https://setsukoandseita.bandcamp.com/track/seonggok-mayat-tembus-pandang');

-- --------------------------------------------------------

--
-- Struktur dari tabel `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`, `photo`) VALUES
(1, 2, 'dapat ikan bang', NULL, '2025-06-25 15:26:12', '1750839972_manceng.jpg'),
(2, 5, 'pulang ges', NULL, '2025-06-26 13:51:40', '1750920700_1750654590_image.jpg'),
(3, 8, 'otw pulang ehh jalan nya macet', NULL, '2025-06-27 00:02:03', '1750957323_IMG_4694.jpeg'),
(7, 19, 'dijual minat cp', NULL, '2025-06-29 20:38:49', '1751204329_1000007454.jpg'),
(8, 23, 'belajar ngoding sarannya dong kakak', NULL, '2025-06-30 11:55:21', '1751259321_Screenshot_2025-01-08_220805.png'),
(11, 4, 'tonton video klip saya ges: https://youtu.be/Mjm7pTq3g7o?si=JFDsbTdSu_si9W8Q', NULL, '2025-06-30 12:39:41', '1751261981_Screenshot_2025-01-02_at_23.59.53.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `birthdate` date DEFAULT NULL,
  `work` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `last_active` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `photo`, `password`, `full_name`, `profile_picture`, `bio`, `created_at`, `birthdate`, `work`, `address`, `last_active`) VALUES
(1, 'Apin Jomok', 'Apin Jomok', 'apinjomok@gmail.com', '1750839275_Apin Jomok.jpg', '$2y$10$TJSkKiXhnWvEJ9ce0GmSy.a0.8uuROIQAbHhDVqpv9DduXX71eQdm', NULL, NULL, 'Saya seorang musisi', '2025-06-25 15:01:29', '2012-07-14', 'College at ISI Surakarta', 'Candiasri 2 gang 2, Plumbungan, Karangmalang, Sragen, Jawa Tengah', '2025-06-29 18:15:19'),
(2, 'Rado Bakar', 'Rado Bakar', 'radobakar@gmail.com', '1750846239_INVN1827.jpg', '$2y$10$4C4Rg01gwPYds/yL.zi.duQXzJshyNJ7i3QCC6.UsUohhqMaIGps2', NULL, NULL, 'Saya seorang Desainer', '2025-06-25 15:19:08', '2006-11-12', 'CEO of Canva', 'Margoasri Gang 3, Puro, Karangmalang, Sragen, Jawa Tengah', '2025-06-29 20:23:28'),
(3, 'jenderal kopral ', 'kopral', 'nur.hashfi17@gmail.com', '1750855518_ROG X EVANGELION-02.jpg', '$2y$10$8ktzuj9mqYC/.sqiCGQEZ.QN7s0EV0usXVu5JnP7OId/opULELV0K', NULL, NULL, 'biologi', '2025-06-25 19:44:34', '2005-08-17', 'akatsuki', 'kkk', '2025-06-29 18:15:19'),
(4, 'Ovic', 'Ovic', 'ovic@gmail.com', '1751192045_1000044208.jpg', '$2y$10$hvxnlSR3Cj18SYZrZ5fHMOihmXnOVb2pw17Tf91p0EnqzO3gcI.ZK', NULL, NULL, 'Produser Songs of Haxovica Studiosl', '2025-06-25 19:45:36', '2005-05-18', 'Haxovica Studio', 'Margoasri Gang 3, Puro, Karangmalang, Sragen', '2025-06-30 13:28:32'),
(5, 'Faris', 'Faris', 'faris@gmail.com', '1750921889_Screenshot 2025-06-26 at 13.55.08.png', '$2y$10$LUuHo697nVOmn3OidVkNzuox0gF38NgYe0obuXe8zaK8rjaZSgkpC', NULL, NULL, 'gabut doang bang', '2025-06-26 13:31:36', '2004-03-03', 'Bussiness of Zerous Shop', 'Boyolali, Jawa Tengah', '2025-06-30 13:28:28'),
(6, 'Radiator NUklir', 'Radiatornuklir', 'ovici1121@gmail.com', '1750925830_1000286331.jpg', '$2y$10$hqvZeUFzSHMH8npt.EVi7uJIQIhCYfDa/a0x7Rf0Cy2N3D4zrLJ2u', NULL, NULL, '', '2025-06-26 13:41:19', NULL, '', '', '2025-06-29 18:15:19'),
(7, 'Fakih', 'Fakih', 'fakih@gmail.com', '1750922878_1750655880_IMG_8301.jpeg', '$2y$10$DhfCnucuClE24gU4rgZDK.pyLQ6iTd7tSDHmtvtny4i1ClwdXEHYy', NULL, NULL, 'Saya seorang Desainer', '2025-06-26 14:25:14', '2004-05-03', 'Desainer of Maustore', 'Boyolali, Jawa Tengah', '2025-06-29 18:15:19'),
(8, 'aji', 'oke', 'ajiputra@gmail.com', '1750956975_DSC07990.jpeg', '$2y$10$myv9oqdeBeiLQ8.9XaaE7eYdgxcWmemzJM08qSV0Z4iIgw8K87wxa', NULL, NULL, 'bioethanol', '2025-06-26 23:46:55', '2005-10-29', 'mahasiswa', 'sragen', '2025-06-29 18:15:19'),
(18, 'Logan', 'Logan', 'ashenoctane@gmail.com', 'default.svg', '$2y$10$j7RrDnnCiuXrZOsr/n2ohuSNm92uO3X8yTjaatJ2uzrncYOBrDlkW', NULL, NULL, NULL, '2025-06-29 20:35:24', NULL, NULL, NULL, '2025-06-29 20:35:24'),
(19, 'Jneavindra', 'vndraa', 'jneavindra17@gmail.com', 'default.svg', '$2y$10$5tXTifW4LCnDZouuchECy.g2WGw2KAcqfJhZ.x6s0gM.NsRzVPlXC', NULL, NULL, NULL, '2025-06-29 20:36:08', NULL, NULL, NULL, '2025-06-29 20:41:53'),
(23, 'prabowo', 'wowo', 'prabs123@gmail.com', 'default.svg', '$2y$10$pexGjYUDi8t2B5aU1dkNr.x56cwUNTykzVTea4gq7x55w.d8Q9oR.', NULL, NULL, NULL, '2025-06-30 11:51:43', NULL, NULL, NULL, '2025-06-30 11:53:51');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`follower_id`),
  ADD KEY `follower_id` (`follower_id`);

--
-- Indeks untuk tabel `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indeks untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `portfolios`
--
ALTER TABLE `portfolios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `portfolios`
--
ALTER TABLE `portfolios`
  ADD CONSTRAINT `portfolios_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
