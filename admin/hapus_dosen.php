<?php
include '../koneksi.php';

$id = $_POST['id_dosen'];

$stmt = $conn->prepare("DELETE FROM dosen WHERE id_dosen = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: datadosen.php");
exit;
