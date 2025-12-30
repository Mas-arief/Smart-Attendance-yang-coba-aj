# 📘 PANDUAN INSTALASI - SMART ATTENDANCE FACE RECOGNITION

## 🎯 Ringkasan Sistem

Sistem absensi otomatis menggunakan AI Face Recognition dengan arsitektur:

- **Backend:** PHP Native
- **AI Engine:** Python + OpenCV + LBPH Face Recognizer
- **Database:** MySQL
- **Server:** XAMPP/WAMPP

---

## 📋 REQUIREMENTS

### Software yang Dibutuhkan:

1. **XAMPP** (PHP 7.4+ dan MySQL)
   - Download: https://www.apachefriends.org

2. **Python 3.8+**
   - Download: https://www.python.org/downloads/

3. **Library Python:**
   ```bash
   pip install opencv-python
   pip install opencv-contrib-python
   pip install numpy
   pip install Pillow
   pip install requests
   ```

4. **Git** (opsional untuk clone)
   - Download: https://git-scm.com/downloads

---

## 🚀 LANGKAH INSTALASI

### STEP 1: Setup Folder Proyek

1. Clone/copy folder project ke:
   ```
   C:/xampp/htdocs/Smart-Attendance-yang-coba-aj/
   ```

2. Struktur folder harus seperti ini:
   ```
   Smart-Attendance-yang-coba-aj/
   ├── ai/
   │   ├── ambil_foto.py
   │   ├── train_face.py
   │   └── detect_faces.py
   ├── api/
   │   ├── registrasiface.php
   │   ├── face_detection_api.php
   │   └── face_attendance.php
   ├── config/
   │   └── koneksi.php
   ├── database/
   │   └── smart_attendance.sql
   ├── setup/
   │   └── install.php
   ├── dataset/       (akan dibuat otomatis)
   └── trainer/       (akan dibuat otomatis)
   ```

---

### STEP 2: Install Python Libraries

Buka **Command Prompt** atau **Terminal**, lalu jalankan:

```bash
pip install opencv-python opencv-contrib-python numpy Pillow requests
```

**Verifikasi instalasi:**
```bash
python -c "import cv2; print('OpenCV Version:', cv2.__version__)"
```

---

### STEP 3: Setup Database

#### Opsi 1: Instalasi Otomatis (Recommended)

1. Start **Apache** dan **MySQL** di XAMPP
2. Buka browser, akses:
   ```
   http://localhost/Smart-Attendance-yang-coba-aj/setup/install.php
   ```
3. Klik tombol **"Mulai Instalasi Database"**
4. Tunggu hingga selesai

#### Opsi 2: Instalasi Manual

1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Import file: `database/smart_attendance.sql`
3. Database `smart_attendance_db` akan terbuat otomatis

---

### STEP 4: Konfigurasi Koneksi Database

File `config/koneksi.php` sudah dikonfigurasi default:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_attendance_db');
```

**Jika password MySQL berbeda**, edit file `config/koneksi.php`

---

### STEP 5: Update Path Python di PHP

Edit file `api/registrasiface.php` dan `api/face_detection_api.php`:

**Untuk Windows:**
```php
$python_path = 'python';
```

**Untuk Linux/Mac:**
```php
$python_path = 'python3';
```

**Cek path Python:**
```bash
# Windows
where python

# Linux/Mac
which python3
```

---

## 🧪 TESTING SISTEM

### Test 1: Koneksi Database

```bash
http://localhost/Smart-Attendance-yang-coba-aj/config/koneksi.php
```

Tambahkan di akhir file koneksi.php (sementara):
```php
define('DEBUG_MODE', true);
```

Hapus setelah testing!

---

### Test 2: Python + OpenCV

Buat file `test_camera.py`:
```python
import cv2

cam = cv2.VideoCapture(0)
ret, frame = cam.read()

if ret:
    print("✅ Kamera OK!")
else:
    print("❌ Kamera ERROR!")

