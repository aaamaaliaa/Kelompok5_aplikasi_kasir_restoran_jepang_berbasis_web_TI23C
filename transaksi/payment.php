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

// Load restaurant config untuk tax & service
$restaurant_config_file = '../config/restaurant.php';
if (file_exists($restaurant_config_file)) {
    require_once $restaurant_config_file;
    $resto_info = getRestaurantInfo();
} else {
    $resto_info = [
        'tax_percentage' => 10,
        'service_charge' => 5,
    ];
}

// Ambil nomor bukti
$nomor_bukti = $_GET['nomor_bukti'] ?? '';
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

// Cek apakah sudah lunas
if ($transaksi['status_bayar'] === 'lunas') {
    header("Location: detail.php?nomor_bukti=" . urlencode($nomor_bukti));
    exit;
}

// HITUNG TAX & SERVICE
$subtotal = $transaksi['total_bayar'];
$tax_amount = ($subtotal * $resto_info['tax_percentage']) / 100;
$service_amount = ($subtotal * $resto_info['service_charge']) / 100;
$grand_total = $subtotal + $tax_amount + $service_amount;

$error = '';
$success = '';

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');
    $jumlah_bayar = null;
    $kembalian = null;

    // Validasi metode pembayaran
    $allowed_methods = ['tunai', 'debit_card', 'qris'];
    if (!in_array($metode_pembayaran, $allowed_methods)) {
        $error = "Metode pembayaran tidak valid.";
    }

    // Jika tunai, validasi jumlah bayar
    if ($metode_pembayaran === 'tunai' && !$error) {
        $jumlah_bayar = floatval($_POST['jumlah_bayar'] ?? 0);
        
        // VALIDASI DENGAN GRAND TOTAL (sudah include tax & service)
        if ($jumlah_bayar < $grand_total) {
            $error = "Jumlah bayar tidak boleh kurang dari total tagihan (Rp " . number_format($grand_total, 0, ',', '.') . ").";
        } else {
            $kembalian = $jumlah_bayar - $grand_total;
        }
    }

    if (!$error) {
        // Update transaksi dengan tax, service, dan grand_total
        $stmt = mysqli_prepare(
            $connection,
            "UPDATE transaksi 
             SET status_bayar = 'lunas',
                 tax_amount = ?,
                 service_amount = ?,
                 grand_total = ?,
                 metode_pembayaran = ?,
                 jumlah_bayar = ?,
                 kembalian = ?
             WHERE nomor_bukti = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "dddsdds",
            $tax_amount,
            $service_amount,
            $grand_total,
            $metode_pembayaran,
            $jumlah_bayar,
            $kembalian,
            $nomor_bukti
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            
            // AUTO-UPDATE STATUS MEJA → KOSONG (transaksi selesai)
            if (!empty($transaksi['id_meja'])) {
                $stmt_meja = mysqli_prepare($connection, "UPDATE meja SET status = 'kosong' WHERE id_meja = ?");
                mysqli_stmt_bind_param($stmt_meja, "i", $transaksi['id_meja']);
                mysqli_stmt_execute($stmt_meja);
                mysqli_stmt_close($stmt_meja);
            }
            
            // Redirect ke struk
            header("Location: struk.php?nomor_bukti=" . urlencode($nomor_bukti));
            exit;
        } else {
            $error = "Gagal memproses pembayaran: " . mysqli_error($connection);
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
            <h2><i class="bi bi-credit-card"></i> Pembayaran</h2>
            <p class="text-muted mb-0">Nomor Bukti: <strong><?= htmlspecialchars($nomor_bukti) ?></strong></p>
        </div>
        <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Info Transaksi -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Transaksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-3">
                        <tr>
                            <th width="40%">Nomor Bukti:</th>
                            <td><strong><?= htmlspecialchars($transaksi['nomor_bukti']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal:</th>
                            <td><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></td>
                        </tr>
                        <tr>
                            <th>Pelanggan:</th>
                            <td><?= htmlspecialchars($transaksi['nama_pelanggan'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Meja:</th>
                            <td>
                                <?php if ($transaksi['id_meja']): ?>
                                    No. <?= htmlspecialchars($transaksi['id_meja']) ?>
                                <?php else: ?>
                                    Takeaway
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <!-- RINCIAN BIAYA (WITH TAX & SERVICE) -->
                    <div class="bg-light p-3 rounded">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Subtotal Item:</td>
                                <td class="text-end">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            </tr>
                            <?php if ($resto_info['tax_percentage'] > 0): ?>
                            <tr>
                                <td>Pajak (<?= $resto_info['tax_percentage'] ?>%):</td>
                                <td class="text-end">Rp <?= number_format($tax_amount, 0, ',', '.') ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($resto_info['service_charge'] > 0): ?>
                            <tr>
                                <td>Service (<?= $resto_info['service_charge'] ?>%):</td>
                                <td class="text-end">Rp <?= number_format($service_amount, 0, ',', '.') ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top">
                                <td class="fw-bold fs-5">TOTAL TAGIHAN:</td>
                                <td class="text-end fw-bold text-primary fs-4">
                                    Rp <?= number_format($grand_total, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Pembayaran -->
        <div class="col-lg-7">
            <form method="POST" id="formPembayaran">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-wallet2"></i> Pilih Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <!-- Metode Pembayaran -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Metode Pembayaran <span class="text-danger">*</span></label>
                            
                            <div class="row g-3">
                                <!-- Tunai -->
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="metode_tunai" value="tunai" required>
                                    <label class="btn btn-outline-success w-100 py-4" for="metode_tunai">
                                        <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                                        <strong>TUNAI</strong>
                                    </label>
                                </div>

                                <!-- Debit Card -->
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="metode_debit" value="debit_card" required>
                                    <label class="btn btn-outline-primary w-100 py-4" for="metode_debit">
                                        <i class="bi bi-credit-card fs-1 d-block mb-2"></i>
                                        <strong>DEBIT CARD</strong>
                                    </label>
                                </div>

                                <!-- QRIS -->
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="metode_pembayaran" id="metode_qris" value="qris" required>
                                    <label class="btn btn-outline-warning w-100 py-4" for="metode_qris">
                                        <i class="bi bi-qr-code fs-1 d-block mb-2"></i>
                                        <strong>QRIS</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Tunai (Hidden by default) -->
                        <div id="formTunai" style="display: none;">
                            <hr>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                Masukkan jumlah uang yang diterima dari pelanggan
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Jumlah Uang Diterima <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Rp</span>
                                    <input 
                                        type="number" 
                                        name="jumlah_bayar" 
                                        id="jumlah_bayar" 
                                        class="form-control" 
                                        min="<?= ceil($grand_total) ?>"
                                        step="1"
                                        placeholder="0">
                                </div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Tagihan:</span>
                                        <span class="fw-bold">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Uang Diterima:</span>
                                        <span class="fw-bold" id="displayJumlahBayar">Rp 0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">Kembalian:</span>
                                        <span class="fs-4 fw-bold text-success" id="displayKembalian">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Proses Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.btn-check:checked + .btn-outline-success {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-check:checked + .btn-outline-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: black;
}

.btn-outline-success:hover,
.btn-outline-primary:hover,
.btn-outline-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodeTunai = document.getElementById('metode_tunai');
    const metodeDebit = document.getElementById('metode_debit');
    const metodeQris = document.getElementById('metode_qris');
    const formTunai = document.getElementById('formTunai');
    const jumlahBayarInput = document.getElementById('jumlah_bayar');
    const displayJumlahBayar = document.getElementById('displayJumlahBayar');
    const displayKembalian = document.getElementById('displayKembalian');
    
    const grandTotal = <?= $grand_total ?>; // GUNAKAN GRAND TOTAL (sudah include tax & service)

    // Show/hide form tunai
    function toggleFormTunai() {
        if (metodeTunai.checked) {
            formTunai.style.display = 'block';
            jumlahBayarInput.required = true;
        } else {
            formTunai.style.display = 'none';
            jumlahBayarInput.required = false;
            jumlahBayarInput.value = '';
            updateKembalian();
        }
    }

    metodeTunai.addEventListener('change', toggleFormTunai);
    metodeDebit.addEventListener('change', toggleFormTunai);
    metodeQris.addEventListener('change', toggleFormTunai);

    // Calculate kembalian
    function updateKembalian() {
        const jumlahBayar = parseFloat(jumlahBayarInput.value) || 0;
        const kembalian = Math.max(0, jumlahBayar - grandTotal);

        displayJumlahBayar.textContent = 'Rp ' + jumlahBayar.toLocaleString('id-ID');
        displayKembalian.textContent = 'Rp ' + kembalian.toLocaleString('id-ID');

        // Validasi
        if (jumlahBayar > 0 && jumlahBayar < grandTotal) {
            displayKembalian.classList.remove('text-success');
            displayKembalian.classList.add('text-danger');
            displayKembalian.textContent = 'KURANG!';
        } else {
            displayKembalian.classList.remove('text-danger');
            displayKembalian.classList.add('text-success');
        }
    }

    jumlahBayarInput.addEventListener('input', updateKembalian);

    // Validasi form sebelum submit
    document.getElementById('formPembayaran').addEventListener('submit', function(e) {
        if (metodeTunai.checked) {
            const jumlahBayar = parseFloat(jumlahBayarInput.value) || 0;
            if (jumlahBayar < grandTotal) {
                e.preventDefault();
                alert('Jumlah uang yang diterima tidak boleh kurang dari total tagihan!\n\nTotal: Rp ' + grandTotal.toLocaleString('id-ID'));
                jumlahBayarInput.focus();
                return false;
            }
        }
    });
});
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>s