<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Operations Workspace</p>
        <h1>Staff Dashboard</h1>
        <p class="muted">Welcome, <?= htmlspecialchars($user['name'] ?? 'Staff', ENT_QUOTES, 'UTF-8'); ?>. Staff can monitor system activity and support day-to-day operations.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/property">Properties</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Bookings</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report">Reports</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
