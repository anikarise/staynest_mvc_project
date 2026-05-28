<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME, ENT_QUOTES, 'UTF-8'); ?> | <?= APP_NAME; ?></title>
    <link rel="stylesheet" href="<?= URL_ROOT; ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?= URL_ROOT; ?>" aria-label="StayNest home">
            <img class="logo-img" src="<?= URL_ROOT; ?>/assets/images/staynest-logo.png" alt="StayNest logo">
            <span class="brand-text">StayNest</span>
        </a>
        <div class="nav-links">
            <a href="<?= URL_ROOT; ?>">Home</a>
            <a href="<?= URL_ROOT; ?>/property">Properties</a>
            <?php if (Auth::check()): ?>
                <?php if (in_array(Auth::role(), ['main_admin', 'booking_property_admin', 'customer', 'staff', 'host'], true)): ?>
                    <a href="<?= URL_ROOT; ?>/booking">Bookings</a>
                <?php endif; ?>
                <?php if (in_array(Auth::role(), ['main_admin', 'host_location_admin'], true)): ?>
                    <a href="<?= URL_ROOT; ?>/host">Hosts</a>
                    <a href="<?= URL_ROOT; ?>/location">Locations</a>
                <?php endif; ?>
                <?php if (Auth::role() === 'host'): ?>
                    <a href="<?= URL_ROOT; ?>/host/profile">Host Profile</a>
                <?php endif; ?>
                <?php if (Auth::role() === 'main_admin'): ?>
                    <a href="<?= URL_ROOT; ?>/user">Users</a>
                <?php endif; ?>
                <?php if (in_array(Auth::role(), ['main_admin', 'booking_property_admin', 'host_location_admin', 'staff', 'host'], true)): ?>
                    <a href="<?= URL_ROOT; ?>/report">Reports</a>
                <?php endif; ?>
                <a href="<?= URL_ROOT; ?>/dashboard">Dashboard</a>
                <a href="<?= URL_ROOT; ?>/user/profile">Profile</a>
                <span class="user-pill"><?= htmlspecialchars(Auth::name() ?? 'User', ENT_QUOTES, 'UTF-8'); ?> · <?= Auth::roleLabel(); ?></span>
                <a class="btn btn-small" href="<?= URL_ROOT; ?>/auth/logout">Logout</a>
            <?php else: ?>
                <a href="<?= URL_ROOT; ?>/auth/login">Login</a>
                <a class="btn btn-small" href="<?= URL_ROOT; ?>/auth/register">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
<?php $flashMessages = Auth::getFlash(); ?>
<?php if (!empty($flashMessages)): ?>
    <div class="container flash-wrap">
        <?php foreach ($flashMessages as $flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
