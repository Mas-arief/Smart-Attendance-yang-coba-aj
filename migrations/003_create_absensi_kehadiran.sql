-- Migration: create absensi_wajah & kehadiran tables
-- Jalankan file ini di phpMyAdmin atau MySQL CLI

CREATE TABLE IF NOT EXISTS `absensi_wajah` (
  `id_absensi` INT NOT NULL AUTO_INCREMENT,
  `nim` VARCHAR(20) NOT NULL,
  `nama_mahasiswa` VARCHAR(100) NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_absen` TIME NOT NULL,
  `confidence` FLOAT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'Hadir',
  PRIMARY KEY (`id_absensi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kehadiran` (
  `id_kehadiran` INT NOT NULL AUTO_INCREMENT,
  `id_jadwal` INT DEFAULT NULL,
  `id_mahasiswa` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Hadir',
  `keterangan` VARCHAR(100) DEFAULT NULL,
  `waktu_absen` DATETIME NOT NULL,
  `confidence_score` FLOAT DEFAULT NULL,
  PRIMARY KEY (`id_kehadiran`),
  INDEX (`id_jadwal`),
  INDEX (`id_mahasiswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
