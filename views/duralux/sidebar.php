<?php
// Get menu configuration
$menuConfig = loadMenuConfig();
$userRole = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'User';

// Define menu items based on role
$menuItems = [];

if ($userRole === 'admin') {
    $menuItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'feather-airplay',
            'url' => base_url('admin/index.php'),
            'badge' => null
        ],
        [
            'label' => 'Menu Makanan',
            'icon' => 'feather-shopping-bag',
            'url' => base_url('menu/index.php'),
            'badge' => null
        ],
        [
            'label' => 'Transaksi',
            'icon' => 'feather-credit-card',
            'url' => base_url('transaksi/index.php'),
            'badge' => 'Hot'
        ],
        [
            'label' => 'Meja',
            'icon' => 'feather-grid',
            'url' => base_url('meja/index.php'),
            'badge' => null
        ],
        [
            'label' => 'Laporan',
            'icon' => 'feather-bar-chart-2',
            'url' => base_url('laporan/index.php'),
            'badge' => null
        ],
        
    ];
} elseif ($userRole === 'kasir') {
    $menuItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'feather-airplay',
            'url' => base_url('kasir/index.php'),
            'badge' => null
        ],
        [
            'label' => 'Transaksi',
            'icon' => 'feather-credit-card',
            'url' => base_url('transaksi/index.php'),
            'badge' => 'New'
        ],
        [
            'label' => 'Menu',
            'icon' => 'feather-shopping-bag',
            'url' => base_url('menu/index.php'),
            'badge' => null
        ],
        [
            'label' => 'Meja',
            'icon' => 'feather-grid',
            'url' => base_url('meja/index.php'),
            'badge' => null
        ]
    ];
}

// Get current page for active state
$currentPath = $_SERVER['REQUEST_URI'];
?>

<!--! ================================================================ !-->
<!--! [Start] Navigation Menu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        
        <!-- Logo Section -->
        <div class="m-header">
            <a href="<?= base_url() ?>" class="b-brand">
                <!-- Change your logo here -->
                <img src="<?= base_url('assets/'.$THEME.'/images/logo-full.png') ?>" 
                     alt="Resto Jepang" 
                     class="logo logo-lg" />
                <img src="<?= base_url('assets/'.$THEME.'/images/logo-abbr.png') ?>" 
                     alt="RJ" 
                     class="logo logo-sm" />
            </a>
        </div>
        
        <!-- Navbar Content -->
        <div class="navbar-content">
            <ul class="nxl-navbar">
                
                <!-- Navigation Label -->
                <li class="nxl-item nxl-caption">
                    <label>Menu Navigasi</label>
                </li>
                
                <!-- Dynamic Menu Items -->
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    // Check if current page matches menu item
                    $isActive = false;
                    $itemPath = parse_url($item['url'], PHP_URL_PATH);
                    
                    if (strpos($currentPath, $itemPath) !== false) {
                        $isActive = true;
                    }
                    ?>
                    
                    <li class="nxl-item <?= $isActive ? 'active' : '' ?>">
                        <a class="nxl-link" href="<?= htmlspecialchars($item['url']) ?>">
                            <span class="nxl-micon"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                            <span class="nxl-mtext"><?= htmlspecialchars($item['label']) ?></span>
                            <?php if ($item['badge']): ?>
                                <span class="badge badge-sm badge-soft-primary ms-auto"><?= htmlspecialchars($item['badge']) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <!-- Divider -->
                <li class="nxl-item nxl-caption">
                    <label>Akun</label>
                </li>
                
                <!-- Profile Menu -->
                <li class="nxl-item">
                    <a class="nxl-link" href="<?= base_url('profile.php') ?>">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Profil Saya</span>
                    </a>
                </li>
                
                <!-- Logout Menu -->
                <li class="nxl-item">
                    <a class="nxl-link" href="<?= base_url('logout.php') ?>" onclick="return confirm('Yakin ingin keluar?')">
                        <span class="nxl-micon"><i class="feather-log-out"></i></span>
                        <span class="nxl-mtext">Keluar</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End] Navigation Menu !-->
<!--! ================================================================ !-->

<!--! ================================================================ !-->
<!--! [Start] Header !-->
<!--! ================================================================ !-->
<header class="nxl-header">
    <div class="header-wrapper">
        <!--! [Start] Header Left !-->
        <div class="header-left d-flex align-items-center gap-4">
            <!--! [Start] nxl-head-mobile-toggler !-->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </a>
            <!--! [End] nxl-head-mobile-toggler !-->
            
            <!--! [Start] nxl-navigation-toggle !-->
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
            </div>
            <!--! [End] nxl-navigation-toggle !-->
            
            <!--! [Start] nxl-lavel-mega-menu-toggle !-->
            <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                    <i class="feather-more-horizontal"></i>
                </a>
            </div>
            <!--! [End] nxl-lavel-mega-menu-toggle !-->
            
            <!--! [Start] nxl-lavel-mega-menu !-->
            <div class="nxl-drp-link nxl-lavel-mega-menu">
                <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                    <a href="javascript:void(0)" id="nxl-lavel-mega-menu-hide">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <!--! [Start] nxl-lavel-mega-menu-wrapper !-->
                <div class="nxl-lavel-mega-menu-wrapper d-flex gap-3">
                    <!--! [Start] nxl-h-item nxl-lavel-menu !-->
                    <div class="dropdown nxl-h-item nxl-lavel-menu">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            <i class="feather-zap"></i>
                            <span>Quick Actions</span>
                        </a>
                        <div class="dropdown-menu nxl-h-dropdown">
                            <a href="<?= base_url('menu/add.php') ?>" class="dropdown-item">
                                <i class="feather-plus"></i>
                                <span>Tambah Menu</span>
                            </a>
                            <a href="<?= base_url('transaksi/add.php') ?>" class="dropdown-item">
                                <i class="feather-credit-card"></i>
                                <span>Transaksi Baru</span>
                            </a>
                            <a href="<?= base_url('laporan/index.php') ?>" class="dropdown-item">
                                <i class="feather-bar-chart"></i>
                                <span>Lihat Laporan</span>
                            </a>
                        </div>
                    </div>
                    <!--! [End] nxl-h-item nxl-lavel-menu !-->
                </div>
                <!--! [End] nxl-lavel-mega-menu-wrapper !-->
            </div>
            <!--! [End] nxl-lavel-mega-menu !-->
        </div>
        <!--! [End] Header Left !-->