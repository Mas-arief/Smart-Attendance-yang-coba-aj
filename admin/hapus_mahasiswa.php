<?php
include '../koneksi.php';

$id = $_POST['id_mahasiswa'];
$stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: datamahasiswa.php");
