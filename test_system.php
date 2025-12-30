<?php
// Test Python
echo "Python: " . shell_exec('python --version 2>&1') . "<br>";

// Test script path
$script = realpath('python/detect_faces.py');
echo "Script: " . ($script ? "✓ Found" : "✗ Not Found") . "<br>";

// Test folder writable
$temp = 'temp/test_' . time() . '.txt';
file_put_contents($temp, 'test');
echo "Temp writable: " . (file_exists($temp) ? "✓ Yes" : "✗ No") . "<br>";
unlink($temp);

// Test database
include 'koneksi.php';
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa");
$row = mysqli_fetch_assoc($result);
echo "Database: ✓ Connected ({$row['total']} mahasiswa)<br>";
