<?php
require_once '../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';

function respond($data)
{
    echo json_encode($data);
    exit;
}

try {
    switch ($action) {
        case 'kuliah':
            // List semua jadwal kuliah
            $res = [];
            $q = mysqli_query($conn, "SELECT * FROM jadwal_kuliah ORDER BY id_jadwal DESC");
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
                }
            }
            while ($r = mysqli_fetch_assoc($q)) $res[] = $r;
            respond(['success' => true, 'data' => $res]);
            break;

        case 'kuliah_detail':
            // Ambil detail jadwal kuliah by id
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) respond(['success' => false, 'message' => 'ID tidak valid']);
            $q = mysqli_query($conn, "SELECT * FROM jadwal_kuliah WHERE id_jadwal = $id LIMIT 1");
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
                }
            }
            $data = mysqli_fetch_assoc($q);
            if (!$data) respond(['success' => false, 'message' => 'Data tidak ditemukan']);
            respond(['success' => true, 'data' => $data]);
            break;
        case 'ruangan':
            $res = [];
            $q = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
                }
            }
            while ($r = mysqli_fetch_assoc($q)) $res[] = $r;
            respond(['success' => true, 'data' => $res]);
            break;

        case 'list':
            $res = [];
            $sql = "SELECT j.*, r.nama_ruangan
                FROM jadwal_ruangan j
                LEFT JOIN ruangan r ON j.id_ruangan = r.id_ruangan
                ORDER BY j.id_jadwal DESC";
            $q = mysqli_query($conn, $sql);
            if (!$q) respond(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
            while ($r = mysqli_fetch_assoc($q)) $res[] = $r;
            respond(['success' => true, 'data' => $res]);
            break;

        case 'detail':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) respond(['success' => false, 'message' => 'ID tidak valid']);
            $sql = "SELECT j.*, r.nama_ruangan FROM jadwal_ruangan j LEFT JOIN ruangan r ON j.id_ruangan = r.id_ruangan WHERE j.id_jadwal = $id LIMIT 1";
            $q = mysqli_query($conn, $sql);
            if (!$q) respond(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
            $data = mysqli_fetch_assoc($q);
            if (!$data) respond(['success' => false, 'message' => 'Data tidak ditemukan']);
            respond(['success' => true, 'data' => $data]);
            break;

        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) respond(['success' => false, 'message' => 'Payload tidak ditemukan']);

            // Kompatibilitas: terima jam_masuk, jam_keluar, jamMasuk, jamKeluar, jam_mulai, jam_selesai
            $jam_mulai = mysqli_real_escape_string($conn, $input['jam_mulai'] ?? $input['jam_masuk'] ?? $input['jamMasuk'] ?? '');
            $jam_selesai = mysqli_real_escape_string($conn, $input['jam_selesai'] ?? $input['jam_keluar'] ?? $input['jamKeluar'] ?? '');
            $id_ruangan = isset($input['id_ruangan']) ? intval($input['id_ruangan']) : 0;
            $id_dosen = isset($input['id_dosen']) ? intval($input['id_dosen']) : null;
            $hari = mysqli_real_escape_string($conn, $input['hari'] ?? '');

            if (!$id_ruangan || !$jam_mulai || !$jam_selesai) {
                respond(['success' => false, 'message' => 'Error: id_ruangan=' . $id_ruangan . ', jam_mulai=' . $jam_mulai . ', jam_selesai=' . $jam_selesai . '. Input: ' . json_encode($input)]);
            }

            $id_dosen_sql = $id_dosen === null ? 'NULL' : $id_dosen;
            $hari_sql = $hari !== '' ? "'$hari'" : 'NULL';

            $sql = "INSERT INTO jadwal_ruangan (id_dosen, id_ruangan, hari, jam_mulai, jam_selesai)
                    VALUES ($id_dosen_sql, $id_ruangan, $hari_sql, '$jam_mulai', '$jam_selesai')";
            $q = mysqli_query($conn, $sql);
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
                }
            }
            respond(['success' => true, 'message' => 'Jadwal berhasil ditambahkan']);
            break;

        case 'update':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) respond(['success' => false, 'message' => 'ID tidak valid']);
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) respond(['success' => false, 'message' => 'Payload tidak ditemukan']);

            // Kompatibilitas: terima jam_masuk, jam_keluar, jamMasuk, jamKeluar, jam_mulai, jam_selesai
            $jam_mulai = mysqli_real_escape_string($conn, $input['jam_mulai'] ?? $input['jam_masuk'] ?? $input['jamMasuk'] ?? '');
            $jam_selesai = mysqli_real_escape_string($conn, $input['jam_selesai'] ?? $input['jam_keluar'] ?? $input['jamKeluar'] ?? '');
            $id_ruangan = isset($input['id_ruangan']) ? intval($input['id_ruangan']) : 0;
            $id_dosen = isset($input['id_dosen']) ? intval($input['id_dosen']) : null;
            $hari = mysqli_real_escape_string($conn, $input['hari'] ?? '');

            if (!$id_ruangan || !$jam_mulai || !$jam_selesai) {
                respond(['success' => false, 'message' => 'Error: id_ruangan=' . $id_ruangan . ', jam_mulai=' . $jam_mulai . ', jam_selesai=' . $jam_selesai . '. Input: ' . json_encode($input)]);
            }

            $id_dosen_sql = $id_dosen === null ? 'NULL' : $id_dosen;
            $hari_sql = $hari !== '' ? "hari = '$hari'" : '';
            $hari_comma = $hari !== '' ? ',' : '';

            $sql = "UPDATE jadwal_ruangan SET id_dosen = $id_dosen_sql, id_ruangan = $id_ruangan, $hari_sql $hari_comma jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai' WHERE id_jadwal = $id";
            $q = mysqli_query($conn, $sql);
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Gagal update: ' . mysqli_error($conn)]);
                }
            }
            respond(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            break;

        case 'delete':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) respond(['success' => false, 'message' => 'ID tidak valid']);
            $sql = "DELETE FROM jadwal_ruangan WHERE id_jadwal = $id";
            $q = mysqli_query($conn, $sql);
            if (!$q) {
                if (mysqli_errno($conn) == 1146) {
                    respond(['success' => false, 'message' => 'Tabel jadwal_ruangan belum dibuat di database. Silakan jalankan migrasi SQL!']);
                } else {
                    respond(['success' => false, 'message' => 'Gagal hapus: ' . mysqli_error($conn)]);
                }
            }
            respond(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
            break;

        default:
            respond(['success' => false, 'message' => 'Aksi tidak dikenal']);
    }
} catch (Exception $e) {
    respond(['success' => false, 'message' => $e->getMessage()]);
}