cam.release()
```

Run:
```bash
python test_camera.py
```

---

### Test 3: Face Recognition Flow

#### A. Registrasi Wajah Mahasiswa

1. Pastikan mahasiswa sudah ada di database:
   ```sql
   SELECT * FROM mahasiswa WHERE mahasiswa_id = 1;
   ```

2. Jalankan registrasi via Postman atau browser:
   ```
   POST: http://localhost/Smart-Attendance-yang-coba-aj/api/registrasiface.php
   Body: mahasiswa_id=1
   ```

3. Atau langsung via Python:
   ```bash
   cd ai/
   python ambil_foto.py 1
   python train_face.py
   ```

#### B. Testing Deteksi Wajah

```bash
cd ai/
python detect_faces.py
```

Hadapkan wajah ke kamera, sistem akan otomatis mengirim absensi ke database.

---

## 🔐 KREDENSIAL LOGIN DEFAULT

Setelah database terinstall:

| Role  | Username | Password   |
|-------|----------|------------|
| Admin | `admin`  | `admin123` |
| Dosen | `dosen1` | `dosen123` |

**⚠️ SEGERA GANTI PASSWORD SETELAH LOGIN PERTAMA!**

---

## 📊 STRUKTUR DATABASE

### Tabel Utama:

1. **mahasiswa** - Data mahasiswa + status registrasi wajah
2. **absensi** - Record kehadiran (termasuk confidence score AI)
3. **mata_kuliah** - Data mata kuliah
4. **sesi_kuliah** - Sesi perkuliahan
5. **admin** - User admin/dosen
6. **system_logs** - Log aktivitas sistem

### View:

- `v_rekap_absensi` - Rekap kehadiran per mahasiswa
- `v_absensi_today` - Absensi hari ini

---

## 🔧 TROUBLESHOOTING

### Error: "No module named 'cv2'"

```bash
pip install opencv-python opencv-contrib-python
```

### Error: "Koneksi database gagal"

- Cek MySQL sudah running di XAMPP
- Cek username/password di `config/koneksi.php`

### Error: "Kamera tidak terdeteksi"

```python
# Test manual
import cv2
cam = cv2.VideoCapture(0)  # Coba 0, 1, atau 2
```

### Error: "trainer.yml not found"

```bash
cd ai/
python train_face.py
```

### Python tidak jalan dari PHP

1. Cek path Python:
   ```bash
   where python  # Windows
   which python3 # Linux
   ```

2. Update di PHP:
   ```php
   $python_path = 'C:/Python39/python.exe';  // Full path
   ```

---

## 📁 FILE PERMISSIONS (Linux/Mac)

```bash
chmod +x ai/ambil_foto.py
chmod +x ai/train_face.py
chmod +x ai/detect_faces.py

chmod 777 dataset/
chmod 777 trainer/
```

---

## 🎯 FLOW SISTEM

### 1️⃣ Registrasi Wajah:

```
Mahasiswa → registrasiface.php → ambil_foto.py → train_face.py → Database
```

### 2️⃣ Absensi:

```
Dosen buka sesi → face_detection_api.php → detect_faces.py
                                               ↓
                                        Face detected
                                               ↓
                                     face_attendance.php
                                               ↓
                                           Database
```

---

## 📈 OPTIMASI

### Tingkatkan Akurasi:

1. **Tambah sampel wajah** (di `ambil_foto.py`):
   ```python
   count >= 50  # Dari 30 ke 50
   ```

2. **Turunkan confidence threshold** (di `detect_faces.py`):
   ```python
   CONFIDENCE_THRESHOLD = 65  # Dari 70 ke 65
   ```

3. **Atau update di database:**
   ```sql
   UPDATE settings
   SET setting_value = '65'
   WHERE setting_key = 'ai_confidence_threshold';
   ```

---

## 📞 BANTUAN

Jika ada masalah:

1. Cek `system_logs` table di database
2. Aktifkan debug mode di `koneksi.php`
3. Cek error di browser console (F12)

---

## ✅ CHECKLIST INSTALASI

- [ ] XAMPP terinstall & running (Apache + MySQL)
- [ ] Python 3.8+ terinstall
- [ ] Library Python terinstall (opencv, numpy, etc)
- [ ] Database `smart_attendance_db` sudah dibuat
- [ ] File `config/koneksi.php` sudah dikonfigurasi
- [ ] Path Python di PHP sudah benar
- [ ] Folder `dataset/` dan `trainer/` exist
- [ ] Kamera laptop/PC berfungsi
- [ ] Test registrasi wajah berhasil
- [ ] Test deteksi wajah berhasil
- [ ] Absensi tersimpan ke database

---

**🎉 Selamat! Sistem Smart Attendance siap digunakan!**

---

*Created: 2025-12-30*
*Version: 1.0*
