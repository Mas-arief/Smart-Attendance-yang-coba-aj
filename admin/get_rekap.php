<?php
// get_rekap.php
header('Content-Type: application/json');
include '../koneksi.php';

$nim = isset($_GET['nim']) ? mysqli_real_escape_string($conn, $_GET['nim']) : '';
$tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($conn, $_GET['tahun']) : '';
$matkul = isset($_GET['matkul']) ? mysqli_real_escape_string($conn, $_GET['matkul']) : '';

if (!$nim) {
    echo json_encode(['courses' => []]);
    exit;
}

// Query rekap absensi per matkul
$query = "SELECT mk.kode_mk, mk.nama_mk, mk.jenis, k.status
          FROM kehadiran k
          JOIN jadwal_ruangan j ON k.id_jadwal = j.id_jadwal
          JOIN matakuliah mk ON j.id_matkul = mk.id_matkul
          JOIN mahasiswa m ON k.id_mahasiswa = m.id_mahasiswa
          WHERE m.nim = '$nim'";
if ($tahun) {
    $query .= " AND j.tahun_ajaran = '$tahun'";
}
if ($matkul) {
    $query .= " AND mk.kode_mk = '$matkul'";
}
$query .= " ORDER BY mk.kode_mk, k.tanggal ASC";

$result = mysqli_query($conn, $query);

$rekap = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kode = $row['kode_mk'];
    if (!isset($rekap[$kode])) {
        $rekap[$kode] = [
            'kode' => $row['kode_mk'],
            'matkul' => $row['nama_mk'],
            'jenis' => $row['jenis'],
            'kehadiran' => []
        ];
    }
    $rekap[$kode]['kehadiran'][] = $row['status'];
}

// Format untuk frontend
$courses = array_values($rekap);
echo json_encode(['courses' => $courses]);
