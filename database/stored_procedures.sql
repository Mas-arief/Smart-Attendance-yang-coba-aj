-- ============================================================================
-- Stored Procedures for Sistem Absensi Face Recognition
-- ============================================================================
-- Version: 2.0
-- Last Updated: 2025-12-30
-- Description: Enhanced stored procedures with improved error handling,
--              validation, and transaction support
-- ============================================================================

DELIMITER $$

-- ============================================================================
-- sp_register_face - Register student face data
-- ============================================================================
-- Description: Registers face encoding data for a student with validation
-- Parameters:
--   IN  p_id_mahasiswa   : Student ID
--   IN  p_face_encoding  : Face encoding data (text/json)
--   IN  p_model_version  : Model version used (e.g., 'v1.0')
--   IN  p_quality_score  : Quality score of the face image (0-100)
--   OUT p_status         : Result status ('success' or 'failed')
--   OUT p_message        : Result message
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_register_face`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_face` (
  IN `p_id_mahasiswa` INT,
  IN `p_face_encoding` TEXT,
  IN `p_model_version` VARCHAR(20),
  IN `p_quality_score` DECIMAL(5,2),
  OUT `p_status` VARCHAR(20),
  OUT `p_message` VARCHAR(255)
)
BEGIN
  DECLARE v_existing_face INT DEFAULT 0;
  DECLARE v_mahasiswa_exists INT DEFAULT 0;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_status = 'failed';
    SET p_message = 'Error: Gagal mendaftarkan data wajah';
  END;

  START TRANSACTION;

  -- Validate student exists
  SELECT COUNT(*) INTO v_mahasiswa_exists
  FROM mahasiswa
  WHERE id_mahasiswa = p_id_mahasiswa;

  IF v_mahasiswa_exists = 0 THEN
    SET p_status = 'failed';
    SET p_message = 'Error: Mahasiswa tidak ditemukan';
    ROLLBACK;
  ELSE
    -- Check if face already registered
    SELECT COUNT(*) INTO v_existing_face
    FROM face_data
    WHERE id_mahasiswa = p_id_mahasiswa;

    IF v_existing_face > 0 THEN
      -- Update existing face data
      UPDATE face_data
      SET face_encoding = p_face_encoding,
          model_version = p_model_version,
          quality_score = p_quality_score,
          updated_at = NOW()
      WHERE id_mahasiswa = p_id_mahasiswa;

      SET p_message = 'Data wajah berhasil diperbarui';
    ELSE
      -- Insert new face data
      INSERT INTO face_data (id_mahasiswa, face_encoding, model_version, quality_score)
      VALUES (p_id_mahasiswa, p_face_encoding, p_model_version, p_quality_score);

      SET p_message = 'Data wajah berhasil didaftarkan';
    END IF;

    -- Update mahasiswa face registration status
    UPDATE mahasiswa
    SET face_registered = 1,
        face_registered_at = NOW()
    WHERE id_mahasiswa = p_id_mahasiswa;

    SET p_status = 'success';
    COMMIT;
  END IF;
END$$

