<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Customer Workspace</p>
        <h1>Customer Dashboard</h1>
        <p class="muted">Welcome, <?= htmlspecialchars($user['name'] ?? 'Customer', ENT_QUOTES, 'UTF-8'); ?>. Browse approved properties, submit booking requests, and monitor your booking status.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/property">Browse Properties</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">My Bookings</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
