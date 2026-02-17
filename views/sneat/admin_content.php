<?php
// Fetch dashboard statistics
global $connection;

// Get statistics
$menuCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM menu"))['total'] ?? 0;
$todayTransactions = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(created_at) = CURDATE()"))['total'] ?? 0;
$todayRevenue = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE DATE(created_at) = CURDATE() AND status_bayar = 'lunas'"))['total'] ?? 0;
$pendingOrders = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi WHERE status_bayar != 'lunas'"))['total'] ?? 0;
?>

<div class="row">
    <!-- Total Menu -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-food-menu bx-sm"></i>
                        </span>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt1">
                            <a class="dropdown-item" href="<?= base_url('menu/index.php') ?>">Lihat Detail</a>
                            <a class="dropdown-item" href="<?= base_url('menu/add.php') ?>">Tambah Menu</a>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Menu</span>
                <h3 class="card-title mb-2"><?= number_format($menuCount) ?></h3>
                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> Item Tersedia</small>
            </div>
        </div>
    </div>

    <!-- Transaksi Hari Ini -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="bx bx-receipt bx-sm"></i>
                        </span>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt2">
                            <a class="dropdown-item" href="<?= base_url('transaksi/index.php') ?>">Lihat Semua</a>
                            <a class="dropdown-item" href="<?= base_url('transaksi/add.php') ?>">Transaksi Baru</a>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Transaksi Hari Ini</span>
                <h3 class="card-title mb-2"><?= number_format($todayTransactions) ?></h3>
                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +8.3%</small>
            </div>
        </div>
    </div>

    <!-- Pendapatan Hari Ini -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-wallet bx-sm"></i>
                        </span>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                            <a class="dropdown-item" href="<?= base_url('laporan/index.php') ?>">Lihat Laporan</a>
                            <a class="dropdown-item" href="<?= base_url('laporan/daily.php') ?>">Laporan Harian</a>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Pendapatan Hari Ini</span>
                <h3 class="card-title text-nowrap mb-1">Rp <?= number_format($todayRevenue, 0, ',', '.') ?></h3>
                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +12.5%</small>
            </div>
        </div>
    </div>

    <!-- Pesanan Pending -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-time bx-sm"></i>
                        </span>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                            <a class="dropdown-item" href="<?= base_url('transaksi/index.php?status=pending') ?>">Lihat Pending</a>
                        </div>
                    </div>
                </div>
                <span class="d-block mb-1">Pesanan Pending</span>
                <h3 class="card-title text-nowrap mb-2"><?= number_format($pendingOrders) ?></h3>
                <small class="text-danger fw-semibold"><i class="bx bx-down-arrow-alt"></i> Perlu Ditindak</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row">
    <div class="col-md-6 col-lg-8 mb-4 order-0">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between pb-0">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Transaksi Terbaru</h5>
                    <small class="text-muted">Hari Ini</small>
                </div>
                <div class="dropdown">
                    <button class="btn p-0" type="button" id="transactionMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionMenu">
                        <a class="dropdown-item" href="<?= base_url('transaksi/index.php') ?>">Lihat Semua</a>
                        <a class="dropdown-item" href="<?= base_url('transaksi/add.php') ?>">Transaksi Baru</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $recentTransactions = mysqli_query($connection, "SELECT * FROM transaksi WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 5");
                if (mysqli_num_rows($recentTransactions) > 0):
                ?>
                <ul class="p-0 m-0">
                    <?php while ($trx = mysqli_fetch_assoc($recentTransactions)): ?>
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-<?= $trx['status_bayar'] == 'lunas' ? 'success' : 'warning' ?>">
                                <i class="bx bx-receipt"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0"><?= htmlspecialchars($trx['nomor_bukti']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($trx['nama_pelanggan']) ?> - Meja #<?= htmlspecialchars($trx['id_meja']) ?></small>
                            </div>
                            <div class="user-progress">
                                <small class="fw-semibold">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></small>
                            </div>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bx bx-receipt bx-lg text-muted mb-3"></i>
                    <p class="text-muted">Belum ada transaksi hari ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Menu Terlaris -->
    <div class="col-md-6 col-lg-4 mb-4 order-2">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Menu Terlaris</h5>
                <div class="dropdown">
                    <button class="btn p-0" type="button" id="menuChart" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="menuChart">
                        <a class="dropdown-item" href="<?= base_url('menu/index.php') ?>">Lihat Semua</a>
                        <a class="dropdown-item" href="<?= base_url('laporan/menu.php') ?>">Laporan Menu</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                // Get top selling menu items (this is a simple example - you'll need proper sales tracking)
                $topMenus = mysqli_query($connection, "SELECT * FROM menu WHERE status_aktif = 'aktif' ORDER BY RAND() LIMIT 5");
                if (mysqli_num_rows($topMenus) > 0):
                ?>
                <ul class="p-0 m-0">
                    <?php 
                    $index = 1;
                    while ($menu = mysqli_fetch_assoc($topMenus)): 
                    ?>
                    <li class="d-flex mb-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <?php if (!empty($menu['foto'])): ?>
                            <img src="<?= base_url('uploads/menu/'.htmlspecialchars($menu['foto'])) ?>" alt="<?= htmlspecialchars($menu['nama_menu']) ?>" class="rounded">
                            <?php else: ?>
                            <span class="avatar-initial rounded bg-label-primary"><?= $index ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-column w-100">
                            <div class="d-flex justify-content-between mb-1">
                                <h6 class="mb-0"><?= htmlspecialchars($menu['nama_menu']) ?></h6>
                                <small class="text-muted">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></small>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($menu['kategori']) ?></small>
                        </div>
                    </li>
                    <?php 
                    $index++;
                    endwhile; 
                    ?>
                </ul>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bx bx-food-menu bx-lg text-muted mb-3"></i>
                    <p class="text-muted">Belum ada data menu</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>