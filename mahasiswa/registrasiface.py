import cv2
import os
import time

# Pastikan folder ada
if not os.path.exists("foto_mahasiswa"):
    os.makedirs("foto_mahasiswa")
    print("✓ Folder foto_mahasiswa dibuat")

# Buka kamera
cap = cv2.VideoCapture(0)

if not cap.isOpened():
    print("❌ Kamera tidak terdeteksi!")
    exit()

print("\n" + "="*50)
print("AMBIL FOTO MAHASISWA (10 FOTO PER MAHASISWA)")
print("="*50)
print("📌 Tekan SPASI untuk ambil 10 foto sekaligus")
print("📌 Tekan ESC untuk keluar\n")

mahasiswa_counter = 1

while True:
    ret, frame = cap.read()
    
    if not ret:
        print("❌ Gagal membaca frame")
        break
    
    # Tampilkan instruksi di layar
    cv2.putText(frame, "SPASI = Ambil 10 Foto | ESC = Keluar", 
                (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
    cv2.putText(frame, f"Mahasiswa ke-{mahasiswa_counter}", 
                (10, 60), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 0), 2)
    
    cv2.imshow('Ambil Foto', frame)
    
    key = cv2.waitKey(1) & 0xFF
    
    # SPASI (32) untuk ambil 10 foto
    if key == 32:
        print(f"\n📸 Mengambil 10 foto untuk Mahasiswa {mahasiswa_counter}...")
        
        foto_berhasil = 0
        
        # Ambil 10 foto
        for i in range(1, 11):
            ret, frame = cap.read()
            
            if not ret:
                print(f"❌ Gagal mengambil foto ke-{i}")
                continue
            
            # Nama file dengan format: mahasiswa1_1.jpg, mahasiswa1_2.jpg, dst
            filename = f"mahasiswa{mahasiswa_counter}_{i}.jpg"
            filepath = os.path.join("foto_mahasiswa", filename)
            
            # Simpan foto
            success = cv2.imwrite(filepath, frame)
            
            if success:
                print(f"  ✓ Foto {i}/10 tersimpan: {filename}")
                foto_berhasil += 1
                
                # Tampilkan feedback visual
                display_frame = frame.copy()
                cv2.putText(display_frame, f"FOTO {i}/10 TERSIMPAN!", 
                           (display_frame.shape[1]//2 - 150, display_frame.shape[0]//2), 
                           cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 3)
                cv2.imshow('Ambil Foto', display_frame)
                cv2.waitKey(200)  # Delay 200ms antar foto
            else:
                print(f"  ❌ Gagal menyimpan foto ke-{i}")
            
            time.sleep(0.1)  # Jeda kecil antar pengambilan
        
        print(f"✅ Selesai! {foto_berhasil}/10 foto tersimpan untuk Mahasiswa {mahasiswa_counter}\n")
        
        # Tampilkan konfirmasi
        ret, frame = cap.read()
        if ret:
            cv2.putText(frame, f"SELESAI! {foto_berhasil} FOTO TERSIMPAN", 
                       (frame.shape[1]//2 - 250, frame.shape[0]//2), 
                       cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 3)
            cv2.imshow('Ambil Foto', frame)
            cv2.waitKey(1500)  # Tahan 1.5 detik
        
        mahasiswa_counter += 1
    
    # ESC (27) untuk keluar
    elif key == 27:
        break

cap.release()
cv2.destroyAllWindows()

print(f"\n✅ Total {mahasiswa_counter-1} mahasiswa dengan {(mahasiswa_counter-1)*10} foto tersimpan")
print(f"📁 Lokasi: foto_mahasiswa/")