<?php
session_start();
header("Content-Type: application/json");
include "koneksi.php";


// ===============================
// CEK ROLE LOGIN & dapatkan username
// ===============================
if (isset($_SESSION['mahasiswa'])) {
    $username = $_SESSION['mahasiswa']['nim'];
} elseif (isset($_SESSION['dosen'])) {
    $username = $_SESSION['dosen']['nik'];
} else {
    echo json_encode([
        "success" => false,
        "message" => "Akses ditolak. Silakan login."
    ]);
    exit;
}

// ===============================
// AMBIL INPUT FRONTEND
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Password tidak boleh kosong."
    ]);
    exit;
}

$password_baru = password_hash($data['password'], PASSWORD_DEFAULT);

// ===============================
// UPDATE PASSWORD di tabel utama (mahasiswa/dosen) dan users
// ===============================
$query = "UPDATE $table SET password = ? WHERE $fieldId = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $password_baru, $id);
$ok1 = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Update juga di tabel users

$password_baru = password_hash($data['password'], PASSWORD_BCRYPT);

// ===============================
// UPDATE PASSWORD di tabel users saja
// ===============================
$stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
mysqli_stmt_bind_param($stmt, "ss", $password_baru, $username);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    session_destroy(); // paksa logout
    echo json_encode([
        "success" => true,
        "message" => "Password berhasil diperbarui."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui password!"
    ]);
}

if ($ok1 && $ok2) {
    session_destroy(); // paksa logout
    echo json_encode([
        "success" => true,
        "message" => "Password berhasil diperbarui."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui password!"
    ]);
}
