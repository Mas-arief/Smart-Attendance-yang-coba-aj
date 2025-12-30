-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Des 2025 pada 17.29
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
-- Database: `sistem_absensi_face`
--

-- --------------------------------------------------------
--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$MPyX8m53vbYNaqEgYANATuN/SyfsYCP22h6M.eIXQC32L9vm0is.m', 'Administrator', 'admin@kampus.ac.id', 'admin', 1, '2025-12-23 14:27:24'),
(2, '3312411080', '$2y$10$PkX0ehpwEWZfEtHfKfccQeYma6mvW1Z3yZP7/g/zDnaE6nw3nZH/C', 'Arief Utama', 'arief@mahasiswa.ac.id', 'mahasiswa', 1, '2025-12-23 14:27:24'),
(3, '2345678091', '$2y$10$F61853M.Z3ySqBnvZyCGXuHXAnBzeYzy5fPEVoFSgXRF/mjGEa1r6', 'Diky Pratama', 'diky@dosen.ac.id', 'dosen', 1, '2025-12-23 14:27:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `id_user`, `nama_admin`, `email`, `telepon`) VALUES
(1, 1, 'Administrator', 'admin@kampus.ac.id', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `id_user`, `nik`, `nama_dosen`, `email`, `jurusan`, `telepon`) VALUES
(1, 3, '2345678091', 'Diky Pratama', 'diky@dosen.ac.id', 'Teknik Informatika', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nim` varchar(30) NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `angkatan` year(4) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `face_registered` tinyint(1) DEFAULT 0,
  `face_registered_at` datetime DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `id_user`, `nim`, `nama_mahasiswa`, `email`, `jurusan`, `angkatan`, `foto`, `face_registered`, `face_registered_at`, `telepon`, `created_at`) VALUES
(1, 2, '3312411080', 'Arief Utama', 'arief@mahasiswa.ac.id', 'Teknik Informatika', '2024', NULL, 0, NULL, NULL, '2025-12-23 14:27:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id_matkul` int(11) NOT NULL,
  `kode_matkul` varchar(20) NOT NULL,
  `nama_matkul` varchar(100) NOT NULL,
  `sks` int(2) DEFAULT NULL,
  `semester` int(2) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id_matkul`, `kode_matkul`, `nama_matkul`, `sks`, `semester`, `jurusan`) VALUES
(1, 'TIF101', 'Pemrograman Dasar', 3, 1, 'Teknik Informatika'),
(2, 'TIF102', 'Basis Data', 3, 3, 'Teknik Informatika'),
(3, 'TIF201', 'Kecerdasan Buatan', 3, 5, 'Teknik Informatika');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruangan` int(11) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `camera_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ruangan`
--

INSERT INTO `ruangan` (`id_ruangan`, `nama_ruangan`, `kapasitas`, `lokasi`, `camera_id`) VALUES
(1, 'Lab Komputer 1', 40, 'Gedung A Lt.2', NULL),
(2, 'Lab Komputer 2', 40, 'Gedung A Lt.3', NULL),
(3, 'Gedung TA 10.5', 60, 'Gedung TA Lt.10', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id_tahun` int(11) NOT NULL,
  `tahun` varchar(9) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Nonaktif',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id_tahun`, `tahun`, `semester`, `status`, `tanggal_mulai`, `tanggal_selesai`) VALUES
(1, '2024/2025', 'Ganjil', 'Aktif', '2024-08-01', '2025-01-31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_kuliah`
--

CREATE TABLE `jadwal_kuliah` (
  `id_jadwal` int(11) NOT NULL,
  `id_matkul` int(11) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `id_tahun` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kelas` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `matakuliah` (legacy table)
--

CREATE TABLE `matakuliah` (
  `id_matkul` int(11) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `jenis` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_ruangan`
--

CREATE TABLE `jadwal_ruangan` (
  `id_jadwal` int(11) NOT NULL,
  `id_matkul` int(11) NOT NULL,
  `tahun_ajaran` varchar(20) NOT NULL,
  `id_dosen` int(11) DEFAULT NULL,
  `id_ruangan` int(11) NOT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `peserta_kuliah`
--

CREATE TABLE `peserta_kuliah` (
  `id_peserta` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `status_peserta` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `face_data`
--

CREATE TABLE `face_data` (
  `id_face` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `face_encoding` text NOT NULL,
  `model_version` varchar(20) DEFAULT 'v1.0',
  `quality_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_wajah`
--

CREATE TABLE `absensi_wajah` (
  `id_absensi` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_absen` time NOT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `status` enum('hadir','terlambat','izin','sakit','alpa') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `foto_absen` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `face_detection_log`
--

CREATE TABLE `face_detection_log` (
  `id_log` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `detected_at` datetime DEFAULT current_timestamp(),
  `confidence` decimal(5,2) DEFAULT NULL,
  `camera_id` varchar(50) DEFAULT NULL,
  `id_ruangan` int(11) DEFAULT NULL,
  `detection_status` enum('success','failed','unknown') DEFAULT 'success',
  `foto_deteksi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kehadiran`
--

CREATE TABLE `kehadiran` (
  `id_kehadiran` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kehadiran_manual`
--

CREATE TABLE `kehadiran_manual` (
  `id_kehadiran` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_absen` datetime DEFAULT current_timestamp(),
  `status` enum('hadir','izin','sakit','alpa') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `diinput_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_face_registered` (`face_registered`),
  ADD KEY `idx_mahasiswa_jurusan` (`jurusan`);

--
-- Indeks untuk tabel `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id_matkul`),
  ADD UNIQUE KEY `kode_matkul` (`kode_matkul`);

--
-- Indeks untuk tabel `matakuliah`
--
ALTER TABLE `matakuliah`
  ADD PRIMARY KEY (`id_matkul`);

--
-- Indeks untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id_ruangan`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_matkul` (`id_matkul`),
  ADD KEY `id_dosen` (`id_dosen`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `id_tahun` (`id_tahun`),
  ADD KEY `idx_jadwal_hari` (`hari`);

--
-- Indeks untuk tabel `jadwal_ruangan`
--
ALTER TABLE `jadwal_ruangan`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_dosen` (`id_dosen`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `fk_jadwal_matkul` (`id_matkul`);

--
-- Indeks untuk tabel `peserta_kuliah`
--
ALTER TABLE `peserta_kuliah`
  ADD PRIMARY KEY (`id_peserta`),
  ADD UNIQUE KEY `unique_peserta` (`id_mahasiswa`,`id_jadwal`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `face_data`
--
ALTER TABLE `face_data`
  ADD PRIMARY KEY (`id_face`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`);

--
-- Indeks untuk tabel `absensi_wajah`
--
ALTER TABLE `absensi_wajah`
  ADD PRIMARY KEY (`id_absensi`),
  ADD UNIQUE KEY `unique_absensi` (`id_mahasiswa`,`id_jadwal`,`tanggal`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_absensi_status` (`status`);

--
-- Indeks untuk tabel `face_detection_log`
--
ALTER TABLE `face_detection_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `idx_detected_at` (`detected_at`);

--
-- Indeks untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD PRIMARY KEY (`id_kehadiran`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `kehadiran_manual`
--
ALTER TABLE `kehadiran_manual`
  ADD PRIMARY KEY (`id_kehadiran`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `diinput_oleh` (`diinput_oleh`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `dosen`
  MODIFY `id_dosen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `mata_kuliah`
  MODIFY `id_matkul` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `matakuliah`
  MODIFY `id_matkul` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ruangan`
  MODIFY `id_ruangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `tahun_ajaran`
  MODIFY `id_tahun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `jadwal_kuliah`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jadwal_ruangan`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `peserta_kuliah`
  MODIFY `id_peserta` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `face_data`
  MODIFY `id_face` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absensi_wajah`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `face_detection_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kehadiran`
  MODIFY `id_kehadiran` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kehadiran_manual`
  MODIFY `id_kehadiran` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_matkul`) REFERENCES `mata_kuliah` (`id_matkul`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_4` FOREIGN KEY (`id_tahun`) REFERENCES `tahun_ajaran` (`id_tahun`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal_ruangan`
--
ALTER TABLE `jadwal_ruangan`
  ADD CONSTRAINT `fk_jadwal_matkul` FOREIGN KEY (`id_matkul`) REFERENCES `matakuliah` (`id_matkul`);

--
-- Ketidakleluasaan untuk tabel `peserta_kuliah`
--
ALTER TABLE `peserta_kuliah`
  ADD CONSTRAINT `peserta_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `peserta_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_kuliah` (`id_jadwal`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `face_data`
--
ALTER TABLE `face_data`
  ADD CONSTRAINT `face_data_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `absensi_wajah`
--
ALTER TABLE `absensi_wajah`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_kuliah` (`id_jadwal`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `face_detection_log`
--
ALTER TABLE `face_detection_log`
  ADD CONSTRAINT `detection_log_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE SET NULL,
  ADD CONSTRAINT `detection_log_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD CONSTRAINT `kehadiran_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`),
  ADD CONSTRAINT `kehadiran_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_ruangan` (`id_jadwal`);

--
-- Ketidakleluasaan untuk tabel `kehadiran_manual`
--
ALTER TABLE `kehadiran_manual`
  ADD CONSTRAINT `kehadiran_manual_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `kehadiran_manual_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_kuliah` (`id_jadwal`) ON DELETE CASCADE,
  ADD CONSTRAINT `kehadiran_manual_ibfk_3` FOREIGN KEY (`diinput_oleh`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_face` (IN `p_id_mahasiswa` INT, IN `p_face_encoding` TEXT, IN `p_model_version` VARCHAR(20), IN `p_quality_score` DECIMAL(5,2))   BEGIN
  -- Insert face data
  INSERT INTO face_data (id_mahasiswa, face_encoding, model_version, quality_score)
  VALUES (p_id_mahasiswa, p_face_encoding, p_model_version, p_quality_score);

  -- Update mahasiswa
  UPDATE mahasiswa
  SET face_registered = 1,
      face_registered_at = NOW()
  WHERE id_mahasiswa = p_id_mahasiswa;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_catat_absensi_wajah` (IN `p_id_mahasiswa` INT, IN `p_id_jadwal` INT, IN `p_tanggal` DATE, IN `p_jam_absen` TIME, IN `p_confidence` DECIMAL(5,2), IN `p_camera_id` VARCHAR(50), OUT `p_status` VARCHAR(20), OUT `p_message` VARCHAR(255))   BEGIN
  DECLARE v_jam_mulai TIME;
  DECLARE v_jam_selesai TIME;
  DECLARE v_toleransi_menit INT DEFAULT 15;
  DECLARE v_existing INT;

  -- Cek jadwal
  SELECT jam_mulai, jam_selesai
  INTO v_jam_mulai, v_jam_selesai
  FROM jadwal_kuliah
  WHERE id_jadwal = p_id_jadwal;

  -- Cek apakah sudah absen
  SELECT COUNT(*) INTO v_existing
  FROM absensi_wajah
  WHERE id_mahasiswa = p_id_mahasiswa
    AND id_jadwal = p_id_jadwal
    AND tanggal = p_tanggal;

  IF v_existing > 0 THEN
    SET p_status = 'failed';
    SET p_message = 'Sudah melakukan absensi untuk jadwal ini';
  ELSE
    -- Tentukan status kehadiran
    IF p_jam_absen <= ADDTIME(v_jam_mulai, SEC_TO_TIME(v_toleransi_menit * 60)) THEN
      SET p_status = 'hadir';
    ELSE
      SET p_status = 'terlambat';
    END IF;

    -- Insert absensi
    INSERT INTO absensi_wajah (id_mahasiswa, id_jadwal, tanggal, jam_absen, confidence, status)
    VALUES (p_id_mahasiswa, p_id_jadwal, p_tanggal, p_jam_absen, p_confidence, p_status);

    -- Log deteksi
    INSERT INTO face_detection_log (id_mahasiswa, confidence, camera_id, detection_status)
    VALUES (p_id_mahasiswa, p_confidence, p_camera_id, 'success');

    SET p_message = CONCAT('Absensi berhasil dengan status: ', p_status);
  END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
--
-- Struktur untuk view `v_mahasiswa_face_registered`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mahasiswa_face_registered` AS
SELECT
  `m`.`id_mahasiswa` AS `id_mahasiswa`,
  `m`.`nim` AS `nim`,
  `m`.`nama_mahasiswa` AS `nama_mahasiswa`,
  `m`.`jurusan` AS `jurusan`,
  `m`.`angkatan` AS `angkatan`,
  `m`.`face_registered` AS `face_registered`,
  `m`.`face_registered_at` AS `face_registered_at`,
  `f`.`model_version` AS `model_version`,
  `f`.`quality_score` AS `quality_score`
FROM (`mahasiswa` `m` LEFT JOIN `face_data` `f` ON(`m`.`id_mahasiswa` = `f`.`id_mahasiswa`))
WHERE `m`.`face_registered` = 1
ORDER BY `m`.`nama_mahasiswa` ASC;

-- --------------------------------------------------------
--
-- Struktur untuk view `v_rekap_kehadiran`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rekap_kehadiran` AS
SELECT
  `m`.`nim` AS `nim`,
  `m`.`nama_mahasiswa` AS `nama_mahasiswa`,
  `mk`.`nama_matkul` AS `nama_matkul`,
  COUNT(CASE WHEN `a`.`status` = 'hadir' THEN 1 END) AS `jumlah_hadir`,
  COUNT(CASE WHEN `a`.`status` = 'terlambat' THEN 1 END) AS `jumlah_terlambat`,
  COUNT(CASE WHEN `a`.`status` = 'izin' THEN 1 END) AS `jumlah_izin`,
  COUNT(CASE WHEN `a`.`status` = 'sakit' THEN 1 END) AS `jumlah_sakit`,
  COUNT(CASE WHEN `a`.`status` = 'alpa' THEN 1 END) AS `jumlah_alpa`,
  COUNT(*) AS `total_pertemuan`,
  ROUND(COUNT(CASE WHEN `a`.`status` IN ('hadir','terlambat') THEN 1 END) / COUNT(*) * 100, 2) AS `persentase_kehadiran`
FROM (((`mahasiswa` `m`
  JOIN `absensi_wajah` `a` ON(`m`.`id_mahasiswa` = `a`.`id_mahasiswa`))
  JOIN `jadwal_kuliah` `j` ON(`a`.`id_jadwal` = `j`.`id_jadwal`))
  JOIN `mata_kuliah` `mk` ON(`j`.`id_matkul` = `mk`.`id_matkul`))
GROUP BY `m`.`nim`, `m`.`nama_mahasiswa`, `mk`.`nama_matkul`;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
