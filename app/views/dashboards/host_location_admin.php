<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Host & Location Control</p>
        <h1>Host & Location Admin Dashboard</h1>
        <p class="muted">Manage host companies, location records, host permissions, and location-based property activity.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/host">Manage Hosts</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/location">Manage Locations</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report">Reports</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
