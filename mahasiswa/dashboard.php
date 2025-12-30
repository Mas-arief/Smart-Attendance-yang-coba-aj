<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - RKM</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6fb;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #0E2F80;
            color: #fff;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h3 {
            margin: 0;
            font-weight: 600;
        }

        header .user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        main {
            padding: 40px 20px;
            max-width: 1000px;
            margin: auto;
            text-align: center;
        }

        main h4 {
            color: #0E2F80;
            font-weight: 700;
            margin-bottom: 30px;
            margin-top: 50px;
        }

        .menu-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }

        .menu-card {
            background: #fff;
            width: 250px;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .menu-card i {
            font-size: 35px;
            color: #0E2F80;
            margin-bottom: 10px;
        }

        .menu-card h5 {
            margin: 8px 0 5px;
            font-weight: 600;
        }

        .menu-card p {
            font-size: 13px;
            color: #777;
        }

        footer {
            text-align: center;
            color: #888;
            font-size: 13px;
            padding: 15px;
            margin-top: 40px;
            border-top: 1px solid #ddd;
        }

        footer span {
            color: #0E2F80;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Sidebar & Navbar -->
    <?php include 'navside.php'; ?>
    <main>
        <h4>Selamat Datang di Sistem RKM</h4>

        <div class="menu-container">
            <div class="menu-card" onclick="location.href='registrasiface.php'">
                <i class="fa-solid fa-camera"></i>
                <h5>Registrasi Wajah</h5>
                <p>Daftarkan wajah Anda untuk absensi otomatis.</p>
            </div>

            <div class="menu-card" onclick="location.href='rekapabsen.php'">
                <i class="fa-solid fa-list-check"></i>
                <h5>Rekap Absensi</h5>
                <p>Lihat riwayat kehadiran Anda.</p>
            </div>
        </div>
    </main>
</body>

</html>