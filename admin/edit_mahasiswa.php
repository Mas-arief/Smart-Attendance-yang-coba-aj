<?php
include '../koneksi.php';

$id = $_POST['id_mahasiswa'];
$nama = $_POST['nama_mahasiswa'];
$email = $_POST['email'];
$jurusan = $_POST['jurusan'];
$angkatan = $_POST['angkatan'];

// Ambil foto lama
$result = $conn->query("SELECT foto FROM mahasiswa WHERE id_mahasiswa = $id");
$row = $result->fetch_assoc();
$fotoLama = $row['foto'];

// Upload foto baru
if (!empty($_FILES['foto']['name'])) {
    $namaFile = time() . "_" . $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    move_uploaded_file($tmp, "../uploads/foto_mahasiswa/" . $namaFile);

    if (!empty($fotoLama) && file_exists("../uploads/foto_mahasiswa/" . $fotoLama)) {
        unlink("../uploads/foto_mahasiswa/" . $fotoLama);
    }

    $fotoBaru = $namaFile;
} else {
    $fotoBaru = $fotoLama;
}

// Update data tanpa mengubah NIM
$stmt = $conn->prepare("
    UPDATE mahasiswa 
    SET nama_mahasiswa=?, email=?, jurusan=?, angkatan=?, foto=? 
    WHERE id_mahasiswa=?
");
$stmt->bind_param("sssssi", $nama, $email, $jurusan, $angkatan, $fotoBaru, $id);
$stmt->execute();

echo "<script>
    alert('Perubahan berhasil disimpan!');
    window.location.href = 'datamahasiswa.php';
</script>";
