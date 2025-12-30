<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Wajah Otomatis</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0E2F80;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            text-align: center;
            color: gray;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .camera-box {
            position: relative;
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
        }

        video,
        canvas {
            width: 100%;
            border-radius: 12px;
            background: black;
        }

        canvas {
            display: none;
        }

        .oval-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 240px;
            height: 300px;
            border-radius: 50%;
            border: 3px dashed rgba(14, 47, 128, 0.6);
            animation: pulse 2s infinite;
            pointer-events: none;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: .6
            }

            50% {
                opacity: 1
            }
        }

        .status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }

        .info {
            background: #e3f2fd;
            color: #1976d2;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .warning {
            background: #fff3e0;
            color: #e65100;
        }

        .spinner {
            width: 35px;
            height: 35px;
            border: 4px solid #eee;
            border-top: 4px solid #0E2F80;
            border-radius: 50%;
            margin: 15px auto;
            display: none;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1><i class="fas fa-fingerprint"></i> Absensi Wajah Otomatis</h1>
        <p class="subtitle">Sistem akan mengambil foto otomatis ketika wajah terdeteksi</p>

        <div class="status info" id="statusMsg">Mengaktifkan kamera...</div>

        <div class="camera-box">
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas"></canvas>

            <div class="oval-overlay"></div>
            <div class="spinner" id="spinner"></div>
        </div>
    </div>

    <script>
        const video = document.getElementById("video");
        const canvas = document.getElementById("canvas");
        const statusMsg = document.getElementById("statusMsg");
        const spinner = document.getElementById("spinner");
        let stream;

        function status(text, type = "info") {
            statusMsg.className = "status " + type;
            statusMsg.innerHTML = text;
            statusMsg.style.display = "block";
        }

        // Start camera automatically
        async function startCamera() {
            try {
                status("Mengaktifkan kamera...", "info");

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "user"
                    }
                });

                video.srcObject = stream;

                status("Kamera aktif. Mendeteksi wajah...", "info");

                // Simulasi deteksi wajah
                simulateFaceDetection();

            } catch (err) {
                status("Gagal mengakses kamera. Izinkan akses kamera!", "warning");
            }
        }

        // Simulasi pendeteksian + stabilisasi wajah
        function simulateFaceDetection() {
            let countdown = 3;

            status("Wajah terdeteksi. Mohon tetap diam... (" + countdown + ")", "info");

            let timer = setInterval(() => {
                countdown--;

                if (countdown > 0) {
                    status("Wajah stabil. Foto otomatis dalam " + countdown + " detik...", "info");
                } else {
                    clearInterval(timer);
                    takeAutoPhoto();
                }

            }, 1000);
        }

        // Auto capture
        function takeAutoPhoto() {
            spinner.style.display = "block";

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(video, 0, 0);

            status("Mengambil foto & mencatat absensi...", "info");

            let imageData = canvas.toDataURL("image/png");

            // Kirim foto ke backend untuk deteksi wajah
            fetch('face_detection_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'detect',
                        image: imageData
                    })
                })
                .then(res => res.json())
                .then(data => {
                    spinner.style.display = "none";
                    if (data.success && data.recognized && data.recognized.length > 0) {
                        status("Absensi berhasil! Terima kasih.", "success");
                        // Kirim data absensi ke save_attendance.php
                        const attendance = {};
                        data.recognized.forEach(person => {
                            attendance[person.nim] = {
                                name: person.name,
                                confidence: person.confidence,
                                time: person.time
                            };
                        });
                        fetch('save_attendance.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    attendance,
                                    date: new Date().toISOString().split('T')[0],
                                    time: new Date().toLocaleTimeString('id-ID')
                                })
                            })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    status("Absensi berhasil disimpan!", "success");
                                } else {
                                    status("Gagal simpan absensi: " + result.message, "warning");
                                }
                            });
                    } else {
                        status("Wajah tidak dikenali. Silakan ulangi.", "warning");
                    }
                    // stop camera
                    stream.getTracks().forEach(t => t.stop());
                })
                .catch(err => {
                    spinner.style.display = "none";
                    status("Gagal proses absensi: " + err, "warning");
                    stream.getTracks().forEach(t => t.stop());
                });
        }

        startCamera();
    </script>

</body>

</html>