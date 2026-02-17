<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('transaksi');
require_once '../config/database.php';

$nomor_bukti = trim($_GET['nomor_bukti'] ?? '');

if ($nomor_bukti !== '') {
    
    // Ambil data transaksi (untuk ambil id_meja)
    $stmt = mysqli_prepare($connection, "SELECT id_meja FROM transaksi WHERE nomor_bukti = ?");
    mysqli_stmt_bind_param($stmt, "s", $nomor_bukti);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transaksi = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $id_meja = $transaksi['id_meja'] ?? null;

    // 1. Hapus detail transaksi dulu
    $stmtDetail = mysqli_prepare(
        $connection,
        "DELETE FROM detail_transaksi WHERE nomor_bukti = ?"
    );
    mysqli_stmt_bind_param($stmtDetail, "s", $nomor_bukti);
    mysqli_stmt_execute($stmtDetail);
    mysqli_stmt_close($stmtDetail);

    // 2. Hapus transaksi utama
    $stmtTransaksi = mysqli_prepare(
        $connection,
        "DELETE FROM transaksi WHERE nomor_bukti = ?"
    );
    mysqli_stmt_bind_param($stmtTransaksi, "s", $nomor_bukti);
    mysqli_stmt_execute($stmtTransaksi);
    mysqli_stmt_close($stmtTransaksi);
    
    // ✅ AUTO-UPDATE: Set meja jadi "kosong"
    if ($id_meja > 0) {
        $stmt_meja = mysqli_prepare($connection, 
            "UPDATE meja SET status = 'kosong' WHERE id_meja = ?"
        );
        mysqli_stmt_bind_param($stmt_meja, "i", $id_meja);
        mysqli_stmt_execute($stmt_meja);
        mysqli_stmt_close($stmt_meja);
    }
}

// Redirect ke halaman transaksi
header("Location: index.php?success=delete");
exit;