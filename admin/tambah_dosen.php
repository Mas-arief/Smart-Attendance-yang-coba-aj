<?php
include '../koneksi.php';

// Pastikan request berasal dari form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data dari form
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $nama_dosen = isset($_POST['nama_dosen']) ? trim($_POST['nama_dosen']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $jurusan = isset($_POST['jurusan']) ? trim($_POST['jurusan']) : '';

    // Validasi data wajib diisi
    if (empty($nik) || empty($nama_dosen) || empty($email) || empty($jurusan)) {
        echo "<script>
            alert('Semua field harus diisi!');
            window.history.back();
        </script>";
        exit;
    }

    // Cek apakah NIK sudah terdaftar
    $cek = $conn->prepare("SELECT id_dosen FROM dosen WHERE nik = ?");
    $cek->bind_param("s", $nik);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        echo "<script>
            alert('NIK sudah terdaftar!');
            window.history.back();
        </script>";
        $cek->close();
        $conn->close();
        exit;
    }
    $cek->close();

    // Insert ke tabel users dulu
    $password = password_hash($nik, PASSWORD_DEFAULT); // Default password: nik
    $stmt_user = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'dosen', ?)");
    $stmt_user->bind_param("sss", $nik, $password, $email);
    $stmt_user->execute();

    if ($stmt_user->affected_rows < 1) {
        echo "<script>alert('Gagal insert user!');window.history.back();</script>";
        exit;
    }

    $id_user = $stmt_user->insert_id;
    $stmt_user->close();

    // Insert ke tabel dosen dengan id_user
    $stmt = $conn->prepare("INSERT INTO dosen (nik, nama_dosen, email, jurusan, id_user) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nik, $nama_dosen, $email, $jurusan, $id_user);

    if ($stmt->execute()) {
        echo "<script>
            alert('Data dosen berhasil ditambahkan!');
            window.location.href = 'datadosen.php';
        </script>";
    } else {
        echo "Terjadi kesalahan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika bukan dari POST, arahkan kembali ke halaman data dosen
    header("Location: datadosen.php");
    exit;
}
