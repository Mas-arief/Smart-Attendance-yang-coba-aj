<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrasi | Polibatam</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: linear-gradient(135deg, #a3d8ff, #c2e9fb);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      position: relative;
    }

    /* Tombol toggle */
    .toggle-mode {
      position: fixed;
      top: 20px;
      right: 25px;
      background: #ffffff;
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      cursor: pointer;
      font-size: 20px;
      color: #f5c542;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: 0.3s;
    }

    .toggle-mode:hover {
      transform: scale(1.1);
    }

    /* Container card */
    .container {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      width: 450px;
      padding: 20px 35px;
      text-align: center;
    }

    .container img {
      width: 80px;
      margin-bottom: 5px;
    }

    h2 {
      color: #2e5aac;
      font-weight: 600;
      margin-bottom: 15px;
      font-size: 1.5rem;
    }

    .form-group {
      text-align: left;
      margin-bottom: 10px;
      position: relative;
    }

    label {
      font-size: 0.85rem;
      color: #444;
      display: block;
      margin-bottom: 4px;
    }

    input,
    select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #cfd8dc;
      border-radius: 4px;
      font-size: 0.9rem;
      outline: none;
      background-color: #f9fbff;
      transition: all 0.3s ease;
      height: 40px;
    }

    input:focus,
    select:focus {
      border-color: #3b77ff;
      box-shadow: 0 0 0 3px rgba(59, 119, 255, 0.15);
      background-color: #ffffff;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 30px;
      cursor: pointer;
      color: #3b77ff;
      font-size: 16px;
    }

    .btn-register {
      width: 100%;
      background: linear-gradient(90deg, #69a8ff, #3b77ff);
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      margin-top: 5px;
      transition: 0.3s;
    }

    .btn-register:hover {
      background: linear-gradient(90deg, #3b77ff, #69a8ff);
    }

    /* Gaya untuk pesan notifikasi */
    .alert {
      border-radius: 4px;
      padding: 8px;
      font-size: 0.85rem;
      margin-bottom: 10px;
      border: 1px solid transparent;
    }

    /* Gaya untuk pesan error */
    .alert-error {
      background-color: #ffe3e3;
      color: #c0392b;
      border-color: #e0a6a6;
    }

    /* Gaya untuk pesan sukses */
    .alert-success {
      background-color: #e3ffe3;
      color: #2b9e3a;
      border-color: #a6e0a6;
    }

    p {
      font-size: 0.8rem;
      color: #555;
      margin-top: 10px;
    }

    a {
      color: #3b77ff;
      text-decoration: none;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
    }

    footer {
      margin-top: 15px;
      font-size: 0.75rem;
      color: #777;
    }

    /* DARK MODE */
    body.dark {
      background: linear-gradient(135deg, #1b1d3b, #243163);
    }

    body.dark .container {
      background: #222b50;
      color: #eaeaea;
    }

    body.dark h2 {
      color: #a7c7ff;
    }

    body.dark label {
      color: #ccc;
    }

    body.dark input,
    body.dark select {
      background-color: #303b66;
      border: 1px solid #47538a;
      color: #fff;
    }

    body.dark .btn-register {
      background: linear-gradient(90deg, #5285f4, #2c53ff);
    }

    body.dark .toggle-password {
      color: #a7c7ff;
    }

    /* Dark mode alert styles */
    body.dark .alert-error {
      background-color: #4a1e1e;
      color: #ffbaba;
      border-color: #6a3434;
    }

    body.dark .alert-success {
      background-color: #1e4a1e;
      color: #baffba;
      border-color: #346a34;
    }
  </style>
</head>

<body>
  <button class="toggle-mode" id="toggleMode" title="Ganti Mode">🌙</button>

  <div class="container">
    <img src="image/poltek.png" alt="Logo Polibatam" />
    <h2>Registrasi Akun</h2>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-error">
        <?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?>
      </div>
    <?php elseif (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <form action="proses_daftar.php" method="POST">
      <div class="form-group">
        <label for="nik_nim">NIK / NIM</label>
        <input type="text" id="nik_nim" name="nik_nim" placeholder="Masukkan NIK atau NIM..." required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password..." required />
        <span class="toggle-password" id="togglePassword">👁️</span>
      </div>

      <div class="form-group">
        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password..."
          required />
        <span class="toggle-password" id="toggleConfirmPassword">👁️</span>
      </div>

      <div class="form-group">
        <label for="role">Daftar Sebagai</label>
        <select id="role" name="role" required>
          <option value="" disabled selected>Pilih peran...</option>
          <option value="dosen">Dosen</option>
          <option value="mahasiswa">Mahasiswa</option>
        </select>
      </div>

      <button type="submit" class="btn-register">DAFTAR</button>
    </form>

    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>

    <footer>&copy; 2025 Polibatam | Sistem Absensi Otomatis</footer>
  </div>

  <script>
    // Toggle Dark Mode
    const toggle = document.getElementById('toggleMode');
    const body = document.body;

    // Cek dan terapkan preferensi mode
    if (localStorage.getItem('darkMode') === 'true') {
      body.classList.add('dark');
      toggle.textContent = '☀️';
    }

    toggle.addEventListener('click', () => {
      body.classList.toggle('dark');
      toggle.textContent = body.classList.contains('dark') ? '☀️' : '🌙';
      localStorage.setItem('darkMode', body.classList.contains('dark'));
    });

    // Toggle Password Visibility (Password)
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    togglePassword.addEventListener('click', () => {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
    });

    // Toggle Password Visibility (Konfirmasi Password)
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPassword = document.getElementById('confirm_password');
    toggleConfirmPassword.addEventListener('click', () => {
      const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPassword.setAttribute('type', type);
    });
  </script>
</body>

</html>