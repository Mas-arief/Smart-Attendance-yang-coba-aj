<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'dosen') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran Mahasiswa - Dosen</title>

    <!-- Font Awesome & Google Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6fb;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 90px 30px 40px;
            transition: margin-left 0.3s ease;
        }

        .container {
            background: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        h3 {
            color: #0E2F80;
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Filter Section */
        .filter-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        .filter-group select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: 'Poppins';
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border 0.3s;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #0E2F80;
        }

        /* Info Mahasiswa */
        .student-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
        }

        .info-card.primary {
            background: linear-gradient(135deg, #0E2F80 0%, #1e4ba8 100%);
        }

        .info-card h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            opacity: 0.9;
            font-weight: 500;
        }

        .info-card p {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        /* Summary Cards */
        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .summary-card.hadir {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .summary-card.izin {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .summary-card.sakit {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .summary-card.alfa {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            color: white;
        }

        .summary-card h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: 500;
            opacity: 0.95;
        }

        .summary-card .count {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        .summary-card .percentage {
            font-size: 12px;
            margin-top: 5px;
            opacity: 0.9;
        }

        /* Table Section */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 13px;
            min-width: 900px;
        }

        th {
            background-color: #0E2F80;
            color: white;
            padding: 12px 8px;
            font-weight: 600;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f9fafc;
        }

        .kiri {
            text-align: left;
        }

        /* Status dengan icon */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .status.hadir {
            background: #d4edda;
            color: #155724;
        }

        .status.izin {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status.sakit {
            background: #fff3cd;
            color: #856404;
        }

        .status.alfa {
            background: #f8d7da;
            color: #721c24;
        }

        /* Button Export */
        .export-btn {
            background: #0E2F80;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Poppins';
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .export-btn:hover {
            background: #1e4ba8;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 220px;
                padding: 80px 20px 30px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 15px 20px;
            }

            .container {
                padding: 15px;
            }

            h3 {
                font-size: 18px;
            }

            .filter-section {
                grid-template-columns: 1fr;
            }

            .summary-section {
                grid-template-columns: repeat(2, 1fr);
            }

            .summary-card .count {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            h3 {
                font-size: 16px;
            }

            .summary-section {
                grid-template-columns: 1fr;
            }

            .info-card p {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar & Navbar -->
    <?php include 'navside.php'; ?>

    <!-- Konten Utama -->
    <div class="main-content">
        <!-- Filter Section -->
        <div class="container">
            <h3><i class="fa-solid fa-filter"></i> Filter Data Kehadiran</h3>
            <div class="filter-section">
                <div class="filter-group">
                    <label for="mahasiswa"><i class="fa-solid fa-user"></i> Pilih Mahasiswa</label>
                    <select id="mahasiswa" onchange="loadStudentData()">
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php
                        include '../koneksi.php';
                        $q = mysqli_query($conn, "SELECT nim, nama_mahasiswa FROM mahasiswa ORDER BY nama_mahasiswa ASC");
                        while ($m = mysqli_fetch_assoc($q)) {
                            echo '<option value="' . htmlspecialchars($m['nim']) . '">' . htmlspecialchars($m['nim']) . ' - ' . htmlspecialchars($m['nama_mahasiswa']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="tahun"><i class="fa-solid fa-calendar"></i> Tahun Ajaran</label>
                    <select id="tahun" onchange="loadStudentData()">
                        <option value="1">2024/2025 Ganjil</option>
                        <option value="2">2024/2025 Genap</option>
                        <option value="3">2023/2024 Ganjil</option>
                        <option value="4">2023/2024 Genap</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="matkul"><i class="fa-solid fa-book"></i> Mata Kuliah</label>
                    <select id="matkul" onchange="filterMatkul()">
                        <option value="">Semua Mata Kuliah</option>
                        <?php
                        $qmk = mysqli_query($conn, "SELECT kode_mk, nama_mk FROM matakuliah ORDER BY kode_mk ASC");
                        while ($mk = mysqli_fetch_assoc($qmk)) {
                            echo '<option value="' . htmlspecialchars($mk['kode_mk']) . '">' . htmlspecialchars($mk['kode_mk']) . ' - ' . htmlspecialchars($mk['nama_mk']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Info Mahasiswa -->
        <div class="container" id="studentInfoContainer" style="display: none;">
            <h3><i class="fa-solid fa-user-graduate"></i> Informasi Mahasiswa</h3>
            <div class="student-info">
                <div class="info-card primary">
                    <h4>NIM</h4>
                    <p id="studentNIM">-</p>
                </div>
                <div class="info-card">
                    <h4>Nama Lengkap</h4>
                    <p id="studentName">-</p>
                </div>
                <div class="info-card">
                    <h4>Program Studi</h4>
                    <p id="studentProdi">-</p>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="container" id="summaryContainer" style="display: none;">
            <h3><i class="fa-solid fa-chart-pie"></i> Ringkasan Kehadiran</h3>
            <div class="summary-section">
                <div class="summary-card hadir">
                    <h4>HADIR</h4>
                    <p class="count" id="countHadir">0</p>
                    <p class="percentage" id="percentHadir">0%</p>
                </div>
                <div class="summary-card izin">
                    <h4>IZIN</h4>
                    <p class="count" id="countIzin">0</p>
                    <p class="percentage" id="percentIzin">0%</p>
                </div>
                <div class="summary-card sakit">
                    <h4>SAKIT</h4>
                    <p class="count" id="countSakit">0</p>
                    <p class="percentage" id="percentSakit">0%</p>
                </div>
                <div class="summary-card alfa">
                    <h4>ALFA</h4>
                    <p class="count" id="countAlfa">0</p>
                    <p class="percentage" id="percentAlfa">0%</p>
                </div>
            </div>
        </div>

        <!-- Tabel Kehadiran -->
        <div class="container" id="attendanceContainer" style="display: none;">
            <div class="action-bar">
                <h3><i class="fa-solid fa-calendar-check"></i> Detail Kehadiran Per Mata Kuliah</h3>
                <button class="export-btn" onclick="exportData()">
                    <i class="fa-solid fa-file-excel"></i> Export ke Excel
                </button>
            </div>

            <div class="table-container">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>KODE MK</th>
                            <th class="kiri">MATAKULIAH</th>
                            <th>JENIS</th>
                            <th colspan="14">MINGGU KE</th>
                        </tr>
                        <tr>
                            <th colspan="3"></th>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                            <th>4</th>
                            <th>5</th>
                            <th>6</th>
                            <th>7</th>
                            <th>8</th>
                            <th>9</th>
                            <th>10</th>
                            <th>11</th>
                            <th>12</th>
                            <th>13</th>
                            <th>14</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        async function loadStudentData() {
            const selectMhs = document.getElementById('mahasiswa');
            const selectedNim = selectMhs.value;

            if (!selectedNim) {
                document.getElementById('studentInfoContainer').style.display = 'none';
                document.getElementById('summaryContainer').style.display = 'none';
                document.getElementById('attendanceContainer').style.display = 'none';
                return;
            }

            // Ambil info mahasiswa
            try {
                const res = await fetch('get_mahasiswa.php?nim=' + encodeURIComponent(selectedNim));
                const mhs = await res.json();

                if (mhs.status === 'success') {
                    document.getElementById('studentNIM').textContent = mhs.nim;
                    document.getElementById('studentName').textContent = mhs.nama;
                    document.getElementById('studentProdi').textContent = mhs.prodi || '-';
                    document.getElementById('studentInfoContainer').style.display = 'block';

                    // Ambil rekap absensi dari backend
                    const tahun = document.getElementById('tahun').value;
                    const matkul = document.getElementById('matkul').value;

                    const rekapRes = await fetch('get_rekap.php?nim=' + encodeURIComponent(selectedNim) +
                        '&tahun=' + encodeURIComponent(tahun) +
                        '&matkul=' + encodeURIComponent(matkul));
                    const rekap = await rekapRes.json();

                    if (rekap && rekap.courses) {
                        calculateStatistics(rekap.courses);
                        displayTable(rekap.courses);
                        document.getElementById('summaryContainer').style.display = 'block';
                        document.getElementById('attendanceContainer').style.display = 'block';
                    }
                } else {
                    alert('Data mahasiswa tidak ditemukan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data');
            }
        }

        function calculateStatistics(courses) {
            let totalHadir = 0,
                totalIzin = 0,
                totalSakit = 0,
                totalAlfa = 0;
            let totalPertemuan = 0;

            courses.forEach(course => {
                (course.kehadiran || []).forEach(status => {
                    if (status && status !== '-') {
                        totalPertemuan++;
                        if (status === 'Hadir') totalHadir++;
                        else if (status === 'Izin') totalIzin++;
                        else if (status === 'Sakit') totalSakit++;
                        else if (status === 'Alfa') totalAlfa++;
                    }
                });
            });

            document.getElementById('countHadir').textContent = totalHadir;
            document.getElementById('countIzin').textContent = totalIzin;
            document.getElementById('countSakit').textContent = totalSakit;
            document.getElementById('countAlfa').textContent = totalAlfa;

            document.getElementById('percentHadir').textContent = totalPertemuan ? ((totalHadir / totalPertemuan) * 100).toFixed(1) + '%' : '0%';
            document.getElementById('percentIzin').textContent = totalPertemuan ? ((totalIzin / totalPertemuan) * 100).toFixed(1) + '%' : '0%';
            document.getElementById('percentSakit').textContent = totalPertemuan ? ((totalSakit / totalPertemuan) * 100).toFixed(1) + '%' : '0%';
            document.getElementById('percentAlfa').textContent = totalPertemuan ? ((totalAlfa / totalPertemuan) * 100).toFixed(1) + '%' : '0%';
        }

        function displayTable(courses) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            if (!courses || courses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="17" style="text-align:center;color:#888;">Tidak ada data kehadiran</td></tr>';
                return;
            }

            courses.forEach(course => {
                const row = document.createElement('tr');
                row.setAttribute('data-kode', course.kode);

                let html = `
                    <td><strong>${course.kode || '-'}</strong></td>
                    <td class="kiri">${course.matkul || '-'}</td>
                    <td>${course.jenis || '-'}</td>
                `;

                // Loop 14 minggu
                for (let i = 1; i <= 14; i++) {
                    const status = course.kehadiran ? course.kehadiran[i] || '-' : '-';
                    const statusClass = status.toLowerCase();
                    const icon = {
                        'hadir': 'fa-check',
                        'izin': 'fa-info',
                        'sakit': 'fa-notes-medical',
                        'alfa': 'fa-xmark'
                    } [statusClass] || '';

                    if (status !== '-') {
                        html += `<td><span class="status ${statusClass}"><i class="fa-solid ${icon}"></i> ${status}</span></td>`;
                    } else {
                        html += `<td>-</td>`;
                    }
                }

                row.innerHTML = html;
                tbody.appendChild(row);
            });
        }

        function filterMatkul() {
            const filter = document.getElementById('matkul').value;
            const rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                if (!filter || row.getAttribute('data-kode') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportData() {
            const nim = document.getElementById('mahasiswa').value;
            const tahun = document.getElementById('tahun').value;
            const matkul = document.getElementById('matkul').value;

            if (!nim) {
                alert('Pilih mahasiswa terlebih dahulu');
                return;
            }

            window.open('../admin/export_rekap.php?nim=' + encodeURIComponent(nim) +
                '&tahun=' + encodeURIComponent(tahun) +
                '&matkul=' + encodeURIComponent(matkul), '_blank');
        }
    </script>

</body>

</html>