<?php
include '../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$id_mhs = isset($_GET['id_mhs']) ? intval($_GET['id_mhs']) : 0;
$id_tahun = isset($_GET['id_tahun']) ? intval($_GET['id_tahun']) : 0;
$kode_mk = isset($_GET['kode_mk']) ? mysqli_real_escape_string($conn, $_GET['kode_mk']) : '';

$where = [];
if ($id_mhs) $where[] = "k.id_mahasiswa = $id_mhs";
if ($id_tahun) $where[] = "k.id_tahun = $id_tahun";
if ($kode_mk) $where[] = "k.kode_mk = '$kode_mk'";

$w = $where ? "AND " . implode(" AND ", $where) : "";

$sql = "SELECT k.kode_mk, m.nama_mk, m.jenis, k.minggu, k.status
        FROM kehadiran k
        LEFT JOIN matakuliah m ON k.kode_mk = m.kode_mk
        WHERE 1 $w
        ORDER BY k.kode_mk, k.minggu ASC";
$res = mysqli_query($conn, $sql);

$data = [];
while ($r = mysqli_fetch_assoc($res)) {
    $kode = $r['kode_mk'];
    if (!isset($data[$kode])) {
        // inisialisasi 14 minggu sebagai '-' default
        $data[$kode] = [
            'kode' => $kode,
            'matkul' => $r['nama_mk'],
            'jenis' => $r['jenis'],
            'kehadiran' => array_fill(1, 14, '-') // minggu 1..14
        ];
    }
    $m = intval($r['minggu']);
    if ($m >= 1 && $m <= 14) $data[$kode]['kehadiran'][$m] = $r['status'];
}

// kembalikan sebagai array ter-index
$out = array_values($data);
echo json_encode($out);
