-- Migration: create jadwal_ruangan table
-- Jalankan file ini di phpMyAdmin atau MySQL CLI

CREATE TABLE IF NOT EXISTS `jadwal_ruangan` (
  `id_jadwal` INT NOT NULL AUTO_INCREMENT,
  `id_dosen` INT DEFAULT NULL,
  `id_ruangan` INT NOT NULL,
  `hari` VARCHAR(20) DEFAULT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  INDEX (`id_dosen`),
  INDEX (`id_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
