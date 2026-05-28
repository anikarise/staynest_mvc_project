<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Full System Control</p>
        <h1>Main Admin Dashboard</h1>
        <p class="muted">Full website access for users, bookings, properties, hosts, locations, analytics, and reports.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/user">Users</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/property">Properties</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Bookings</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/host">Hosts</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/location">Locations</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report">Reports</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
