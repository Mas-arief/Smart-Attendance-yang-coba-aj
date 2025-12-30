# train_face.py
import os
import face_recognition
import pickle

DATA_DIR = '../uploads/foto_mahasiswa_training'
OUTPUT_FILE = 'face_data.pkl'

def main():
    all_data = {}
    for nim in os.listdir(DATA_DIR):
        folder = os.path.join(DATA_DIR, nim)
        if not os.path.isdir(folder):
            continue
        encodings = []
        nama = None
        for img_name in os.listdir(folder):
            img_path = os.path.join(folder, img_name)
            image = face_recognition.load_image_file(img_path)
            faces = face_recognition.face_encodings(image)
            if faces:
                encodings.append(faces[0])
        # Optionally, get name from database or filename
        # For now, just use NIM as name
        all_data[nim] = {
            'nama': nim,
            'encodings': encodings
        }
    with open(OUTPUT_FILE, 'wb'):
        pickle.dump(all_data, open(OUTPUT_FILE, 'wb'))
    print(f"Training complete. Saved to {OUTPUT_FILE}")

if __name__ == '__main__':
    main()
