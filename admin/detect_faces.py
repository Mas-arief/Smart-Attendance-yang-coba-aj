import cv2
import numpy as np
import os
from datetime import datetime
import pickle

# ========================================
# KONFIGURASI
# ========================================

FOTO_FOLDER = "foto_mahasiswa"
MODEL_FILE = "face_data.pkl"

# Load haarcascade untuk deteksi wajah
face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)

print("="*50)
print("SMART ATTENDANCE SYSTEM")
print("Prototipe Absensi Mahasiswa Berbasis Wajah")
print("="*50)

# ========================================
# FUNGSI EKSTRAK FITUR WAJAH
# ========================================

def extract_face_features(image, face):
    """Ekstrak fitur wajah menggunakan histogram"""
    x, y, w, h = face
    face_roi = image[y:y+h, x:x+w]
    
    # Resize untuk konsistensi
    face_roi = cv2.resize(face_roi, (100, 100))
    
    # Hitung histogram dari grayscale image
    hist = cv2.calcHist([face_roi], [0], None, [256], [0, 256])
    hist = cv2.normalize(hist, hist).flatten()
    
    return hist

def compare_histograms(hist1, hist2):
    """Bandingkan dua histogram menggunakan Bhattacharyya Distance"""
    if hist1 is None or hist2 is None:
        return 1.0
    
    return cv2.compareHist(hist1, hist2, cv2.HISTCMP_BHATTACHARYYA)

# ========================================
# LOAD ATAU TRAINING DATA
# ========================================

print("\n🔄 Memuat data wajah mahasiswa...")

known_histograms = []
known_names = []
known_nims = []

# Cek apakah sudah ada model tersimpan
if os.path.exists(MODEL_FILE):
    print("📂 Loading data dari file tersimpan...")
    with open(MODEL_FILE, 'rb') as f:
        saved_data = pickle.load(f)
        known_histograms = saved_data['histograms']
        known_names = saved_data['names']
        known_nims = saved_data['nims']
    print(f"✓ Berhasil load {len(known_names)} mahasiswa")
else:
    print("🔄 Training model dari folder foto...")
    
    if not os.path.exists(FOTO_FOLDER):
        print(f"❌ Folder '{FOTO_FOLDER}' tidak ditemukan!")
        print("💡 Jalankan program pengambilan foto terlebih dahulu")
        exit()
    
    # Dapatkan daftar mahasiswa dari nama file
    files = os.listdir(FOTO_FOLDER)
    mahasiswa_dict = {}
    
    # Kelompokkan foto berdasarkan mahasiswa
    for filename in files:
        if filename.endswith('.jpg') or filename.endswith('.png'):
            # Format: mahasiswa1_1.jpg, mahasiswa1_2.jpg, dst
            parts = filename.replace('.jpg', '').replace('.png', '').split('_')
            if len(parts) >= 1:
                mahasiswa_id = parts[0]  # mahasiswa1, mahasiswa2, dst
                
                if mahasiswa_id not in mahasiswa_dict:
                    mahasiswa_dict[mahasiswa_id] = []
                mahasiswa_dict[mahasiswa_id].append(filename)
    
    print(f"📊 Ditemukan {len(mahasiswa_dict)} mahasiswa")
    
    # Training untuk setiap mahasiswa
    for mahasiswa_id in sorted(mahasiswa_dict.keys()):
        foto_list = mahasiswa_dict[mahasiswa_id]
        
        # Minta input nama dan NIM
        print(f"\n📸 {mahasiswa_id} ({len(foto_list)} foto)")
        nama = input(f"  Masukkan nama: ").strip()
        nim = input(f"  Masukkan NIM: ").strip()
        
        if not nama or not nim:
            print("  ⚠ Dilewati (nama/NIM kosong)")
            continue
        
        # Ekstrak fitur dari semua foto mahasiswa ini
        histograms_mahasiswa = []
        
        for foto_file in foto_list:
            filepath = os.path.join(FOTO_FOLDER, foto_file)
            
            if os.path.exists(filepath):
                image = cv2.imread(filepath)
                gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
                
                # Deteksi wajah
                faces = face_cascade.detectMultiScale(gray, 1.3, 5)
                
                if len(faces) > 0:
                    face = faces[0]  # Ambil wajah pertama
                    hist = extract_face_features(gray, face)
                    histograms_mahasiswa.append(hist)
        
        if len(histograms_mahasiswa) > 0:
            # Simpan semua histogram dari mahasiswa ini
            known_histograms.extend(histograms_mahasiswa)
            known_names.extend([nama] * len(histograms_mahasiswa))
            known_nims.extend([nim] * len(histograms_mahasiswa))
            print(f"  ✓ {nama} - {len(histograms_mahasiswa)} foto berhasil di-training")
        else:
            print(f"  ✗ Tidak ada wajah terdeteksi")
    
    # Simpan model
    if len(known_names) > 0:
        with open(MODEL_FILE, 'wb') as f:
            pickle.dump({
                'histograms': known_histograms,
                'names': known_names,
                'nims': known_nims
            }, f)
        print(f"\n💾 Model tersimpan: {MODEL_FILE}")
        print(f"📊 Total {len(known_histograms)} fitur wajah tersimpan")

