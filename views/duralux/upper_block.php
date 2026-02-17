<!--! ================================================================ !-->
<!--! [Start] Main Content !-->
<!--! ================================================================ !-->
<main class="nxl-container">
    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                    <?php if (isset($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><?= htmlspecialchars($crumb['label']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if (isset($pageHeaderButtons)): ?>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2">
                        <?= $pageHeaderButtons ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- End Page Header -->

        <!-- Main Content -->
        <div class="main-content">