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

// Ambil nomor bukti dari URL
$nomor_bukti = $_GET['nomor_bukti'] ?? '';
if (!$nomor_bukti) {
    header("Location: index.php");
    exit;
}

// ===== PROSES UPDATE STATUS (INLINE) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $status_bayar = trim($_POST['status_bayar'] ?? '');
    
    if ($status_bayar === 'lunas') {
        $stmt_update = mysqli_prepare($connection, "UPDATE transaksi SET status_bayar = 'lunas' WHERE nomor_bukti = ?");
        mysqli_stmt_bind_param($stmt_update, "s", $nomor_bukti);
        
        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            // Redirect ke struk
            header("Location: struk.php?nomor_bukti=" . urlencode($nomor_bukti));
            exit;
        } else {
            mysqli_stmt_close($stmt_update);
        }
    }
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

// Ambil detail items
$stmt_detail = mysqli_prepare(
    $connection,
    "SELECT dt.*, m.nama_menu, m.kode_menu, m.kategori
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     WHERE dt.nomor_bukti = ?
     ORDER BY dt.id_detail"
);
mysqli_stmt_bind_param($stmt_detail, "s", $nomor_bukti);
mysqli_stmt_execute($stmt_detail);
$result_detail = mysqli_stmt_get_result($stmt_detail);
$detail_items = [];
while ($row = mysqli_fetch_assoc($result_detail)) {
    $detail_items[] = $row;
}
mysqli_stmt_close($stmt_detail);

// Cek apakah bisa cetak struk
$can_print = ($transaksi['status_bayar'] === 'lunas');

// Cek apakah bisa edit/delete
$can_modify = ($transaksi['status_bayar'] !== 'lunas');

