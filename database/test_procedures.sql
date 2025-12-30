-- ============================================================================
-- Test Script for Stored Procedures
-- ============================================================================
-- Purpose: Test and demonstrate usage of all stored procedures
-- Usage: Run this script after importing stored_procedures.sql
-- ============================================================================

-- Test 1: Register Face Data
-- ============================================================================
SELECT '=== TEST 1: Register Face Data ===' AS test;

-- Test 1a: Register new face (should succeed)
CALL sp_register_face(
  1,
  '{"encoding": [0.123, 0.456, 0.789, 0.012, 0.345]}',
  'v1.0',
  95.50,
  @status,
  @message
);
SELECT @status AS status, @message AS message, '1a: New registration' AS test_case;

-- Test 1b: Update existing face (should succeed)
CALL sp_register_face(
  1,
  '{"encoding": [0.111, 0.222, 0.333, 0.444, 0.555]}',
  'v1.5',
  97.00,
  @status,
  @message
);
SELECT @status AS status, @message AS message, '1b: Update existing' AS test_case;

-- Test 1c: Register for non-existent student (should fail)
CALL sp_register_face(
  9999,
  '{"encoding": [0.111, 0.222, 0.333]}',
  'v1.0',
  95.00,
  @status,
  @message
);
SELECT @status AS status, @message AS message, '1c: Invalid student' AS test_case;

-- Verify face registration
SELECT
  m.nim,
  m.nama_mahasiswa,
  m.face_registered,
  f.model_version,
  f.quality_score
FROM mahasiswa m
LEFT JOIN face_data f ON m.id_mahasiswa = f.id_mahasiswa
WHERE m.id_mahasiswa = 1;


-- Test 2: Setup Test Data for Attendance
-- ============================================================================
SELECT '=== TEST 2: Setup Test Data ===' AS test;

-- Create test schedule (if not exists)
INSERT INTO jadwal_kuliah (id_matkul, id_dosen, id_ruangan, id_tahun, hari, jam_mulai, jam_selesai, kelas)
SELECT 1, 1, 1, 1, 'Senin', '08:00:00', '10:00:00', 'A'
WHERE NOT EXISTS (
  SELECT 1 FROM jadwal_kuliah
  WHERE id_matkul = 1 AND id_dosen = 1 AND hari = 'Senin'
)
LIMIT 1;

SET @test_jadwal_id = LAST_INSERT_ID();
IF @test_jadwal_id = 0 THEN
  SELECT @test_jadwal_id := id_jadwal FROM jadwal_kuliah
  WHERE id_matkul = 1 AND id_dosen = 1 AND hari = 'Senin' LIMIT 1;
END IF;

-- Enroll student in course (if not already enrolled)
INSERT INTO peserta_kuliah (id_mahasiswa, id_jadwal, status_peserta)
SELECT 1, @test_jadwal_id, 'aktif'
WHERE NOT EXISTS (
  SELECT 1 FROM peserta_kuliah
  WHERE id_mahasiswa = 1 AND id_jadwal = @test_jadwal_id
);

SELECT @test_jadwal_id AS created_schedule_id;


-- Test 3: Record Attendance
-- ============================================================================
SELECT '=== TEST 3: Record Attendance ===' AS test;

-- Get a Monday date for testing
SET @test_date = DATE_ADD(CURDATE(), INTERVAL (9 - DAYOFWEEK(CURDATE())) % 7 DAY);

-- Test 3a: Valid attendance - on time (should succeed)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '08:05:00',
  92.50,
  'CAM-001',
  'test_photo_1.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3a: On-time attendance' AS test_case;

-- Clean up for next test
DELETE FROM absensi_wajah WHERE id_mahasiswa = 1 AND id_jadwal = @test_jadwal_id AND tanggal = @test_date;

-- Test 3b: Valid attendance - late (should succeed)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '08:30:00',
  91.00,
  'CAM-001',
  'test_photo_2.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3b: Late attendance' AS test_case;

-- Test 3c: Duplicate attendance (should fail)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '08:35:00',
  90.00,
  'CAM-001',
  'test_photo_3.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3c: Duplicate attendance' AS test_case;

-- Clean up and create for low confidence test
DELETE FROM absensi_wajah WHERE id_mahasiswa = 1 AND id_jadwal = @test_jadwal_id AND tanggal = @test_date;

-- Test 3d: Low confidence score (should fail)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '08:05:00',
  75.00,
  'CAM-001',
  'test_photo_4.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3d: Low confidence' AS test_case;

-- Test 3e: Too early (should fail)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '07:00:00',
  92.00,
  'CAM-001',
  'test_photo_5.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3e: Too early' AS test_case;

-- Test 3f: After class ends (should fail)
CALL sp_catat_absensi_wajah(
  1,
  @test_jadwal_id,
  @test_date,
  '11:00:00',
  92.00,
  'CAM-001',
  'test_photo_6.jpg',
  @status,
  @message
);
SELECT @status AS status, @message AS message, '3f: After class' AS test_case;

-- Create valid attendance for remaining tests
DELETE FROM absensi_wajah WHERE id_mahasiswa = 1 AND id_jadwal = @test_jadwal_id AND tanggal = @test_date;
CALL sp_catat_absensi_wajah(
  1, @test_jadwal_id, @test_date, '08:20:00', 93.00, 'CAM-001', 'test_final.jpg',
  @status, @message
);


-- Test 4: Get Today's Schedule
-- ============================================================================
SELECT '=== TEST 4: Get Schedule ===' AS test;

-- Test 4a: Get schedule for Monday
CALL sp_get_jadwal_hari_ini(1, @test_date);
SELECT '4a: Schedule for test date' AS test_case;

