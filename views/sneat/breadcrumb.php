<?php
// Default breadcrumb if not set
if (!isset($breadcrumbs)) {
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => base_url()]
    ];
}
?>

<!-- Breadcrumb -->
<div class="row mb-3">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-2">
            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>"><i class="bx bx-home-alt"></i></a>
                </li>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($crumb['label']) ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= htmlspecialchars($crumb['url']) ?>">
                                <?= htmlspecialchars($crumb['label']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>
<!-- / Breadcrumb -->