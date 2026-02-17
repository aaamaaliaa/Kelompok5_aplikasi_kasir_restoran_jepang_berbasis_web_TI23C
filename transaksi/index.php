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

// Hitung statistik
$total_transaksi = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi"))['total'];
$total_lunas = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE status_bayar = 'lunas'"))['total'];
$total_belum_lunas = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE status_bayar = 'belum_lunas'"))['total'];

$result = mysqli_query(
    $connection,
    "SELECT * FROM transaksi ORDER BY created_at DESC"
);
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
                <i class="bi bi-receipt"></i> Daftar Transaksi
            </h2>
            <p class="text-muted mb-0">Kelola transaksi penjualan</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Transaksi
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Transaksi</p>
                            <h3 class="mb-0 fw-bold"><?= $total_transaksi ?></h3>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #e3f2fd;">
                            <i class="bi bi-receipt-cutoff text-primary" style="font-size: 1.5rem;"></i>
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
                            <p class="text-muted mb-1 small">Transaksi Lunas</p>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_lunas ?></h3>
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
                            <p class="text-muted mb-1 small">Belum Lunas</p>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_belum_lunas ?></h3>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #fff3e0;">
                            <i class="bi bi-clock-history text-warning" style="font-size: 1.5rem;"></i>
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
                <i class="bi bi-table"></i> List Transaksi
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Nomor Bukti</th>
                            <th width="8%">Tanggal</th>
                            <th width="7%">Waktu</th>
                            <th width="8%">Meja</th>
                            <th width="12%">Pelanggan</th>
                            <th width="10%">Total Bayar</th>
                            <th width="8%">Status</th>
                            <th width="15%">Catatan</th>
                            <th width="10%">Dibuat</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($row['nomor_bukti']) ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($row['tanggal']) ?></td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($row['waktu']) ?>
                                </small>
                            </td>

                            <td>
                                <?php 
                                if ($row['id_meja']) {
                                    // Query nama meja
                                    $stmt_meja = mysqli_prepare($connection, "SELECT nomor_meja FROM meja WHERE id_meja = ?");
                                    mysqli_stmt_bind_param($stmt_meja, "i", $row['id_meja']);
                                    mysqli_stmt_execute($stmt_meja);
                                    $result_meja = mysqli_stmt_get_result($stmt_meja);
                                    $meja_data = mysqli_fetch_assoc($result_meja);
                                    mysqli_stmt_close($stmt_meja);
                                    
                                    echo '<span class="badge bg-info">';
                                    echo $meja_data ? htmlspecialchars($meja_data['nomor_meja']) : 'Meja #' . $row['id_meja'];
                                    echo '</span>';
                                } else {
                                    echo '<span class="badge bg-dark">Takeaway</span>';
                                }
                                ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                            </td>

                            <td>
                                <strong class="text-success">
                                    Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                                </strong>
                            </td>

                            <td>
                                <?php if ($row['status_bayar'] === 'lunas'): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Lunas
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock-history"></i> Belum Lunas
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr($row['catatan'], 0, 30)) ?>
                                    <?= strlen($row['catatan']) > 30 ? '...' : '' ?>
                                </small>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($row['created_at']) ?>
                                </small>
                            </td>

                            <td>
                                <div class="btn-group" role="group">
                                    <a href="detail.php?nomor_bukti=<?= urlencode($row['nomor_bukti']) ?>" 
                                       class="btn btn-sm btn-info"
                                       title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <?php if ($row['status_bayar'] !== 'lunas'): ?>
                                        <a href="edit.php?nomor_bukti=<?= urlencode($row['nomor_bukti']) ?>" 
                                           class="btn btn-sm btn-warning"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <a href="delete.php?nomor_bukti=<?= urlencode($row['nomor_bukti']) ?>"
                                           class="btn btn-sm btn-danger"
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus transaksi <?= htmlspecialchars($row['nomor_bukti']) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
                <p class="text-muted mt-3 mb-2">Belum ada transaksi yang tercatat</p>
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Transaksi Pertama
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