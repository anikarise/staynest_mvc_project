<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section dashboard-hero">
    <div>
        <p class="eyebrow">Host Workspace</p>
        <h1>Host Dashboard</h1>
        <p class="muted">Welcome, <?= htmlspecialchars($user['name'] ?? 'Host', ENT_QUOTES, 'UTF-8'); ?>. Manage your property listings, check moderation status, and track bookings connected to your own properties.</p>
        <?php if (empty($hostProfile)): ?>
            <div class="alert alert-error">Your host profile is missing. Create it before adding properties.</div>
        <?php endif; ?>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/host/profile">My Host Profile</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/property">My Properties</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Booking Overview</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report">Reports</a>
    </div>
</section>
<section class="container section section-tight">
    <?php require APP_ROOT . '/app/views/dashboards/_stats.php'; ?>
    <?php require APP_ROOT . '/app/views/dashboards/_analytics.php'; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
