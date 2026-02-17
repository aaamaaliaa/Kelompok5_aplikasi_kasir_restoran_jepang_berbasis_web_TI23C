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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $nomor_meja = trim($_POST['nomor_meja'] ?? '');
    $kapasitas = (int)($_POST['kapasitas'] ?? 0);
    $status = trim($_POST['status'] ?? 'kosong');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi
    if (empty($nomor_meja)) {
        $error = "Nomor meja harus diisi.";
    } elseif ($kapasitas < 1) {
        $error = "Kapasitas minimal 1 orang.";
    } elseif (!in_array($status, ['kosong', 'terisi', 'reserved'])) {
        $error = "Status tidak valid.";
    } else {
        // Cek duplicate (kecuali diri sendiri)
        $stmt = mysqli_prepare($connection, "SELECT id_meja FROM meja WHERE nomor_meja = ? AND id_meja != ?");
        mysqli_stmt_bind_param($stmt, "si", $nomor_meja, $id_meja);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_fetch_assoc($result)) {
            $error = "Nomor meja sudah digunakan. Gunakan nomor lain.";
        } else {
            // Update
            $stmt = mysqli_prepare($connection, 
                "UPDATE meja SET nomor_meja = ?, kapasitas = ?, status = ?, lokasi = ?, deskripsi = ? 
                 WHERE id_meja = ?"
            );
            mysqli_stmt_bind_param($stmt, "sisssi", $nomor_meja, $kapasitas, $status, $lokasi, $deskripsi, $id_meja);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?success=edit");
                exit;
            } else {
                $error = "Gagal mengupdate meja: " . mysqli_error($connection);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$csrfToken = generateCSRFToken();
$page_title = 'Edit Meja';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-pencil"></i> Edit Meja</h2>
            <p class="text-muted mb-0">Edit informasi meja: <?= htmlspecialchars($meja['nomor_meja']) ?></p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Informasi Meja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nomor Meja <span class="text-danger">*</span></label>
                                    <input type="text" name="nomor_meja" class="form-control" 
                                           value="<?= htmlspecialchars($meja['nomor_meja']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kapasitas <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="kapasitas" class="form-control" 
                                               value="<?= $meja['kapasitas'] ?>" min="1" max="50" required>
                                        <span class="input-group-text">orang</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_kosong" 
                                               value="kosong" <?= $meja['status'] === 'kosong' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="status_kosong">
                                            <span class="badge bg-success">KOSONG</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_terisi" 
                                               value="terisi" <?= $meja['status'] === 'terisi' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="status_terisi">
                                            <span class="badge bg-danger">TERISI</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_reserved" 
                                               value="reserved" <?= $meja['status'] === 'reserved' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="status_reserved">
                                            <span class="badge bg-warning">RESERVED</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control" 
                                   value="<?= htmlspecialchars($meja['lokasi'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($meja['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Update Meja
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>ID Meja:</th>
                            <td><?= $meja['id_meja'] ?></td>
                        </tr>
                        <tr>
                            <th>Dibuat:</th>
                            <td><?= date('d/m/Y H:i', strtotime($meja['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Update Terakhir:</th>
                            <td><?= date('d/m/Y H:i', strtotime($meja['updated_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>