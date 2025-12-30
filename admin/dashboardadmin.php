<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

// Hitung total data
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM mahasiswa"))['total'];
$total_dosen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM dosen"))['total'];
$total_ruangan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM ruangan"))['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.css" rel="stylesheet" />

    <style>
        body {
            background: #f5f5f5;
            padding-top: 100px;
            font-family: Arial, sans-serif;
        }

        .stat-card {
            background: #ffffff;
            padding: 20px;
            width: 250px;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            transition: .25s;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.10);
            margin: 10px;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #0d6efd;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>

    <?php include 'navsideadmin.php'; ?>

    <div class="text-center">
        <div class="stat-card" id="cardMahasiswa">
            <i class="fas fa-users stat-icon"></i>
            <p>Total Mahasiswa</p>
            <p class="stat-number"><?= $total_mahasiswa ?></p>
        </div>

        <div class="stat-card" id="cardDosen">
            <i class="fas fa-chalkboard-teacher stat-icon"></i>
            <p>Total Dosen</p>
            <p class="stat-number"><?= $total_dosen ?></p>
        </div>

        <div class="stat-card" id="ruanganCard">
            <i class="fas fa-door-open stat-icon"></i>
            <p>Total Ruangan</p>
            <p class="stat-number" id="totalRuangan"><?= $total_ruangan ?></p>
        </div>
    </div>

    <!-- MODAL RUANGAN -->
    <div class="modal fade" id="ruanganModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-building"></i> Manajemen Ruangan</h5>
                    <button class="btn-close btn-close-white" data-mdb-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <button class="btn btn-success mb-3" id="btnTambahRuangan">
                        <i class="fa fa-plus"></i> Tambah Ruangan
                    </button>

                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th width="10%">No</th>
                                <th width="70%">Nama Ruangan</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dataRuangan"></tbody>
                    </table>

                    <div id="formTambahRuangan" class="mt-4" style="display:none;">
                        <label>Nama Ruangan</label>
                        <input type="text" id="namaRuangan" class="form-control" placeholder="Masukkan Nama Ruangan…">
                        <button class="btn btn-primary mt-2" id="simpanRuangan">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.js"></script>

    <script>
        function loadRuangan() {
            $.get("get_ruangan.php", function(data) {
                $("#dataRuangan").html(data);
            });
        }

        $("#ruanganCard").click(function() {
            $("#ruanganModal").modal("show");
            loadRuangan();
        });

        $("#btnTambahRuangan").click(function() {
            $("#formTambahRuangan").slideToggle();
        });

        $("#simpanRuangan").click(function() {
            let nama = $("#namaRuangan").val().trim();
            if (nama === "") {
                Swal.fire("Oops!", "Nama ruangan wajib diisi!", "warning");
                return;
            }

            $.post("tambah_ruangan.php", {
                nama_ruangan: nama
            }, function(res) {
                if (res === "success") {
                    Swal.fire("Sukses!", "Ruangan ditambahkan!", "success");
                    loadRuangan();
                    updateTotal();
                    $("#namaRuangan").val("");
                    $("#formTambahRuangan").hide();
                }
            });
        });

        function hapusRuangan(id) {
            Swal.fire({
                title: "Yakin Hapus?",
                text: "Data tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post("hapus_ruangan.php", {
                        id_ruangan: id
                    }, function(data) {
                        if (data === "success") {
                            Swal.fire("Berhasil", "Ruangan dihapus!", "success");
                            loadRuangan();
                            updateTotal();
                        }
                    });
                }
            });
        }

        function updateTotal() {
            $.get("get_total_ruangan.php", function(total) {
                $("#totalRuangan").text(total);
            });
        }

        // klik card mahasiswa
        $("#cardMahasiswa").click(function() {
            window.location.href = "datamahasiswa.php";
        });

        // klik card dosen
        $("#cardDosen").click(function() {
            window.location.href = "datadosen.php";
        });
    </script>

</body>

</html>