-- ============================================================================
-- sp_catat_absensi_wajah - Record attendance via face recognition
-- ============================================================================
-- Description: Records student attendance with comprehensive validation
-- Parameters:
--   IN  p_id_mahasiswa : Student ID
--   IN  p_id_jadwal    : Schedule ID
--   IN  p_tanggal      : Attendance date
--   IN  p_jam_absen    : Attendance time
--   IN  p_confidence   : Face recognition confidence score (0-100)
--   IN  p_camera_id    : Camera identifier
--   IN  p_foto_absen   : Photo filename (optional)
--   OUT p_status       : Result status
--   OUT p_message      : Result message
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_catat_absensi_wajah`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_catat_absensi_wajah` (
  IN `p_id_mahasiswa` INT,
  IN `p_id_jadwal` INT,
  IN `p_tanggal` DATE,
  IN `p_jam_absen` TIME,
  IN `p_confidence` DECIMAL(5,2),
  IN `p_camera_id` VARCHAR(50),
  IN `p_foto_absen` VARCHAR(255),
  OUT `p_status` VARCHAR(20),
  OUT `p_message` VARCHAR(255)
)
BEGIN
  DECLARE v_jam_mulai TIME;
  DECLARE v_jam_selesai TIME;
  DECLARE v_toleransi_menit INT DEFAULT 15;
  DECLARE v_existing INT DEFAULT 0;
  DECLARE v_jadwal_exists INT DEFAULT 0;
  DECLARE v_is_enrolled INT DEFAULT 0;
  DECLARE v_attendance_status VARCHAR(20);
  DECLARE v_hari_jadwal VARCHAR(20);
  DECLARE v_hari_sekarang VARCHAR(20);
  DECLARE v_min_confidence DECIMAL(5,2) DEFAULT 85.00;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_status = 'failed';
    SET p_message = 'Error: Terjadi kesalahan sistem saat mencatat absensi';
  END;

  START TRANSACTION;

  -- Validate confidence score
  IF p_confidence < v_min_confidence THEN
    SET p_status = 'failed';
    SET p_message = CONCAT('Error: Confidence score terlalu rendah (', p_confidence, '%). Minimum: ', v_min_confidence, '%');
    ROLLBACK;
  ELSE
    -- Check if schedule exists and get schedule details
    SELECT COUNT(*), MAX(jam_mulai), MAX(jam_selesai), MAX(hari)
    INTO v_jadwal_exists, v_jam_mulai, v_jam_selesai, v_hari_jadwal
    FROM jadwal_kuliah
    WHERE id_jadwal = p_id_jadwal;

    IF v_jadwal_exists = 0 THEN
      SET p_status = 'failed';
      SET p_message = 'Error: Jadwal kuliah tidak ditemukan';
      ROLLBACK;
    ELSE
      -- Get current day name in Indonesian
      SET v_hari_sekarang = CASE DAYNAME(p_tanggal)
        WHEN 'Monday' THEN 'Senin'
        WHEN 'Tuesday' THEN 'Selasa'
        WHEN 'Wednesday' THEN 'Rabu'
        WHEN 'Thursday' THEN 'Kamis'
        WHEN 'Friday' THEN 'Jumat'
        WHEN 'Saturday' THEN 'Sabtu'
        WHEN 'Sunday' THEN 'Minggu'
      END;

      -- Check if day matches schedule
      IF v_hari_jadwal != v_hari_sekarang THEN
        SET p_status = 'failed';
        SET p_message = CONCAT('Error: Hari tidak sesuai jadwal. Jadwal: ', v_hari_jadwal, ', Sekarang: ', v_hari_sekarang);
        ROLLBACK;
      ELSE
        -- Check if student is enrolled in this class
        SELECT COUNT(*) INTO v_is_enrolled
        FROM peserta_kuliah
        WHERE id_mahasiswa = p_id_mahasiswa
          AND id_jadwal = p_id_jadwal
          AND status_peserta = 'aktif';

        IF v_is_enrolled = 0 THEN
          SET p_status = 'failed';
          SET p_message = 'Error: Mahasiswa tidak terdaftar dalam mata kuliah ini';
          ROLLBACK;
        ELSE
          -- Check if already recorded attendance for this schedule and date
          SELECT COUNT(*) INTO v_existing
          FROM absensi_wajah
          WHERE id_mahasiswa = p_id_mahasiswa
            AND id_jadwal = p_id_jadwal
            AND tanggal = p_tanggal;

          IF v_existing > 0 THEN
            SET p_status = 'failed';
            SET p_message = 'Error: Sudah melakukan absensi untuk jadwal ini';
            ROLLBACK;
          ELSE
            -- Validate attendance time is within reasonable range
            -- Allow attendance from 30 minutes before until class ends
            IF p_jam_absen < SUBTIME(v_jam_mulai, '00:30:00') THEN
              SET p_status = 'failed';
              SET p_message = CONCAT('Error: Terlalu dini untuk absen. Jadwal mulai: ', v_jam_mulai);
              ROLLBACK;
            ELSEIF p_jam_absen > v_jam_selesai THEN
              SET p_status = 'failed';
              SET p_message = CONCAT('Error: Waktu kuliah sudah selesai. Jadwal selesai: ', v_jam_selesai);
              ROLLBACK;
            ELSE
              -- Determine attendance status based on time
              IF p_jam_absen <= ADDTIME(v_jam_mulai, SEC_TO_TIME(v_toleransi_menit * 60)) THEN
                SET v_attendance_status = 'hadir';
              ELSE
                SET v_attendance_status = 'terlambat';
              END IF;

              -- Insert attendance record
              INSERT INTO absensi_wajah (
                id_mahasiswa,
                id_jadwal,
                tanggal,
                jam_absen,
                confidence,
                status,
                foto_absen
              ) VALUES (
                p_id_mahasiswa,
                p_id_jadwal,
                p_tanggal,
                p_jam_absen,
                p_confidence,
                v_attendance_status,
                p_foto_absen
              );

              -- Log face detection
              INSERT INTO face_detection_log (
                id_mahasiswa,
                confidence,
                camera_id,
                detection_status
              ) VALUES (
                p_id_mahasiswa,
                p_confidence,
                p_camera_id,
                'success'
              );

              SET p_status = 'success';
              SET p_message = CONCAT(
                'Absensi berhasil! Status: ',
                v_attendance_status,
                ' | Confidence: ',
                p_confidence,
                '%'
              );

              COMMIT;
            END IF;
          END IF;
        END IF;
      END IF;
    END IF;
  END IF;
