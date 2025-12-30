<?php
session_start();
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../koneksi.php';

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data
$nim = isset($_POST['nim']) ? mysqli_real_escape_string($conn, $_POST['nim']) : '';
$nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
$photo_count = isset($_POST['photo_count']) ? intval($_POST['photo_count']) : 0;

if (empty($nim) || $photo_count < 10) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Buat folder jika belum ada
$upload_dir = "../uploads/foto_mahasiswa_training/{$nim}";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder']);
        exit;
    }
}

// Simpan semua foto
$saved_count = 0;
$filenames = [];

for ($i = 0; $i < $photo_count; $i++) {
    $photo_key = "photo_{$i}";

    if (!isset($_POST[$photo_key])) {
        continue;
    }

    // Decode base64
    $image_data = $_POST[$photo_key];
    $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
    $image_data = str_replace(' ', '+', $image_data);
    $decoded = base64_decode($image_data);

    if ($decoded === false) {
        continue;
    }

    // Nama file
    $filename = "{$nim}_" . ($i + 1) . ".jpg";
    $filepath = "{$upload_dir}/{$filename}";

    // Simpan file
    if (file_put_contents($filepath, $decoded)) {
        $saved_count++;
        $filenames[] = $filename;
    }
}

// Cek hasil
if ($saved_count < 10) {
    echo json_encode([
        'success' => false,
        'message' => "Hanya {$saved_count}/10 foto yang tersimpan"
    ]);
    exit;
}

// Update database - tandai sudah registrasi wajah
$update_query = "UPDATE mahasiswa SET face_registered = 1, face_registered_at = NOW() WHERE nim = '$nim'";
$update_result = mysqli_query($conn, $update_query);

if (!$update_result) {
    echo json_encode([
        'success' => false,
        'message' => 'Foto tersimpan tapi gagal update database: ' . mysqli_error($conn)
    ]);
    exit;
}

// Log aktivitas (opsional)
$log_query = "INSERT INTO face_registration_log (nim, nama, photo_count, registration_date) 
              VALUES ('$nim', '$nama', $saved_count, NOW())";
mysqli_query($conn, $log_query);

// Sukses
echo json_encode([
    'success' => true,
    'message' => 'Registrasi wajah berhasil disimpan!',
    'data' => [
        'nim' => $nim,
        'nama' => $nama,
        'photo_count' => $saved_count,
        'filenames' => $filenames,
        'folder' => $upload_dir
    ]
]);
