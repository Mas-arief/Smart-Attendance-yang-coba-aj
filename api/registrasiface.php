<?php
/**
 * ==========================================
 * API: REGISTRASI WAJAH MAHASISWA
 * Menghubungkan Python AI dengan Database
 * ==========================================
 */

require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Ambil data
$mahasiswa_id = $_POST['mahasiswa_id'] ?? null;

if (!$mahasiswa_id) {
    jsonError('Mahasiswa ID wajib diisi');
}

// Cek apakah mahasiswa ada di database
$mahasiswa = getSingle("SELECT * FROM mahasiswa WHERE mahasiswa_id = '$mahasiswa_id'");

if (!$mahasiswa) {
    jsonError('Mahasiswa tidak ditemukan', 404);
}

// Cek apakah sudah pernah registrasi wajah
if ($mahasiswa['face_registered']) {
    jsonError('Mahasiswa sudah melakukan registrasi wajah sebelumnya. Gunakan fitur update jika ingin memperbarui.');
}

try {
    // Path Python Script
    $python_path = 'python3'; // atau 'python' sesuai sistem
    $script_capture = realpath('../ai/ambil_foto.py');
    $script_train = realpath('../ai/train_face.py');

    // Step 1: Capture wajah (30 sampel)
    $cmd_capture = escapeshellcmd("$python_path $script_capture $mahasiswa_id");
    $output_capture = shell_exec($cmd_capture . ' 2>&1');

    // Log output
    logActivity($mahasiswa_id, 'mahasiswa', 'face_capture', "Output: $output_capture");

    // Cek apakah capture berhasil
    $dataset_path = "../dataset/User.$mahasiswa_id.*.jpg";
    $files = glob($dataset_path);

    if (count($files) < 30) {
        jsonError('Gagal menangkap wajah. Pastikan wajah terlihat jelas di kamera.', 500);
    }

    // Step 2: Training wajah
    $cmd_train = escapeshellcmd("$python_path $script_train");
    $output_train = shell_exec($cmd_train . ' 2>&1');

    logActivity($mahasiswa_id, 'mahasiswa', 'face_training', "Output: $output_train");

    // Step 3: Update database
    update('mahasiswa', [
        'face_registered' => 1,
        'face_dataset_path' => "dataset/User.$mahasiswa_id",
        'face_registered_at' => date('Y-m-d H:i:s')
    ], "mahasiswa_id = $mahasiswa_id");

    // Success response
    jsonSuccess('Registrasi wajah berhasil!', [
        'mahasiswa_id' => $mahasiswa_id,
        'nama' => $mahasiswa['nama_lengkap'],
        'nim' => $mahasiswa['nim'],
        'total_samples' => count($files),
        'registered_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    logActivity($mahasiswa_id, 'mahasiswa', 'face_registration_error', $e->getMessage());
    jsonError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
