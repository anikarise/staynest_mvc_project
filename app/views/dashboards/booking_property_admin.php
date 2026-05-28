<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Booking & Property Control</p>
        <h1>Booking & Property Admin Dashboard</h1>
        <p class="muted">Manage booking records, approve or reject property listings, and monitor booking/property performance.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/property">Manage Properties</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Manage Bookings</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report">Reports</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
