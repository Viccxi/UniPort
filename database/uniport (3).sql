-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 26 Jun 2025 pada 12.16
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
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `edited_at`, `photo`) VALUES
(1, 1, 1, 'wih mantap mas rado, itu kalau digoreng dengan sambal enak sekali pasti.', '2025-06-25 15:30:29', '2025-06-25 16:40:26', NULL),
(2, 1, 1, 'mending maen roblox bang', '2025-06-25 16:19:01', NULL, '1750843141_Mantap.webp'),
(3, 1, 3, 'apin kontol', '2025-06-25 19:46:16', NULL, NULL),
(4, 1, 4, 'ikan apa itu bang', '2025-06-25 20:25:55', NULL, NULL),
(5, 1, 5, 'aku jadi lapar bang', '2025-06-26 13:33:10', NULL, NULL),
(6, 1, 7, 'wih rezeki', '2025-06-26 15:10:14', NULL, NULL);

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
(13, 4, 5, '2025-06-26 14:11:46', 'accepted'),
(14, 4, 7, '2025-06-26 15:17:49', 'accepted'),
(15, 5, 7, '2025-06-26 15:17:54', 'accepted'),
(16, 2, 7, '2025-06-26 15:18:10', 'accepted'),
(17, 3, 6, '2025-06-26 15:18:43', 'accepted'),
(19, 3, 7, '2025-06-26 16:24:45', 'accepted'),
(20, 1, 7, '2025-06-26 16:26:09', 'accepted'),
(21, 5, 2, '2025-06-26 16:26:25', 'accepted'),
(22, 7, 2, '2025-06-26 16:26:41', 'accepted');

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
(32, 7, 1, '2025-06-26 15:30:13');

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
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 2, 'Gorengan Silit', 'Enak dan mantap', '1750853200_WhatsApp Image 2025-06-04 at 19.11.15_785f9394.jpg', '2025-06-25 19:06:40', 'Pemrograman', 'published', 'https://www.instagram.com/');

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
(2, 5, 'pulang ges', NULL, '2025-06-26 13:51:40', '1750920700_1750654590_image.jpg');

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
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `photo`, `password`, `full_name`, `profile_picture`, `bio`, `created_at`, `birthdate`, `work`, `address`) VALUES
(1, 'Apin Jomok', 'Apin Jomok', 'apinjomok@gmail.com', '1750839275_Apin Jomok.jpg', '$2y$10$TJSkKiXhnWvEJ9ce0GmSy.a0.8uuROIQAbHhDVqpv9DduXX71eQdm', NULL, NULL, 'Saya seorang musisi', '2025-06-25 15:01:29', '2012-07-14', 'College at ISI Surakarta', 'Candiasri 2 gang 2, Plumbungan, Karangmalang, Sragen, Jawa Tengah'),
(2, 'Rado Bakar', 'Rado Bakar', 'radobakar@gmail.com', '1750846239_INVN1827.jpg', '$2y$10$4C4Rg01gwPYds/yL.zi.duQXzJshyNJ7i3QCC6.UsUohhqMaIGps2', NULL, NULL, 'Saya seorang Desainer', '2025-06-25 15:19:08', '2006-11-12', 'CEO of Canva', 'Margoasri Gang 3, Puro, Karangmalang, Sragen, Jawa Tengah'),
(3, 'jenderal kopral ', 'kopral', 'nur.hashfi17@gmail.com', '1750855518_ROG X EVANGELION-02.jpg', '$2y$10$8ktzuj9mqYC/.sqiCGQEZ.QN7s0EV0usXVu5JnP7OId/opULELV0K', NULL, NULL, 'biologi', '2025-06-25 19:44:34', '2005-08-17', 'akatsuki', 'kkk'),
(4, 'Ovic', 'Ovic', 'ovic@gmail.com', 'default.svg', '$2y$10$hvxnlSR3Cj18SYZrZ5fHMOihmXnOVb2pw17Tf91p0EnqzO3gcI.ZK', NULL, NULL, NULL, '2025-06-25 19:45:36', NULL, NULL, NULL),
(5, 'Faris', 'Faris', 'faris@gmail.com', '1750921889_Screenshot 2025-06-26 at 13.55.08.png', '$2y$10$LUuHo697nVOmn3OidVkNzuox0gF38NgYe0obuXe8zaK8rjaZSgkpC', NULL, NULL, 'gabut doang bang', '2025-06-26 13:31:36', '2004-03-03', 'Bussiness of Zerous Shop', 'Boyolali, Jawa Tengah'),
(6, 'Radiator NUklir', 'Radiatornuklir', 'ovici1121@gmail.com', '1750925830_1000286331.jpg', '$2y$10$hqvZeUFzSHMH8npt.EVi7uJIQIhCYfDa/a0x7Rf0Cy2N3D4zrLJ2u', NULL, NULL, '', '2025-06-26 13:41:19', NULL, '', ''),
(7, 'Fakih', 'Fakih', 'fakih@gmail.com', '1750922878_1750655880_IMG_8301.jpeg', '$2y$10$DhfCnucuClE24gU4rgZDK.pyLQ6iTd7tSDHmtvtny4i1ClwdXEHYy', NULL, NULL, 'Saya seorang Desainer', '2025-06-26 14:25:14', '2004-05-03', 'Desainer of Maustore', 'Boyolali, Jawa Tengah');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
