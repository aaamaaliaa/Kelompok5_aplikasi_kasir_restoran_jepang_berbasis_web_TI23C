<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
require_once '../config/database.php';

$error = '';
$success = '';

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
        // Cek duplicate
        $stmt = mysqli_prepare($connection, "SELECT id_meja FROM meja WHERE nomor_meja = ?");
        mysqli_stmt_bind_param($stmt, "s", $nomor_meja);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_fetch_assoc($result)) {
            $error = "Nomor meja sudah digunakan. Gunakan nomor lain.";
        } else {
            // Insert
            $stmt = mysqli_prepare($connection, 
                "INSERT INTO meja (nomor_meja, kapasitas, status, lokasi, deskripsi) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sisss", $nomor_meja, $kapasitas, $status, $lokasi, $deskripsi);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?success=add");
                exit;
            } else {
                $error = "Gagal menambahkan meja: " . mysqli_error($connection);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$csrfToken = generateCSRFToken();
$page_title = 'Tambah Meja';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-plus-circle"></i> Tambah Meja Baru</h2>
            <p class="text-muted mb-0">Tambahkan meja baru ke restoran</p>
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informasi Meja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nomor Meja <span class="text-danger">*</span></label>
                                    <input type="text" name="nomor_meja" class="form-control" 
                                           placeholder="Contoh: Meja 1, VIP-A" required>
                                    <small class="text-muted">Nomor atau nama unik untuk meja</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kapasitas <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="kapasitas" class="form-control" 
                                               value="4" min="1" max="50" required>
                                        <span class="input-group-text">orang</span>
                                    </div>
                                    <small class="text-muted">Jumlah kursi/orang yang bisa duduk</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_kosong" value="kosong" checked>
                                        <label class="form-check-label" for="status_kosong">
                                            <span class="badge bg-success">KOSONG</span> - Tersedia
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_terisi" value="terisi">
                                        <label class="form-check-label" for="status_terisi">
                                            <span class="badge bg-danger">TERISI</span> - Sedang dipakai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_reserved" value="reserved">
                                        <label class="form-check-label" for="status_reserved">
                                            <span class="badge bg-warning">RESERVED</span> - Dipesan
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control" 
                                   placeholder="Contoh: Lantai 1, Area Outdoor, VIP Room">
                            <small class="text-muted">Lokasi penempatan meja (opsional)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" 
                                      placeholder="Keterangan tambahan tentang meja ini..."></textarea>
                            <small class="text-muted">Informasi tambahan (opsional)</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Meja
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Preview Meja</h6>
                </div>
                <div class="card-body text-center">
                    <div class="fs-1 mb-3">
                        <i class="bi bi-table text-primary"></i>
                    </div>
                    <h5 id="preview-nomor">Meja 1</h5>
                    <span class="badge bg-success mb-2" id="preview-status">KOSONG</span>
                    <div class="text-muted small">
                        <i class="bi bi-person-fill"></i> Kapasitas: <span id="preview-kapasitas">4</span> orang
                    </div>
                    <div class="text-muted small mt-2" id="preview-lokasi-container" style="display: none;">
                        <i class="bi bi-geo-alt-fill"></i> <span id="preview-lokasi"></span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <strong><i class="bi bi-info-circle"></i> Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Gunakan nomor unik untuk setiap meja</li>
                    <li>Set kapasitas sesuai jumlah kursi</li>
                    <li>Status bisa diubah kapan saja</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview
document.querySelector('input[name="nomor_meja"]').addEventListener('input', function() {
    document.getElementById('preview-nomor').textContent = this.value || 'Meja 1';
});

document.querySelector('input[name="kapasitas"]').addEventListener('input', function() {
    document.getElementById('preview-kapasitas').textContent = this.value || '4';
});

document.querySelectorAll('input[name="status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const badge = document.getElementById('preview-status');
        badge.className = 'badge mb-2';
        
        if (this.value === 'kosong') {
            badge.classList.add('bg-success');
            badge.textContent = 'KOSONG';
        } else if (this.value === 'terisi') {
            badge.classList.add('bg-danger');
            badge.textContent = 'TERISI';
        } else {
            badge.classList.add('bg-warning');
            badge.textContent = 'RESERVED';
        }
    });
});

document.querySelector('input[name="lokasi"]').addEventListener('input', function() {
    const container = document.getElementById('preview-lokasi-container');
    const text = document.getElementById('preview-lokasi');
    
    if (this.value) {
        text.textContent = this.value;
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>