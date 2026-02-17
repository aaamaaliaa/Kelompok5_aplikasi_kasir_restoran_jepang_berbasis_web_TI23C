<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Resto Jepang POS</title>
    
    <meta name="description" content="Sistem Kasir Restoran Jepang - Modern POS System">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/'.$THEME.'/img/favicon/favicon.ico') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    
    <!-- Icons - Boxicons -->
    <link rel="stylesheet" href="<?= base_url('assets/'.$THEME.'/vendor/fonts/boxicons.css') ?>">
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/'.$THEME.'/vendor/css/core.css') ?>" class="template-customizer-core-css">
    <link rel="stylesheet" href="<?= base_url('assets/'.$THEME.'/vendor/css/theme-default.css') ?>" class="template-customizer-theme-css">
    <link rel="stylesheet" href="<?= base_url('assets/'.$THEME.'/css/demo.css') ?>">
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/'.$THEME.'/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>">
    
    <!-- Page CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Helpers -->
    <script src="<?= base_url('assets/'.$THEME.'/vendor/js/helpers.js') ?>"></script>
    
    <!-- Config -->
    <script src="<?= base_url('assets/'.$THEME.'/js/config.js') ?>"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">