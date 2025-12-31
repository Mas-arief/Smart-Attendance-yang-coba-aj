<?php
// get_mahasiswa.php - Dosen
header('Content-Type: application/json');
include '../koneksi.php';

$nim = isset($_GET['nim']) ? mysqli_real_escape_string($conn, $_GET['nim']) : '';

if (!$nim) {
    echo json_encode(["status" => "error", "message" => "NIM tidak ditemukan"]);
    exit;
}

$query = mysqli_query($conn, "SELECT nim, nama_mahasiswa, prodi, jurusan, kelas FROM mahasiswa WHERE nim='$nim' LIMIT 1");
$data = mysqli_fetch_assoc($query);

if ($data) {
    echo json_encode([
        "status" => "success",
        "nim" => $data['nim'],
        "nama" => $data['nama_mahasiswa'],
        "prodi" => $data['prodi'] ?? $data['jurusan'] ?? '-',
        "kelas" => $data['kelas'] ?? '-'
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Data mahasiswa tidak ditemukan"
    ]);
}
