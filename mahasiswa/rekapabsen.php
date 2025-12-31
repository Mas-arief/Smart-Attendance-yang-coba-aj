<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$nim = $_SESSION['username'];

// Ambil data rekap absensi dari database
// PERBAIKAN: Menggunakan id_mk bukan id_matkul, dan kode_mk dari kehadiran
$query = "SELECT mk.kode_mk, mk.nama_mk, mk.jenis, k.status, k.tanggal, k.minggu
          FROM kehadiran k
          LEFT JOIN jadwal_ruangan j ON k.id_jadwal = j.id_jadwal
          LEFT JOIN matakuliah mk ON k.kode_mk = mk.kode_mk
          JOIN mahasiswa m ON k.id_mahasiswa = m.id_mahasiswa
          WHERE m.nim = '$nim'
          ORDER BY mk.kode_mk, k.minggu ASC";
$result = mysqli_query($conn, $query);

// Cek error query
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$rekap = [];
$max_weeks = 14; // Default 14 minggu
while ($row = mysqli_fetch_assoc($result)) {
    $kode = $row['kode_mk'] ?? 'UNKNOWN';
    if (!isset($rekap[$kode])) {
        $rekap[$kode] = [
            'nama_mk' => $row['nama_mk'] ?? '-',
            'jenis' => $row['jenis'] ?? '-',
            'kehadiran' => array_fill(1, 14, '-') // Inisialisasi 14 minggu dengan '-'
        ];
    }
    // Masukkan status ke minggu yang sesuai
    $minggu = intval($row['minggu'] ?? 0);
    if ($minggu >= 1 && $minggu <= 14) {
        $rekap[$kode]['kehadiran'][$minggu] = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran Mahasiswa</title>

    <!-- Font Awesome & Google Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #1f2937;
            line-height: 1.6;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 100px 30px 40px;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, #0E2F80 0%, #1e40af 100%);
            padding: 30px;
            border-radius: 16px;
            color: white;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #e0e7ff;
            font-size: 14px;
            font-weight: 400;
        }

        /* Filter Section */
        .filter-section {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .filter-row {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            white-space: nowrap;
        }

        .filter-group select {
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background-color: #ffffff;
            color: #1f2937;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 200px;
        }

        .filter-group select:hover {
            border-color: #0E2F80;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #0E2F80;
            box-shadow: 0 0 0 3px rgba(14, 47, 128, 0.1);
        }

        /* Legend Section */
        .legend-section {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .legend-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .legend-items {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-dot.hadir {
            background-color: #10b981;
        }

        .legend-dot.izin {
            background-color: #f59e0b;
        }

        .legend-dot.sakit {
            background-color: #8b5cf6;
        }

        .legend-dot.alfa {
            background-color: #ef4444;
        }

        /* Table Container */
        .table-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 600px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 1200px;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #0E2F80;
        }

        th {
            background-color: #0E2F80;
            color: #ffffff;
            padding: 16px 12px;
            font-weight: 600;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:last-child {
            border-right: none;
        }

        th.kiri {
            text-align: left;
        }

        tbody tr {
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
            text-align: center;
            font-size: 13px;
        }

        td:last-child {
            border-right: none;
        }

        td.kiri {
            text-align: left;
            font-weight: 500;
        }

        td.kode-mk {
            font-weight: 600;
            color: #0E2F80;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 12px;
            white-space: nowrap;
        }

        .status-badge.hadir {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-badge.izin {
            background-color: #fed7aa;
            color: #92400e;
        }

        .status-badge.sakit {
            background-color: #e9d5ff;
            color: #5b21b6;
        }

        .status-badge.alfa {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-badge.belum {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* Week Headers Styling */
        .week-header {
            font-size: 11px;
            padding: 12px 8px !important;
            min-width: 70px;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #d1d5db;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 220px;
                padding: 90px 24px 32px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            table {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 16px 24px;
            }

            .page-header {
                padding: 24px;
                border-radius: 12px;
            }

            .page-header h1 {
                font-size: 20px;
            }

            .filter-section {
                padding: 16px;
            }

            .filter-group select {
                min-width: 150px;
            }

            .legend-items {
                gap: 16px;
            }

            th,
            td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 18px;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select {
                width: 100%;
                min-width: auto;
            }

            .legend-items {
                flex-direction: column;
                gap: 8px;
            }
        }

        /* Scrollbar Styling */
        .table-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Export Button */
        .export-section {
            margin: 24px 0 0 0;
            text-align: right;
        }

        .btn-export {
            background: #0E2F80;
            color: #fff;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-export:hover {
            background: #1e40af;
        }
    </style>
</head>

<body>
    <!-- Sidebar & Navbar -->
    <?php include 'navside.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fa-solid fa-calendar-check"></i>
                Rekap Kehadiran Mahasiswa
            </h1>
            <p>Pantau kehadiran Anda di setiap pertemuan mata kuliah per semester</p>

            <!-- Export PDF Button -->
            <div class="export-section">
                <form method="get" action="../download_rekap_pdf.php" target="_blank" style="display: inline;">
                    <input type="hidden" name="nim" value="<?= htmlspecialchars($nim) ?>">
                    <input type="hidden" name="tahun" value="2024-1">
                    <button type="submit" class="btn-export">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="tahun">
                        <i class="fa-solid fa-calendar"></i> Tahun Ajaran
                    </label>
                    <select id="tahun">
                        <option value="2024-1" selected>2024/2025 Ganjil</option>
                        <option value="2024-2">2024/2025 Genap</option>
                        <option value="2023-2">2023/2024 Genap</option>
                        <option value="2023-1">2023/2024 Ganjil</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Legend Section -->
        <div class="legend-section">
            <div class="legend-title">Keterangan Status:</div>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="legend-dot hadir"></span>
                    <span>Hadir</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot izin"></span>
                    <span>Izin</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot sakit"></span>
                    <span>Sakit</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot alfa"></span>
                    <span>Alfa (Tanpa Keterangan)</span>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-wrapper">
                <?php if (empty($rekap)): ?>
                    <div class="no-data">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Belum ada data kehadiran</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="min-width: 100px;">KODE MK</th>
                                <th class="kiri" style="min-width: 250px;">MATA KULIAH</th>
                                <th style="min-width: 150px;">JENIS</th>
                                <?php for ($w = 1; $w <= 14; $w++): ?>
                                    <th class="week-header">W<?= $w ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rekap as $kode => $data): ?>
                                <tr>
                                    <td class="kode-mk"><?= htmlspecialchars($kode) ?></td>
                                    <td class="kiri"><?= htmlspecialchars($data['nama_mk']) ?></td>
                                    <td><?= htmlspecialchars($data['jenis']) ?></td>
                                    <?php for ($w = 1; $w <= 14; $w++): ?>
                                        <?php
                                        $status = $data['kehadiran'][$w] ?? '-';
                                        $badge = 'belum';
                                        if ($status == 'Hadir') $badge = 'hadir';
                                        elseif ($status == 'Izin') $badge = 'izin';
                                        elseif ($status == 'Sakit') $badge = 'sakit';
                                        elseif ($status == 'Alfa') $badge = 'alfa';
                                        ?>
                                        <td>
                                            <?php if ($status != '-'): ?>
                                                <span class="status-badge <?= $badge ?>"><?= htmlspecialchars($status) ?></span>
                                            <?php else: ?>
                                                <span class="status-badge belum">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>