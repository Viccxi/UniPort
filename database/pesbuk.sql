-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Bulan Mei 2025 pada 10.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pesbuk`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$kG4jRcEIIOpBy7WEo8pFsu.nAIPm.VY8pGr9sODAS5bRP7pFzLQMy', 'Administrator', 'admin@pesbuk.com', '2025-05-23 08:06:32'),
(2, 'bayu', '123456', 'bayu', 'bayujaringan@gmail.com', '2025-05-23 08:06:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `created_at`) VALUES
(1, 7, 'Saya berhasil mendapatkan ikan mujair', '2025-05-23 07:42:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL DEFAULT 'default.svg',
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `photo`, `role`, `created_at`) VALUES
(1, 'ardianta', 'ardianta_pargo@yahoo.co.id', '$2y$10$HIEq2w.8Et9RsJmY4TMKye4aVCxOd9xJTOtG4vyOEmo.GIBxaPQHK', 'Ardianta Pargo', 'default.svg', 'user', '2025-05-23 15:40:44'),
(3, 'petanikode', 'info@petanikode.com', '$2y$10$uXa.Hz9rr8gkv4ztaqf6FO84iW64gXHbeyEOy1tIQ5wmqMjTk0yQa', 'Petani Kode', 'default.svg', 'user', '2025-05-23 15:40:44'),
(4, 'Opek', 'ovicilham123@gmail.com', '$2y$10$AUV7ZCU4SqHDC/T/XRPt2eM3d7OIbbGJHeH1XXkTG6Sv1hnqqBKmK', 'IBRAHIMOVIC ILHAM PRASTAMA', 'default.svg', 'user', '2025-05-23 15:40:44'),
(5, 'Bayu', 'bayujaringan@gmail.com', '$2y$10$k2aNDTfkNw2AsUoD6iBBtuc2n27ru3QH6vQI7EA6B7jGebB4zSB7S', 'IBRAHIMOVIC ILHAM PRASTAMA', 'img/uploads/viccxi.png', 'user', '2025-05-23 15:40:44'),
(7, 'BayuSilit', 'bayujaringan@gmail.com', '$2y$10$fA5tMkTYWXLHKpM5nQEV1OO3N5nN28q5bra6SRethbyF.FoyqFefm', 'BAYU AJI PAMUNGKAS', 'default.svg', 'user', '2025-05-23 15:40:44');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
