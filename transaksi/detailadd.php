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

/* ===== Ambil nomor bukti ===== */
$nomor_bukti = $_GET['nomor_bukti'] ?? '';
if (!$nomor_bukti) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

/* ===== Simpan item ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $id_menu = trim($_POST['id_menu'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $catatan_item = trim($_POST['catatan_item'] ?? '');

    if (!$id_menu || $jumlah <= 0) {
        $error = "Menu dan Jumlah wajib diisi.";
    }

    if (!$error) {
        // Ambil harga menu dari database
        $stmt_harga = mysqli_prepare($connection, "SELECT harga FROM menu WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt_harga, "i", $id_menu);
        mysqli_stmt_execute($stmt_harga);
        $result_harga = mysqli_stmt_get_result($stmt_harga);
        $menu_data = mysqli_fetch_assoc($result_harga);
        mysqli_stmt_close($stmt_harga);

        if (!$menu_data) {
            $error = "Menu tidak ditemukan.";
        } else {
            $harga = $menu_data['harga'];
            $subtotal = $harga * $jumlah;

            // Insert ke detail_transaksi dengan subtotal
            $stmt = mysqli_prepare(
                $connection,
                "INSERT INTO detail_transaksi
                (nomor_bukti, id_menu, jumlah, harga, subtotal, catatan_item)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "siidds",
                $nomor_bukti,
                $id_menu,
                $jumlah,
                $harga,
                $subtotal,
                $catatan_item
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                // Update total transaksi di tabel master
                $sql_update_total = "UPDATE transaksi 
                                     SET total_bayar = (
                                         SELECT COALESCE(SUM(subtotal), 0) 
                                         FROM detail_transaksi 
                                         WHERE nomor_bukti = ?
                                     )
                                     WHERE nomor_bukti = ?";
                $stmt_update = mysqli_prepare($connection, $sql_update_total);
                mysqli_stmt_bind_param($stmt_update, "ss", $nomor_bukti, $nomor_bukti);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);

                header("Location: detail.php?nomor_bukti=" . urlencode($nomor_bukti));
                exit;
            } else {
                $error = "Gagal menyimpan item: " . mysqli_error($connection);
            }
        }
    }
}

// Ambil data menu untuk dropdown
$query_menu = "SELECT id_menu, kode_menu, nama_menu, harga, kategori, deskripsi
               FROM menu 
               WHERE status_aktif = 'aktif' 
               ORDER BY kategori, nama_menu";
$result_menu = mysqli_query($connection, $query_menu);

$csrfToken = generateCSRFToken();
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Tambah Item Transaksi</h2>
            <p class="text-muted mb-0">Nomor Bukti: <strong><?= htmlspecialchars($nomor_bukti) ?></strong></p>
        </div>
        <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>

    <?php if ($success): ?>
        <?= showAlert($success, 'success') ?>
    <?php endif; ?>

    <div class="row">
        <!-- Form Input -->
        <div class="col-lg-8">
            <form method="POST" id="formTambahItem" class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Form Tambah Item</h5>
                </div>
                <div class="card-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <!-- Pilih Menu -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Menu <span class="text-danger">*</span></label>
                        <select name="id_menu" id="id_menu" class="form-control form-select" required>
                            <option value="">-- Pilih Menu --</option>
                            <?php 
                            $current_category = '';
                            while ($menu = mysqli_fetch_assoc($result_menu)): 
                                // Group by category
                                if ($current_category != $menu['kategori']) {
                                    if ($current_category != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($menu['kategori']) . '">';
                                    $current_category = $menu['kategori'];
                                }
                            ?>
                                <option 
                                    value="<?= $menu['id_menu'] ?>"
                                    data-kode="<?= htmlspecialchars($menu['kode_menu']) ?>"
                                    data-nama="<?= htmlspecialchars($menu['nama_menu']) ?>"
                                    data-harga="<?= $menu['harga'] ?>"
                                    data-kategori="<?= htmlspecialchars($menu['kategori']) ?>"
                                    data-deskripsi="<?= htmlspecialchars($menu['deskripsi'] ?? '') ?>">
                                    [<?= htmlspecialchars($menu['kode_menu']) ?>] <?= htmlspecialchars($menu['nama_menu']) ?> - 
                                    Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
                                </option>
                            <?php 
                            endwhile; 
                            if ($current_category != '') echo '</optgroup>';
                            ?>
                        </select>
                        <small class="text-muted">Pilih menu yang ingin ditambahkan ke transaksi</small>
                    </div>

                    <!-- Jumlah -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" id="btnKurang">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input 
                                    type="number" 
                                    name="jumlah" 
                                    id="jumlah" 
                                    class="form-control text-center" 
                                    min="1" 
                                    value="1"
                                    required>
                                <button type="button" class="btn btn-outline-secondary" id="btnTambah">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Harga Satuan</label>
                            <input 
                                type="text" 
                                id="harga_display" 
                                class="form-control bg-light" 
                                readonly
                                placeholder="Pilih menu terlebih dahulu">
                        </div>
                    </div>

                    <!-- Subtotal -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Subtotal</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">
                                <i class="bi bi-cash-stack"></i>
                            </span>
                            <input 
                                type="text" 
                                id="subtotal_display" 
                                class="form-control fw-bold text-end fs-4" 
                                readonly
                                value="Rp 0"
                                style="background-color: #e8f5e9;">
                        </div>
                    </div>

                    <!-- Catatan Item -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan Item <span class="text-muted">(Opsional)</span></label>
                        <textarea 
                            name="catatan_item" 
                            class="form-control" 
                            rows="3"
                            placeholder="Contoh: Tidak pakai wasabi, pedas level 3, extra topping, dll"></textarea>
                        <small class="text-muted">Catatan khusus untuk item ini</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" 
                           class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> Tambah Item ke Pesanan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview Menu -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Preview Menu</h5>
                </div>
                <div class="card-body">
                    <div id="menuPreview" class="text-center">
                        <!-- Default State -->
                        <div id="defaultPreview">
                            <div class="mb-3">
                                <i class="bi bi-image" style="font-size: 80px; color: #ddd;"></i>
                            </div>
                            <h5 class="text-muted">Pilih menu untuk melihat detail</h5>
                            <p class="small text-muted">Informasi menu akan ditampilkan di sini</p>
                        </div>

                        <!-- Menu Selected State -->
                        <div id="selectedPreview" style="display: none;">
                            <!-- Kode Menu -->
                            <div class="mb-3">
                                <span id="previewKode" class="badge bg-secondary fs-6"></span>
                            </div>

                            <!-- Nama Menu -->
                            <h4 id="previewNama" class="fw-bold mb-2"></h4>

                            <!-- Kategori -->
                            <p class="mb-3">
                                <span id="previewKategori" class="badge bg-primary"></span>
                            </p>

                            <!-- Deskripsi -->
                            <div class="alert alert-light mb-3" role="alert">
                                <small id="previewDeskripsi" class="text-muted"></small>
                            </div>

                            <!-- Harga -->
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted d-block">Harga per Porsi</small>
                                <h3 id="previewHarga" class="text-primary fw-bold mb-0"></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles */
