<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Mahasiswa belum dipilih"]);
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT nim, nama_mahasiswa, prodi FROM mahasiswa WHERE nim='$id'");
$data = mysqli_fetch_assoc($query);
if ($data) {
    echo json_encode([
        "status" => "success",
        "nim" => $data['nim'],
        "nama" => $data['nama_mahasiswa'],
        "prodi" => $data['prodi']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Data mahasiswa tidak ditemukan"
    ]);
}
