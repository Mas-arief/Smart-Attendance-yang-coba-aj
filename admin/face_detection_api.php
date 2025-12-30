<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action']) || $input['action'] !== 'detect') {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$image_path = null;
if (isset($input['image'])) {
    $base64 = $input['image'];
    $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
    $data = base64_decode($base64);
    if ($data === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid image data']);
        exit;
    }
    $image_path = '../uploads/temp_face_' . uniqid() . '.png';
    file_put_contents($image_path, $data);
} else {
    echo json_encode(['success' => false, 'message' => 'No image provided']);
    exit;
}

$python = 'python'; // or full path to python
$script = '../admin/detect_faces.py';
$cmd = escapeshellcmd("$python $script $image_path");
$output = [];
$status = 0;
exec($cmd, $output, $status);

if ($image_path && file_exists($image_path)) {
    unlink($image_path);
}

if ($status === 0 && !empty($output)) {
    $result = implode("", $output);
    echo $result;
} else {
    echo json_encode(['success' => false, 'message' => 'Face detection failed']);
}
