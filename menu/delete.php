<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('menu');

require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {

    // 🔹 Ambil nama file foto terlebih dahulu
    $stmt = mysqli_prepare(
        $connection,
        "SELECT foto FROM menu WHERE id_menu = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // 🔹 Hapus file foto jika ada
    if (!empty($row['foto'])) {
        $file_path = UPLOAD_DIR_MENU . $row['foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // 🔹 Hapus data menu dari database
    $stmt = mysqli_prepare(
        $connection,
        "DELETE FROM menu WHERE id_menu = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// 🔹 Redirect kembali ke index menu
redirect('menu/index.php');
