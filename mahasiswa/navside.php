<?php
// Cegah session_start() ganda
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Load koneksi
include "../koneksi.php";

// Pastikan user login dan role mahasiswa
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'mahasiswa') {
  header("Location: ../login.php");
  exit;
}

// Ambil id_user
$id_user = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;

// Ambil data mahasiswa
$mahasiswa = null;
if ($id_user > 0 && isset($conn)) {
  $sql = "SELECT * FROM mahasiswa WHERE id_user = $id_user LIMIT 1";
  $res = mysqli_query($conn, $sql);
  if ($res) {
    $mahasiswa = mysqli_fetch_assoc($res);
  }
}

// Default fallback
if (!$mahasiswa) {
  $mahasiswa = ['nama_mahasiswa' => 'Mahasiswa'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - RKM</title>

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- MDB UI Kit -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.2.0/mdb.min.css" rel="stylesheet">

  <style>
    /* ================= GLOBAL ================= */
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f7f7f7;
      transition: all 0.3s ease;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 230px;
      height: 100vh;
      background-color: #0E2F80;
      color: white;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .sidebar.collapsed {
      width: 70px;
      padding: 20px 10px;
      align-items: center;
    }

    .sidebar-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 25px;
    }

    .sidebar-header img {
      height: 45px;
      margin-right: 10px;
    }

    .sidebar.collapsed .sidebar-title {
      display: none;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
      width: 100%;
    }

    .sidebar-menu li {
      list-style: none;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      color: white;
      text-decoration: none;
      padding: 10px 0;
      font-weight: 600;
      font-size: 14px;
    }

    .sidebar-menu a:hover {
      background: rgba(255, 255, 255, 0.15);
      border-left: 4px solid #fff;
    }

    .sidebar-menu a i {
      margin-right: 10px;
      font-size: 15px;
    }

    .sidebar.collapsed a span {
      display: none;
    }

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      right: 0;
      left: 230px;
      height: 70px;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      transition: 0.3s;
      z-index: 900;
    }

    .navbar.collapsed {
      left: 70px;
    }

    .navbar h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .navbar p {
      margin: 0;
      font-size: 13px;
      color: #666;
    }

    .menu-toggle {
      border: none;
      background: none;
      font-size: 22px;
      cursor: pointer;
      color: #0E2F80;
    }

    /* Navbar Icons */
    .nav-icons {
      display: flex;
      align-items: center;
      gap: 20px;
      position: relative;
    }

    .icon-btn {
      font-size: 20px;
      cursor: pointer;
      color: #0E2F80;
    }

    /* Dropdown */
    .dropdown-menu-custom {
      position: absolute;
      top: 45px;
      right: 0;
      min-width: 200px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      display: none;
      overflow: hidden;
      z-index: 2000;
    }

    .dropdown-menu-custom.show {
      display: block;
    }

    .dropdown-item-custom {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      cursor: pointer;
      text-decoration: none;
      color: #333;
    }

    .dropdown-item-custom:hover {
      background: #f0f0f0;
    }

    .dropdown-item-custom i {
      margin-right: 10px;
      width: 18px;
      text-align: center;
      color: #0E2F80;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../image/poli.png" alt="Logo" style="height:40px;">
      <div class="sidebar-title">
        <h5 style="margin:0; font-size:16px;">RKM</h5>
        <p style="margin:0; font-size:12px;">Rekap Kehadiran Mahasiswa</p>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i><span> Beranda</span></a></li>
      <hr>
      <li><a href="registrasiface.php" class="<?= basename($_SERVER['PHP_SELF']) == 'registrasiface.php' ? 'active' : '' ?>"><i class="fas fa-camera"></i><span> Registrasi Wajah</span></a></li>
      <hr>
      <li><a href="rekapabsen.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rekapabsen.php' ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i><span> Rekap Absen</span></a></li>
    </ul>
  </div>

  <!-- Navbar -->
  <div class="navbar" id="navbar">
    <div style="display:flex; align-items:center; gap:12px;">
      <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button>
      <div>
        <h4>Selamat Datang, <?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></h4>
        <p id="datetime"></p>
      </div>
    </div>

    <div class="nav-icons">
      <i class="fa-solid fa-user icon-btn" id="userIcon"></i>

      <div class="dropdown-menu-custom" id="userDropdown">
        <a href="ganti_password.php" class="dropdown-item-custom"><i class="fas fa-key"></i><span>Ganti Password</span></a>
        <div class="dropdown-item-custom" id="logoutBtn" style="border-top:1px solid #eee;">
          <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </div>
      </div>
    </div>
  </div>

  <!-- SCRIPT -->
  <script>
    // Tanggal & Jam
    function updateDateTime() {
      const now = new Date();
      document.getElementById('datetime').textContent =
        now.toLocaleDateString('id-ID', {
          weekday: 'long',
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        }) + ", " +
        now.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit'
        });
    }
    updateDateTime();
    setInterval(updateDateTime, 60000);

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const navbar = document.getElementById('navbar');
    document.getElementById('menu-toggle').addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      navbar.classList.toggle('collapsed');
    });

    // Dropdown
    const userIcon = document.getElementById('userIcon');
    const userDropdown = document.getElementById('userDropdown');

    userIcon.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', () => {
      userDropdown.classList.remove('show');
    });

    // Logout
    document.getElementById('logoutBtn').addEventListener('click', () => {
      if (confirm("Keluar dari akun mahasiswa?")) {
        window.location.href = "../logout.php";
      }
    });
  </script>

</body>

</html>