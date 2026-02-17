<?php
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
$roleLabel = getRoleLabel($role);
$userAvatar = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=696cff&color=fff&size=128";
?>

<!-- Layout Container -->
<div class="layout-page">
    
    <!-- Navbar -->
    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                    <i class="bx bx-search fs-4 lh-0"></i>
                    <input type="text" class="form-control border-0 shadow-none" placeholder="Cari..." aria-label="Cari..." />
                </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                
                <!-- Quick Actions -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bx bx-grid-alt bx-sm"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0">
                        <div class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="text-body mb-0 me-auto">Akses Cepat</h5>
                            </div>
                        </div>
                        <div class="dropdown-shortcuts-list scrollable-container">
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                        <i class="bx bx-food-menu fs-4"></i>
                                    </span>
                                    <a href="<?= base_url('menu/add.php') ?>" class="stretched-link">Tambah Menu</a>
                                    <small class="text-muted mb-0">Makanan Baru</small>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                        <i class="bx bx-receipt fs-4"></i>
                                    </span>
                                    <a href="<?= base_url('transaksi/add.php') ?>" class="stretched-link">Transaksi Baru</a>
                                    <small class="text-muted mb-0">Buat Pesanan</small>
                                </div>
                            </div>
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                        <i class="bx bx-bar-chart-alt-2 fs-4"></i>
                                    </span>
                                    <a href="<?= base_url('laporan/index.php') ?>" class="stretched-link">Laporan</a>
                                    <small class="text-muted mb-0">Lihat Laporan</small>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                        <i class="bx bx-cog fs-4"></i>
                                    </span>
                                    <a href="<?= base_url('settings/index.php') ?>" class="stretched-link">Pengaturan</a>
                                    <small class="text-muted mb-0">Sistem</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!--/ Quick Actions -->
                
                <!-- Notification -->
                <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-3 me-xl-2">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bx bx-bell bx-sm"></i>
                        <span class="badge bg-danger rounded-pill badge-notifications">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end py-0">
                        <li class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="text-body mb-0 me-auto">Notifikasi</h5>
                                <a href="javascript:void(0)" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Tandai sudah dibaca">
                                    <i class="bx fs-4 bx-envelope-open"></i>
                                </a>
                            </div>
                        </li>
                        <li class="dropdown-notifications-list scrollable-container">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <span class="avatar-initial rounded-circle bg-label-success"><i class="bx bx-cart"></i></span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Pesanan Baru!</h6>
                                            <p class="mb-0">Meja #5 memesan 3 item</p>
                                            <small class="text-muted">2 menit yang lalu</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <span class="avatar-initial rounded-circle bg-label-warning"><i class="bx bx-error"></i></span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Stok Menipis</h6>
                                            <p class="mb-0">Salmon tersisa 5 porsi</p>
                                            <small class="text-muted">1 jam yang lalu</small>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <img src="<?= $userAvatar ?>" alt class="w-px-40 h-auto rounded-circle" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Transaksi Lunas</h6>
                                            <p class="mb-0">Pembayaran #TR001 berhasil</p>
                                            <small class="text-muted">15 menit yang lalu</small>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown-menu-footer border-top p-3">
                            <a href="<?= base_url('notifications.php') ?>" class="btn btn-primary text-uppercase w-100">Lihat Semua Notifikasi</a>
                        </li>
                    </ul>
                </li>
                <!--/ Notification -->

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="<?= htmlspecialchars($userAvatar) ?>" alt class="w-px-40 h-auto rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="<?= htmlspecialchars($userAvatar) ?>" alt class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block"><?= htmlspecialchars($username) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($roleLabel) ?></small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('profile.php') ?>">
                                <i class="bx bx-user me-2"></i>
                                <span class="align-middle">Profil Saya</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('settings.php') ?>">
                                <i class="bx bx-cog me-2"></i>
                                <span class="align-middle">Pengaturan</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('logout.php') ?>" onclick="return confirm('Yakin ingin keluar?')">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Keluar</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
                
            </ul>
        </div>
    </nav>
    <!-- / Navbar -->