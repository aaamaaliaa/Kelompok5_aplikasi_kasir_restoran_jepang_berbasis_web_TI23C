<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Sistem Kasir Restoran Jepang - Modern POS System" />
    <meta name="keyword" content="restaurant, pos, kasir, jepang, duralux" />
    <meta name="author" content="Resto Jepang Team" />
    
    <!--! BEGIN: Apps Title-->
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Resto Jepang POS</title>
    <!--! END: Apps Title-->
    
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/'.$THEME.'/images/favicon.ico') ?>" />
    <!--! END: Favicon-->
    
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/'.$THEME.'/css/bootstrap.min.css') ?>" />
    <!--! END: Bootstrap CSS-->
    
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/'.$THEME.'/vendors/css/vendors.min.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/'.$THEME.'/vendors/css/daterangepicker.min.css') ?>" />
    <!--! END: Vendors CSS-->
    
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/'.$THEME.'/css/theme.min.css') ?>" />
    <!--! END: Custom CSS-->
    
    <!--! BEGIN: Page CSS (if any) -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <!--! END: Page CSS -->
</head>

<body>