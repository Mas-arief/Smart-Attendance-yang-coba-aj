# detect_faces.py
import sys
import face_recognition
import pickle
import json

frame_path = sys.argv[1]

# Load model
with open('face_data.pkl', 'rb') as f:
    known_data = pickle.load(f)

# Load frame
image = face_recognition.load_image_file(frame_path)
face_locations = face_recognition.face_locations(image)
face_encodings = face_recognition.face_encodings(image, face_locations)

recognized = []

for face_encoding in face_encodings:
    for nim, data in known_data.items():
        # Bandingkan dengan semua encoding mahasiswa ini
        matches = face_recognition.compare_faces(
            data['encodings'], 
            face_encoding, 
            tolerance=0.6
        )
        
        if True in matches:
            # Hitung confidence
            face_distances = face_recognition.face_distance(
                data['encodings'], 
                face_encoding
            )
            best_match = min(face_distances)
            confidence = round((1 - best_match) * 100, 2)
            
            recognized.append({
                'nim': nim,
                'name': data['nama'],
                'confidence': confidence
            })
            break

# Return JSON
result = {
    'success': True,
    'detected_count': len(face_locations),
    'recognized': recognized
}

print(json.dumps(result))