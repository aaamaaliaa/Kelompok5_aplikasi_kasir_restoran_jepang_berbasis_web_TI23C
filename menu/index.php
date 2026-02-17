<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('menu');

require_once '../config/database.php';

// Hitung statistik
$total_menu = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM menu"))['total'];
$total_aktif = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM menu WHERE status_aktif = 'aktif'"))['total'];
$total_nonaktif = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM menu WHERE status_aktif = 'nonaktif'"))['total'];

// Query data menu
$result = mysqli_query($connection, "SELECT * FROM `menu` ORDER BY id_menu DESC");
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
                <i class="bi bi-list-ul"></i> Daftar Menu
            </h2>
            <p class="text-muted mb-0">Kelola menu makanan dan minuman</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Menu
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Menu</p>
                            <h3 class="mb-0 fw-bold"><?= $total_menu ?></h3>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #e3f2fd;">
                            <i class="bi bi-card-list text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Menu Aktif</p>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_aktif ?></h3>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #e8f5e9;">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Menu Nonaktif</p>
                            <h3 class="mb-0 fw-bold text-secondary"><?= $total_nonaktif ?></h3>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #f5f5f5;">
                            <i class="bi bi-x-circle text-secondary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-table"></i> List Menu
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Foto</th>
                            <th width="10%">Kode Menu</th>
                            <th width="18%">Nama Menu</th>
                            <th width="12%">Kategori</th>
                            <th width="12%">Harga</th>
                            <th width="20%">Deskripsi</th>
                            <th width="8%">Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= htmlspecialchars($row['id_menu']) ?></td>

                            <td class="text-center">
                                <?php if (!empty($row['foto'])): ?>
                                    <img src="../uploads/menu/<?= htmlspecialchars($row['foto']) ?>"
                                         alt="<?= htmlspecialchars($row['nama_menu']) ?>"
                                         class="rounded"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded d-flex align-items-center justify-content-center bg-light" 
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($row['kode_menu']) ?>
                                </span>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($row['nama_menu']) ?></strong>
                            </td>

                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($row['kategori']) ?>
                                </span>
                            </td>

                            <td>
                                <strong class="text-success">
                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                </strong>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) ?>
                                    <?= strlen($row['deskripsi']) > 50 ? '...' : '' ?>
                                </small>
                            </td>

                            <td>
                                <?php if ($row['status_aktif'] === 'aktif'): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?= $row['id_menu'] ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $row['id_menu'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus menu <?= htmlspecialchars($row['nama_menu']) ?>?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ddd;"></i>
                <p class="text-muted mt-3 mb-2">Belum ada menu yang ditambahkan</p>
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Menu Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>