<?php
/**
 * ==========================================
 * SMART ATTENDANCE - INSTALLER
 * Automatic Database Setup
 * Created: 2025-12-30
 * ==========================================
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Config
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'smart_attendance_db';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Attendance - Installer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .info-box ul {
            margin-left: 20px;
        }

        .info-box li {
            margin: 5px 0;
            color: #555;
        }

        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .credentials {
            background: #e7f3ff;
            border: 2px dashed #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .credentials h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .credentials table {
            width: 100%;
        }

        .credentials td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .credentials td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 14px;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Smart Attendance</h1>
        <p class="subtitle">Face Recognition System - Database Installer</p>

        <div class="info-box">
            <h3>📋 Persiapan Instalasi</h3>
            <ul>
                <li>Pastikan <strong>XAMPP/WAMPP</strong> sudah aktif</li>
                <li>Service <strong>Apache</strong> dan <strong>MySQL</strong> harus running</li>
                <li>File <code>smart_attendance.sql</code> ada di folder <code>database/</code></li>
            </ul>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div style="margin-top: 20px;">';

            // Step 1: Koneksi ke MySQL
            echo '<div class="status">';
            $conn = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);

            if (!$conn) {
                echo '<div class="error">❌ GAGAL: Tidak bisa koneksi ke MySQL!<br>';
                echo 'Error: ' . mysqli_connect_error() . '</div>';
                echo '</div>';
            } else {
                echo '<div class="success">✅ Koneksi ke MySQL berhasil!</div>';

                // Step 2: Drop database lama (jika ada)
                mysqli_query($conn, "DROP DATABASE IF EXISTS $DB_NAME");

                // Step 3: Baca file SQL
                $sql_file = '../database/smart_attendance.sql';

                if (!file_exists($sql_file)) {
                    echo '<div class="error">❌ GAGAL: File <code>smart_attendance.sql</code> tidak ditemukan!</div>';
                } else {
                    $sql = file_get_contents($sql_file);

                    // Step 4: Execute SQL
                    if (mysqli_multi_query($conn, $sql)) {
                        // Tunggu semua query selesai
                        do {
                            if ($result = mysqli_store_result($conn)) {
                                mysqli_free_result($result);
                            }
                        } while (mysqli_next_result($conn));

                        echo '<div class="success">✅ Database <strong>' . $DB_NAME . '</strong> berhasil dibuat!</div>';
                        echo '<div class="success">✅ Semua tabel berhasil dibuat!</div>';
                        echo '<div class="success">✅ Data default berhasil diisi!</div>';

                        // Tampilkan kredensial login
                        echo '<div class="credentials">';
                        echo '<h3>🔐 Kredensial Login Default</h3>';
                        echo '<table>';
                        echo '<tr><td>👤 Admin Username:</td><td><code>admin</code></td></tr>';
                        echo '<tr><td>🔑 Admin Password:</td><td><code>admin123</code></td></tr>';
                        echo '<tr><td>👨‍🏫 Dosen Username:</td><td><code>dosen1</code></td></tr>';
                        echo '<tr><td>🔑 Dosen Password:</td><td><code>dosen123</code></td></tr>';
                        echo '</table>';
                        echo '<p style="margin-top: 15px; color: #856404; font-weight: bold;">⚠️ Segera ganti password setelah login pertama!</p>';
                        echo '</div>';

                        echo '<div class="warning">📝 Jangan lupa update file <code>config/koneksi.php</code> jika mengubah konfigurasi database!</div>';

                        echo '<button onclick="window.location.href=\'../login.php\'">Login Sekarang →</button>';

                    } else {
                        echo '<div class="error">❌ GAGAL: Error saat menjalankan SQL!<br>';
                        echo 'Error: ' . mysqli_error($conn) . '</div>';
                    }
                }

                mysqli_close($conn);
                echo '</div>';
            }
        } else {
            // Form Install
            ?>
            <div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
                <h3>⚠️ PERHATIAN</h3>
                <ul>
                    <li>Proses ini akan <strong>menghapus database lama</strong> (jika ada)</li>
                    <li>Semua data akan diganti dengan data baru</li>
                    <li>Pastikan Anda sudah backup data penting</li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit">🚀 Mulai Instalasi Database</button>
            </form>

            <?php
        }
        ?>

        <div class="footer">
            <p>Smart Attendance Face Recognition System</p>
            <p>© 2025 - Created with ❤️</p>
        </div>
    </div>
</body>
</html>
