<?php
include '../koneksi.php';

$id_mhs = $_GET['id_mhs'];

$query = "SELECT k.kode_mk, m.nama_mk, m.jenis, k.minggu, k.status 
          FROM kehadiran k
          JOIN matakuliah m ON k.kode_mk = m.kode_mk
          WHERE k.id_mahasiswa = '$id_mhs'
          ORDER BY k.kode_mk, k.minggu ASC";

$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kode = $row['kode_mk'];
    if (!isset($data[$kode])) {
        $data[$kode] = [
            'kode' => $row['kode_mk'],
            'matkul' => $row['nama_mk'],
            'jenis' => $row['jenis'],
            'kehadiran' => []
        ];
    }
    $data[$kode]['kehadiran'][] = $row['status'];
}

echo json_encode(array_values($data));
