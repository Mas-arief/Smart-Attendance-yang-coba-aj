<?php
include '../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$res = [];
$q = mysqli_query($conn, "SELECT id_mk, kode_mk, nama_mk FROM matakuliah ORDER BY kode_mk ASC");
while ($r = mysqli_fetch_assoc($q)) $res[] = $r;
echo json_encode($res);
