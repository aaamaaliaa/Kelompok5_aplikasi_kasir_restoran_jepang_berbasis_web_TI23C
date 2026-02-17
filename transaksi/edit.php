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

// Ambil nomor_bukti dari URL
$nomor_bukti = trim($_GET['nomor_bukti'] ?? '');
if (!$nomor_bukti) {
    header("Location: index.php");
    exit;
}

// Ambil data transaksi
$stmt = mysqli_prepare($connection, "SELECT * FROM transaksi WHERE nomor_bukti = ?");
mysqli_stmt_bind_param($stmt, "s", $nomor_bukti);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$transaksi) {
    header("Location: index.php");
    exit;
}

// Ambil data meja untuk dropdown
$meja_list = [];
$meja_result = mysqli_query($connection, "SELECT * FROM meja ORDER BY nomor_meja ASC");
if ($meja_result) {
    while ($row = mysqli_fetch_assoc($meja_result)) {
        $meja_list[] = $row;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $tanggal = trim($_POST['tanggal'] ?? '');
    $waktu = trim($_POST['waktu'] ?? '');
    $id_meja_baru = !empty($_POST['id_meja']) ? (int)$_POST['id_meja'] : null;
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $status_bayar = trim($_POST['status_bayar'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if (empty($tanggal)) {
        $error = "Tanggal wajib diisi.";
    }

    if (!$error) {
        $stmt = mysqli_prepare(
            $connection, 
            "UPDATE transaksi 
             SET tanggal = ?, 
                 waktu = ?, 
                 id_meja = ?, 
                 nama_pelanggan = ?, 
                 status_bayar = ?, 
                 catatan = ?
             WHERE nomor_bukti = ?"
        );

        mysqli_stmt_bind_param(
            $stmt, 
            "ssissss", 
            $tanggal, 
            $waktu, 
            $id_meja_baru, 
            $nama_pelanggan, 
            $status_bayar, 
            $catatan,
            $nomor_bukti
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            
            // ✅ AUTO-UPDATE: Handle perubahan meja
            $id_meja_lama = (int)($transaksi['id_meja'] ?? 0);
            
            if ($id_meja_lama != $id_meja_baru) {
                // Meja LAMA → kosong
                if ($id_meja_lama > 0) {
                    $stmt_old = mysqli_prepare($connection, 
                        "UPDATE meja SET status = 'kosong' WHERE id_meja = ?"
                    );
                    mysqli_stmt_bind_param($stmt_old, "i", $id_meja_lama);
                    mysqli_stmt_execute($stmt_old);
                    mysqli_stmt_close($stmt_old);
                }
                
                // Meja BARU → terisi
                if ($id_meja_baru > 0) {
                    $stmt_new = mysqli_prepare($connection, 
                        "UPDATE meja SET status = 'terisi' WHERE id_meja = ?"
                    );
                    mysqli_stmt_bind_param($stmt_new, "i", $id_meja_baru);
                    mysqli_stmt_execute($stmt_new);
                    mysqli_stmt_close($stmt_new);
                }
            }
            
            $success = "Data transaksi berhasil diperbarui.";
            
            // Reload data transaksi
            $stmt = mysqli_prepare($connection, "SELECT * FROM transaksi WHERE nomor_bukti = ?");
            mysqli_stmt_bind_param($stmt, "s", $nomor_bukti);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $transaksi = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            $error = "Gagal memperbarui transaksi: " . mysqli_error($connection);
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Edit Transaksi</h2>
            <p class="text-muted mb-0">Nomor Bukti: <strong><?= htmlspecialchars($nomor_bukti) ?></strong></p>
        </div>
        <div>
            <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="btn btn-info">
                <i class="bi bi-eye"></i> Lihat Detail
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <?php if ($success): ?>
        <?= showAlert($success, 'success') ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Form Edit Transaksi</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Bukti</label>
                            <input type="text" class="form-control bg-light" 
                                   value="<?= htmlspecialchars($transaksi['nomor_bukti']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" 
                                   value="<?= htmlspecialchars($transaksi['tanggal']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Waktu</label>
                            <input type="time" name="waktu" class="form-control" 
                                   value="<?= htmlspecialchars($transaksi['waktu']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Meja</label>
                            <select name="id_meja" class="form-select">
                                <option value="">-- Takeaway / Tidak Ada Meja --</option>
                                <?php foreach ($meja_list as $meja): 
                                    $selected = ($meja['id_meja'] == $transaksi['id_meja']) ? 'selected' : '';
                                    $disabled = ($meja['status'] !== 'kosong' && $meja['id_meja'] != $transaksi['id_meja']) ? 'disabled' : '';
                                    $status_badge = strtoupper($meja['status']);
                                ?>
                                <option value="<?= $meja['id_meja'] ?>" <?= $selected ?> <?= $disabled ?>>
                                    <?= htmlspecialchars($meja['nomor_meja']) ?> 
                                    (<?= $meja['kapasitas'] ?> orang) 
                                    - <?= $status_badge ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Kosongkan untuk takeaway</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" 
                                   value="<?= htmlspecialchars($transaksi['nama_pelanggan'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control bg-light fw-bold" 
                                       value="<?= number_format($transaksi['total_bayar'], 0, ',', '.') ?>" readonly>
                            </div>
                            <small class="text-muted">Total dihitung otomatis dari detail item</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="status_bayar" class="form-select" required>
                                <option value="belum_lunas" <?= $transaksi['status_bayar'] == 'belum_lunas' ? 'selected' : '' ?>>
                                    Belum Lunas
                                </option>
                                <option value="pending" <?= $transaksi['status_bayar'] == 'pending' ? 'selected' : '' ?>>
                                    Pending
                                </option>
                                <option value="lunas" <?= $transaksi['status_bayar'] == 'lunas' ? 'selected' : '' ?>>
                                    Lunas
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="5"><?= htmlspecialchars($transaksi['catatan'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Perbarui Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>