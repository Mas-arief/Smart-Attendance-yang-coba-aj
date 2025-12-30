<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik_nim = trim($_POST['nik_nim']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    // ==========================
    // VALIDASI INPUT
    // ==========================
    if (empty($nik_nim) || empty($password) || empty($confirm_password) || empty($role)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Konfirmasi password tidak sesuai.";
        header("Location: register.php");
        exit();
    }

    // ==========================
    // CEK APAKAH USERNAME SUDAH ADA DI USERS
    // ==========================
    $cekUser = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
    $cekUser->bind_param("s", $nik_nim);
    $cekUser->execute();
    $cekUser->store_result();

    if ($cekUser->num_rows > 0) {
        $_SESSION['error'] = "Akun sudah terdaftar dengan username: $nik_nim. Silakan login.";
        header("Location: login.php");
        exit();
    }
    $cekUser->close();

    // ==========================
    // CEK APAKAH DATA DOSEN/MAHASISWA SUDAH ADA DI SISTEM
    // ==========================
    if ($role === "dosen") {
        $cekData = $conn->prepare("SELECT id_dosen, id_user FROM dosen WHERE nik = ?");
    } elseif ($role === "mahasiswa") {
        $cekData = $conn->prepare("SELECT id_mahasiswa, id_user FROM mahasiswa WHERE nim = ?");
    } else {
        $_SESSION['error'] = "Role tidak valid.";
        header("Location: register.php");
        exit();
    }

    $cekData->bind_param("s", $nik_nim);
    $cekData->execute();
    $result = $cekData->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Data $role dengan NIK/NIM tersebut belum dimasukkan oleh admin.";
        header("Location: register.php");
        exit();
    }

    $row = $result->fetch_assoc();
    $cekData->close();

    // Cek apakah sudah punya id_user (sudah pernah daftar)
    if (!empty($row['id_user'])) {
        $_SESSION['error'] = "Akun ini sudah memiliki user. Silakan login.";
        header("Location: login.php");
        exit();
    }

    // ==========================
    // BUAT AKUN BARU
    // ==========================
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $nik_nim, $hashed_password, $role);

    if ($insert->execute()) {
        $id_user_baru = $insert->insert_id;

        // ==========================
        // HUBUNGKAN KE DOSEN/MAHASISWA
        // ==========================
        if ($role === "dosen") {
            $update = $conn->prepare("UPDATE dosen SET id_user = ? WHERE id_dosen = ?");
            $update->bind_param("ii", $id_user_baru, $row['id_dosen']);
        } else {
            $update = $conn->prepare("UPDATE mahasiswa SET id_user = ? WHERE id_mahasiswa = ?");
            $update->bind_param("ii", $id_user_baru, $row['id_mahasiswa']);
        }
        $update->execute();
        $update->close();

        $_SESSION['success'] = "Akun berhasil dibuat! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal membuat akun. Error: " . $insert->error;
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
