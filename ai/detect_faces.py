#!/usr/bin/env python3
"""
==========================================
SMART ATTENDANCE - FACE DETECTION
Deteksi wajah real-time dan kirim ke API
==========================================
"""

import cv2
import requests
import os
import sys
import datetime

# Konfigurasi
API_URL = "http://localhost/Smart-Attendance-yang-coba-aj/api/face_attendance.php"
CONFIDENCE_THRESHOLD = 70  # Minimal confidence untuk absensi
COOLDOWN_TIME = 60  # Cooldown 60 detik setelah absen

# Dictionary untuk tracking cooldown
last_attendance = {}


def send_attendance(mahasiswa_id, confidence, sesi_id=None, matkul_id=None):
    """
    Kirim data absensi ke PHP API
    """
    try:
        data = {
            'mahasiswa_id': mahasiswa_id,
            'confidence': confidence,
            'sesi_id': sesi_id,
            'matkul_id': matkul_id
        }

        response = requests.post(API_URL, data=data, timeout=5)

        if response.status_code == 200:
            result = response.json()
            if result.get('status') == 'success':
                print(f"✅ Absensi berhasil: {result.get('message')}")
                return True
            else:
                print(f"⚠️ {result.get('message')}")
                return False
        else:
            print(f"❌ HTTP Error: {response.status_code}")
            return False

    except requests.exceptions.RequestException as e:
        print(f"❌ Network Error: {str(e)}")
        return False
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False


def detect_faces(sesi_id=None, matkul_id=None):
    """
    Deteksi wajah real-time dari kamera
    """
    # Cek apakah model sudah ada
    if not os.path.exists('trainer/trainer.yml'):
        print("❌ ERROR: Model belum dilatih! Jalankan train_face.py terlebih dahulu.")
        sys.exit(1)

    # Load recognizer dan detector
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.read('trainer/trainer.yml')

    face_cascade = cv2.CascadeClassifier(
        cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    )

    if face_cascade.empty():
        print("❌ ERROR: Gagal load haarcascade XML")
        sys.exit(1)

    # Inisialisasi kamera
    cam = cv2.VideoCapture(0)
    cam.set(3, 640)  # Width
    cam.set(4, 480)  # Height

    print("\n" + "="*50)
    print("🎓 SMART ATTENDANCE - FACE RECOGNITION")
    print("="*50)
    print(f"📅 Tanggal: {datetime.datetime.now().strftime('%Y-%m-%d')}")
    print(f"🕒 Waktu: {datetime.datetime.now().strftime('%H:%M:%S')}")
    if sesi_id:
        print(f"📚 Sesi ID: {sesi_id}")
    if matkul_id:
        print(f"📖 Mata Kuliah ID: {matkul_id}")
    print("="*50)
    print("ℹ️  Tekan 'Q' untuk keluar")
    print("="*50 + "\n")

    # Font untuk teks
    font = cv2.FONT_HERSHEY_SIMPLEX

    while True:
        ret, img = cam.read()

        if not ret:
            print("❌ Gagal membaca dari kamera")
            break

        # Convert ke grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Deteksi wajah
        faces = face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.2,
            minNeighbors=5,
            minSize=(100, 100)
        )

        # Process setiap wajah
        for (x, y, w, h) in faces:
            # Prediksi
            id, confidence = recognizer.predict(gray[y:y+h, x:x+w])

            # Convert confidence ke percentage (lebih rendah = lebih baik)
            confidence_percent = 100 - confidence

            # Cek apakah confidence cukup
            if confidence_percent >= CONFIDENCE_THRESHOLD:
                # Cek cooldown
                current_time = datetime.datetime.now()
                if id in last_attendance:
                    time_diff = (current_time - last_attendance[id]).total_seconds()
                    if time_diff < COOLDOWN_TIME:
                        label = f"ID:{id} - Cooldown"
                        color = (0, 165, 255)  # Orange
                    else:
                        # Kirim absensi
                        if send_attendance(id, round(confidence_percent, 2), sesi_id, matkul_id):
                            last_attendance[id] = current_time
                            label = f"ID:{id} - Hadir!"
                            color = (0, 255, 0)  # Green
                        else:
                            label = f"ID:{id} - Error"
                            color = (0, 0, 255)  # Red
                else:
                    # First time detected
                    if send_attendance(id, round(confidence_percent, 2), sesi_id, matkul_id):
                        last_attendance[id] = current_time
                        label = f"ID:{id} - Hadir!"
                        color = (0, 255, 0)  # Green
                    else:
                        label = f"ID:{id} - Error"
                        color = (0, 0, 255)  # Red

                # Tampilkan confidence
                confidence_text = f"{confidence_percent:.1f}%"

            else:
                label = "Tidak Dikenal"
                confidence_text = f"{confidence_percent:.1f}%"
                color = (0, 0, 255)  # Red

            # Gambar kotak di sekitar wajah
            cv2.rectangle(img, (x, y), (x+w, y+h), color, 2)

            # Tampilkan label dan confidence
            cv2.putText(img, label, (x+5, y-25), font, 0.7, color, 2)
            cv2.putText(img, confidence_text, (x+5, y-5), font, 0.6, color, 2)

        # Tampilkan info di layar
        info_text = f"Deteksi: {len(faces)} wajah | Waktu: {datetime.datetime.now().strftime('%H:%M:%S')}"
        cv2.putText(img, info_text, (10, 30), font, 0.6, (255, 255, 255), 2)

        # Tampilkan frame
        cv2.imshow('Smart Attendance - Tekan Q untuk keluar', img)

        # Wait key
        k = cv2.waitKey(10) & 0xff
        if k == ord('q') or k == 27:  # Q atau ESC
            break

    # Cleanup
    print("\n[INFO] Menutup kamera...")
    cam.release()
    cv2.destroyAllWindows()
    print("✅ Selesai!")


if __name__ == "__main__":
    # Ambil parameter dari command line
    sesi_id = sys.argv[1] if len(sys.argv) > 1 else None
    matkul_id = sys.argv[2] if len(sys.argv) > 2 else None

    try:
        detect_faces(sesi_id, matkul_id)
        sys.exit(0)
    except KeyboardInterrupt:
        print("\n\n[INFO] Dihentikan oleh user (Ctrl+C)")
        sys.exit(0)
    except Exception as e:
        print(f"\n❌ ERROR: {str(e)}")
        sys.exit(1)
