<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
require_once '../config/database.php';

// Ambil semua data meja
$query = "SELECT * FROM meja ORDER BY nomor_meja ASC";
$result = mysqli_query($connection, $query);
$meja_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $meja_list[] = $row;
}

// Hitung statistik
$total_meja = count($meja_list);
$meja_kosong = count(array_filter($meja_list, fn($m) => $m['status'] === 'kosong'));
$meja_terisi = count(array_filter($meja_list, fn($m) => $m['status'] === 'terisi'));
$meja_reserved = count(array_filter($meja_list, fn($m) => $m['status'] === 'reserved'));

$page_title = 'Manajemen Meja';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-grid-3x3"></i> Manajemen Meja</h2>
            <p class="text-muted mb-0">Kelola meja dan ketersediaan tempat duduk</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Meja
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Meja</h6>
                            <h3 class="mb-0"><?= $total_meja ?></h3>
                            <small class="text-muted">Semua meja</small>
                        </div>
                        <div class="fs-1 text-primary">
                            <i class="bi bi-grid-3x3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Kosong</h6>
                            <h3 class="mb-0 text-success"><?= $meja_kosong ?></h3>
                            <small class="text-muted">Tersedia</small>
                        </div>
                        <div class="fs-1 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Terisi</h6>
                            <h3 class="mb-0 text-danger"><?= $meja_terisi ?></h3>
                            <small class="text-muted">Sedang dipakai</small>
                        </div>
                        <div class="fs-1 text-danger">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Reserved</h6>
                            <h3 class="mb-0 text-warning"><?= $meja_reserved ?></h3>
                            <small class="text-muted">Dipesan</small>
                        </div>
                        <div class="fs-1 text-warning">
                            <i class="bi bi-bookmark-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Grid View -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-layout-three-columns"></i> Layout Meja</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($meja_list as $meja): ?>
                <div class="col-md-3">
                    <div class="card border-<?= $meja['status'] === 'kosong' ? 'success' : ($meja['status'] === 'terisi' ? 'danger' : 'warning') ?> shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 mb-2">
                                <?php if ($meja['status'] === 'kosong'): ?>
                                    <i class="bi bi-table text-success"></i>
                                <?php elseif ($meja['status'] === 'terisi'): ?>
                                    <i class="bi bi-people-fill text-danger"></i>
                                <?php else: ?>
                                    <i class="bi bi-bookmark-fill text-warning"></i>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="mb-1"><?= htmlspecialchars($meja['nomor_meja']) ?></h5>
                            
                            <span class="badge bg-<?= $meja['status'] === 'kosong' ? 'success' : ($meja['status'] === 'terisi' ? 'danger' : 'warning') ?> mb-2">
                                <?= strtoupper($meja['status']) ?>
                            </span>
                            
                            <div class="text-muted small mb-2">
                                <i class="bi bi-person-fill"></i> Kapasitas: <?= $meja['kapasitas'] ?> orang
                            </div>
                            
                            <?php if ($meja['lokasi']): ?>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($meja['lokasi']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="btn-group w-100 mt-2">
                                <a href="edit.php?id=<?= $meja['id_meja'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-<?= $meja['status'] === 'kosong' ? 'success' : 'secondary' ?>" 
                                        onclick="changeStatus(<?= $meja['id_meja'] ?>, '<?= $meja['status'] === 'kosong' ? 'terisi' : 'kosong' ?>')">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <a href="delete.php?id=<?= $meja['id_meja'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Hapus meja ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Table List View -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Meja</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Nomor Meja</th>
                            <th width="10%">Kapasitas</th>
                            <th width="15%">Status</th>
                            <th width="20%">Lokasi</th>
                            <th width="20%">Deskripsi</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($meja_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada data meja. <a href="add.php">Tambah meja baru</a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($meja_list as $meja): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($meja['nomor_meja']) ?></strong></td>
                            <td>
                                <i class="bi bi-person-fill"></i> <?= $meja['kapasitas'] ?> orang
                            </td>
                            <td>
                                <span class="badge bg-<?= $meja['status'] === 'kosong' ? 'success' : ($meja['status'] === 'terisi' ? 'danger' : 'warning') ?>">
                                    <?= strtoupper($meja['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($meja['lokasi'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($meja['deskripsi'] ?: '-') ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $meja['id_meja'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $meja['id_meja'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Hapus meja ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function changeStatus(id, newStatus) {
    if (confirm('Ubah status meja menjadi ' + newStatus.toUpperCase() + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'change_status.php';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_meja';
        inputId.value = id;
        
        const inputStatus = document.createElement('input');
        inputStatus.type = 'hidden';
        inputStatus.name = 'status';
        inputStatus.value = newStatus;
        
        form.appendChild(inputId);
        form.appendChild(inputStatus);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>