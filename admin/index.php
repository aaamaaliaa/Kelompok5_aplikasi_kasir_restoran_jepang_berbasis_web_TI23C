<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // atur nilai menjadi 1 jika di publish ke real public server
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
require_once '../lib/functions.php';
requireAuth();

if (getUserRole() !== 'admin') {
    redirect('../login.php');
}

// Koneksi database
require_once '../config/database.php';

// Get current user info
$current_user = $_SESSION['username'] ?? 'Admin';

// =====================================================
// STATISTIK DASHBOARD
// =====================================================

// 1. Total Menu
$query_menu = "SELECT COUNT(*) as total FROM menu";
$result_menu = mysqli_query($connection, $query_menu);
$total_menu = mysqli_fetch_assoc($result_menu)['total'];

// 2. Transaksi Hari Ini
$today = date('Y-m-d');
$query_transaksi_today = "SELECT COUNT(*) as total FROM transaksi WHERE tanggal = ?";
$stmt = mysqli_prepare($connection, $query_transaksi_today);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result_transaksi_today = mysqli_stmt_get_result($stmt);
$transaksi_hari_ini = mysqli_fetch_assoc($result_transaksi_today)['total'];
mysqli_stmt_close($stmt);

// 3. Pendapatan Hari Ini (hanya yang lunas)
$query_pendapatan_today = "SELECT SUM(grand_total) as total FROM transaksi WHERE tanggal = ? AND status_bayar = 'lunas'";
$stmt = mysqli_prepare($connection, $query_pendapatan_today);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result_pendapatan = mysqli_stmt_get_result($stmt);
$pendapatan_hari_ini = mysqli_fetch_assoc($result_pendapatan)['total'] ?? 0;
mysqli_stmt_close($stmt);

// 4. Pesanan Pending
$query_pending = "SELECT COUNT(*) as total FROM transaksi WHERE status_bayar != 'lunas'";
$result_pending = mysqli_query($connection, $query_pending);
$pesanan_pending = mysqli_fetch_assoc($result_pending)['total'];

// =====================================================
// TRANSAKSI TERBARU (5 terakhir)
// =====================================================
$query_latest = "SELECT t.*, m.nomor_meja 
                 FROM transaksi t 
                 LEFT JOIN meja m ON t.id_meja = m.id_meja 
                 ORDER BY t.created_at DESC 
                 LIMIT 5";
$result_latest = mysqli_query($connection, $query_latest);
$transaksi_terbaru = [];
while ($row = mysqli_fetch_assoc($result_latest)) {
    $transaksi_terbaru[] = $row;
}

// =====================================================
// MENU TERLARIS (5 teratas hari ini)
// =====================================================
$query_bestseller = "SELECT m.nama_menu, m.kategori, m.harga, SUM(dt.jumlah) as total_terjual
                     FROM detail_transaksi dt
                     JOIN menu m ON dt.id_menu = m.id_menu
                     JOIN transaksi t ON dt.nomor_bukti = t.nomor_bukti
                     WHERE t.tanggal = ? AND t.status_bayar = 'lunas'
                     GROUP BY dt.id_menu
                     ORDER BY total_terjual DESC
                     LIMIT 5";
$stmt = mysqli_prepare($connection, $query_bestseller);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result_bestseller = mysqli_stmt_get_result($stmt);
$menu_terlaris = [];
while ($row = mysqli_fetch_assoc($result_bestseller)) {
    $menu_terlaris[] = $row;
}
mysqli_stmt_close($stmt);

?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="mb-4">
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">
            Welcome, <strong><?= htmlspecialchars($current_user) ?></strong>! 
            <span class="ms-2">📅 <?= date('l, d F Y') ?></span>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Menu -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Menu</p>
                            <h2 class="mb-0 fw-bold"><?= number_format($total_menu) ?></h2>
                            <small class="text-muted">Item tersedia</small>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #e3f2fd;">
                            <i class="bi bi-list-ul text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Transaksi Hari Ini</p>
                            <h2 class="mb-0 fw-bold text-success"><?= number_format($transaksi_hari_ini) ?></h2>
                            <small class="text-muted">Total transaksi</small>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #e8f5e9;">
                            <i class="bi bi-receipt text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendapatan Hari Ini -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pendapatan Hari Ini</p>
                            <h2 class="mb-0 fw-bold text-warning">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></h2>
                            <small class="text-muted">Transaksi lunas</small>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #fff3e0;">
                            <i class="bi bi-currency-dollar text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesanan Pending -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pesanan Pending</p>
                            <h2 class="mb-0 fw-bold text-danger"><?= number_format($pesanan_pending) ?></h2>
                            <small class="text-muted">Belum lunas</small>
                        </div>
                        <div class="rounded-3 p-3" style="background-color: #ffebee;">
                            <i class="bi bi-hourglass-split text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row g-3">
        <!-- Transaksi Terbaru -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history text-primary"></i> Transaksi Terbaru
                    </h5>
                    <a href="../transaksi/index.php" class="btn btn-sm btn-outline-primary">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transaksi_terbaru)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada transaksi hari ini</p>
                        <a href="../transaksi/add.php" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-plus-circle"></i> Buat Transaksi
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Bukti</th>
                                    <th>Pelanggan</th>
                                    <th>Meja</th>
                                    <th class="text-end">Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transaksi_terbaru as $trx): ?>
                                <tr>
                                    <td>
                                        <a href="../transaksi/detail.php?nomor_bukti=<?= urlencode($trx['nomor_bukti']) ?>" 
                                           class="text-decoration-none fw-bold">
                                            <?= htmlspecialchars($trx['nomor_bukti']) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($trx['waktu'])) ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($trx['nama_pelanggan'] ?: '-') ?></td>
                                    <td>
                                        <?php if ($trx['nomor_meja']): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($trx['nomor_meja']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Takeaway</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp <?= number_format($trx['grand_total'] ?: $trx['total_bayar'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($trx['status_bayar'] === 'lunas'): ?>
                                            <span class="badge bg-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Menu Terlaris -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-trophy text-warning"></i> Menu Terlaris
                    </h5>
                    <a href="../reports/index.php" class="btn btn-sm btn-outline-primary">
                        Laporan <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($menu_terlaris)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-basket" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada penjualan hari ini</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php 
                        $rank = 1;
                        foreach ($menu_terlaris as $menu): 
                        ?>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if ($rank <= 3): ?>
                                        <span class="badge bg-<?= $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'bronze') ?> rounded-circle" 
                                              style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                            <?= $rank ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted fw-bold" style="width: 32px; display: inline-block; text-align: center;">
                                            <?= $rank ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($menu['nama_menu']) ?></h6>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($menu['kategori']) ?> • 
                                        Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill">
                                        <?= $menu['total_terjual'] ?>x terjual
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="bi bi-lightning-charge text-warning"></i> Quick Actions
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="../transaksi/add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Transaksi Baru
                        </a>
                        <a href="../transaksi/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-receipt"></i> Daftar Transaksi
                        </a>
                        <a href="../menu/index.php" class="btn btn-outline-success">
                            <i class="bi bi-list-ul"></i> Kelola Menu
                        </a>
                        <a href="../meja/index.php" class="btn btn-outline-info">
                            <i class="bi bi-grid-3x3"></i> Manajemen Meja
                        </a>
                        <a href="../reports/index.php" class="btn btn-outline-warning">
                            <i class="bi bi-graph-up"></i> Laporan
                        </a>
                        <a href="../logout.php" class="btn btn-outline-danger ms-auto">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.badge.bg-bronze {
    background-color: #cd7f32 !important;
    color: white;
}

.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>