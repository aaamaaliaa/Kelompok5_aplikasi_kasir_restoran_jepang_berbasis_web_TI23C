<!-- Footer -->
        <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                    © <script>document.write(new Date().getFullYear());</script>
                    , made with ❤️ by
                    <a href="<?= base_url() ?>" target="_blank" class="footer-link fw-bolder">Resto Jepang Team</a>
                </div>
                <div>
                    <a href="<?= base_url('help.php') ?>" class="footer-link me-4" target="_blank">Bantuan</a>
                    <a href="https://themeselection.com/" target="_blank" class="footer-link me-4">Sneat Theme</a>
                </div>
            </div>
        </footer>
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->
</div>
<!-- / Layout page -->

        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?= base_url('assets/'.$THEME.'/vendor/libs/jquery/jquery.js') ?>"></script>
    <script src="<?= base_url('assets/'.$THEME.'/vendor/libs/popper/popper.js') ?>"></script>
    <script src="<?= base_url('assets/'.$THEME.'/vendor/js/bootstrap.js') ?>"></script>
    <script src="<?= base_url('assets/'.$THEME.'/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>

    <script src="<?= base_url('assets/'.$THEME.'/vendor/js/menu.js') ?>"></script>
    <!-- endbuild -->

    <!-- Main JS -->
    <script src="<?= base_url('assets/'.$THEME.'/js/main.js') ?>"></script>

    <!-- Page JS -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($inlineJS)): ?>
        <script>
            <?= $inlineJS ?>
        </script>
    <?php endif; ?>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>