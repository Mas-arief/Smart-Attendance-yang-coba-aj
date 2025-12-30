<?php
/**
 * ==========================================
 * API: FACE DETECTION (START CAMERA)
 * Jalankan script Python untuk deteksi wajah real-time
 * ==========================================
 */

require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Ambil parameter
$sesi_id = $_POST['sesi_id'] ?? null;
$matkul_id = $_POST['matkul_id'] ?? null;

if (!$sesi_id && !$matkul_id) {
    jsonError('Sesi ID atau Mata Kuliah ID wajib diisi');
}

// Cek apakah sesi kuliah aktif
if ($sesi_id) {
    $sesi = getSingle("SELECT * FROM sesi_kuliah WHERE sesi_id = '$sesi_id'");

    if (!$sesi) {
        jsonError('Sesi kuliah tidak ditemukan', 404);
    }

    if (!$sesi['sesi_aktif']) {
        jsonError('Sesi kuliah belum dibuka');
    }

    $matkul_id = $sesi['matkul_id'];
}

try {
    // Path Python Script
    $python_path = 'python3'; // atau 'python'
    $script_detect = realpath('../ai/detect_faces.py');

    // Kirim parameter ke Python script
    $cmd = escapeshellcmd("$python_path $script_detect $sesi_id $matkul_id");

    // Jalankan di background (non-blocking)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        // Linux / Mac
        exec($cmd . " > /dev/null 2>&1 &");
    }

    // Log activity
    logActivity(getCurrentUser() ?? 0, 'admin', 'face_detection_started', "Sesi: $sesi_id, Matkul: $matkul_id");

    // Success response
    jsonSuccess('Face detection started', [
        'sesi_id' => $sesi_id,
        'matkul_id' => $matkul_id,
        'status' => 'running',
        'message' => 'Kamera deteksi wajah sedang berjalan. Mahasiswa dapat melakukan absensi.'
    ]);

} catch (Exception $e) {
    logActivity(getCurrentUser() ?? 0, 'admin', 'face_detection_error', $e->getMessage());
    jsonError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
