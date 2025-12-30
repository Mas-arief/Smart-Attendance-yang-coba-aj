<?php
session_start();
include '../koneksi.php';

// Ambil data dosen
$result = $conn->query("SELECT * FROM dosen ORDER BY id_dosen ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dosen - Admin</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f7f7f7;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            display: flex;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 100px 40px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .content-wrapper {
            background-color: #fff;
            border-radius: 20px;
            width: 90%;
            max-width: 1100px;
            padding: 25px 35px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            font-weight: 600;
            color: #0E2F80;
            margin-bottom: 20px;
        }

        .btn-tambah {
            background-color: #0E2F80;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 3px 5px rgba(14, 47, 128, 0.2);
        }

        .btn-tambah:hover {
            background-color: #173a9b;
        }

        .table th {
            color: #0E2F80;
            background-color: #f2f5ff;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }

        .table td {
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
        }

        .btn-action {
            border: none;
            background: none;
            padding: 4px 8px;
            cursor: pointer;
        }

        .btn-edit {
            color: #ffc107;
        }

        .btn-delete {
            color: #dc3545;
        }

        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            background-color: #0E2F80;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 20px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navsideadmin.php'; ?>

    <div class="main-content">
        <div class="content-wrapper">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="m-0">Daftar Dosen</h3>
                <button class="btn-tambah" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="fa-solid fa-plus"></i> Tambah Dosen
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id_dosen']; ?></td>
                                    <td><?= htmlspecialchars($row['nik']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_dosen']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['jurusan']); ?></td>
                                    <td>
                                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_dosen']; ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form action="hapus_dosen.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_dosen" value="<?= $row['id_dosen']; ?>">
                                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('Hapus data ini?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="editModal<?= $row['id_dosen']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Data Dosen</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form action="edit_dosen.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_dosen" value="<?= $row['id_dosen']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">NIK</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                value="<?= htmlspecialchars($row['nik']); ?>" readonly
                                                                style="background-color:#e9ecef; cursor:not-allowed; font-weight:600;">
                                                            <span class="input-group-text bg-secondary text-white">
                                                                <i class="fa-solid fa-lock"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Lengkap</label>
                                                        <input type="text" name="nama_dosen" class="form-control" value="<?= htmlspecialchars($row['nama_dosen']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Jurusan</label>
                                                        <input type="text" name="jurusan" class="form-control" value="<?= htmlspecialchars($row['jurusan']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data dosen</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="tambah_dosen.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_dosen" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" name="jurusan" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>