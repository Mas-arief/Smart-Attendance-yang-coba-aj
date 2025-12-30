<?php
include '../koneksi.php';

$id_mhs = isset($_GET['id_mhs']) ? intval($_GET['id_mhs']) : 0;
$id_tahun = isset($_GET['id_tahun']) ? intval($_GET['id_tahun']) : 0;
$kode_mk = isset($_GET['kode_mk']) ? mysqli_real_escape_string($conn, $_GET['kode_mk']) : '';

$where = [];
if ($id_mhs) $where[] = "k.id_mahasiswa = $id_mhs";
if ($id_tahun) $where[] = "k.id_tahun = $id_tahun";
if ($kode_mk) $where[] = "k.kode_mk = '$kode_mk'";

$w = $where ? "AND " . implode(" AND ", $where) : "";

$sql = "SELECT k.kode_mk, m.nama_mk, m.jenis, k.minggu, k.status, k.tanggal
        FROM kehadiran k
        LEFT JOIN matakuliah m ON k.kode_mk = m.kode_mk
        WHERE 1 $w
        ORDER BY k.kode_mk, k.minggu ASC";
$res = mysqli_query($conn, $sql);

// header CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_kehadiran.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Kode MK', 'Matakuliah', 'Jenis', 'Minggu', 'Status', 'Tanggal']);

while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, [$row['kode_mk'], $row['nama_mk'], $row['jenis'], $row['minggu'], $row['status'], $row['tanggal']]);
}
fclose($out);
exit;
