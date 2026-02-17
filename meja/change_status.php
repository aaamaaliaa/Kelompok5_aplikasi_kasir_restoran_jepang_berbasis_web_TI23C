<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
require_once '../config/database.php';

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_meja = (int)($_POST['id_meja'] ?? 0);
$status = trim($_POST['status'] ?? '');

// Validasi
if (!$id_meja || !in_array($status, ['kosong', 'terisi', 'reserved'])) {
    header("Location: index.php?error=invalid");
    exit;
}

// Update status
$stmt = mysqli_prepare($connection, "UPDATE meja SET status = ? WHERE id_meja = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id_meja);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php?success=status");
} else {
    header("Location: index.php?error=update");
}

mysqli_stmt_close($stmt);
exit;