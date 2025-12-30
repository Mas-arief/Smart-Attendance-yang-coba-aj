<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../koneksi.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['attendance']) || empty($input['attendance'])) {
    echo json_encode(['success' => false, 'message' => 'No attendance data']);
    exit;
}

$attendance = $input['attendance'];
$date = $input['date'] ?? date('Y-m-d');
$session_time = $input['time'] ?? date('H:i:s');

$saved_count = 0;
$errors = [];

// Ambil id_jadwal aktif hari ini (sesuaikan dengan logic sistem Anda)
// Untuk sementara, kita anggap ada jadwal dengan id tertentu
$hari = date('l'); // Monday, Tuesday, etc
$hari_indo = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
$hari_sekarang = $hari_indo[$hari];

// Ambil jadwal hari ini
$jadwal_query = "SELECT id_jadwal FROM jadwal_ruangan 
                 WHERE hari = '$hari_sekarang' 
                 AND '$session_time' BETWEEN jam_mulai AND jam_selesai 
                 LIMIT 1";
$jadwal_result = mysqli_query($conn, $jadwal_query);
$jadwal = mysqli_fetch_assoc($jadwal_result);

if (!$jadwal) {
    // Jika tidak ada jadwal, buat entry generic
    $id_jadwal = null;
} else {
    $id_jadwal = $jadwal['id_jadwal'];
}

// Loop setiap mahasiswa yang hadir
foreach ($attendance as $nim => $data) {
    $nama = mysqli_real_escape_string($conn, $data['name']);
    $confidence = floatval($data['confidence']);
    $time = mysqli_real_escape_string($conn, $data['time']);

    // Ambil id_mahasiswa
    $mhs_query = "SELECT id_mahasiswa FROM mahasiswa WHERE nim = '$nim' LIMIT 1";
    $mhs_result = mysqli_query($conn, $mhs_query);
    $mhs = mysqli_fetch_assoc($mhs_result);

    if (!$mhs) {
        $errors[] = "Mahasiswa $nim tidak ditemukan";
        continue;
    }

    $id_mahasiswa = $mhs['id_mahasiswa'];

    // Cek apakah sudah absen hari ini
    $check_query = "SELECT id_kehadiran FROM kehadiran 
                    WHERE id_mahasiswa = $id_mahasiswa 
                    AND DATE(waktu_absen) = '$date'";

    if ($id_jadwal) {
        $check_query .= " AND id_jadwal = $id_jadwal";
    }

    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "$nama ($nim) sudah tercatat hadir hari ini";
        continue;
    }

    // Insert kehadiran
    $id_jadwal_sql = $id_jadwal ? $id_jadwal : 'NULL';

    $insert_query = "INSERT INTO kehadiran 
                     (id_jadwal, id_mahasiswa, tanggal, status, keterangan, waktu_absen, confidence_score) 
                     VALUES 
                     ($id_jadwal_sql, $id_mahasiswa, '$date', 'Hadir', 'Face Recognition', '$date $time', $confidence)";

    if (mysqli_query($conn, $insert_query)) {
        $saved_count++;
    } else {
        $errors[] = "Gagal simpan $nama: " . mysqli_error($conn);
    }
}

// Buat file report
$report_dir = '../reports/attendance';
if (!file_exists($report_dir)) {
    mkdir($report_dir, 0755, true);
}

$report_file = $report_dir . '/absensi_' . date('Y-m-d_H-i-s') . '.txt';
$report_content = "=" . str_repeat("=", 59) . "\n";
$report_content .= "DAFTAR HADIR MAHASISWA\n";
$report_content .= "Sistem Absensi Wajah Otomatis\n";
$report_content .= "=" . str_repeat("=", 59) . "\n";
$report_content .= "Tanggal: " . date('d F Y') . "\n";
$report_content .= "Waktu Sesi: $session_time\n\n";
$report_content .= "MAHASISWA HADIR:\n";
$report_content .= str_repeat("-", 60) . "\n";

foreach ($attendance as $nim => $data) {
    $report_content .= "✓ {$data['name']}\n";
    $report_content .= "  NIM: $nim\n";
    $report_content .= "  Waktu: {$data['time']}\n";
    $report_content .= "  Confidence: {$data['confidence']}%\n";
    $report_content .= str_repeat("-", 60) . "\n";
}

$report_content .= "\nRINGKASAN:\n";
$report_content .= "Total Hadir: $saved_count\n";
$report_content .= "Persentase Kehadiran: " . number_format(($saved_count / max(1, count($attendance))) * 100, 1) . "%\n";

file_put_contents($report_file, $report_content);

// Response
echo json_encode([
    'success' => true,
    'saved_count' => $saved_count,
    'errors' => $errors,
    'report_file' => basename($report_file),
    'message' => "Absensi berhasil disimpan untuk $saved_count mahasiswa"
]);
