<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Polibatam</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
      transition: background-color 0.4s ease, color 0.3s ease;
    }

    body {
      background: linear-gradient(135deg, #b6e0ff, #91c9f7);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      position: relative;
    }

    /* Tombol toggle di luar container (pojok kanan atas layar) */
    .toggle-mode {
      position: fixed;
      top: 20px;
      right: 25px;
      background: rgba(255, 255, 255, 0.8);
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      cursor: pointer;
      font-size: 20px;
      color: #3b43b7;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      transition: 0.3s;
      backdrop-filter: blur(4px);
    }

    .toggle-mode:hover {
      background: #e1f1ff;
      transform: scale(1.1);
    }

    .container {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      width: 450px;
      padding: 40px 35px;
      text-align: center;
    }

    .container img {
      width: 90px;
      margin-bottom: 12px;
    }

    h2 {
      color: #2e5aac;
      margin-bottom: 25px;
      font-weight: 600;
    }

    .form-group {
      text-align: left;
      margin-bottom: 18px;
    }

    label {
      font-size: 0.9rem;
      color: #444;
      display: block;
      margin-bottom: 5px;
    }

    input {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 0.9rem;
      outline: none;
      transition: 0.3s;
    }

    input:focus {
      border-color: #3b43b7;
      box-shadow: 0 0 0 3px rgba(59, 67, 183, 0.15);
    }

    .btn-login {
      width: 100%;
      background: linear-gradient(90deg, #69a8ff, #3b77ff);
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      margin-top: 10px;
      transition: 0.3s;
    }

    .btn-login:hover {
      background: linear-gradient(90deg, #3b77ff, #69a8ff);
    }

    .alert {
      background-color: #ffe3e3;
      color: #c0392b;
      border: 1px solid #e0a6a6;
      border-radius: 8px;
      padding: 10px;
      font-size: 0.9rem;
      margin-bottom: 15px;
    }

    p {
      font-size: 0.85rem;
      color: #555;
      margin-top: 18px;
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
      margin-top: 25px;
      font-size: 0.8rem;
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

    body.dark input {
      background-color: #303b66;
      border: 1px solid #47538a;
      color: #fff;
    }

    body.dark .btn-login {
      background: linear-gradient(90deg, #5285f4, #2c53ff);
    }

    body.dark .toggle-mode {
      background: rgba(56, 68, 122, 0.8);
      color: #fff;
    }

    body.dark a {
      color: #a7c7ff;
    }
  </style>
</head>

<body>
  <!-- Toggle Mode di luar container -->
  <button class="toggle-mode" id="toggleMode" title="Ganti Mode">🌙</button>

  <div class="container">
    <img src="image/poltek.png" alt="Logo Polibatam" />
    <h2>Login</h2>

    <?php if (isset($_GET['updated'])): ?>
      <div class="alert alert-success text-center">
        Password berhasil diperbarui. Silakan login kembali.
      </div>
    <?php endif ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert">
        <?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">
      <div class="form-group">
        <label for="username">Username / NIM / NIK</label>
        <input type="text" id="username" name="username" placeholder="Masukkan username..." required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password..." required />
      </div>

      <button type="submit" class="btn-login">LOGIN</button>
    </form>

    <p>Belum punya akun? <a href="daftar.php">Daftar di sini</a></p>

    <footer>&copy; 2025 Polibatam | Sistem Absensi Otomatis</footer>
  </div>

  <script>
    const toggle = document.getElementById('toggleMode');
    const body = document.body;

    toggle.addEventListener('click', () => {
      body.classList.toggle('dark');
      toggle.textContent = body.classList.contains('dark') ? '☀️' : '🌙';
    });
  </script>
</body>

</html>