$csrfToken = generateCSRFToken();
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="container-fluid">
    <!-- Header dengan Tombol Aksi -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Detail Transaksi</h2>
            <p class="text-muted mb-0">Nomor Bukti: <strong><?= htmlspecialchars($nomor_bukti) ?></strong></p>
        </div>
        <div class="btn-group" role="group">
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            
            <?php if ($can_modify): ?>
            <a href="edit.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
            
            <?php if ($can_print): ?>
            <a href="struk.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
               class="btn btn-success"
               target="_blank"
               title="Cetak Struk">
                <i class="bi bi-printer"></i> Cetak Struk
            </a>
            <?php else: ?>
            <button class="btn btn-secondary" disabled 
                    title="Struk hanya bisa dicetak untuk transaksi yang sudah lunas">
                <i class="bi bi-printer"></i> Cetak Struk
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Status Lunas -->
    <?php if ($can_print): ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center mb-4" role="alert">
        <div>
            <i class="bi bi-check-circle-fill"></i>
            <strong>Transaksi Lunas!</strong> Pembayaran telah selesai. Anda dapat mencetak struk.
        </div>
        <a href="struk.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
           class="btn btn-success btn-sm"
           target="_blank">
            <i class="bi bi-printer"></i> Cetak Struk Sekarang
        </a>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Info Transaksi -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Nomor Bukti</th>
                            <td><strong><?= htmlspecialchars($transaksi['nomor_bukti']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td><?= htmlspecialchars($transaksi['waktu']) ?></td>
                        </tr>
                        <tr>
                            <th>Nomor Meja</th>
                            <td>
                                <?php if ($transaksi['id_meja']): ?>
                                    <span class="badge bg-info">Meja No. <?= htmlspecialchars($transaksi['id_meja']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Takeaway</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Pelanggan</th>
                            <td><?= htmlspecialchars($transaksi['nama_pelanggan'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Status Bayar</th>
                            <td>
                                <?php
                                $status_colors = [
                                    'lunas' => 'success',
                                    'belum_lunas' => 'warning',
                                    'pending' => 'secondary'
                                ];
                                $status_color = $status_colors[$transaksi['status_bayar']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $status_color ?>">
                                    <?= strtoupper(str_replace('_', ' ', $transaksi['status_bayar'])) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Bayar</th>
                            <td class="fs-5 fw-bold text-primary">
                                Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php if ($transaksi['catatan']): ?>
                        <tr>
                            <th>Catatan</th>
                            <td><em><?= htmlspecialchars($transaksi['catatan']) ?></em></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>
                                <small><?= date('d/m/Y H:i:s', strtotime($transaksi['created_at'])) ?></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Item -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Detail Item Pesanan</h5>
                    <?php if ($can_modify): ?>
                    <a href="detailadd.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
                       class="btn btn-sm btn-light">
                        <i class="bi bi-plus-circle"></i> Tambah Item
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($detail_items)): ?>
                    <div class="alert alert-warning m-3" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        Belum ada item dalam transaksi ini.
                        <?php if ($can_modify): ?>
                        <a href="detailadd.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="alert-link">
                            Tambah item sekarang
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="35%">Menu</th>
                                    <th width="10%" class="text-center">Jumlah</th>
                                    <th width="15%" class="text-end">Harga</th>
                                    <th width="15%" class="text-end">Subtotal</th>
                                    <th width="20%">Catatan</th>
                                    <?php if ($can_modify): ?>
                                    <th width="10%" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($detail_items as $item): 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nama_menu']) ?></strong><br>
                                        <small class="text-muted">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($item['kode_menu']) ?></span>
                                            <?= htmlspecialchars($item['kategori']) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= $item['jumlah'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($item['catatan_item']): ?>
                                        <small class="text-muted fst-italic">
                                            <i class="bi bi-chat-left-text"></i>
                                            <?= htmlspecialchars($item['catatan_item']) ?>
                                        </small>
                                        <?php else: ?>
                                        <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($can_modify): ?>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="confirmDeleteItem(<?= $item['id_detail'] ?>, '<?= htmlspecialchars(addslashes($item['nama_menu'])) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="<?= $can_modify ? 4 : 3 ?>" class="text-end fw-bold">TOTAL:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">
                                        Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?>
                                    </td>
                                    <td colspan="<?= $can_modify ? 2 : 1 ?>"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Aksi Cepat -->
            <?php if ($can_modify): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="detailadd.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
                               class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-plus-circle"></i><br>
                                Tambah Item
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="edit.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
                               class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-pencil"></i><br>
                                Edit Transaksi
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="payment.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
                               class="btn btn-success w-100 mb-2">
                                <i class="bi bi-credit-card"></i><br>
                                Proses Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hidden Form untuk Submit (INLINE - TIDAK PERLU FILE TERPISAH) -->
<form id="formLunas" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="status_bayar" value="lunas">
</form>

<style>
.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
}

/* SweetAlert2 Custom Styling */
.swal2-popup {
    font-family: Arial, sans-serif;
}
</style>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Fungsi konfirmasi lunas dengan SweetAlert2
function confirmLunas() {
    Swal.fire({
        title: 'Konfirmasi Pembayaran Lunas',
        html: `
            <div class="text-start">
                <p><i class="bi bi-info-circle text-info"></i> Setelah status diubah menjadi <strong>LUNAS</strong>, transaksi tidak dapat diubah lagi.</p>
                <table class="table table-sm table-bordered mt-3">
                    <tr>
                        <th width="40%">Nomor Bukti:</th>
                        <td><?= htmlspecialchars($nomor_bukti) ?></td>
                    </tr>
                    <tr>
                        <th>Total Bayar:</th>
                        <td class="fs-5 fw-bold text-success">Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></td>
                    </tr>
                </table>
                <p class="mt-3">Apakah Anda yakin pembayaran untuk transaksi ini sudah diterima?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle"></i> Ya, Tandai Lunas',
        cancelButtonText: 'Batal',
        width: '600px',
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form
            document.getElementById('formLunas').submit();
        }
    });
}

// Fungsi konfirmasi hapus item dengan SweetAlert2
function confirmDeleteItem(idDetail, namaMenu) {
    Swal.fire({
        title: 'Hapus Item?',
        html: `Anda akan menghapus item:<br><strong>${namaMenu}</strong><br><br>Item yang dihapus tidak dapat dikembalikan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect ke delete page
            window.location.href = `detaildelete.php?id=${idDetail}&nomor_bukti=<?= urlencode($nomor_bukti) ?>`;
        }
    });
}
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>