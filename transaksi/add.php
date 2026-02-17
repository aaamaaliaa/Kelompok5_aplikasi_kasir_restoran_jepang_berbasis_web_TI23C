<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('transaksi');
require_once '../config/database.php';

$error = '';

/* =====================================================
   GENERATE NOMOR BUKTI OTOMATIS
   Format: TRX-YYYYMMDD-XXX
   ===================================================== */
function generateNomorBukti($connection) {
    $date = date('Ymd');
    $prefix = "TRX-$date";

    $stmt = mysqli_prepare(
        $connection,
        "SELECT nomor_bukti 
         FROM transaksi 
         WHERE nomor_bukti LIKE CONCAT(?, '%')
         ORDER BY nomor_bukti DESC 
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $prefix);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $last = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($last) {
        $num = (int)substr($last['nomor_bukti'], -3) + 1;
    } else {
        $num = 1;
    }

    return sprintf("%s-%03d", $prefix, $num);
}

// Ambil data meja untuk dropdown
$meja_list = [];
$meja_result = mysqli_query($connection, "SELECT * FROM meja ORDER BY nomor_meja ASC");
if ($meja_result) {
    while ($row = mysqli_fetch_assoc($meja_result)) {
        $meja_list[] = $row;
    }
}

/* =====================================================
   PROSES SIMPAN
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $tanggal         = $_POST['tanggal'] ?? date('Y-m-d');
    $waktu           = $_POST['waktu'] ?? date('H:i:s');
    $id_meja         = !empty($_POST['id_meja']) ? (int)$_POST['id_meja'] : null;
    $nama_pelanggan  = trim($_POST['nama_pelanggan'] ?? '');
    $catatan         = trim($_POST['catatan'] ?? '');
    
    // ✅ AUTO-CAPTURE: Nama kasir dari session
    $kasir = $_SESSION['username'] ?? 'Unknown';

    if (empty($tanggal)) {
        $error = "Tanggal wajib diisi.";
    }

    if (!$error) {
        $nomor_bukti = generateNomorBukti($connection);
        $total_bayar = 0;
        $status      = 'belum_lunas';

        $stmt = mysqli_prepare($connection,
            "INSERT INTO transaksi
             (nomor_bukti, tanggal, waktu, id_meja, nama_pelanggan, kasir, total_bayar, status_bayar, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssissdss",
            $nomor_bukti,
            $tanggal,
            $waktu,
            $id_meja,
            $nama_pelanggan,
            $kasir,
            $total_bayar,
            $status,
            $catatan
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            
            // ✅ AUTO-UPDATE: Set meja jadi "terisi"
            if ($id_meja > 0) {
                $stmt_meja = mysqli_prepare($connection, 
                    "UPDATE meja SET status = 'terisi' WHERE id_meja = ?"
                );
                mysqli_stmt_bind_param($stmt_meja, "i", $id_meja);
                mysqli_stmt_execute($stmt_meja);
                mysqli_stmt_close($stmt_meja);
            }
            
            header("Location: detail.php?nomor_bukti=" . urlencode($nomor_bukti));
            exit;
        } else {
            $error = "Gagal menyimpan transaksi.";
        }
    }
}

$csrfToken = generateCSRFToken();
$current_kasir = $_SESSION['username'] ?? 'Unknown';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-plus-circle"></i> Tambah Transaksi</h2>
            <p class="text-muted mb-0">Kasir: <strong><?= htmlspecialchars($current_kasir) ?></strong></p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Bukti</label>
                            <input type="text" class="form-control bg-light" value="Otomatis" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kasir</label>
                            <input type="text" class="form-control bg-warning fw-bold text-dark" 
                                   value="<?= htmlspecialchars($current_kasir) ?>" readonly>
                            <small class="text-muted">Otomatis dari user yang login</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal *</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Waktu</label>
                            <input type="time" name="waktu" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Meja</label>
                            <select name="id_meja" class="form-select">
                                <option value="">-- Takeaway / Tidak Ada Meja --</option>
                                <?php foreach ($meja_list as $meja): 
                                    $disabled = ($meja['status'] !== 'kosong') ? 'disabled' : '';
                                    $status_badge = strtoupper($meja['status']);
                                ?>
                                <option value="<?= $meja['id_meja'] ?>" <?= $disabled ?>>
                                    <?= htmlspecialchars($meja['nomor_meja']) ?> 
                                    (<?= $meja['kapasitas'] ?> orang) 
                                    - <?= $status_badge ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hanya meja KOSONG yang bisa dipilih</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" 
                                   placeholder="Nama pelanggan (opsional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3" 
                                      placeholder="Catatan khusus..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
        </div>
    </form>
</div>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>