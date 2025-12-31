<?php
// get_rekap.php - Dosen
header('Content-Type: application/json');
include '../koneksi.php';

$nim = isset($_GET['nim']) ? mysqli_real_escape_string($conn, $_GET['nim']) : '';
$tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($conn, $_GET['tahun']) : '';
$matkul = isset($_GET['matkul']) ? mysqli_real_escape_string($conn, $_GET['matkul']) : '';

if (!$nim) {
    echo json_encode(['courses' => []]);
    exit;
}

// Ambil id_mahasiswa berdasarkan nim
$qMhs = mysqli_query($conn, "SELECT id_mahasiswa FROM mahasiswa WHERE nim = '$nim' LIMIT 1");
$mhs = mysqli_fetch_assoc($qMhs);

if (!$mhs) {
    echo json_encode(['courses' => [], 'error' => 'Mahasiswa tidak ditemukan']);
    exit;
}

$id_mahasiswa = $mhs['id_mahasiswa'];

// Query rekap absensi per matkul - FIXED: menggunakan kode_mk dari kehadiran
$query = "SELECT mk.kode_mk, mk.nama_mk, mk.jenis, k.status, k.minggu
          FROM kehadiran k
          LEFT JOIN matakuliah mk ON k.kode_mk = mk.kode_mk
          WHERE k.id_mahasiswa = '$id_mahasiswa'";

if ($tahun) {
    $query .= " AND k.id_tahun = '$tahun'";
}
if ($matkul) {
    $query .= " AND k.kode_mk = '$matkul'";
}
$query .= " ORDER BY mk.kode_mk, k.minggu ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['courses' => [], 'error' => mysqli_error($conn)]);
    exit;
}

$rekap = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kode = $row['kode_mk'] ?? 'UNKNOWN';
    if (!isset($rekap[$kode])) {
        $rekap[$kode] = [
            'kode' => $kode,
            'matkul' => $row['nama_mk'] ?? '-',
            'jenis' => $row['jenis'] ?? '-',
            'kehadiran' => array_fill(1, 14, '-') // Inisialisasi 14 minggu
        ];
    }
    $minggu = intval($row['minggu'] ?? 0);
    if ($minggu >= 1 && $minggu <= 14) {
        $rekap[$kode]['kehadiran'][$minggu] = $row['status'];
    }
}

// Format untuk frontend
$courses = array_values($rekap);
echo json_encode(['courses' => $courses]);
