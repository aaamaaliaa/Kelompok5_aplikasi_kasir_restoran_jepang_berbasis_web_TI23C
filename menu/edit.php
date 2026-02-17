<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('menu');

require_once '../config/database.php';

define('UPLOAD_DIR', '../uploads/menu/');

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

/* Ambil data menu */
$stmt = mysqli_prepare($connection,
    "SELECT id_menu, kode_menu, nama_menu, kategori, harga, deskripsi, status_aktif, foto
     FROM menu
     WHERE id_menu = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$menu = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$menu) {
    redirect('index.php');
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode_menu    = trim($_POST['kode_menu'] ?? '');
    $nama_menu    = trim($_POST['nama_menu'] ?? '');
    $kategori     = trim($_POST['kategori'] ?? '');
    $harga        = trim($_POST['harga'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $status_aktif = trim($_POST['status_aktif'] ?? '');

    $foto = $menu['foto']; // default pakai foto lama

    if (empty($kode_menu) || empty($nama_menu) || empty($harga)) {
        $error = "Kode Menu, Nama Menu, dan Harga wajib diisi.";
    }

    /* Upload foto baru (jika ada) */
    if (!$error && !empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Format foto harus JPG, PNG, atau WEBP.";
        } else {
            $newName = uniqid('menu_') . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $newName)) {

                // hapus foto lama (jika ada)
                if ($foto && file_exists(UPLOAD_DIR . $foto)) {
                    unlink(UPLOAD_DIR . $foto);
                }

                $foto = $newName;
            } else {
                $error = "Gagal upload foto.";
            }
        }
    }

    if (!$error) {
        $stmt = mysqli_prepare($connection,
            "UPDATE menu
             SET kode_menu=?, nama_menu=?, kategori=?, harga=?, deskripsi=?, status_aktif=?, foto=?
             WHERE id_menu=?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "sssdsssi",
            $kode_menu,
            $nama_menu,
            $kategori,
            $harga,
            $deskripsi,
            $status_aktif,
            $foto,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            header("Location: index.php?success=1");
            exit;
        } else {
            $error = "Gagal update menu.";
        }
        mysqli_stmt_close($stmt);
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
                <i class="bi bi-pencil-square"></i> Edit Menu
            </h2>
            <p class="text-muted mb-0">Perbarui informasi menu makanan atau minuman</p>
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

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <?= htmlspecialchars($success) ?>
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
                                Kode Menu <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_menu" class="form-control" 
                                   value="<?= htmlspecialchars($menu['kode_menu']) ?>" required>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Kode unik untuk identifikasi menu
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Menu <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_menu" class="form-control" 
                                   value="<?= htmlspecialchars($menu['nama_menu']) ?>" 
                                   placeholder="Contoh: Tonkotsu Ramen" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="drink" <?= $menu['kategori']=='drink'?'selected':'' ?>>🥤 Drink</option>
                                <option value="dessert" <?= $menu['kategori']=='dessert'?'selected':'' ?>>🍰 Dessert</option>
                                <option value="sushi" <?= $menu['kategori']=='sushi'?'selected':'' ?>>🍣 Sushi</option>
                                <option value="ramen" <?= $menu['kategori']=='ramen'?'selected':'' ?>>🍜 Ramen</option>
                                <option value="side_dish" <?= $menu['kategori']=='side_dish'?'selected':'' ?>>🍛 Side Dish</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Harga <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga" class="form-control" 
                                       value="<?= htmlspecialchars($menu['harga']) ?>" 
                                       placeholder="50000" min="0" step="1000" required>
                            </div>
                            <small class="text-muted">Masukkan harga dalam Rupiah</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status_aktif" class="form-select">
                                <option value="aktif" <?= $menu['status_aktif']=='aktif'?'selected':'' ?>>✅ Aktif (Tersedia)</option>
                                <option value="nonaktif" <?= $menu['status_aktif']=='nonaktif'?'selected':'' ?>>❌ Nonaktif (Tidak Tersedia)</option>
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
                        <div class="mb-3" id="imagePreviewContainer" <?php if (!$menu['foto']): ?>style="display: none;"<?php endif; ?>>
                            <label class="form-label fw-bold">Preview Foto</label>
                            <div class="text-center">
                                <img id="imagePreview" 
                                     src="<?= $menu['foto'] ? '../uploads/menu/'.$menu['foto'] : '' ?>" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" 
                              placeholder="Deskripsi lengkap menu (opsional)"><?= htmlspecialchars($menu['deskripsi']) ?></textarea>
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
                        <i class="bi bi-save"></i> Simpan Perubahan
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