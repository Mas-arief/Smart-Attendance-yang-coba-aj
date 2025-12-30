-- Migration: create jadwal_ruangan table
-- Run this SQL in your database (phpMyAdmin or mysql CLI)

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

-- Optional foreign keys (uncomment and adjust if you have these tables)
-- ALTER TABLE `jadwal_ruangan` ADD CONSTRAINT fk_jadwal_dosen FOREIGN KEY (id_dosen) REFERENCES dosen(id_dosen);
-- ALTER TABLE `jadwal_ruangan` ADD CONSTRAINT fk_jadwal_ruangan FOREIGN KEY (id_ruangan) REFERENCES ruangan(id_ruangan);
