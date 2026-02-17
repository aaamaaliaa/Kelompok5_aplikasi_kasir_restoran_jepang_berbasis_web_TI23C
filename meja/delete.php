<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
require_once '../config/database.php';

$id_meja = (int)($_GET['id'] ?? 0);
if (!$id_meja) {
    header("Location: index.php");
    exit;
}

// Ambil data meja
$stmt = mysqli_prepare($connection, "SELECT * FROM meja WHERE id_meja = ?");
mysqli_stmt_bind_param($stmt, "i", $id_meja);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meja = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$meja) {
    header("Location: index.php");
    exit;
}

$error = '';

// Cek apakah meja sedang dipakai (ada transaksi aktif)
$stmt = mysqli_prepare($connection, 
    "SELECT COUNT(*) as count FROM transaksi 
     WHERE id_meja = ? AND status_bayar != 'lunas'"
);
mysqli_stmt_bind_param($stmt, "i", $id_meja);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$check = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$has_active_transaction = $check['count'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    if ($has_active_transaction) {
        $error = "Tidak dapat menghapus meja yang masih memiliki transaksi aktif!";
    } else {
        // Delete
        $stmt = mysqli_prepare($connection, "DELETE FROM meja WHERE id_meja = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_meja);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?success=delete");
            exit;
        } else {
            $error = "Gagal menghapus meja: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    }
}

$csrfToken = generateCSRFToken();
$page_title = 'Hapus Meja';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-trash"></i> Hapus Meja</h2>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Penghapusan</h5>
                </div>
                <div class="card-body">
                    <?php if ($has_active_transaction): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">
                            <i class="bi bi-x-circle"></i> Tidak Dapat Dihapus!
                        </h5>
                        <p>Meja ini masih memiliki <strong><?= $check['count'] ?> transaksi aktif</strong> yang belum lunas.</p>
                        <hr>
                        <p class="mb-0">Selesaikan semua transaksi terlebih dahulu sebelum menghapus meja.</p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">
                            <i class="bi bi-exclamation-circle-fill"></i> Perhatian!
                        </h5>
                        <p>Anda akan menghapus meja berikut:</p>
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Nomor Meja</th>
                            <td><strong><?= htmlspecialchars($meja['nomor_meja']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Kapasitas</th>
                            <td><?= $meja['kapasitas'] ?> orang</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $meja['status'] === 'kosong' ? 'success' : ($meja['status'] === 'terisi' ? 'danger' : 'warning') ?>">
                                    <?= strtoupper($meja['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td><?= htmlspecialchars($meja['lokasi'] ?: '-') ?></td>
                        </tr>
                    </table>

                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Catatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan!
                    </div>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Ya, Hapus Meja
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>