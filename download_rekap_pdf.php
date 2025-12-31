<?php
require_once 'koneksi.php';
require_once 'vendor/autoload.php';

session_start();

// Ambil parameter filter
$nim = isset($_GET['nim']) ? mysqli_real_escape_string($conn, $_GET['nim']) : '';
$tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($conn, $_GET['tahun']) : '';
$matkul = isset($_GET['matkul']) ? mysqli_real_escape_string($conn, $_GET['matkul']) : '';

// Validasi minimal nim
if (empty($nim)) {
    die("Parameter NIM diperlukan");
}

// Ambil data mahasiswa
$qMhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim = '$nim' LIMIT 1");
$mhsData = mysqli_fetch_assoc($qMhs);

if (!$mhsData) {
    die("Data mahasiswa tidak ditemukan");
}

$id_mahasiswa = $mhsData['id_mahasiswa'];

// Query data kehadiran - FIXED: menggunakan kolom yang benar
$query = "SELECT mk.kode_mk, mk.nama_mk, mk.jenis, k.minggu, k.status, k.tanggal, k.waktu_absen
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
    die("Query Error: " . mysqli_error($conn));
}

// Build HTML untuk PDF
$html = '
<style>
    body { font-family: Arial, sans-serif; }
    h2 { text-align: center; color: #0E2F80; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
    .info-table { width: 100%; margin-bottom: 20px; }
    .info-table td { padding: 5px 10px; }
    .info-label { font-weight: bold; width: 150px; }
    table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
    table.data th { background: #0E2F80; color: white; padding: 8px 5px; text-align: center; }
    table.data td { border: 1px solid #ddd; padding: 6px 5px; text-align: center; }
    .hadir { background: #d4edda; color: #155724; }
    .izin { background: #fff3cd; color: #856404; }
    .sakit { background: #e2d5f1; color: #5b21b6; }
    .alfa { background: #f8d7da; color: #721c24; }
</style>
';

$html .= '<h2>REKAP KEHADIRAN MAHASISWA</h2>';
$html .= '<p class="subtitle">Sistem Absensi Wajah Otomatis - Polibatam</p>';

// Info Mahasiswa
$html .= '
<table class="info-table">
    <tr>
        <td class="info-label">NIM</td>
        <td>: ' . htmlspecialchars($mhsData['nim']) . '</td>
        <td class="info-label">Jurusan</td>
        <td>: ' . htmlspecialchars($mhsData['jurusan'] ?? '-') . '</td>
    </tr>
    <tr>
        <td class="info-label">Nama</td>
        <td>: ' . htmlspecialchars($mhsData['nama_mahasiswa']) . '</td>
        <td class="info-label">Angkatan</td>
        <td>: ' . htmlspecialchars($mhsData['angkatan'] ?? '-') . '</td>
    </tr>
</table>
';

// Tabel Data
$html .= '<table class="data">';
$html .= '<thead><tr>';
$html .= '<th>Kode MK</th>';
$html .= '<th>Mata Kuliah</th>';
$html .= '<th>Jenis</th>';
$html .= '<th>Minggu</th>';
$html .= '<th>Status</th>';
$html .= '<th>Tanggal</th>';
$html .= '</tr></thead><tbody>';

$rowCount = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $rowCount++;
    $statusClass = strtolower($row['status'] ?? '');

    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['kode_mk'] ?? '-') . '</td>';
    $html .= '<td style="text-align:left;">' . htmlspecialchars($row['nama_mk'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['jenis'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['minggu'] ?? '-') . '</td>';
    $html .= '<td class="' . $statusClass . '">' . htmlspecialchars($row['status'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['tanggal'] ?? '-') . '</td>';
    $html .= '</tr>';
}

if ($rowCount == 0) {
    $html .= '<tr><td colspan="6" style="text-align:center;color:#991b1b;padding:20px;">Tidak ada data kehadiran ditemukan.</td></tr>';
}

$html .= '</tbody></table>';

// Footer
$html .= '<p style="margin-top:20px;font-size:10px;color:#666;text-align:right;">Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>';

// Generate PDF
try {
    $mpdf = new mPDF([
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_left' => 15,
        'margin_right' => 15
    ]);
    $mpdf->SetTitle('Rekap Kehadiran - ' . $mhsData['nama_mahasiswa']);
    $mpdf->WriteHTML($html);
    $mpdf->Output('rekap_kehadiran_' . $nim . '.pdf', 'D');
    exit;
} catch (Exception $e) {
    echo '<div style="color:#991b1b;font-weight:bold;padding:20px;">Gagal generate PDF: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<p>Pastikan library mPDF sudah terinstall dengan benar.</p>';
}
