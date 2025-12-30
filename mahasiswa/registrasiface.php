<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

include '../koneksi.php';

// Ambil data mahasiswa yang sedang login
$id_user = $_SESSION['id_user'];
$query = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id_user = '$id_user' LIMIT 1");
$mahasiswa = mysqli_fetch_assoc($query);

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan!");
}

$nim = $mahasiswa['nim'];
$nama = $mahasiswa['nama_mahasiswa'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Wajah - <?= $nama ?></title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0E2F80;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .header-section h1 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .header-section .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }

        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            margin-bottom: 20px;
        }

        .info-box strong {
            display: block;
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-size: 18px;
            font-weight: 700;
        }

        /* Camera Section */
        .camera-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .camera-wrapper {
            position: relative;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
        }

        video,
        canvas {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
        }

        canvas {
            display: none;
        }

        .face-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 240px;
            height: 300px;
            border: 3px dashed rgba(14, 47, 128, 0.6);
            border-radius: 50%;
            pointer-events: none;
            display: none;
        }

        .face-overlay.active {
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        /* Photo Counter */
        .photo-counter {
            text-align: center;
            margin: 15px 0;
            font-size: 18px;
            font-weight: 600;
            color: #0E2F80;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .photo-grid canvas {
            display: block;
            width: 100%;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .photo-grid canvas.captured {
            border-color: #10b981;
        }

        /* Status Messages */
        .status-message {
            text-align: center;
            padding: 10px 16px;
            border-radius: 8px;
            margin: 12px 0;
            font-size: 13px;
            font-weight: 500;
            display: none;
        }

        .status-message.info {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
            display: block;
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

        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* Guidelines */
        .guidelines {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 18px;
            margin-top: 20px;
        }

        .guidelines h3 {
            color: #2c3e50;
            font-size: 15px;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guidelines ul {
            margin: 0;
            padding-left: 22px;
            color: #555;
        }

        .guidelines li {
            margin-bottom: 8px;
            line-height: 1.5;
            font-size: 13px;
        }

        .spinner {
            display: none;
            width: 35px;
            height: 35px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0E2F80;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 15px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .spinner.active {
            display: block;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0E2F80, #1e40af);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <h1>
                <i class="fas fa-user-circle"></i>
                Registrasi Wajah Mahasiswa
            </h1>
            <p class="subtitle">Ambil 10 foto untuk sistem absensi otomatis</p>
        </div>

        <!-- Info Mahasiswa -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
            <div class="info-box">
                <strong>NIM</strong>
                <div class="value"><?= $nim ?></div>
            </div>
            <div class="info-box">
                <strong>Nama</strong>
                <div class="value"><?= $nama ?></div>
            </div>
        </div>

        <!-- Status Message -->
        <div class="status-message info" id="statusMsg">
            <i class="fas fa-info-circle"></i> Klik "Mulai Pengambilan Foto" untuk memulai
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progressBar">0/10</div>
        </div>

        <!-- Photo Counter -->
        <div class="photo-counter" id="photoCounter">
            Foto Tersimpan: <span id="photoCount">0</span>/10
        </div>

        <!-- Camera Section -->
        <div class="camera-section">
            <div class="camera-wrapper">
                <video id="video" autoplay playsinline></video>
                <canvas id="captureCanvas" width="640" height="480"></canvas>
                <div class="face-overlay" id="faceOverlay"></div>
            </div>
            <div class="spinner" id="loadingSpinner"></div>
        </div>

        <!-- Preview Grid -->
        <div class="photo-grid" id="photoGrid"></div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button id="startBtn" class="btn btn-primary">
                <i class="fas fa-play"></i> Mulai Pengambilan Foto
            </button>
            <button id="resetBtn" class="btn btn-secondary" style="display: none;">
                <i class="fas fa-redo"></i> Mulai Ulang
            </button>
            <button id="saveBtn" class="btn btn-success" disabled>
                <i class="fas fa-check-circle"></i> Simpan & Daftar
            </button>
        </div>

        <!-- Guidelines -->
        <div class="guidelines">
            <h3>
                <i class="fas fa-lightbulb"></i> Panduan Pengambilan Foto
            </h3>
            <ul>
                <li><strong>Pencahayaan:</strong> Pastikan ruangan terang dan tidak ada bayangan di wajah</li>
                <li><strong>Posisi:</strong> Hadapkan wajah ke kamera dengan jarak 30-50 cm</li>
                <li><strong>Variasi:</strong> Foto akan diambil otomatis dengan interval 0.5 detik</li>
                <li><strong>Gerakan:</strong> Sedikit gerakkan kepala (kiri-kanan-atas-bawah) selama proses</li>
                <li><strong>Aksesoris:</strong> Lepas masker, kacamata hitam, atau topi</li>
            </ul>
        </div>

        <!-- Navigation -->
        <div style="margin-top: 25px; text-align: center; padding-top: 18px; border-top: 2px solid #f0f0f0;">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const captureCanvas = document.getElementById('captureCanvas');
        const startBtn = document.getElementById('startBtn');
        const resetBtn = document.getElementById('resetBtn');
        const saveBtn = document.getElementById('saveBtn');
        const statusMsg = document.getElementById('statusMsg');
        const faceOverlay = document.getElementById('faceOverlay');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const photoCounter = document.getElementById('photoCount');
        const photoGrid = document.getElementById('photoGrid');
        const progressBar = document.getElementById('progressBar');
        const ctx = captureCanvas.getContext('2d');

        const NIM = '<?= $nim ?>';
        const NAMA = '<?= $nama ?>';
        const MAX_PHOTOS = 10;
        let capturedPhotos = [];
        let stream;
        let capturing = false;

        // Update status message
        function updateStatus(message, type = 'info') {
            statusMsg.className = `status-message ${type}`;
            const icons = {
                success: 'check-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            statusMsg.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${message}`;
            statusMsg.style.display = 'block';
        }

        // Update progress
        function updateProgress(count) {
            const percent = (count / MAX_PHOTOS) * 100;
            progressBar.style.width = percent + '%';
            progressBar.textContent = `${count}/${MAX_PHOTOS}`;
            photoCounter.textContent = count;
        }

        // Create preview canvases
        function initPhotoGrid() {
            photoGrid.innerHTML = '';
            for (let i = 0; i < MAX_PHOTOS; i++) {
                const canvas = document.createElement('canvas');
                canvas.width = 120;
                canvas.height = 90;
                canvas.id = `preview-${i}`;
                photoGrid.appendChild(canvas);
            }
        }

        // Start camera
        startBtn.addEventListener('click', async () => {
            try {
                loadingSpinner.classList.add('active');
                updateStatus('Mengakses kamera...', 'info');

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
                faceOverlay.classList.add('active');

                loadingSpinner.classList.remove('active');
                updateStatus('Kamera aktif! Pengambilan foto akan dimulai otomatis...', 'success');

                startBtn.disabled = true;
                initPhotoGrid();
                capturedPhotos = [];
                updateProgress(0);

                // Start auto capture after countdown
                let countdown = 3;
                const countdownInterval = setInterval(() => {
                    updateStatus(`Pengambilan dimulai dalam ${countdown}...`, 'info');
                    countdown--;
                    if (countdown < 0) {
                        clearInterval(countdownInterval);
                        startAutoCapture();
                    }
                }, 1000);

            } catch (err) {
                loadingSpinner.classList.remove('active');
                updateStatus('Gagal mengakses kamera. Pastikan izin kamera diberikan!', 'warning');
                console.error('Camera error:', err);
            }
        });

        // Auto capture 10 photos
        function startAutoCapture() {
            capturing = true;
            let photoCount = 0;

            updateStatus('Mengambil foto... Tetap di posisi dan sedikit gerakkan kepala', 'info');

            const captureInterval = setInterval(() => {
                if (photoCount >= MAX_PHOTOS) {
                    clearInterval(captureInterval);
                    capturing = false;
                    onCaptureComplete();
                    return;
                }

                // Capture photo
                ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
                const imageData = captureCanvas.toDataURL('image/jpeg', 0.9);
                capturedPhotos.push(imageData);

                // Update preview
                const previewCanvas = document.getElementById(`preview-${photoCount}`);
                const previewCtx = previewCanvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    previewCtx.drawImage(img, 0, 0, previewCanvas.width, previewCanvas.height);
                    previewCanvas.classList.add('captured');
                };
                img.src = imageData;

                photoCount++;
                updateProgress(photoCount);

                // Flash effect
                faceOverlay.style.borderColor = '#10b981';
                setTimeout(() => {
                    faceOverlay.style.borderColor = 'rgba(14, 47, 128, 0.6)';
                }, 100);

            }, 500); // Ambil foto setiap 0.5 detik
        }

        // Capture complete
        function onCaptureComplete() {
            faceOverlay.classList.remove('active');
            updateStatus('Pengambilan foto selesai! Periksa hasil dan klik "Simpan & Daftar"', 'success');

            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            saveBtn.disabled = false;
            resetBtn.style.display = 'inline-flex';
        }

        // Reset
        resetBtn.addEventListener('click', () => {
            capturedPhotos = [];
            updateProgress(0);
            startBtn.disabled = false;
            resetBtn.style.display = 'none';
            saveBtn.disabled = true;
            initPhotoGrid();
            updateStatus('Klik "Mulai Pengambilan Foto" untuk mencoba lagi', 'info');
        });

        // Save registration
        saveBtn.addEventListener('click', async () => {
            loadingSpinner.classList.add('active');
            updateStatus('Menyimpan data registrasi...', 'info');
            saveBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('nim', NIM);
                formData.append('nama', NAMA);
                formData.append('photo_count', capturedPhotos.length);

                // Add all photos
                capturedPhotos.forEach((photo, index) => {
                    formData.append(`photo_${index}`, photo);
                });

                const response = await fetch('save_face_registration.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                loadingSpinner.classList.remove('active');

                if (result.success) {
                    updateStatus('Registrasi wajah berhasil! Dialihkan ke dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    updateStatus('Gagal menyimpan: ' + result.message, 'warning');
                    saveBtn.disabled = false;
                }

            } catch (err) {
                loadingSpinner.classList.remove('active');
                updateStatus('Terjadi kesalahan saat menyimpan data', 'warning');
                saveBtn.disabled = false;
                console.error('Save error:', err);
            }
        });

        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>

</body>

</html>