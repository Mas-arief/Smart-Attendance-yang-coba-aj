// process_frame.php
$input = json_decode(file_get_contents('php://input'), true);
$imageData = $input['image'];
$jadwalId = $input['jadwal_id'];

// Simpan frame sementara
$tempFile = 'temp/frame_' . time() . '.jpg';
$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
file_put_contents($tempFile, base64_decode($imageData));

// Panggil Python untuk deteksi
$pythonScript = realpath('python/detect_faces.py');
$command = "python $pythonScript $tempFile 2>&1";
$output = shell_exec($command);

// Hapus file temp
unlink($tempFile);

// Parse hasil Python
$result = json_decode($output, true);

if ($result['success'] && !empty($result['recognized'])) {
foreach ($result['recognized'] as $person) {
// Cek apakah sudah absen hari ini
$nim = $person['nim'];
$tanggal = date('Y-m-d');

$check = mysqli_query($conn,
"SELECT * FROM absensi_wajah
WHERE id_mahasiswa = (SELECT id_mahasiswa FROM mahasiswa WHERE nim='$nim')
AND id_jadwal = $jadwalId
AND tanggal = '$tanggal'"
);

if (mysqli_num_rows($check) == 0) {
// Catat absensi baru
$idMhs = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT id_mahasiswa FROM mahasiswa WHERE nim='$nim'"
))['id_mahasiswa'];

$jamAbsen = date('H:i:s');
$confidence = $person['confidence'];

mysqli_query($conn,
"INSERT INTO absensi_wajah
(id_mahasiswa, id_jadwal, tanggal, jam_absen, confidence, status)
VALUES ($idMhs, $jadwalId, '$tanggal', '$jamAbsen', $confidence, 'hadir')"
);

// Log deteksi
mysqli_query($conn,
"INSERT INTO face_detection_log
(id_mahasiswa, confidence, camera_id, detection_status)
VALUES ($idMhs, $confidence, 'CAM_01', 'success')"
);
}
}
}

echo json_encode($result);