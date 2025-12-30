<?php
require '../koneksi.php';

if (isset($_POST['nama_ruangan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $query = "INSERT INTO ruangan (nama_ruangan) VALUES ('$nama')";
    mysqli_query($conn, $query);

    echo "success";
}
