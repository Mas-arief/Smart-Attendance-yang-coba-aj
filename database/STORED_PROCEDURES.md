# Stored Procedures Documentation

## Overview

This document provides detailed information about all stored procedures in the Face Recognition Attendance System. Version 2.0 includes enhanced error handling, validation, and transaction support.

## Table of Contents

1. [sp_register_face](#sp_register_face)
2. [sp_catat_absensi_wajah](#sp_catat_absensi_wajah)
3. [sp_get_jadwal_hari_ini](#sp_get_jadwal_hari_ini)
4. [sp_get_rekap_kehadiran_mahasiswa](#sp_get_rekap_kehadiran_mahasiswa)
5. [sp_update_status_absensi](#sp_update_status_absensi)
6. [sp_delete_face_data](#sp_delete_face_data)

---

## sp_register_face

### Description
Registers or updates face encoding data for a student with comprehensive validation.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_mahasiswa | INT | IN | Student ID |
| p_face_encoding | TEXT | IN | Face encoding data (JSON/serialized) |
| p_model_version | VARCHAR(20) | IN | Model version (e.g., 'v1.0', 'v2.0') |
| p_quality_score | DECIMAL(5,2) | IN | Quality score (0-100) |
| p_status | VARCHAR(20) | OUT | 'success' or 'failed' |
| p_message | VARCHAR(255) | OUT | Result message |

### Features
- ✅ Validates student existence
- ✅ Updates existing face data if already registered
- ✅ Inserts new face data if not registered
- ✅ Updates face registration status in mahasiswa table
- ✅ Transaction support with rollback on error
- ✅ Error handling

### Usage Example

```sql
-- Register face data for student
CALL sp_register_face(
  1,                                    -- id_mahasiswa
  '{"encoding": [0.123, 0.456, ...]}', -- face_encoding
  'v1.0',                              -- model_version
  95.50,                               -- quality_score
  @status,                             -- OUT status
  @message                             -- OUT message
);

SELECT @status AS status, @message AS message;
```

### Return Values

**Success:**
```
status: 'success'
message: 'Data wajah berhasil didaftarkan' OR 'Data wajah berhasil diperbarui'
```

**Failure:**
```
status: 'failed'
message: 'Error: Mahasiswa tidak ditemukan' OR 'Error: Gagal mendaftarkan data wajah'
```

---

## sp_catat_absensi_wajah

### Description
Records student attendance via face recognition with comprehensive validation including schedule validation, enrollment check, time validation, and confidence score verification.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_mahasiswa | INT | IN | Student ID |
| p_id_jadwal | INT | IN | Schedule ID |
| p_tanggal | DATE | IN | Attendance date |
| p_jam_absen | TIME | IN | Attendance time |
| p_confidence | DECIMAL(5,2) | IN | Recognition confidence (0-100) |
| p_camera_id | VARCHAR(50) | IN | Camera identifier |
| p_foto_absen | VARCHAR(255) | IN | Photo filename (optional) |
| p_status | VARCHAR(20) | OUT | Result status |
| p_message | VARCHAR(255) | OUT | Result message |

### Validation Rules

1. **Confidence Score:** Minimum 85% required
2. **Schedule Validation:** Schedule must exist
3. **Day Matching:** Attendance day must match schedule day
4. **Enrollment:** Student must be enrolled in the course
5. **Duplicate Check:** No duplicate attendance for same day
6. **Time Window:** 30 minutes before class start to class end time
7. **Tolerance:** 15 minutes late tolerance

### Attendance Status Logic

- **hadir:** Attendance within 15 minutes of start time
- **terlambat:** Attendance after 15 minutes tolerance

### Features
- ✅ Validates confidence score (min 85%)
- ✅ Checks schedule existence
- ✅ Validates day matches schedule
- ✅ Verifies student enrollment
- ✅ Prevents duplicate attendance
- ✅ Validates time window
- ✅ Determines status (hadir/terlambat)
- ✅ Logs face detection
- ✅ Transaction support

### Usage Example

```sql
-- Record attendance
CALL sp_catat_absensi_wajah(
  1,                          -- id_mahasiswa
  5,                          -- id_jadwal
  '2025-01-15',              -- tanggal
  '08:05:00',                -- jam_absen
  92.50,                     -- confidence
  'CAM-001',                 -- camera_id
  'photo_20250115_080500.jpg', -- foto_absen
  @status,                   -- OUT status
  @message                   -- OUT message
);

SELECT @status AS status, @message AS message;
```

### Return Values

**Success:**
```
status: 'success'
message: 'Absensi berhasil! Status: hadir | Confidence: 92.50%'
```

**Failures:**
```
'Error: Confidence score terlalu rendah (80.00%). Minimum: 85.00%'
'Error: Jadwal kuliah tidak ditemukan'
'Error: Hari tidak sesuai jadwal. Jadwal: Senin, Sekarang: Selasa'
'Error: Mahasiswa tidak terdaftar dalam mata kuliah ini'
'Error: Sudah melakukan absensi untuk jadwal ini'
'Error: Terlalu dini untuk absen. Jadwal mulai: 08:00:00'
'Error: Waktu kuliah sudah selesai. Jadwal selesai: 10:00:00'
```

---

## sp_get_jadwal_hari_ini

### Description
Retrieves all schedules for a student on a specific date with attendance status.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_mahasiswa | INT | IN | Student ID |
| p_tanggal | DATE | IN | Date to check |

### Returns
Result set with columns:
- `id_jadwal` - Schedule ID
- `kode_matkul` - Course code
- `nama_matkul` - Course name
- `sks` - Credit hours
- `nama_dosen` - Lecturer name
- `nama_ruangan` - Room name
- `lokasi` - Room location
- `hari` - Day of week
- `jam_mulai` - Start time
- `jam_selesai` - End time
- `kelas` - Class identifier
- `tahun` - Academic year
- `semester` - Semester (Ganjil/Genap)
- `status_absensi` - Attendance status or 'belum_absen'
- `jam_absen` - Attendance time (if recorded)
- `confidence` - Confidence score (if recorded)

### Usage Example

```sql
-- Get today's schedule for student
CALL sp_get_jadwal_hari_ini(1, CURDATE());
```

### Sample Output

```
+------------+-------------+--------------------+-----+---------------+------------------+
| id_jadwal  | kode_matkul | nama_matkul        | ... | status_absensi| jam_absen |
+------------+-------------+--------------------+-----+---------------+-----------+
| 5          | TIF101      | Pemrograman Dasar  | ... | hadir         | 08:05:00  |
| 8          | TIF102      | Basis Data         | ... | belum_absen   | NULL      |
+------------+-------------+--------------------+-----+---------------+-----------+
```

---

## sp_get_rekap_kehadiran_mahasiswa

### Description
Gets attendance summary statistics for a student. Can provide summary for all courses or detailed records for a specific course.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_mahasiswa | INT | IN | Student ID |
| p_id_jadwal | INT | IN | Schedule ID (NULL for all courses) |

### Returns

#### When p_id_jadwal is NULL (Summary for all courses):

| Column | Description |
|--------|-------------|
| kode_matkul | Course code |
| nama_matkul | Course name |
| nama_dosen | Lecturer name |
| total_pertemuan | Total meetings |
| jumlah_hadir | Number of present |
| jumlah_terlambat | Number of late |
| jumlah_izin | Number of excused |
| jumlah_sakit | Number of sick |
| jumlah_alpa | Number of absent |
| persentase_kehadiran | Attendance percentage |

#### When p_id_jadwal is specified (Detailed records):

| Column | Description |
|--------|-------------|
| tanggal | Date |
| jam_absen | Attendance time |
| status | Status |
| confidence | Confidence score |
| keterangan | Notes |
| hari | Day |
| jam_mulai | Class start time |
| jam_selesai | Class end time |
| nama_matkul | Course name |
| nama_ruangan | Room name |

### Usage Examples

```sql
-- Get summary for all courses
CALL sp_get_rekap_kehadiran_mahasiswa(1, NULL);

-- Get detailed attendance for specific course
CALL sp_get_rekap_kehadiran_mahasiswa(1, 5);
```

### Sample Output (Summary)

```
+-------------+--------------------+---------------+------------------+-------------+
| kode_matkul | nama_matkul        | total_pertemuan | jumlah_hadir   | persentase  |
+-------------+--------------------+-----------------+----------------+-------------+
| TIF101      | Pemrograman Dasar  | 12              | 10             | 91.67       |
| TIF102      | Basis Data         | 10              | 9              | 90.00       |
+-------------+--------------------+-----------------+----------------+-------------+
```

---

## sp_update_status_absensi

### Description
Allows manual correction of attendance status by admin or lecturer with audit trail.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_absensi | INT | IN | Attendance record ID |
| p_status_baru | VARCHAR(20) | IN | New status |
| p_keterangan | TEXT | IN | Reason for change |
| p_diubah_oleh | INT | IN | User ID who made change |
| p_status | VARCHAR(20) | OUT | Result status |
| p_message | VARCHAR(255) | OUT | Result message |

### Valid Status Values
- `hadir` - Present
- `terlambat` - Late
- `izin` - Excused
- `sakit` - Sick
- `alpa` - Absent

### Features
- ✅ Validates attendance record existence
- ✅ Creates audit trail in keterangan field
- ✅ Logs old and new status
- ✅ Logs user ID and timestamp
- ✅ Transaction support

### Usage Example

```sql
-- Update attendance status
CALL sp_update_status_absensi(
  15,                                  -- id_absensi
  'izin',                              -- status_baru
  'Surat izin sakit dari dokter',     -- keterangan
  1,                                   -- diubah_oleh (admin user_id)
  @status,
  @message
);

SELECT @status AS status, @message AS message;
```

### Return Values

**Success:**
```
status: 'success'
message: 'Status berhasil diubah dari "terlambat" ke "izin"'
```

**Failure:**
```
status: 'failed'
message: 'Error: Data absensi tidak ditemukan'
```

### Audit Trail Example

The keterangan field will contain:
```
Diubah dari "terlambat" ke "izin" oleh user_id:1 pada 2025-01-15 10:30:00.
Alasan: Surat izin sakit dari dokter
```

---

## sp_delete_face_data

### Description
Removes face registration data for a student, allowing them to re-register their face.

### Parameters

| Parameter | Type | Direction | Description |
|-----------|------|-----------|-------------|
| p_id_mahasiswa | INT | IN | Student ID |
| p_status | VARCHAR(20) | OUT | Result status |
| p_message | VARCHAR(255) | OUT | Result message |

### Features
- ✅ Validates face data existence
- ✅ Deletes face_data record
- ✅ Updates mahasiswa registration status
- ✅ Transaction support

### Usage Example

```sql
-- Delete face data to allow re-registration
CALL sp_delete_face_data(
  1,          -- id_mahasiswa
  @status,
  @message
);

SELECT @status AS status, @message AS message;
```

### Return Values

**Success:**
```
status: 'success'
message: 'Data wajah berhasil dihapus. Mahasiswa dapat mendaftar ulang.'
```

**Failure:**
```
status: 'failed'
message: 'Error: Data wajah tidak ditemukan'
```

---

## Installation

### Import Stored Procedures

```bash
# Via MySQL CLI
mysql -u root -p sistem_absensi_face < database/stored_procedures.sql

# Or via phpMyAdmin
# 1. Select database 'sistem_absensi_face'
# 2. Go to SQL tab
# 3. Copy and paste content from stored_procedures.sql
# 4. Click 'Go'
```

### Verify Installation

```sql
-- Check installed procedures
SHOW PROCEDURE STATUS WHERE Db = 'sistem_absensi_face';

-- Check specific procedure
SHOW CREATE PROCEDURE sp_catat_absensi_wajah;
```

---

## Error Handling

All stored procedures use:
- **Transaction support:** COMMIT on success, ROLLBACK on error
- **Exit handlers:** Automatic error catching
- **Descriptive messages:** Clear error messages for debugging

### Common Error Messages

| Message | Cause | Solution |
|---------|-------|----------|
| Error: Mahasiswa tidak ditemukan | Invalid student ID | Verify student exists |
| Error: Confidence score terlalu rendah | Low recognition confidence | Improve lighting/angle |
| Error: Jadwal kuliah tidak ditemukan | Invalid schedule ID | Verify schedule exists |
| Error: Sudah melakukan absensi | Duplicate attendance | Check existing records |

---

## Performance Considerations

### Indexes

Ensure these indexes exist for optimal performance:
```sql
-- Already included in schema
CREATE INDEX idx_face_registered ON mahasiswa(face_registered);
CREATE INDEX idx_tanggal ON absensi_wajah(tanggal);
CREATE INDEX idx_detected_at ON face_detection_log(detected_at);
```

### Best Practices

1. **Use prepared statements** when calling from application code
2. **Handle OUT parameters** properly in your application
3. **Log procedure results** for debugging
4. **Monitor long-running queries** using EXPLAIN

---

## PHP Integration Examples

### Example 1: Register Face

```php
<?php
require_once 'koneksi.php';

$id_mahasiswa = 1;
$face_encoding = json_encode(['encoding' => [/* array of floats */]]);
$model_version = 'v1.0';
$quality_score = 95.50;

$stmt = $conn->prepare("CALL sp_register_face(?, ?, ?, ?, @status, @message)");
$stmt->bind_param("issd", $id_mahasiswa, $face_encoding, $model_version, $quality_score);
$stmt->execute();
$stmt->close();

// Get output parameters
$result = $conn->query("SELECT @status AS status, @message AS message");
$row = $result->fetch_assoc();

echo json_encode([
    'status' => $row['status'],
    'message' => $row['message']
]);
?>
```

### Example 2: Record Attendance

```php
<?php
require_once 'koneksi.php';

$id_mahasiswa = 1;
$id_jadwal = 5;
$tanggal = date('Y-m-d');
$jam_absen = date('H:i:s');
$confidence = 92.50;
$camera_id = 'CAM-001';
$foto_absen = 'photo_' . time() . '.jpg';

$stmt = $conn->prepare(
    "CALL sp_catat_absensi_wajah(?, ?, ?, ?, ?, ?, ?, @status, @message)"
);
$stmt->bind_param(
    "iissdss",
    $id_mahasiswa,
    $id_jadwal,
    $tanggal,
    $jam_absen,
    $confidence,
    $camera_id,
    $foto_absen
);
$stmt->execute();
$stmt->close();

// Get output parameters
$result = $conn->query("SELECT @status AS status, @message AS message");
$row = $result->fetch_assoc();

echo json_encode([
    'status' => $row['status'],
    'message' => $row['message']
]);
?>
```

### Example 3: Get Today's Schedule

```php
<?php
require_once 'koneksi.php';

$id_mahasiswa = 1;
$tanggal = date('Y-m-d');

$stmt = $conn->prepare("CALL sp_get_jadwal_hari_ini(?, ?)");
$stmt->bind_param("is", $id_mahasiswa, $tanggal);
$stmt->execute();

$result = $stmt->get_result();
$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

$stmt->close();

echo json_encode([
    'status' => 'success',
    'data' => $schedules
]);
?>
```

---

## Changelog

### Version 2.0 (2025-12-30)
- ✅ Added comprehensive error handling
- ✅ Added transaction support
- ✅ Enhanced validation (confidence, enrollment, time window)
- ✅ Added day matching validation
- ✅ Added new stored procedures (get_jadwal, get_rekap, update_status, delete_face)
- ✅ Improved audit trail
- ✅ Better error messages

### Version 1.0 (2025-12-24)
- Initial stored procedures
- Basic face registration
- Basic attendance recording

---

## Support

For questions or issues:
1. Check error messages carefully
2. Verify all input parameters
3. Check database logs
4. Contact system administrator

---

**Last Updated:** 2025-12-30
**Maintained by:** System Administrator
