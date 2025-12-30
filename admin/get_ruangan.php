<?php
require_once "../koneksi.php";

$query = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY id_ruangan DESC");

$no = 1;

while ($row = mysqli_fetch_assoc($query)) {
    echo "
        <tr>
            <td>{$no}</td>
            <td>{$row['nama_ruangan']}</td>
            <td>
                <button class='btn btn-danger btn-sm' onclick='hapusRuangan({$row['id_ruangan']})'>
                    <i class='fa fa-trash'></i>
                </button>
            </td>
        </tr>
    ";
    $no++;
}