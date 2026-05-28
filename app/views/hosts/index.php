<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1>Hosts Management</h1>
            <p class="muted">Manage host company profiles linked with host user accounts.</p>
        </div>
        <a class="btn" href="<?= URL_ROOT; ?>/host/create">Add Host Profile</a>
    </div>

    <form class="toolbar" method="get" action="<?= URL_ROOT; ?>/host">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search company, host name, email, contact">
        <button class="btn" type="submit">Search</button>
        <?php if (!empty($search)): ?>
            <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/host">Clear</a>
        <?php endif; ?>
    </form>

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
                <?php if (empty($hosts)): ?>
                    <tr>
                        <td colspan="8" class="center muted">No hosts found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($hosts as $host): ?>
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
                        <td><span class="badge badge-green"><?= htmlspecialchars($host['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
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
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
