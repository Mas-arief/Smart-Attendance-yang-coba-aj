-- ==========================================
-- DATABASE SMART ATTENDANCE - FACE RECOGNITION
-- Created: 2025-12-30
-- ==========================================

-- Buat Database
CREATE DATABASE IF NOT EXISTS smart_attendance_db
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE smart_attendance_db;

-- ==========================================
-- TABEL 1: ADMIN / DOSEN
-- ==========================================
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    no_telp VARCHAR(15),
    role ENUM('admin', 'dosen') DEFAULT 'dosen',
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 2: MAHASISWA
-- ==========================================
CREATE TABLE IF NOT EXISTS mahasiswa (
    mahasiswa_id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    no_telp VARCHAR(15),
    jurusan VARCHAR(50),
    semester INT DEFAULT 1,
    angkatan YEAR,
    foto_profil VARCHAR(255) DEFAULT NULL,

    -- Data Wajah AI
    face_registered BOOLEAN DEFAULT FALSE,
    face_dataset_path VARCHAR(255) DEFAULT NULL,
    face_registered_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('aktif', 'nonaktif', 'cuti') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 3: MATA KULIAH
-- ==========================================
CREATE TABLE IF NOT EXISTS mata_kuliah (
    matkul_id INT AUTO_INCREMENT PRIMARY KEY,
    kode_matkul VARCHAR(20) NOT NULL UNIQUE,
    nama_matkul VARCHAR(100) NOT NULL,
    sks INT DEFAULT 3,
    semester INT,
    dosen_id INT,
    ruangan VARCHAR(50),
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'),
    jam_mulai TIME,
    jam_selesai TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',

    FOREIGN KEY (dosen_id) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 4: KELAS (Relasi Mahasiswa & Mata Kuliah)
-- ==========================================
CREATE TABLE IF NOT EXISTS kelas (
    kelas_id INT AUTO_INCREMENT PRIMARY KEY,
    matkul_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    tahun_ajaran VARCHAR(10), -- Contoh: 2024/2025
    semester ENUM('Ganjil', 'Genap'),
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (matkul_id) REFERENCES mata_kuliah(matkul_id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(mahasiswa_id) ON DELETE CASCADE,

    UNIQUE KEY unique_enrollment (matkul_id, mahasiswa_id, tahun_ajaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 5: SESI PERKULIAHAN
-- ==========================================
CREATE TABLE IF NOT EXISTS sesi_kuliah (
    sesi_id INT AUTO_INCREMENT PRIMARY KEY,
    matkul_id INT NOT NULL,
    dosen_id INT,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    materi VARCHAR(255),
    ruangan VARCHAR(50),

    -- Status Sesi
    sesi_aktif BOOLEAN DEFAULT FALSE,
    sesi_dibuka_at TIMESTAMP NULL,
    sesi_ditutup_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (matkul_id) REFERENCES mata_kuliah(matkul_id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 6: ABSENSI (INTI - FACE RECOGNITION)
-- ==========================================
CREATE TABLE IF NOT EXISTS absensi (
    absensi_id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    sesi_id INT,
    matkul_id INT,

    tanggal DATE NOT NULL,
    jam TIME NOT NULL,

    -- Status Kehadiran
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') DEFAULT 'Hadir',

    -- Data AI Face Recognition
    metode_absensi ENUM('face_recognition', 'manual') DEFAULT 'face_recognition',
    confidence_score DECIMAL(5,2) DEFAULT NULL, -- Tingkat kepercayaan AI (0-100)
    face_image_path VARCHAR(255) DEFAULT NULL, -- Path foto saat absen

    keterangan TEXT,
    ip_address VARCHAR(45), -- IPv4 atau IPv6
    device_info VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(mahasiswa_id) ON DELETE CASCADE,
    FOREIGN KEY (sesi_id) REFERENCES sesi_kuliah(sesi_id) ON DELETE SET NULL,
    FOREIGN KEY (matkul_id) REFERENCES mata_kuliah(matkul_id) ON DELETE SET NULL,

    -- Cegah absen ganda di sesi yang sama
    UNIQUE KEY unique_attendance (mahasiswa_id, sesi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 7: LOG SISTEM (TRACKING AI & SISTEM)
-- ==========================================
CREATE TABLE IF NOT EXISTS system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('admin', 'mahasiswa'),
    action VARCHAR(100) NOT NULL, -- Contoh: 'face_registration', 'attendance_detected'
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABEL 8: SETTINGS SISTEM
-- ==========================================
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- INSERT DEFAULT DATA
-- ==========================================

-- Default Admin
INSERT INTO admin (username, password, nama_lengkap, email, role) VALUES
('admin', MD5('admin123'), 'Administrator', 'admin@smartattendance.com', 'admin'),
('dosen1', MD5('dosen123'), 'Dr. Budi Santoso, M.Kom', 'budi@university.ac.id', 'dosen');

-- Default Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('ai_confidence_threshold', '70', 'Minimum confidence score untuk face recognition (0-100)'),
('absensi_tolerance_menit', '15', 'Toleransi keterlambatan dalam menit'),
('face_samples_required', '30', 'Jumlah sampel wajah untuk registrasi'),
('system_name', 'Smart Attendance - Face Recognition', 'Nama aplikasi'),
('academic_year', '2024/2025', 'Tahun ajaran aktif'),
('current_semester', 'Ganjil', 'Semester aktif');

-- Sample Mahasiswa (untuk testing)
INSERT INTO mahasiswa (nim, nama_lengkap, email, jurusan, semester, angkatan) VALUES
('210001', 'Ahmad Rizki', 'ahmad.rizki@student.ac.id', 'Teknik Informatika', 5, 2021),
('210002', 'Siti Nurhaliza', 'siti.nurhaliza@student.ac.id', 'Sistem Informasi', 5, 2021),
('210003', 'Budi Setiawan', 'budi.setiawan@student.ac.id', 'Teknik Informatika', 5, 2021);

-- Sample Mata Kuliah
INSERT INTO mata_kuliah (kode_matkul, nama_matkul, sks, semester, dosen_id, hari, jam_mulai, jam_selesai) VALUES
('TIF301', 'Pemrograman Web', 3, 5, 2, 'Senin', '08:00:00', '10:30:00'),
('TIF302', 'Kecerdasan Buatan', 3, 5, 2, 'Rabu', '10:00:00', '12:30:00'),
('TIF303', 'Basis Data Lanjut', 3, 5, 2, 'Jumat', '13:00:00', '15:30:00');

-- ==========================================
-- INDEX UNTUK PERFORMA
-- ==========================================
CREATE INDEX idx_mahasiswa_nim ON mahasiswa(nim);
CREATE INDEX idx_mahasiswa_face ON mahasiswa(face_registered);
CREATE INDEX idx_absensi_tanggal ON absensi(tanggal);
CREATE INDEX idx_absensi_mahasiswa ON absensi(mahasiswa_id);
CREATE INDEX idx_sesi_tanggal ON sesi_kuliah(tanggal);

-- ==========================================
-- VIEWS (LAPORAN CEPAT)
-- ==========================================

-- View: Rekap Absensi per Mahasiswa
CREATE OR REPLACE VIEW v_rekap_absensi AS
SELECT
    m.mahasiswa_id,
    m.nim,
    m.nama_lengkap,
    mk.nama_matkul,
    COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
    COUNT(CASE WHEN a.status = 'Izin' THEN 1 END) as total_izin,
    COUNT(CASE WHEN a.status = 'Sakit' THEN 1 END) as total_sakit,
    COUNT(CASE WHEN a.status = 'Alpa' THEN 1 END) as total_alpa,
    COUNT(*) as total_pertemuan,
    ROUND((COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) / COUNT(*)) * 100, 2) as persentase_kehadiran
FROM mahasiswa m
LEFT JOIN absensi a ON m.mahasiswa_id = a.mahasiswa_id
LEFT JOIN mata_kuliah mk ON a.matkul_id = mk.matkul_id
GROUP BY m.mahasiswa_id, mk.matkul_id;

-- View: Absensi Hari Ini
CREATE OR REPLACE VIEW v_absensi_today AS
SELECT
    a.absensi_id,
    m.nim,
    m.nama_lengkap,
    mk.nama_matkul,
    a.jam,
    a.status,
    a.confidence_score,
    a.metode_absensi
FROM absensi a
JOIN mahasiswa m ON a.mahasiswa_id = m.mahasiswa_id
LEFT JOIN mata_kuliah mk ON a.matkul_id = mk.matkul_id
WHERE a.tanggal = CURDATE()
ORDER BY a.jam DESC;

-- ==========================================
-- SELESAI ✅
-- ==========================================