.form-label.fw-bold {
    color: #2c3e50;
}

#subtotal_display {
    border: 2px solid #4caf50 !important;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.input-group-text {
    border: 1px solid #ced4da;
}

select.form-select option {
    padding: 8px;
}

optgroup {
    font-weight: bold;
    color: #2c3e50;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectMenu = document.getElementById('id_menu');
    const inputJumlah = document.getElementById('jumlah');
    const btnKurang = document.getElementById('btnKurang');
    const btnTambah = document.getElementById('btnTambah');
    const hargaDisplay = document.getElementById('harga_display');
    const subtotalDisplay = document.getElementById('subtotal_display');
    
    const defaultPreview = document.getElementById('defaultPreview');
    const selectedPreview = document.getElementById('selectedPreview');
    const previewKode = document.getElementById('previewKode');
    const previewNama = document.getElementById('previewNama');
    const previewKategori = document.getElementById('previewKategori');
    const previewDeskripsi = document.getElementById('previewDeskripsi');
    const previewHarga = document.getElementById('previewHarga');

    let hargaSatuan = 0;

    // Fungsi format rupiah
    function formatRupiah(angka) {
        return 'Rp ' + angka.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Fungsi update preview dan hitung subtotal
    function updateMenuInfo() {
        const selectedOption = selectMenu.options[selectMenu.selectedIndex];
        
        if (selectMenu.value === '') {
            // Reset preview
            defaultPreview.style.display = 'block';
            selectedPreview.style.display = 'none';
            hargaDisplay.value = '';
            subtotalDisplay.value = 'Rp 0';
            hargaSatuan = 0;
            return;
        }

        // Ambil data dari option
        const kode = selectedOption.dataset.kode;
        const nama = selectedOption.dataset.nama;
        const harga = parseFloat(selectedOption.dataset.harga);
        const kategori = selectedOption.dataset.kategori;
        const deskripsi = selectedOption.dataset.deskripsi || 'Tidak ada deskripsi';

        hargaSatuan = harga;

        // Update preview
        defaultPreview.style.display = 'none';
        selectedPreview.style.display = 'block';
        
        previewKode.textContent = kode;
        previewNama.textContent = nama;
        previewKategori.textContent = kategori;
        previewDeskripsi.textContent = deskripsi;
        previewHarga.textContent = formatRupiah(harga);

        // Update harga display
        hargaDisplay.value = formatRupiah(harga);

        // Hitung subtotal
        hitungSubtotal();
    }

    // Fungsi hitung subtotal
    function hitungSubtotal() {
        const jumlah = parseInt(inputJumlah.value) || 0;
        const subtotal = hargaSatuan * jumlah;
        subtotalDisplay.value = formatRupiah(subtotal);
    }

    // Fungsi tambah/kurang jumlah
    btnTambah.addEventListener('click', function() {
        let jumlah = parseInt(inputJumlah.value) || 1;
        inputJumlah.value = jumlah + 1;
        hitungSubtotal();
    });

    btnKurang.addEventListener('click', function() {
        let jumlah = parseInt(inputJumlah.value) || 1;
        if (jumlah > 1) {
            inputJumlah.value = jumlah - 1;
            hitungSubtotal();
        }
    });

    // Event listeners
    selectMenu.addEventListener('change', updateMenuInfo);
    inputJumlah.addEventListener('input', hitungSubtotal);

    // Validasi form
    document.getElementById('formTambahItem').addEventListener('submit', function(e) {
        if (selectMenu.value === '' || inputJumlah.value <= 0) {
            e.preventDefault();
            alert('⚠️ Mohon pilih menu dan masukkan jumlah yang valid!');
            return false;
        }
        
        // Konfirmasi sebelum submit
        const menuText = selectMenu.options[selectMenu.selectedIndex].text;
        const jumlah = inputJumlah.value;
        const subtotal = subtotalDisplay.value;
        
        if (!confirm(`Tambahkan item ini ke pesanan?\n\n${menuText}\nJumlah: ${jumlah}\nSubtotal: ${subtotal}`)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>