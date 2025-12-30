<?php
/**
 * ==========================================
 * API: SIMPAN ABSENSI DARI AI
 * Dipanggil oleh Python saat wajah terdeteksi
 * ==========================================
 */

require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Ambil data dari Python
$mahasiswa_id = $_POST['mahasiswa_id'] ?? null;
$confidence = $_POST['confidence'] ?? 0;
$sesi_id = $_POST['sesi_id'] ?? null;
$matkul_id = $_POST['matkul_id'] ?? null;
$face_image = $_POST['face_image'] ?? null; // Path gambar wajah saat absen

if (!$mahasiswa_id) {
    jsonError('Mahasiswa ID wajib diisi');
}

// Cek apakah mahasiswa ada
$mahasiswa = getSingle("SELECT * FROM mahasiswa WHERE mahasiswa_id = '$mahasiswa_id'");

if (!$mahasiswa) {
    jsonError('Mahasiswa tidak ditemukan', 404);
}

// Cek apakah mahasiswa sudah registrasi wajah
if (!$mahasiswa['face_registered']) {
    jsonError('Mahasiswa belum melakukan registrasi wajah');
}

// Ambil confidence threshold dari settings
$threshold = getSetting('ai_confidence_threshold') ?? 70;

// Cek apakah confidence mencukupi
if ($confidence < $threshold) {
    logActivity($mahasiswa_id, 'mahasiswa', 'attendance_rejected', "Confidence terlalu rendah: $confidence%");
    jsonError("Wajah tidak cukup jelas (confidence: $confidence%). Minimum: $threshold%");
}

// Data absensi
$tanggal = date('Y-m-d');
$jam = date('H:i:s');

// Cek apakah sudah absen hari ini di sesi yang sama
if ($sesi_id) {
    $cek_absen = getSingle("
        SELECT * FROM absensi
        WHERE mahasiswa_id = '$mahasiswa_id'
        AND sesi_id = '$sesi_id'
    ");

    if ($cek_absen) {
        jsonError('Anda sudah melakukan absensi di sesi ini');
    }
}

// Cek absen di hari yang sama untuk matkul yang sama (tanpa sesi)
if ($matkul_id && !$sesi_id) {
    $cek_absen = getSingle("
        SELECT * FROM absensi
        WHERE mahasiswa_id = '$mahasiswa_id'
        AND matkul_id = '$matkul_id'
        AND tanggal = '$tanggal'
    ");

    if ($cek_absen) {
        jsonError('Anda sudah melakukan absensi untuk mata kuliah ini hari ini');
    }
}

try {
    // Simpan absensi
    $absensi_id = insert('absensi', [
        'mahasiswa_id' => $mahasiswa_id,
        'sesi_id' => $sesi_id ?? 'NULL',
        'matkul_id' => $matkul_id ?? 'NULL',
        'tanggal' => $tanggal,
        'jam' => $jam,
        'status' => 'Hadir',
        'metode_absensi' => 'face_recognition',
        'confidence_score' => $confidence,
        'face_image_path' => $face_image ?? 'NULL',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);

    // Log activity
    logActivity($mahasiswa_id, 'mahasiswa', 'attendance_success', "Absensi berhasil dengan confidence: $confidence%");

    // Success response
    jsonSuccess('Absensi berhasil dicatat!', [
        'absensi_id' => $absensi_id,
        'mahasiswa_id' => $mahasiswa_id,
        'nim' => $mahasiswa['nim'],
        'nama' => $mahasiswa['nama_lengkap'],
        'confidence' => $confidence . '%',
        'waktu' => $tanggal . ' ' . $jam,
        'status' => 'Hadir'
    ]);

} catch (Exception $e) {
    logActivity($mahasiswa_id, 'mahasiswa', 'attendance_error', $e->getMessage());
    jsonError('Gagal menyimpan absensi: ' . $e->getMessage(), 500);
}
?>
