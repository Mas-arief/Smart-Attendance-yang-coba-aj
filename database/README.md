# Database Setup - Sistem Absensi Face Recognition

## Deskripsi Database

Database `sistem_absensi_face` adalah database untuk sistem absensi berbasis pengenalan wajah (face recognition) yang digunakan untuk mencatat kehadiran mahasiswa di perkuliahan.

## Struktur Database

### 1. Tabel User Management

#### `users`
Tabel utama untuk autentikasi pengguna
- Primary Key: `id_user`
- Roles: admin, dosen, mahasiswa
- Password terenkripsi dengan bcrypt

#### `admin`
Data administrator sistem
- Foreign Key: `id_user` → `users(id_user)`

#### `dosen`
Data dosen/pengajar
- Foreign Key: `id_user` → `users(id_user)`
- Unique: `nik` (Nomor Induk Karyawan)

#### `mahasiswa`
Data mahasiswa
- Foreign Key: `id_user` → `users(id_user)`
- Unique: `nim` (Nomor Induk Mahasiswa)
- Fields: face_registered, face_registered_at untuk tracking registrasi wajah

### 2. Tabel Academic

#### `mata_kuliah`
Master data mata kuliah
- Primary Key: `id_matkul`
- Unique: `kode_matkul`

#### `tahun_ajaran`
Master data tahun ajaran dan semester
- Primary Key: `id_tahun`
- Status: Aktif/Nonaktif

#### `ruangan`
Master data ruangan kuliah
- Primary Key: `id_ruangan`
- Optional: `camera_id` untuk integrasi kamera

#### `jadwal_kuliah`
Jadwal perkuliahan
- Foreign Keys:
  - `id_matkul` → `mata_kuliah(id_matkul)`
  - `id_dosen` → `dosen(id_dosen)`
  - `id_ruangan` → `ruangan(id_ruangan)`
  - `id_tahun` → `tahun_ajaran(id_tahun)`

#### `peserta_kuliah`
Daftar mahasiswa yang mengambil mata kuliah
- Unique constraint: `id_mahasiswa` + `id_jadwal`

### 3. Tabel Face Recognition

#### `face_data`
Data encoding wajah mahasiswa
- Foreign Key: `id_mahasiswa` → `mahasiswa(id_mahasiswa)`
- Fields: face_encoding (TEXT), model_version, quality_score

#### `face_detection_log`
Log deteksi wajah
- Fields: confidence, camera_id, detection_status

### 4. Tabel Attendance

#### `absensi_wajah`
Absensi melalui face recognition
- Unique constraint: `id_mahasiswa` + `id_jadwal` + `tanggal`
- Status: hadir, terlambat, izin, sakit, alpa

#### `kehadiran_manual`
Absensi manual yang diinput oleh dosen/admin
- Field: `diinput_oleh` untuk tracking siapa yang input

### 5. Stored Procedures

#### `sp_register_face`
Mendaftarkan data wajah mahasiswa
```sql
CALL sp_register_face(p_id_mahasiswa, p_face_encoding, p_model_version, p_quality_score);
```

#### `sp_catat_absensi_wajah`
Mencatat absensi melalui face recognition dengan validasi jadwal dan waktu
```sql
CALL sp_catat_absensi_wajah(
  p_id_mahasiswa,
  p_id_jadwal,
  p_tanggal,
  p_jam_absen,
  p_confidence,
  p_camera_id,
  @status,
  @message
);
```

### 6. Views

#### `v_mahasiswa_face_registered`
View daftar mahasiswa yang sudah registrasi wajah

#### `v_rekap_kehadiran`
View rekap kehadiran mahasiswa per mata kuliah dengan persentase

## Instalasi Database

### Metode 1: Import Lengkap (Recommended)

