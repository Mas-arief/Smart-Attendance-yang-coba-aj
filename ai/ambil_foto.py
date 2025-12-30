#!/usr/bin/env python3
"""
==========================================
SMART ATTENDANCE - FACE CAPTURE
Menangkap sampel wajah untuk registrasi
==========================================
"""

import cv2
import os
import sys

def capture_face(mahasiswa_id):
    """
    Menangkap 30 sampel wajah dari kamera
    """
    # Inisialisasi kamera
    cam = cv2.VideoCapture(0)
    cam.set(3, 640)  # Width
    cam.set(4, 480)  # Height

    # Load face detector
    face_detector = cv2.CascadeClassifier(
        cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    )

    if face_detector.empty():
        print("ERROR: Gagal load haarcascade XML")
        sys.exit(1)

    # Buat folder dataset jika belum ada
    dataset_path = "dataset"
    os.makedirs(dataset_path, exist_ok=True)

    print(f"\n[INFO] Mulai menangkap wajah untuk Mahasiswa ID: {mahasiswa_id}")
    print("[INFO] Hadapkan wajah ke kamera...")
    print("[INFO] Tekan 'q' untuk berhenti (atau otomatis setelah 30 sampel)\n")

    count = 0
    success_count = 0

    while True:
        ret, img = cam.read()

        if not ret:
            print("ERROR: Gagal membaca dari kamera")
            break

        # Convert ke grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Deteksi wajah
        faces = face_detector.detectMultiScale(
            gray,
            scaleFactor=1.3,
            minNeighbors=5,
            minSize=(100, 100)
        )

        for (x, y, w, h) in faces:
            count += 1

            # Simpan wajah yang terdeteksi
            face_img = gray[y:y+h, x:x+w]
            filename = f"{dataset_path}/User.{mahasiswa_id}.{count}.jpg"

            cv2.imwrite(filename, face_img)
            success_count += 1

            # Gambar kotak di sekitar wajah
            cv2.rectangle(img, (x, y), (x+w, y+h), (0, 255, 0), 2)

            # Tampilkan counter
            cv2.putText(
                img,
                f"Sample: {count}/30",
                (x, y-10),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.8,
                (0, 255, 0),
                2
            )

        # Tampilkan frame
        cv2.imshow('Registrasi Wajah - Tekan Q untuk keluar', img)

        # Wait key
        k = cv2.waitKey(100) & 0xff

        if k == ord('q') or k == 27:  # ESC
            print("\n[INFO] Dibatalkan oleh user")
            break
        elif count >= 30:
            print(f"\n[SUCCESS] Berhasil menangkap {success_count} sampel wajah!")
            break

    # Cleanup
    cam.release()
    cv2.destroyAllWindows()

    return success_count


if __name__ == "__main__":
    # Cek argumen
    if len(sys.argv) < 2:
        print("Usage: python ambil_foto.py <mahasiswa_id>")
        sys.exit(1)

    mahasiswa_id = sys.argv[1]

    try:
        total_samples = capture_face(mahasiswa_id)

        if total_samples >= 30:
            print(f"\n✅ Registrasi wajah berhasil untuk Mahasiswa ID: {mahasiswa_id}")
            sys.exit(0)
        else:
            print(f"\n❌ Hanya berhasil menangkap {total_samples} sampel (minimum 30)")
            sys.exit(1)

    except Exception as e:
        print(f"\n❌ ERROR: {str(e)}")
        sys.exit(1)
