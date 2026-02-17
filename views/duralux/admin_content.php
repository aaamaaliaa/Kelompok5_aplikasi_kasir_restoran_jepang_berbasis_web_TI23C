<?php
// Fetch dashboard statistics
global $connection;

// Get statistics
$menuCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$todayTransactions = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(created_at) = CURDATE()"))['total'] ?? 0;
$todayRevenue = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE DATE(created_at) = CURDATE() AND status_bayar = 'lunas'"))['total'] ?? 0;
$pendingOrders = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE status_bayar != 'lunas'"))['total'] ?? 0;
$monthRevenue = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status_bayar = 'lunas'"))['total'] ?? 0;
?>

<div class="row">
    
    <!-- Total Menu -->
    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-lg bg-soft-primary text-primary">
                            <i class="feather-shopping-bag"></i>
                        </div>
                        <div>
                            <h2 class="fs-4 fw-bold text-dark mb-0"><?= number_format($menuCount) ?></h2>
                            <p class="fs-12 fw-medium text-muted mb-0">Total Menu</p>
                        </div>
                    </div>
                    <a href="<?= base_url('menu/index.php') ?>" class="btn btn-sm btn-light-brand">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <div class="pt-4 border-top border-dashed">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fs-12 fw-medium text-muted">Item Tersedia</span>
                        <span class="fs-12 fw-bold text-success"><i class="feather-trending-up fs-10 me-1"></i> Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaksi Hari Ini -->
    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-lg bg-soft-success text-success">
                            <i class="feather-credit-card"></i>
                        </div>
                        <div>
                            <h2 class="fs-4 fw-bold text-dark mb-0"><?= number_format($todayTransactions) ?></h2>
                            <p class="fs-12 fw-medium text-muted mb-0">Transaksi Hari Ini</p>
                        </div>
                    </div>
                    <a href="<?= base_url('transaksi/index.php') ?>" class="btn btn-sm btn-light-brand">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <div class="pt-4 border-top border-dashed">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fs-12 fw-medium text-muted">Progress</span>
                        <span class="fs-12 fw-bold text-success"><i class="feather-trending-up fs-10 me-1"></i> +8.3%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pendapatan Hari Ini -->
    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-lg bg-soft-warning text-warning">
                            <i class="feather-dollar-sign"></i>
                        </div>
                        <div>
                            <h2 class="fs-4 fw-bold text-dark mb-0">Rp <?= number_format($todayRevenue/1000, 0) ?>K</h2>
                            <p class="fs-12 fw-medium text-muted mb-0">Pendapatan Hari Ini</p>
                        </div>
                    </div>
                    <a href="<?= base_url('laporan/index.php') ?>" class="btn btn-sm btn-light-brand">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <div class="pt-4 border-top border-dashed">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fs-12 fw-medium text-muted">Growth</span>
                        <span class="fs-12 fw-bold text-success"><i class="feather-trending-up fs-10 me-1"></i> +12.5%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pesanan Pending -->
    <div class="col-xxl-3 col-md-6">
        <div class="card stretch stretch-full">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-lg bg-soft-danger text-danger">
                            <i class="feather-clock"></i>
                        </div>
                        <div>
                            <h2 class="fs-4 fw-bold text-dark mb-0"><?= number_format($pendingOrders) ?></h2>
                            <p class="fs-12 fw-medium text-muted mb-0">Pesanan Pending</p>
                        </div>
                    </div>
                    <a href="<?= base_url('transaksi/index.php?status=pending') ?>" class="btn btn-sm btn-light-brand">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <div class="pt-4 border-top border-dashed">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fs-12 fw-medium text-muted">Perlu Ditindak</span>
                        <span class="fs-12 fw-bold text-danger"><i class="feather-alert-circle fs-10 me-1"></i> Urgent</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<div class="row">
    
    <!-- Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Transaksi Terbaru</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                            <div data-bs-toggle="tooltip" title="Options">
                                <i class="feather-more-vertical"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="<?= base_url('transaksi/index.php') ?>" class="dropdown-item"><i class="feather-eye"></i>Lihat Semua</a>
                            <a href="<?= base_url('transaksi/add.php') ?>" class="dropdown-item"><i class="feather-plus"></i>Transaksi Baru</a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= base_url('laporan/transaksi.php') ?>" class="dropdown-item"><i class="feather-download"></i>Export</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $recentTransactions = mysqli_query($connection, "SELECT * FROM transaksi WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 6");
                if (mysqli_num_rows($recentTransactions) > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Bukti</th>
                                <th>Pelanggan</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trx = mysqli_fetch_assoc($recentTransactions)): ?>
                            <tr>
                                <td><a href="<?= base_url('transaksi/detail.php?id='.urlencode($trx['nomor_bukti'])) ?>" class="fw-bold text-primary"><?= htmlspecialchars($trx['nomor_bukti']) ?></a></td>
                                <td><?= htmlspecialchars($trx['nama_pelanggan']) ?></td>
                                <td><span class="badge bg-soft-info text-info">Meja #<?= htmlspecialchars($trx['id_meja']) ?></span></td>
                                <td class="fw-bold">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($trx['status_bayar'] == 'lunas'): ?>
                                        <span class="badge bg-soft-success text-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-soft-warning text-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('H:i', strtotime($trx['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <div class="avatar-text avatar-lg bg-soft-primary text-primary mb-3">
                        <i class="feather-inbox"></i>
                    </div>
                    <h6 class="fw-bold">Belum Ada Transaksi</h6>
                    <p class="text-muted">Transaksi hari ini akan muncul di sini</p>
                    <a href="<?= base_url('transaksi/add.php') ?>" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>Buat Transaksi
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Menu Terlaris -->
    <div class="col-lg-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Menu Terlaris</h5>
                <div class="card-header-action">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                            <div data-bs-toggle="tooltip" title="Options">
                                <i class="feather-more-vertical"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="<?= base_url('menu/index.php') ?>" class="dropdown-item"><i class="feather-eye"></i>Lihat Semua</a>
                            <a href="<?= base_url('menu/add.php') ?>" class="dropdown-item"><i class="feather-plus"></i>Tambah Menu</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $topMenus = mysqli_query($connection, "SELECT * FROM menu WHERE status_aktif = 'aktif' ORDER BY RAND() LIMIT 5");
                if (mysqli_num_rows($topMenus) > 0):
                ?>
                <ul class="list-unstyled mb-0">
                    <?php 
                    $index = 1;
                    $colors = ['primary', 'success', 'warning', 'danger', 'info'];
                    while ($menu = mysqli_fetch_assoc($topMenus)): 
                    ?>
                    <li class="mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-md bg-soft-<?= $colors[($index-1) % 5] ?> text-<?= $colors[($index-1) % 5] ?>">
                                <?php if (!empty($menu['foto'])): ?>
                                    <img src="<?= base_url('uploads/menu/'.htmlspecialchars($menu['foto'])) ?>" 
                                         alt="<?= htmlspecialchars($menu['nama_menu']) ?>" 
                                         class="img-fluid rounded">
                                <?php else: ?>
                                    <?= $index ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <a href="<?= base_url('menu/edit.php?id='.$menu['id_menu']) ?>" class="d-block">
                                    <span class="d-block fw-bold text-dark text-truncate-1-line"><?= htmlspecialchars($menu['nama_menu']) ?></span>
                                    <span class="fs-12 text-muted"><?= htmlspecialchars($menu['kategori']) ?></span>
                                </a>
                            </div>
                            <div class="text-end">
                                <span class="d-block fw-bold text-dark">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </li>
                    <?php 
                    $index++;
                    endwhile; 
                    ?>
                </ul>
                <?php else: ?>
                <div class="text-center py-4">
                    <div class="avatar-text avatar-lg bg-soft-primary text-primary mb-3">
                        <i class="feather-shopping-bag"></i>
                    </div>
                    <p class="text-muted mb-0">Belum ada menu tersedia</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>