END$$

-- ============================================================================
-- sp_get_jadwal_hari_ini - Get today's schedule for a student
-- ============================================================================
-- Description: Retrieves all schedules for a student on a specific date
-- Parameters:
--   IN p_id_mahasiswa : Student ID
--   IN p_tanggal      : Date to check
-- Returns: Result set of schedules
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_get_jadwal_hari_ini`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_jadwal_hari_ini` (
  IN `p_id_mahasiswa` INT,
  IN `p_tanggal` DATE
)
BEGIN
  DECLARE v_hari VARCHAR(20);

  SET v_hari = CASE DAYNAME(p_tanggal)
    WHEN 'Monday' THEN 'Senin'
    WHEN 'Tuesday' THEN 'Selasa'
    WHEN 'Wednesday' THEN 'Rabu'
    WHEN 'Thursday' THEN 'Kamis'
    WHEN 'Friday' THEN 'Jumat'
    WHEN 'Saturday' THEN 'Sabtu'
    WHEN 'Sunday' THEN 'Minggu'
  END;

  SELECT
    j.id_jadwal,
    mk.kode_matkul,
    mk.nama_matkul,
    mk.sks,
    d.nama_dosen,
    r.nama_ruangan,
    r.lokasi,
    j.hari,
    j.jam_mulai,
    j.jam_selesai,
    j.kelas,
    ta.tahun,
    ta.semester,
    IFNULL(a.status, 'belum_absen') AS status_absensi,
    a.jam_absen,
    a.confidence
  FROM peserta_kuliah pk
  JOIN jadwal_kuliah j ON pk.id_jadwal = j.id_jadwal
  JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
  JOIN dosen d ON j.id_dosen = d.id_dosen
  JOIN ruangan r ON j.id_ruangan = r.id_ruangan
  JOIN tahun_ajaran ta ON j.id_tahun = ta.id_tahun
  LEFT JOIN absensi_wajah a ON a.id_mahasiswa = pk.id_mahasiswa
    AND a.id_jadwal = j.id_jadwal
    AND a.tanggal = p_tanggal
  WHERE pk.id_mahasiswa = p_id_mahasiswa
    AND pk.status_peserta = 'aktif'
    AND j.hari = v_hari
    AND ta.status = 'Aktif'
  ORDER BY j.jam_mulai ASC;
END$$

-- ============================================================================
-- sp_get_rekap_kehadiran_mahasiswa - Get attendance summary for a student
-- ============================================================================
-- Description: Get attendance statistics for a student in a course
-- Parameters:
--   IN p_id_mahasiswa : Student ID
--   IN p_id_jadwal    : Schedule ID (optional, NULL for all courses)
-- Returns: Result set with attendance statistics
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_get_rekap_kehadiran_mahasiswa`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_rekap_kehadiran_mahasiswa` (
  IN `p_id_mahasiswa` INT,
  IN `p_id_jadwal` INT
)
BEGIN
  IF p_id_jadwal IS NULL THEN
    -- Get summary for all courses
    SELECT
      mk.kode_matkul,
      mk.nama_matkul,
      d.nama_dosen,
      COUNT(*) AS total_pertemuan,
      SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS jumlah_hadir,
      SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) AS jumlah_terlambat,
      SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS jumlah_izin,
      SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS jumlah_sakit,
      SUM(CASE WHEN a.status = 'alpa' THEN 1 ELSE 0 END) AS jumlah_alpa,
      ROUND(
        (SUM(CASE WHEN a.status IN ('hadir', 'terlambat') THEN 1 ELSE 0 END) / COUNT(*)) * 100,
        2
      ) AS persentase_kehadiran
    FROM absensi_wajah a
    JOIN jadwal_kuliah j ON a.id_jadwal = j.id_jadwal
    JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
    JOIN dosen d ON j.id_dosen = d.id_dosen
    WHERE a.id_mahasiswa = p_id_mahasiswa
    GROUP BY mk.id_matkul, mk.kode_matkul, mk.nama_matkul, d.nama_dosen
    ORDER BY mk.kode_matkul;
  ELSE
    -- Get detailed attendance for specific course
    SELECT
      a.tanggal,
      a.jam_absen,
      a.status,
      a.confidence,
      a.keterangan,
      j.hari,
      j.jam_mulai,
      j.jam_selesai,
      mk.nama_matkul,
      r.nama_ruangan
    FROM absensi_wajah a
    JOIN jadwal_kuliah j ON a.id_jadwal = j.id_jadwal
    JOIN mata_kuliah mk ON j.id_matkul = mk.id_matkul
    JOIN ruangan r ON j.id_ruangan = r.id_ruangan
    WHERE a.id_mahasiswa = p_id_mahasiswa
      AND a.id_jadwal = p_id_jadwal
    ORDER BY a.tanggal DESC;
  END IF;
