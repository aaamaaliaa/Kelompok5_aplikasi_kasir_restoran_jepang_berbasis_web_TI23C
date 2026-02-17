<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('menu');
require_once '../config/database.php';

$error = '';

/* =========================================================
   FALLBACK UPLOAD FOTO (AMAN)
   ========================================================= */
if (!function_exists('handle_file_upload')) {
    function handle_file_upload($file) {
        if (empty($file['name'])) return '';

        $dir = '../uploads/menu/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) return false;
        if ($file['size'] > 2097152) return false;
        if (!getimagesize($file['tmp_name'])) return false;

        $name = 'menu_' . date('YmdHis') . '_' . rand(1000,9999) . '.' . $ext;
        return move_uploaded_file($file['tmp_name'], $dir.$name) ? $name : false;
    }
}

/* =========================================================
   GENERATE KODE MENU OTOMATIS
   ========================================================= */
if (!function_exists('generateKodeMenu')) {
    function generateKodeMenu($connection, $kategori) {
        $map = [
            'drink'   => 'DRI',
            'dessert' => 'DES',
            'sushi'   => 'SUS',
            'ramen'   => 'RAM',
            'side_dish' => 'SID'
        ];

        $prefix = $map[$kategori] ?? 'MN';

        $stmt = mysqli_prepare($connection,
            "SELECT kode_menu 
             FROM menu 
             WHERE kode_menu LIKE CONCAT(?, '-%')
             ORDER BY kode_menu DESC 
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $prefix);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $last = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        $num = $last ? ((int)substr($last['kode_menu'], -3) + 1) : 1;
        return sprintf('%s-%03d', $prefix, $num);
    }
}

/* =========================================================
   PROSES SIMPAN
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kategori     = strtolower(trim($_POST['kategori'] ?? ''));
    $nama_menu    = trim($_POST['nama_menu'] ?? '');
    $harga        = trim($_POST['harga'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $status_aktif = trim($_POST['status_aktif'] ?? 'aktif');

    if (!$kategori || !$nama_menu || !$harga) {
        $error = "Kategori, Nama Menu, dan Harga wajib diisi.";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $error = "Harga harus berupa angka positif.";
    }

    $foto = '';
    if (!$error && isset($_FILES['foto'])) {
        $foto = handle_file_upload($_FILES['foto']);
        if ($foto === false) {
            $error = "Upload foto gagal (jpg/png/webp max 2MB).";
        }
    }

    if (!$error) {
        $kode_menu = generateKodeMenu($connection, $kategori);

        $stmt = mysqli_prepare($connection,
            "INSERT INTO menu
            (kode_menu, nama_menu, kategori, harga, deskripsi, status_aktif, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssdsss",
            $kode_menu,
            $nama_menu,
            $kategori,
            $harga,
            $deskripsi,
            $status_aktif,
            $foto
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                alert('Menu berhasil ditambahkan');
                window.location.href = 'index.php';
            </script>";
            exit;
        } else {
            $error = "Gagal menyimpan menu.";
        }
    }
}
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-plus-circle"></i> Tambah Menu Baru
            </h2>
            <p class="text-muted mb-0">Tambahkan menu makanan atau minuman baru</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Form Full Width -->
    <form method="POST" enctype="multipart/form-data">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-card-text"></i> Informasi Menu</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="drink">🥤 Drink</option>
                                <option value="dessert">🍰 Dessert</option>
                                <option value="sushi">🍣 Sushi</option>
                                <option value="ramen">🍜 Ramen</option>
                                <option value="side_dish">🍛 Side Dish</option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Kode menu akan dibuat otomatis
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Menu <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_menu" class="form-control" 
                                   placeholder="Contoh: Tonkotsu Ramen" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Harga <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga" class="form-control" 
                                       placeholder="50000" min="0" step="1000" required>
                            </div>
                            <small class="text-muted">Masukkan harga dalam Rupiah</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status_aktif" class="form-select">
                                <option value="aktif" selected>✅ Aktif (Tersedia)</option>
                                <option value="nonaktif">❌ Nonaktif (Tidak Tersedia)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Foto Menu</label>
                            <input type="file" name="foto" class="form-control" 
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   onchange="previewImage(event)">
                            <small class="text-muted">
                                Format: JPG, PNG, WEBP (Max 2MB)
                            </small>
                        </div>

                        <!-- Preview Foto -->
                        <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                            <label class="form-label fw-bold">Preview Foto</label>
                            <div class="text-center">
                                <img id="imagePreview" src="" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" 
                              placeholder="Deskripsi lengkap menu (opsional)"></textarea>
                    <small class="text-muted">
                        Jelaskan menu secara detail: bahan, rasa, dll.
                    </small>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Menu
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Preview image sebelum upload
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    } else {
        container.style.display = 'none';
    }
}

// Format harga input
document.querySelector('input[name="harga"]').addEventListener('input', function(e) {
    // Hapus karakter non-digit
    this.value = this.value.replace(/\D/g, '');
});
</script>

<style>
.card {
    transition: transform 0.2s;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.img-thumbnail {
    border-radius: 8px;
}
</style>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>