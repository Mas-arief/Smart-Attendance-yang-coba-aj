<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin - RKM</title>

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- MDB UI Kit -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.2.0/mdb.min.css" rel="stylesheet" />

  <style>
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
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 20px;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      z-index: 999;
      transition: all 0.3s ease;
    }

    .sidebar.collapsed {
      width: 70px;
      align-items: center;
      padding: 20px 10px;
    }

    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      width: 100%;
      margin-bottom: 30px;
    }

    .sidebar-header img {
      height: 45px;
      margin-right: 10px;
      transition: all 0.3s ease;
    }

    .sidebar-title h5 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #ffffff;
    }

    .sidebar-title p {
      margin: 0;
      font-size: 12px;
      color: #dcdcdc;
      letter-spacing: 0.3px;
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
      width: 100%;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      color: #ffffff;
      text-decoration: none;
      padding: 12px 20px;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
      white-space: nowrap;
    }

    .sidebar-menu a:hover {
      background-color: rgba(255, 255, 255, 0.15);
      border-left: 4px solid #ffffff;
    }

    .sidebar-menu a i {
      margin-right: 10px;
      width: 18px;
      text-align: center;
    }

    .sidebar.collapsed a span {
      display: none;
    }

    /* Navbar */
    .navbar {
      background-color: #ffffff;
      position: fixed;
      left: 230px;
      right: 0;
      top: 0;
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      z-index: 900;
      transition: all 0.3s ease;
    }

    .navbar.collapsed {
      left: 70px;
    }

    .menu-toggle {
      background: none;
      border: none;
      font-size: 22px;
      cursor: pointer;
      color: #0E2F80;
    }

    .navbar h4 {
      margin: 0;
      font-weight: 600;
      font-size: 18px;
      color: #000;
    }

    .navbar p {
      font-size: 13px;
      margin: 0;
      color: #666;
    }

    .nav-icons {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .logout-btn {
      background-color: #0E2F80;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: 0.3s;
    }

    .logout-btn:hover {
      background-color: #0b2362;
    }

    /* Responsif */
    @media (max-width: 991px) {
      .sidebar {
        left: -250px;
      }

      .sidebar.active {
        left: 0;
      }

      .navbar {
        left: 0;
      }

      .navbar.collapsed {
        left: 0;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../image/poli.png" alt="Logo Polibatam">
      <div class="sidebar-title">
        <h5>RKM</h5>
        <p>Rekap Kehadiran Mahasiswa</p>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardadmin.php"><i class="fas fa-home"></i><span> Beranda</span></a></li>
      <hr>
      <li><a href="datamahasiswa.php"><i class="fas fa-user-graduate"></i><span> Data Mahasiswa</span></a></li>
      <hr>
      <li><a href="datadosen.php"><i class="fas fa-chalkboard-teacher"></i><span> Data Dosen</span></a></li>
      <hr>
      <li><a href="rekapabsen.php"><i class="fas fa-list-check"></i><span> Rekap Kehadiran</span></a></li>
      <hr>
      <li><a href="aturabsen.php"><i class="fas fa-cog"></i><span> Atur Absensi</span></a></li>
      <hr>
      <li><a href="face_attendance.php"><i class="fas fa-fingerprint"></i><span> Absensi Wajah</span></a></li>
    </ul>
  </div>

  <!-- Navbar -->
  <!-- Navbar -->
  <div class="navbar" id="navbar">
    <div class="d-flex align-items-center gap-3">
      <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button>
      <div>
        <h4>Selamat Datang, Admin</h4>
        <p id="datetime"></p>
      </div>
    </div>

    <div class="nav-icons">
      <button class="logout-btn" id="logoutBtn">
        <i class="fas fa-right-from-bracket me-1"></i> Logout
      </button>
    </div>
  </div>

  <!-- Script -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.2.0/mdb.umd.min.js"></script>
  <script>
    // Update waktu
    function updateDateTime() {
      const now = new Date();
      const options = {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      };
      const date = now.toLocaleDateString('id-ID', options);
      const time = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });
      document.getElementById('datetime').textContent = `${date}, ${time}`;
    }
    updateDateTime();
    setInterval(updateDateTime, 60000);

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const navbar = document.getElementById('navbar');
    const toggleBtn = document.getElementById('menu-toggle');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      navbar.classList.toggle('collapsed');
    });

    // Konfirmasi Logout
    document.getElementById('logoutBtn').addEventListener('click', () => {
      const confirmLogout = confirm('Apakah Anda yakin ingin logout dari akun admin?');
      if (confirmLogout) {
        window.location.href = '../logout.php';
      }
    });
  </script>
</body>

</html>