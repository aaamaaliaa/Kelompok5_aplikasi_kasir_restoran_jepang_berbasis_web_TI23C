<?php
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
$roleLabel = getRoleLabel($role);
$userAvatar = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=0D8ABC&color=fff&size=128";
?>

        <!--! [Start] Header Right !-->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                
                <!--! [Start] Header Search !-->
                <div class="dropdown nxl-h-item nxl-header-search">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-search"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-search-dropdown">
                        <div class="input-group search-form">
                            <span class="input-group-text">
                                <i class="feather-search fs-6 text-muted"></i>
                            </span>
                            <input type="text" class="form-control search-input-field" placeholder="Cari menu, transaksi..." />
                            <span class="input-group-text">
                                <button type="button" class="btn-close"></button>
                            </span>
                        </div>
                        <div class="dropdown-divider mt-0"></div>
                        <div class="search-items-wrapper">
                            <div class="searching-for px-4 py-2">
                                <p class="fs-11 fw-medium text-muted">Pencarian Cepat</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--! [End] Header Search !-->
                
                <!--! [Start] Header Notification !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link me-3" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        <span class="badge bg-danger nxl-h-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                        <div class="d-flex justify-content-between align-items-center notifications-head">
                            <h6 class="fw-bold text-dark mb-0">Notifikasi</h6>
                            <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto">
                                <i class="feather-check"></i>
                                <span>Tandai Dibaca</span>
                            </a>
                        </div>
                        <div class="notifications-item">
                            <img src="<?= $userAvatar ?>" alt="" class="rounded me-3 border" />
                            <div class="notifications-desc">
                                <a href="javascript:void(0);" class="font-body text-truncate-2-line">
                                    <span class="fw-semibold text-dark">Pesanan Baru!</span> Meja #5 memesan 3 item
                                </a>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="notifications-date text-muted border-bottom border-bottom-dashed">2 menit yang lalu</div>
                                    <div class="d-flex align-items-center float-end gap-2">
                                        <a href="javascript:void(0);" class="d-block wd-8 ht-8 rounded-circle bg-primary"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="notifications-item">
                            <img src="<?= $userAvatar ?>" alt="" class="rounded me-3 border" />
                            <div class="notifications-desc">
                                <a href="javascript:void(0);" class="font-body text-truncate-2-line">
                                    <span class="fw-semibold text-dark">Pembayaran Berhasil</span> Transaksi #TR001 lunas
                                </a>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="notifications-date text-muted border-bottom border-bottom-dashed">15 menit yang lalu</div>
                                </div>
                            </div>
                        </div>
                        <div class="notifications-item">
                            <img src="<?= $userAvatar ?>" alt="" class="rounded me-3 border" />
                            <div class="notifications-desc">
                                <a href="javascript:void(0);" class="font-body text-truncate-2-line">
                                    <span class="fw-semibold text-dark">Stok Menipis</span> Bahan Sushi Salmon tersisa 5
                                </a>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="notifications-date text-muted border-bottom border-bottom-dashed">1 jam yang lalu</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center notifications-footer">
                            <a href="<?= base_url('notifications.php') ?>" class="fs-13 fw-semibold text-dark">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>
                </div>
                <!--! [End] Header Notification !-->
                
                <!--! [Start] Header Profile !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="user-image" class="img-fluid user-avtar me-0" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($userAvatar) ?>" alt="user-image" class="img-fluid user-avtar" />
                                <div>
                                    <h6 class="text-dark mb-0"><?= htmlspecialchars($username) ?> 
                                        <span class="badge bg-soft-success text-success ms-1"><?= htmlspecialchars($roleLabel) ?></span>
                                    </h6>
                                    <span class="fs-12 fw-medium text-muted">Resto Jepang POS</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="dropdown">
                                <span class="hstack">
                                    <i class="wd-10 ht-10 border border-2 border-gray-1 bg-success rounded-circle me-2"></i>
                                    <span>Active</span>
                                </span>
                                <i class="feather-chevron-right ms-auto me-0"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-10 ht-10 border border-2 border-gray-1 bg-warning rounded-circle me-2"></i>
                                        <span>Away</span>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-10 ht-10 border border-2 border-gray-1 bg-danger rounded-circle me-2"></i>
                                        <span>Busy</span>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-10 ht-10 border border-2 border-gray-1 bg-info rounded-circle me-2"></i>
                                        <span>Offline</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="dropdown">
                                <span class="hstack">
                                    <i class="feather-dollar-sign me-2"></i>
                                    <span>Subscription</span>
                                </span>
                                <i class="feather-chevron-right ms-auto me-0"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-5 ht-5 bg-gray-3 rounded-circle me-3"></i>
                                        <span>Plan A</span>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-5 ht-5 bg-gray-3 rounded-circle me-3"></i>
                                        <span>Plan B</span>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <span class="hstack">
                                        <i class="wd-5 ht-5 bg-gray-3 rounded-circle me-3"></i>
                                        <span>Plan C</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('profile.php') ?>" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="<?= base_url('settings.php') ?>" class="dropdown-item">
                            <i class="feather-settings"></i>
                            <span>Pengaturan</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('logout.php') ?>" class="dropdown-item" onclick="return confirm('Yakin ingin keluar?')">
                            <i class="feather-log-out"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
                <!--! [End] Header Profile !-->
                
            </div>
        </div>
        <!--! [End] Header Right !-->
        
    </div>
</header>
<!--! ================================================================ !-->
<!--! [End] Header !-->
<!--! ================================================================ !-->