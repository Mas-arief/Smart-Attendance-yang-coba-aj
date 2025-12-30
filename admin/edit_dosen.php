<?php
include '../koneksi.php';

$id = $_POST['id_dosen'];
$nama = $_POST['nama_dosen'];
$email = $_POST['email'];
$jurusan = $_POST['jurusan'];

// Update data tanpa mengubah NIK
$stmt = $conn->prepare("UPDATE dosen SET nama_dosen = ?, email = ?, jurusan = ? WHERE id_dosen = ?");
$stmt->bind_param("sssi", $nama, $email, $jurusan, $id);

if ($stmt->execute()) {
    echo "<script>
        alert('Data dosen berhasil diperbarui!');
        window.location.href = 'datadosen.php';
    </script>";
} else {
    echo "Terjadi kesalahan: " . $stmt->error;
}

$stmt->close();
$conn->close();