if len(known_names) == 0:
    print("\n❌ TIDAK ADA DATA MAHASISWA!")
    print("💡 Pastikan sudah ada foto di folder 'foto_mahasiswa'")
    exit()

# Hitung jumlah mahasiswa unik
unique_nims = list(set(known_nims))
print(f"\n✅ Sistem siap! {len(unique_nims)} mahasiswa terdaftar")
print("="*50)

# ========================================
# DETEKSI REAL-TIME - MULTI FACE
# ========================================

cap = cv2.VideoCapture(0)

if not cap.isOpened():
    print("❌ Kamera tidak terdeteksi!")
    exit()

# Set resolusi kamera untuk performa lebih baik
cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

absensi_hari_ini = {}

print("\n🎥 Kamera aktif!")
print("="*50)
print("📌 KONTROL:")
print("   Q = Keluar dan simpan absensi")
print("   R = Reset model (training ulang)")
print("   ESC = Keluar tanpa menyimpan")
print("="*50)
print("🔍 Sistem mendeteksi semua wajah dalam frame\n")

frame_counter = 0
CONFIDENCE_THRESHOLD = 0.35  # Threshold untuk pengenalan (lebih rendah = lebih ketat)

while True:
    ret, frame = cap.read()
    
    if not ret:
        print("❌ Gagal mengambil frame")
        break
    
    frame_counter += 1
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    
    # Deteksi wajah (setiap 3 frame untuk performa)
    if frame_counter % 3 == 0:
        faces = face_cascade.detectMultiScale(
            gray, 
            scaleFactor=1.2, 
            minNeighbors=5,
            minSize=(60, 60)
        )
        
        detected_count = len(faces)
        
        # Proses SEMUA wajah yang terdeteksi
        for idx, (x, y, w, h) in enumerate(faces):
            # Ekstrak histogram dari wajah
            current_hist = extract_face_features(gray, (x, y, w, h))
            
            # Bandingkan dengan semua data di database
            best_match_idx = None
            best_distance = float('inf')
            
            for i, known_hist in enumerate(known_histograms):
                distance = compare_histograms(current_hist, known_hist)
                if distance < best_distance:
                    best_distance = distance
                    best_match_idx = i
            
            # Cek apakah match cukup baik
            if best_distance < CONFIDENCE_THRESHOLD:
                name = known_names[best_match_idx]
                nim = known_nims[best_match_idx]
                confidence = (1 - best_distance) * 100
                
                # Catat absensi (hanya sekali per mahasiswa)
                if nim not in absensi_hari_ini:
                    waktu = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    absensi_hari_ini[nim] = {
                        "nama": name,
                        "waktu": waktu
                    }
                    print(f"✓ HADIR: {name} ({nim}) - Confidence: {confidence:.1f}%")
                
                # Draw box hijau untuk mahasiswa teridentifikasi
                cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 255, 0), 2)
                cv2.rectangle(frame, (x, y-40), (x+w, y), (0, 255, 0), cv2.FILLED)
                
                # Tampilkan nama dan NIM
                cv2.putText(frame, name, (x+5, y-22), 
                           cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
                cv2.putText(frame, f"{nim} ({confidence:.0f}%)", (x+5, y-5), 
                           cv2.FONT_HERSHEY_SIMPLEX, 0.4, (255, 255, 255), 1)
            else:
                # Draw box merah untuk wajah tidak dikenali
                cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 0, 255), 2)
                cv2.rectangle(frame, (x, y-30), (x+w, y), (0, 0, 255), cv2.FILLED)
                cv2.putText(frame, "Unknown", (x+5, y-8), 
                           cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
        
        # Tampilkan jumlah wajah terdeteksi
        if detected_count > 0:
            cv2.putText(frame, f"Terdeteksi: {detected_count} wajah", 
                       (frame.shape[1] - 220, 30), 
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 0), 2)
    
    # Info status di layar (kiri atas)
    cv2.rectangle(frame, (0, 0), (250, 50), (0, 0, 0), cv2.FILLED)
    cv2.putText(frame, f"Hadir: {len(absensi_hari_ini)}/{len(unique_nims)}", 
                (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
    
    # Kontrol info (kiri bawah)
    cv2.putText(frame, "Q=Simpan | R=Reset | ESC=Keluar", 
                (10, frame.shape[0] - 10), cv2.FONT_HERSHEY_SIMPLEX, 
                0.5, (255, 255, 255), 1)
     
    cv2.imshow('Smart Attendance System', frame)
    
    key = cv2.waitKey(1) & 0xFF
    
    if key == ord('q'):
        print("\n💾 Menyimpan absensi...")
        break
    elif key == ord('r'):
        confirm = input("\n⚠ Reset model? Training ulang diperlukan (y/n): ")
        if confirm.lower() == 'y':
            if os.path.exists(MODEL_FILE):
                os.remove(MODEL_FILE)
                print("🔄 Model direset! Silakan restart program.")
                cap.release()
                cv2.destroyAllWindows()
                exit()
    elif key == 27:  # ESC
        print("\n⚠ Keluar tanpa menyimpan")
        cap.release()
        cv2.destroyAllWindows()
        exit()

# ========================================
# SIMPAN HASIL ABSENSI
# ========================================

cap.release()
cv2.destroyAllWindows()

print("\n" + "="*50)
print("REKAPITULASI ABSENSI")
print("="*50)

if len(absensi_hari_ini) == 0:
    print("❌ Tidak ada yang hadir")
else:
    for nim, data in sorted(absensi_hari_ini.items()):
        print(f"✓ {data['nama']} ({nim})")
        print(f"  Waktu: {data['waktu']}")

print("="*50)
print(f"Total Hadir: {len(absensi_hari_ini)}/{len(unique_nims)}")

# Simpan ke file TXT
timestamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
filename = f"absensi_{timestamp}.txt"

with open(filename, "w", encoding="utf-8") as f:
    f.write("="*60 + "\n")
    f.write("DAFTAR HADIR MAHASISWA\n")
    f.write("Prototipe Smart Attendance System\n")
    f.write("="*60 + "\n")
    f.write(f"Tanggal: {datetime.now().strftime('%d %B %Y')}\n")
    f.write(f"Waktu: {datetime.now().strftime('%H:%M:%S')}\n\n")
    
    f.write("MAHASISWA HADIR:\n")
    f.write("-"*60 + "\n")
    
    if len(absensi_hari_ini) > 0:
        for nim, data in sorted(absensi_hari_ini.items()):
            f.write(f"✓ {data['nama']}\n")
            f.write(f"  NIM: {nim}\n")
            f.write(f"  Waktu: {data['waktu']}\n")
            f.write("-"*60 + "\n")
    else:
        f.write("(Tidak ada mahasiswa yang hadir)\n")
    
    f.write(f"\nRINGKASAN:\n")
    f.write(f"Total Hadir: {len(absensi_hari_ini)}\n")
    f.write(f"Total Terdaftar: {len(unique_nims)}\n")
    f.write(f"Persentase Kehadiran: {(len(absensi_hari_ini)/len(unique_nims)*100):.1f}%\n")

print(f"\n✅ Absensi tersimpan: {filename}")
print("="*50)