-- Test 4b: Get schedule for different day (should return empty)
SET @test_date_tuesday = DATE_ADD(@test_date, INTERVAL 1 DAY);
CALL sp_get_jadwal_hari_ini(1, @test_date_tuesday);
SELECT '4b: Schedule for different day' AS test_case;


-- Test 5: Get Attendance Summary
-- ============================================================================
SELECT '=== TEST 5: Get Attendance Summary ===' AS test;

-- Create multiple attendance records for testing
SET @test_date_2 = DATE_ADD(@test_date, INTERVAL 7 DAY);
SET @test_date_3 = DATE_ADD(@test_date, INTERVAL 14 DAY);

INSERT INTO absensi_wajah (id_mahasiswa, id_jadwal, tanggal, jam_absen, confidence, status)
VALUES
  (1, @test_jadwal_id, @test_date_2, '08:05:00', 94.00, 'hadir'),
  (1, @test_jadwal_id, @test_date_3, '08:25:00', 91.50, 'terlambat')
ON DUPLICATE KEY UPDATE jam_absen = VALUES(jam_absen);

-- Test 5a: Get summary for all courses
CALL sp_get_rekap_kehadiran_mahasiswa(1, NULL);
SELECT '5a: Summary for all courses' AS test_case;

-- Test 5b: Get detailed records for specific course
CALL sp_get_rekap_kehadiran_mahasiswa(1, @test_jadwal_id);
SELECT '5b: Detailed for specific course' AS test_case;


-- Test 6: Update Attendance Status
-- ============================================================================
SELECT '=== TEST 6: Update Attendance Status ===' AS test;

-- Get an attendance ID for testing
SET @test_absensi_id = (
  SELECT id_absensi
  FROM absensi_wajah
  WHERE id_mahasiswa = 1
    AND id_jadwal = @test_jadwal_id
    AND tanggal = @test_date
  LIMIT 1
);

-- Test 6a: Valid update (should succeed)
CALL sp_update_status_absensi(
  @test_absensi_id,
  'izin',
  'Surat izin dari orang tua',
  1,
  @status,
  @message
);
SELECT @status AS status, @message AS message, '6a: Valid status update' AS test_case;

-- Verify the update
SELECT id_absensi, status, keterangan
FROM absensi_wajah
WHERE id_absensi = @test_absensi_id;

-- Test 6b: Update non-existent record (should fail)
CALL sp_update_status_absensi(
  99999,
  'sakit',
  'Test',
  1,
  @status,
  @message
);
SELECT @status AS status, @message AS message, '6b: Invalid record' AS test_case;


-- Test 7: Delete Face Data
-- ============================================================================
SELECT '=== TEST 7: Delete Face Data ===' AS test;

-- Test 7a: Delete existing face data (should succeed)
CALL sp_delete_face_data(1, @status, @message);
SELECT @status AS status, @message AS message, '7a: Delete existing' AS test_case;

-- Verify deletion
SELECT
  m.nim,
  m.nama_mahasiswa,
  m.face_registered,
  COUNT(f.id_face) AS face_data_count
FROM mahasiswa m
LEFT JOIN face_data f ON m.id_mahasiswa = f.id_mahasiswa
WHERE m.id_mahasiswa = 1
GROUP BY m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.face_registered;

-- Test 7b: Delete already deleted (should fail)
CALL sp_delete_face_data(1, @status, @message);
SELECT @status AS status, @message AS message, '7b: Already deleted' AS test_case;


-- Test 8: Check Face Detection Logs
-- ============================================================================
SELECT '=== TEST 8: Face Detection Logs ===' AS test;

SELECT
  l.id_log,
  m.nim,
  m.nama_mahasiswa,
  l.detected_at,
  l.confidence,
  l.camera_id,
  l.detection_status
FROM face_detection_log l
JOIN mahasiswa m ON l.id_mahasiswa = m.id_mahasiswa
WHERE l.id_mahasiswa = 1
ORDER BY l.detected_at DESC
LIMIT 10;


-- Test 9: Check Views
-- ============================================================================
SELECT '=== TEST 9: Check Views ===' AS test;

-- Re-register face for view test
CALL sp_register_face(
  1,
  '{"encoding": [0.111, 0.222, 0.333]}',
  'v2.0',
  96.00,
  @status,
  @message
);

-- Test view: v_mahasiswa_face_registered
SELECT * FROM v_mahasiswa_face_registered WHERE id_mahasiswa = 1;

-- Test view: v_rekap_kehadiran
SELECT * FROM v_rekap_kehadiran WHERE nim = '3312411080';


-- ============================================================================
-- Cleanup (Optional - uncomment to clean test data)
-- ============================================================================
/*
DELETE FROM face_detection_log WHERE id_mahasiswa = 1;
DELETE FROM absensi_wajah WHERE id_mahasiswa = 1;
DELETE FROM peserta_kuliah WHERE id_mahasiswa = 1 AND id_jadwal = @test_jadwal_id;
DELETE FROM jadwal_kuliah WHERE id_jadwal = @test_jadwal_id;
DELETE FROM face_data WHERE id_mahasiswa = 1;
UPDATE mahasiswa SET face_registered = 0, face_registered_at = NULL WHERE id_mahasiswa = 1;
*/


-- ============================================================================
-- Summary
-- ============================================================================
SELECT '=== TEST SUMMARY ===' AS summary;

SELECT
  'Total Stored Procedures' AS metric,
  COUNT(*) AS count
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sistem_absensi_face'
  AND ROUTINE_TYPE = 'PROCEDURE';

SELECT
  ROUTINE_NAME AS procedure_name,
  CREATED AS created_date
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sistem_absensi_face'
  AND ROUTINE_TYPE = 'PROCEDURE'
ORDER BY ROUTINE_NAME;

SELECT '=== END OF TESTS ===' AS end_marker;
