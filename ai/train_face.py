#!/usr/bin/env python3
"""
==========================================
SMART ATTENDANCE - FACE TRAINING
Melatih model face recognizer dengan LBPH
==========================================
"""

import cv2
import numpy as np
from PIL import Image
import os
import sys

def train_faces():
    """
    Training model wajah dari dataset
    """
    # Path dataset
    path = 'dataset'

    # Cek apakah folder dataset ada
    if not os.path.exists(path):
        print("ERROR: Folder dataset tidak ditemukan!")
        sys.exit(1)

    # Load face recognizer dan detector
    recognizer = cv2.face.LBPHFaceRecognizer_create()
    detector = cv2.CascadeClassifier(
        cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    )

    if detector.empty():
        print("ERROR: Gagal load haarcascade XML")
        sys.exit(1)

    print("\n[INFO] Memulai training wajah...")

    def get_images_and_labels(path):
        """
        Ambil semua gambar dan label dari dataset
        """
        image_paths = [os.path.join(path, f) for f in os.listdir(path) if f.endswith('.jpg')]
        face_samples = []
        ids = []

        print(f"[INFO] Ditemukan {len(image_paths)} gambar di dataset")

        for image_path in image_paths:
            try:
                # Buka gambar dan convert ke grayscale
                pil_img = Image.open(image_path).convert('L')
                img_numpy = np.array(pil_img, 'uint8')

                # Ambil ID dari nama file (format: User.ID.count.jpg)
                filename = os.path.split(image_path)[-1]
                id = int(filename.split(".")[1])

                # Deteksi wajah
                faces = detector.detectMultiScale(img_numpy)

                # Simpan setiap wajah yang terdeteksi
                for (x, y, w, h) in faces:
                    face_samples.append(img_numpy[y:y+h, x:x+w])
                    ids.append(id)

            except Exception as e:
                print(f"[WARNING] Skip file {image_path}: {str(e)}")
                continue

        return face_samples, ids

    # Ambil semua wajah dan ID
    faces, ids = get_images_and_labels(path)

    if len(faces) == 0:
        print("ERROR: Tidak ada wajah yang valid di dataset!")
        sys.exit(1)

    print(f"[INFO] Total {len(faces)} wajah akan dilatih")

    # Training
    recognizer.train(faces, np.array(ids))

    # Buat folder trainer jika belum ada
    os.makedirs('trainer', exist_ok=True)

    # Simpan model
    recognizer.write('trainer/trainer.yml')

    print(f"\n✅ Training selesai! Model disimpan di trainer/trainer.yml")
    print(f"✅ Total mahasiswa terlatih: {len(set(ids))}")
    print(f"✅ Total sampel wajah: {len(faces)}")


if __name__ == "__main__":
    try:
        train_faces()
        sys.exit(0)
    except Exception as e:
        print(f"\n❌ ERROR: {str(e)}")
        sys.exit(1)
