<?php
session_start();
include '../koneksi.php';

// Ambil form

$nim = $_POST['nim'];
$nama = $_POST['nama_mahasiswa'];
$email = $_POST['email'];
$jurusan = $_POST['jurusan'];
$angkatan = $_POST['angkatan'];
$password = password_hash($nim, PASSWORD_DEFAULT); // Default password: nim


// Cek duplikasi NIM
$cek = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
$cek->bind_param("s", $nim);
$cek->execute();
$cek->store_result();


if ($cek->num_rows > 0) {
    echo "<script>
        alert('Gagal! NIM sudah terdaftar.');
        window.history.back();
    </script>";
    exit;
}
$cek->close();

// Insert ke tabel users dulu
$stmt_user = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'mahasiswa', ?)");
$stmt_user->bind_param("sss", $nim, $password, $email);
$stmt_user->execute();

if ($stmt_user->affected_rows < 1) {
    echo "<script>alert('Gagal insert user!');window.history.back();</script>";
    exit;
}

$id_user = $stmt_user->insert_id;
$stmt_user->close();


// Insert ke tabel mahasiswa dengan id_user
$stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama_mahasiswa, email, jurusan, angkatan, id_user) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $nim, $nama, $email, $jurusan, $angkatan, $id_user);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<script>
        alert('Mahasiswa berhasil ditambahkan!');
        window.location.href = 'datamahasiswa.php';
    </script>";
} else {
    echo "<script>alert('Gagal insert mahasiswa!');window.history.back();</script>";
}

$stmt->close();
$conn->close();