END$$

-- ============================================================================
-- sp_update_status_absensi - Update attendance status manually
-- ============================================================================
-- Description: Allows manual correction of attendance status by admin/dosen
-- Parameters:
--   IN  p_id_absensi   : Attendance record ID
--   IN  p_status_baru  : New status
--   IN  p_keterangan   : Reason for change
--   IN  p_diubah_oleh  : User ID who made the change
--   OUT p_status       : Result status
--   OUT p_message      : Result message
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_update_status_absensi`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_status_absensi` (
  IN `p_id_absensi` INT,
  IN `p_status_baru` VARCHAR(20),
  IN `p_keterangan` TEXT,
  IN `p_diubah_oleh` INT,
  OUT `p_status` VARCHAR(20),
  OUT `p_message` VARCHAR(255)
)
BEGIN
  DECLARE v_exists INT DEFAULT 0;
  DECLARE v_status_lama VARCHAR(20);

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_status = 'failed';
    SET p_message = 'Error: Gagal mengubah status absensi';
  END;

  START TRANSACTION;

  -- Check if attendance record exists
  SELECT COUNT(*), MAX(status)
  INTO v_exists, v_status_lama
  FROM absensi_wajah
  WHERE id_absensi = p_id_absensi;

  IF v_exists = 0 THEN
    SET p_status = 'failed';
    SET p_message = 'Error: Data absensi tidak ditemukan';
    ROLLBACK;
  ELSE
    -- Update attendance status
    UPDATE absensi_wajah
    SET status = p_status_baru,
        keterangan = CONCAT(
          IFNULL(keterangan, ''),
          IF(keterangan IS NOT NULL, ' | ', ''),
          'Diubah dari "', v_status_lama, '" ke "', p_status_baru, '" oleh user_id:', p_diubah_oleh,
          ' pada ', NOW(),
          IF(p_keterangan IS NOT NULL, CONCAT('. Alasan: ', p_keterangan), '')
        )
    WHERE id_absensi = p_id_absensi;

    SET p_status = 'success';
    SET p_message = CONCAT('Status berhasil diubah dari "', v_status_lama, '" ke "', p_status_baru, '"');
    COMMIT;
  END IF;
END$$

-- ============================================================================
-- sp_delete_face_data - Remove face registration data
-- ============================================================================
-- Description: Removes face data for a student (for re-registration)
-- Parameters:
--   IN  p_id_mahasiswa : Student ID
--   OUT p_status       : Result status
--   OUT p_message      : Result message
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_delete_face_data`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_face_data` (
  IN `p_id_mahasiswa` INT,
  OUT `p_status` VARCHAR(20),
  OUT `p_message` VARCHAR(255)
)
BEGIN
  DECLARE v_exists INT DEFAULT 0;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_status = 'failed';
    SET p_message = 'Error: Gagal menghapus data wajah';
  END;

  START TRANSACTION;

  -- Check if face data exists
  SELECT COUNT(*) INTO v_exists
  FROM face_data
  WHERE id_mahasiswa = p_id_mahasiswa;

  IF v_exists = 0 THEN
    SET p_status = 'failed';
    SET p_message = 'Error: Data wajah tidak ditemukan';
    ROLLBACK;
  ELSE
    -- Delete face data
    DELETE FROM face_data
    WHERE id_mahasiswa = p_id_mahasiswa;

    -- Update mahasiswa status
    UPDATE mahasiswa
    SET face_registered = 0,
        face_registered_at = NULL
    WHERE id_mahasiswa = p_id_mahasiswa;

    SET p_status = 'success';
    SET p_message = 'Data wajah berhasil dihapus. Mahasiswa dapat mendaftar ulang.';
    COMMIT;
  END IF;
END$$

DELIMITER ;

-- ============================================================================
-- End of Stored Procedures
-- ============================================================================
