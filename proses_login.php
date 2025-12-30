<?php
session_start();
include 'koneksi.php';

// Aktifkan error reporting untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Cek user tanpa filter is_active dulu untuk debug
    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (!$user['is_active']) {
            $_SESSION['error'] = "Akun Anda belum aktif. Hubungi admin.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama_lengkap'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboardadmin.php");
                exit;
            } elseif ($user['role'] === 'dosen') {
                header("Location: dosen/dashboard.php");
                exit;
            } elseif ($user['role'] === 'mahasiswa') {
                header("Location: mahasiswa/dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Role tidak dikenali!";
            }
        } else {
            // Debug info: hash dan input
            $_SESSION['error'] = "Password salah!<br>" .
                "<small>Hash: " . htmlspecialchars($user['password']) . "</small>";
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
    }

    header("Location: login.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}
