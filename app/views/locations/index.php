<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1>Locations Management</h1>
            <p class="muted">Create, view, update, and delete cities/areas used by property listings.</p>
        </div>
        <a class="btn" href="<?= URL_ROOT; ?>/location/create">Add Location</a>
    </div>

    <form class="toolbar" method="get" action="<?= URL_ROOT; ?>/location">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search city, area, country, postal code">
        <button class="btn" type="submit">Search</button>
        <?php if (!empty($search)): ?>
            <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/location">Clear</a>
        <?php endif; ?>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>City</th>
                    <th>Area</th>
                    <th>Country</th>
                    <th>Postal Code</th>
                    <th>Properties</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($locations)): ?>
                    <tr>
                        <td colspan="8" class="center muted">No locations found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($locations as $location): ?>
                    <tr>
                        <td><?= (int) $location['location_id']; ?></td>
                        <td><?= htmlspecialchars($location['city'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($location['area'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($location['country'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($location['postal_code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge"><?= (int) $location['property_count']; ?></span></td>
                        <td><?= htmlspecialchars($location['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn btn-mini" href="<?= URL_ROOT; ?>/location/edit/<?= (int) $location['location_id']; ?>">Edit</a>
                                <form method="post" action="<?= URL_ROOT; ?>/location/delete/<?= (int) $location['location_id']; ?>" class="inline-form">
                                    <?= Auth::csrfField(); ?>
                                    <button class="btn btn-mini btn-danger" type="submit" data-confirm="Delete this location? This is blocked if properties are linked.">Delete</button>
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