1. Buka phpMyAdmin atau MySQL CLI
2. Buat database baru (jika belum ada):
   ```sql
   CREATE DATABASE sistem_absensi_face CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
3. Import file `sistem_absensi_face.sql`:

   **Via phpMyAdmin:**
   - Pilih database `sistem_absensi_face`
   - Klik tab "Import"
   - Pilih file `sistem_absensi_face.sql`
   - Klik "Go"

   **Via MySQL CLI:**
   ```bash
   mysql -u root -p sistem_absensi_face < database/sistem_absensi_face.sql
   ```

### Metode 2: Manual via Migrations (Legacy)

Jika ingin menggunakan file migration yang ada di folder `migrations/`:

```bash
mysql -u root -p sistem_absensi_face < migrations/001_create_jadwal_ruangan.sql
mysql -u root -p sistem_absensi_face < migrations/002_create_jadwal_ruangan.sql
mysql -u root -p sistem_absensi_face < migrations/003_create_absensi_kehadiran.sql
```

**Catatan:** Metode ini hanya membuat sebagian tabel. Gunakan Metode 1 untuk instalasi lengkap.

## Konfigurasi Koneksi

Edit file `koneksi.php` di root project:

```php
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sistem_absensi_face";

$conn = mysqli_connect($host, $user, $pass, $dbname);
```

## Data Default

Setelah import, database akan memiliki data default:

### User Admin
- **Username:** admin
- **Password:** admin123 (silakan ubah setelah login pertama)
- **Email:** admin@kampus.ac.id

### User Mahasiswa
- **Username:** 3312411080 (NIM)
- **Password:** mahasiswa123
- **Nama:** Arief Utama

### User Dosen
- **Username:** 2345678091 (NIK)
- **Password:** dosen123
- **Nama:** Diky Pratama

## Fitur Database

### 1. Auto-increment IDs
Semua tabel menggunakan auto-increment untuk primary key

### 2. Cascade Delete
Foreign key menggunakan CASCADE untuk:
- Hapus user → hapus data terkait (admin/dosen/mahasiswa)
- Hapus mahasiswa → hapus face_data dan absensi
- Hapus jadwal → hapus absensi terkait

### 3. Indexing
Database sudah dilengkapi index untuk:
- Foreign keys
- Kolom pencarian (nim, nik, tanggal)
- Kolom filter (status, role, hari)

### 4. Data Integrity
- Unique constraints untuk mencegah duplikasi
- ENUM untuk status yang terbatas
- NOT NULL untuk field wajib

## Maintenance

### Backup Database
```bash
mysqldump -u root -p sistem_absensi_face > backup_$(date +%Y%m%d).sql
```

### Reset Database
```bash
mysql -u root -p -e "DROP DATABASE IF EXISTS sistem_absensi_face;"
mysql -u root -p -e "CREATE DATABASE sistem_absensi_face CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root -p sistem_absensi_face < database/sistem_absensi_face.sql
```

## Troubleshooting

### Error: Table already exists
Hapus database terlebih dahulu kemudian import ulang:
```sql
DROP DATABASE sistem_absensi_face;
CREATE DATABASE sistem_absensi_face CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### Error: DEFINER issue
Jika mengalami error DEFINER pada stored procedure, edit file SQL dan ganti:
```sql
DEFINER=`root`@`localhost`
```
dengan user MySQL Anda.

### Error: Cannot add foreign key constraint
Pastikan:
1. Parent table sudah dibuat
2. Data type dan size kolom sama persis
3. Engine table adalah InnoDB

## Schema Diagram

```
users
  ├─ admin
  ├─ dosen ──┐
  └─ mahasiswa ──┐
                  │
                  ├─ face_data
                  └─ absensi_wajah ─── jadwal_kuliah ─── mata_kuliah
                                                      ├─ ruangan
                                                      ├─ tahun_ajaran
                                                      └─ peserta_kuliah
```

## Changelog

### Version 1.0 (2025-12-24)
- Initial database schema
- 15 tables with complete relationships
- 2 stored procedures for face registration and attendance
- 2 views for reporting
- Sample data for testing

## Support

Untuk pertanyaan atau bantuan, hubungi administrator sistem.
