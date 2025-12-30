<?php
require_once '../koneksi.php';
require_once '../vendor/autoload.php'; // pastikan composer autoload sudah diinstall

use Mpdf\Mpdf;

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'dosen') {
    header('Location: ../login.php');
    exit;
}

// Ambil parameter filter

$nim = isset($_GET['nim']) ? $_GET['nim'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '2024-1';
$matkul = isset($_GET['matkul']) ? $_GET['matkul'] : '';

// Query data kehadiran
// Ganti m.nama menjadi m.nama_mahasiswa
$query = "SELECT mk.kode_mk, mk.nama_mk, mk.jenis, m.nim, m.nama_mahasiswa, k.status, k.tanggal
          FROM kehadiran k
          JOIN jadwal_ruangan j ON k.id_jadwal = j.id_jadwal
          JOIN matakuliah mk ON j.id_matkul = mk.id_matkul
          JOIN mahasiswa m ON k.id_mahasiswa = m.id_mahasiswa
          WHERE 1=1";
if ($nim) {
    $query .= " AND m.nim = '" . mysqli_real_escape_string($conn, $nim) . "'";
}
if ($tahun) {
    $query .= " AND j.tahun_ajaran = '" . mysqli_real_escape_string($conn, $tahun) . "'";
}
if ($matkul) {
    $query .= " AND mk.kode_mk = '" . mysqli_real_escape_string($conn, $matkul) . "'";
}
$query .= " ORDER BY mk.kode_mk, k.tanggal ASC";
$result = mysqli_query($conn, $query);


$html = '<h2 style="text-align:center;">Rekap Kehadiran Mahasiswa</h2>';
$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:12px;">';
$html .= '<thead style="background:#0E2F80;color:#fff;"><tr>';
$html .= '<th>Kode MK</th><th>Mata Kuliah</th><th>Jenis</th><th>NIM</th><th>Nama</th><th>Status</th><th>Tanggal</th>';
$html .= '</tr></thead><tbody>';
$rowCount = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $rowCount++;
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['kode_mk']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['nama_mk']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['jenis']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['nim']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['nama_mahasiswa']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['status']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['tanggal']) . '</td>';
    $html .= '</tr>';
}
if ($rowCount == 0) {
    $html .= '<tr><td colspan="7" style="text-align:center;color:#991b1b;">Tidak ada data kehadiran ditemukan.</td></tr>';
}
$html .= '</tbody></table>';

try {
    $mpdf = new mpdf([
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_left' => 10,
        'margin_right' => 10
    ]);
    $mpdf->SetTitle('Rekap Kehadiran Mahasiswa');
    $mpdf->WriteHTML($html);
    $mpdf->Output('rekap_kehadiran.pdf', 'D');
    exit;
} catch (Exception $e) {
    echo '<div style="color:#991b1b;font-weight:bold;">Gagal generate PDF: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
