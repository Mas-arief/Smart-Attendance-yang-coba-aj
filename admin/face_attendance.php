<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

// Ambil daftar mahasiswa yang sudah registrasi wajah
$query = "SELECT nim, nama_mahasiswa FROM mahasiswa WHERE face_registered = 1 ORDER BY nama_mahasiswa ASC";
$registered_mhs = mysqli_query($conn, $query);
$total_registered = mysqli_num_rows($registered_mhs);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Wajah - Admin Polibatam</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- MDB UI Kit -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet" />

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }

        .content {
            margin-left: 230px;
            margin-top: 90px;
            padding: 30px;
        }

        .main-container {
            background-color: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 0 auto;
        }

        h3 {
            color: #0E2F80;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 12px;
            color: white;
            text-align: center;
        }

        .stat-card.primary {
            background: linear-gradient(135deg, #0E2F80 0%, #1e40af 100%);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            opacity: 0.9;
            font-weight: 500;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        /* Camera Section */
        .camera-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .camera-wrapper {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
        }

        video {
            width: 100%;
            height: auto;
            display: block;
        }

        .detection-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            padding: 12px 16px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .detection-overlay .count {
            color: #10b981;
            font-size: 18px;
        }

        /* Control Buttons */
        .controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #0E2F80;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #0a2460;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* Attendance List */
        .attendance-section {
            margin-top: 30px;
        }

        .attendance-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }

        .attendance-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .attendance-item .info {
            flex: 1;
        }

        .attendance-item .name {
            font-weight: 600;
            color: #2c3e50;
        }

        .attendance-item .nim {
            font-size: 13px;
            color: #7f8c8d;
        }

        .attendance-item .time {
            font-size: 12px;
            color: #10b981;
            font-weight: 600;
        }

        .attendance-item .confidence {
            background: #10b981;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-message.info {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }

        .status-message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .status-message.warning {
            background: #fff3e0;
            color: #e65100;
            border-left: 4px solid #e65100;
        }

        .registered-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
        }

        .registered-item {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 13px;
        }

        @media (max-width: 991px) {
            .content {
                margin-left: 0;
                margin-top: 120px;
                padding: 20px;
            }

            .main-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar & Navbar -->
    <?php include 'navsideadmin.php'; ?>

    <div class="content">
        <div class="main-container">
            <h3>
                <i class="fa-solid fa-camera"></i>
                Sistem Absensi Wajah Otomatis
            </h3>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <h4>Mahasiswa Terdaftar</h4>
                    <p class="value" id="totalRegistered"><?= $total_registered ?></p>
                </div>
                <div class="stat-card success">
                    <h4>Hadir Hari Ini</h4>
                    <p class="value" id="totalPresent">0</p>
                </div>
                <div class="stat-card">
                    <h4>Wajah Terdeteksi</h4>
                    <p class="value" id="detectedFaces">0</p>
                </div>
            </div>

            <!-- Status Message -->
            <div class="status-message info" id="statusMsg">
                <i class="fa-solid fa-info-circle"></i> Klik "Mulai Absensi" untuk mengaktifkan kamera
            </div>

            <!-- Camera Section -->
            <div class="camera-section">
                <div class="camera-wrapper">
                    <video id="video" autoplay playsinline></video>
                    <div class="detection-overlay" id="detectionInfo" style="display: none;">
                        Mendeteksi: <span class="count" id="faceCount">0</span> wajah
                    </div>
                </div>

                <div class="controls">
                    <button id="startBtn" class="btn btn-primary">
                        <i class="fas fa-play"></i> Mulai Absensi
                    </button>
                    <button id="stopBtn" class="btn btn-danger" disabled>
                        <i class="fas fa-stop"></i> Hentikan
                    </button>
                    <button id="saveBtn" class="btn btn-success" disabled>
                        <i class="fas fa-save"></i> Simpan & Selesai
                    </button>
                    <button id="resetBtn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset Data
                    </button>
                </div>
            </div>

            <!-- Attendance List -->
            <div class="attendance-section">
                <h4 style="color: #0E2F80; margin-bottom: 15px;">
                    <i class="fa-solid fa-list-check"></i> Daftar Hadir
                </h4>
                <div class="attendance-list" id="attendanceList">
                    <p style="text-align: center; color: #999; padding: 20px;">
                        Belum ada mahasiswa yang hadir
                    </p>
                </div>
            </div>

            <!-- Registered Students -->
            <div class="attendance-section">
                <h4 style="color: #0E2F80; margin-bottom: 15px;">
                    <i class="fa-solid fa-users"></i> Mahasiswa Terdaftar (<?= $total_registered ?>)
                </h4>
                <div class="registered-list">
                    <?php if ($total_registered > 0): ?>
                        <?php mysqli_data_seek($registered_mhs, 0); ?>
                        <?php while ($mhs = mysqli_fetch_assoc($registered_mhs)): ?>
                            <div class="registered-item">
                                <strong><?= htmlspecialchars($mhs['nama_mahasiswa']) ?></strong> - <?= $mhs['nim'] ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 20px;">
                            Belum ada mahasiswa yang registrasi wajah
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const saveBtn = document.getElementById('saveBtn');
        const resetBtn = document.getElementById('resetBtn');
        const statusMsg = document.getElementById('statusMsg');
        const attendanceList = document.getElementById('attendanceList');
        const detectionInfo = document.getElementById('detectionInfo');
        const totalPresentEl = document.getElementById('totalPresent');
        const detectedFacesEl = document.getElementById('detectedFaces');
        const faceCountEl = document.getElementById('faceCount');

        let stream;
        let isRunning = false;
        let attendanceData = {};
        let detectionInterval;

        // Update status
        function updateStatus(message, type = 'info') {
            statusMsg.className = `status-message ${type}`;
            const icons = {
                success: 'check-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            statusMsg.innerHTML = `<i class="fa-solid fa-${icons[type]}"></i> ${message}`;
        }

        // Start camera & detection
        startBtn.addEventListener('click', async () => {
            try {
                updateStatus('Mengaktifkan kamera...', 'info');

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        },
                        facingMode: 'user'
                    }
                });

                video.srcObject = stream;
                detectionInfo.style.display = 'block';
                isRunning = true;

                startBtn.disabled = true;
                stopBtn.disabled = false;
                saveBtn.disabled = false;

                updateStatus('Sistem aktif! Mahasiswa dapat berdiri di depan kamera untuk absen', 'success');

                // Start face detection simulation
                startFaceDetection();

            } catch (err) {
                updateStatus('Gagal mengakses kamera. Pastikan izin kamera diberikan!', 'warning');
                console.error('Camera error:', err);
            }
        });

        // Simulate face detection & recognition
        function startFaceDetection() {
            detectionInterval = setInterval(async () => {
                if (!isRunning) return;

                // Simulate detection (ganti dengan API call ke Python backend)
                const response = await fetch('face_detection_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'detect',
                        timestamp: Date.now()
                    })
                });

                const result = await response.json();

                if (result.success) {
                    detectedFacesEl.textContent = result.detected_count;
                    faceCountEl.textContent = result.detected_count;

                    // Add new attendance
                    if (result.recognized && result.recognized.length > 0) {
                        result.recognized.forEach(person => {
                            if (!attendanceData[person.nim]) {
                                addAttendance(person);
                            }
                        });
                    }
                }

            }, 1000); // Check setiap 1 detik
        }

        // Add attendance
        function addAttendance(person) {
            attendanceData[person.nim] = person;

            const item = document.createElement('div');
            item.className = 'attendance-item';
            item.innerHTML = `
                <div class="info">
                    <div class="name">${person.name}</div>
                    <div class="nim">NIM: ${person.nim}</div>
                </div>
                <div style="text-align: right;">
                    <div class="confidence">${person.confidence}%</div>
                    <div class="time">${person.time}</div>
                </div>
            `;

            if (attendanceList.querySelector('p')) {
                attendanceList.innerHTML = '';
            }

            attendanceList.insertBefore(item, attendanceList.firstChild);

            // Update counter
            totalPresentEl.textContent = Object.keys(attendanceData).length;

            // Play sound
            playSound();
        }

        // Stop detection
        stopBtn.addEventListener('click', () => {
            stopCamera();
            updateStatus('Sistem dihentikan. Klik "Simpan & Selesai" untuk menyimpan data absensi', 'warning');
        });

        // Stop camera
        function stopCamera() {
            isRunning = false;
            clearInterval(detectionInterval);

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            detectionInfo.style.display = 'none';
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }

        // Save attendance
        saveBtn.addEventListener('click', async () => {
            if (Object.keys(attendanceData).length === 0) {
                alert('Belum ada data absensi yang tercatat!');
                return;
            }

            if (!confirm(`Simpan absensi untuk ${Object.keys(attendanceData).length} mahasiswa?`)) {
                return;
            }

            updateStatus('Menyimpan data absensi...', 'info');

            const response = await fetch('save_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    attendance: attendanceData,
                    date: new Date().toISOString().split('T')[0],
                    time: new Date().toLocaleTimeString('id-ID')
                })
            });

            const result = await response.json();

            if (result.success) {
                updateStatus(`Absensi berhasil disimpan untuk ${result.saved_count} mahasiswa!`, 'success');

                setTimeout(() => {
                    if (confirm('Absensi telah disimpan. Mau memulai sesi baru?')) {
                        location.reload();
                    }
                }, 2000);
            } else {
                updateStatus('Gagal menyimpan absensi: ' + result.message, 'warning');
            }
        });

        // Reset data
        resetBtn.addEventListener('click', () => {
            if (confirm('Reset semua data absensi hari ini?')) {
                attendanceData = {};
                attendanceList.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Belum ada mahasiswa yang hadir</p>';
                totalPresentEl.textContent = '0';
                detectedFacesEl.textContent = '0';
                updateStatus('Data direset. Siap untuk sesi baru', 'info');
            }
        });

        // Play sound effect
        function playSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTcIGWi77eefTRAMUKfj8LZjHAY4ktfyzHksBSR3x/DdkEAKFF606+uoVRQKRp/g8r5sIQUrgc7y2Yk3CBlou+3nn00QDFCn4/C2YxwGOJLX8sx5LAUkd8fw3ZBAAhRet');
            audio.play().catch(() => {});
        }

        // Cleanup on exit
        window.addEventListener('beforeunload', () => {
            stopCamera();
        });
    </script>

</body>

</html>