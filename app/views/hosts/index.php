<?php
/*
|--------------------------------------------------------------------------
| Host Management View
|--------------------------------------------------------------------------
| Presents host profile search, status filtering, and relationship-aware
| management actions.
|
*/
require APP_ROOT . '/app/views/layouts/header.php';
?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1>Hosts Management</h1>
            <p class="muted">Manage host company profiles linked with host user accounts.</p>
        </div>
        <a class="btn" href="<?= URL_ROOT; ?>/host/create">Add Host Profile</a>
    </div>

    <form class="management-filter-panel host-filter-panel" method="get" action="<?= URL_ROOT; ?>/host">
        <div class="booking-filter-field management-filter-search">
            <label for="hostSearch">Search hosts</label>
            <input id="hostSearch" type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Host ID, company, user, email, phone, status">
        </div>
        <div class="booking-filter-field">
            <label for="hostStatus">Status</label>
            <select id="hostStatus" name="status">
                <option value="">All hosts</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : ''; ?>>Active/Approved</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="booking-filter-actions">
            <button class="btn booking-search-btn" type="submit"><span class="search-icon" aria-hidden="true"></span>Search</button>
            <a class="btn btn-secondary booking-reset-btn" href="<?= URL_ROOT; ?>/host">Reset</a>
        </div>
    </form>

    <?php if (empty($hosts)): ?>
        <div class="card center booking-empty-state">
            <h2>No matching records found.</h2>
            <p class="muted">Try a different host, contact, or status filter.</p>
        </div>
    <?php else: ?>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company</th>
                    <th>Linked User</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Properties</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hosts as $host): ?>
                    <?php
                        $accountStatus = $host['account_status'] ?? 'active';
                        $hostStatusLabel = ($host['status'] ?? '') === 'inactive' ? 'Inactive' : ucfirst($accountStatus);
                        $hostStatusClass = ($host['status'] ?? '') === 'inactive' ? 'badge-account-inactive' : 'badge-account-' . $accountStatus;
                    ?>
                    <tr>
                        <td><?= (int) $host['host_id']; ?></td>
                        <td>
                            <strong><?= htmlspecialchars($host['company_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <small class="muted"><?= htmlspecialchars($host['company_description'] ?? 'No description', ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <td><?= htmlspecialchars($host['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($host['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($host['contact_information'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge"><?= (int) $host['property_count']; ?></span></td>
                        <td><span class="badge <?= htmlspecialchars($hostStatusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($hostStatusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn btn-mini" href="<?= URL_ROOT; ?>/host/edit/<?= (int) $host['host_id']; ?>">Edit</a>
                                <form method="post" action="<?= URL_ROOT; ?>/host/delete/<?= (int) $host['host_id']; ?>" class="inline-form">
                                    <?= Auth::csrfField(); ?>
                                    <button class="btn btn-mini btn-danger" type="submit" data-confirm="Delete this host profile? This is blocked if properties are linked.">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
