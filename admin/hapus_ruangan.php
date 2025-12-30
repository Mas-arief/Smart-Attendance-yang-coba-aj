<?php
require '../koneksi.php';

if (isset($_POST['id_ruangan'])) {
    $id = intval($_POST['id_ruangan']);
    $query = "DELETE FROM ruangan WHERE id_ruangan = $id";
    mysqli_query($conn, $query);

    echo "success";
}
