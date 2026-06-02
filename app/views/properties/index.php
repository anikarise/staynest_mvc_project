<?php
/*
|--------------------------------------------------------------------------
| Property Listing View
|--------------------------------------------------------------------------
| Displays public, host, or manager property lists with search filters and
| role-specific action buttons.
|
*/
require APP_ROOT . '/app/views/layouts/header.php';
?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1><?= $mode === 'public' ? 'Available Properties' : 'Property Management'; ?></h1>
            <p class="muted">
                <?php if ($mode === 'manager'): ?>
                    Manage all property listings, approve/reject submissions, and control availability.
                <?php elseif ($mode === 'host'): ?>
                    Manage your own property listings. New or edited listings stay pending until admin approval.
                <?php else: ?>
                    Browse approved StayNest properties by location, title, and availability.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($canManage || $isHost): ?>
            <a class="btn" href="<?= URL_ROOT; ?>/property/create">Add Property</a>
        <?php endif; ?>
    </div>

    <form class="toolbar property-toolbar" method="get" action="<?= URL_ROOT; ?>/property">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search title, category, host, city, area">

        <select name="location_id">
            <option value="">All locations</option>
            <?php foreach ($locations as $location): ?>
                <option value="<?= (int) $location['location_id']; ?>" <?= (int) ($locationId ?? 0) === (int) $location['location_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($location['city'] . (!empty($location['area']) ? ' — ' . $location['area'] : ''), ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="availability">
            <option value="">Any availability</option>
            <option value="available" <?= ($availability ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
            <option value="unavailable" <?= ($availability ?? '') === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
        </select>

        <?php if ($mode !== 'public'): ?>
            <select name="status">
                <option value="">Any status</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        <?php endif; ?>

        <button class="btn" type="submit">Filter</button>
        <?php if (!empty($search) || !empty($locationId) || !empty($availability) || !empty($status)): ?>
            <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/property">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($properties)): ?>
        <div class="card center">
            <h2>No properties found</h2>
            <p class="muted">Try changing the filters or add a new property listing.</p>
        </div>
    <?php else: ?>
        <div class="property-grid-real">
            <?php foreach ($properties as $property): ?>
                <?php
                    $imagePath = PUBLIC_PATH . '/uploads/properties/' . ($property['image'] ?? '');
                    $hasImage = !empty($property['image']) && is_file($imagePath);
                    $locationText = trim(($property['area'] ? $property['area'] . ', ' : '') . $property['city']);
                ?>
                <article class="property-card-real card">
                    <?php if ($hasImage): ?>
                        <a href="<?= URL_ROOT; ?>/property/show/<?= (int) $property['property_id']; ?>">
                            <img class="property-image" src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        </a>
                    <?php else: ?>
                        <a class="property-image property-placeholder" href="<?= URL_ROOT; ?>/property/show/<?= (int) $property['property_id']; ?>">
                            <span><?= htmlspecialchars(substr($property['title'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    <?php endif; ?>

                    <div class="property-content">
                        <div class="property-meta-row">
                            <span class="badge"><?= htmlspecialchars($property['category'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge <?= ($property['availability'] ?? '') === 'available' ? 'badge-green' : 'badge-gray'; ?>">
                                <?= htmlspecialchars(ucfirst($property['availability']), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if ($mode !== 'public'): ?>
                                <span class="badge badge-status-<?= htmlspecialchars($property['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars(ucfirst($property['status']), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2><a href="<?= URL_ROOT; ?>/property/show/<?= (int) $property['property_id']; ?>"><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></a></h2>
                        <p class="muted property-location">📍 <?= htmlspecialchars($locationText, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?php $desc = $property['description'] ?? 'No description added.'; $shortDesc = strlen($desc) > 135 ? substr($desc, 0, 132) . '...' : $desc; ?><?= htmlspecialchars($shortDesc, ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="property-bottom">
                            <strong class="price">DKK <?= number_format((float) $property['price'], 2); ?></strong>
                            <?php if ($mode === 'public'): ?>
                                <a class="btn btn-mini" href="<?= URL_ROOT; ?>/property/show/<?= (int) $property['property_id']; ?>">View Details</a>
                            <?php endif; ?>
                        </div>

                        <?php if ($mode !== 'public'): ?>
                            <div class="property-admin-info muted">
                                Host: <?= htmlspecialchars($property['company_name'], ENT_QUOTES, 'UTF-8'); ?> · Bookings: <?= (int) $property['booking_count']; ?>
                            </div>
                            <div class="row-actions property-actions">
                                <a class="btn btn-mini" href="<?= URL_ROOT; ?>/property/edit/<?= (int) $property['property_id']; ?>">Edit</a>

                                <?php if ($canManage && $property['status'] !== 'approved'): ?>
                                    <form method="post" action="<?= URL_ROOT; ?>/property/approve/<?= (int) $property['property_id']; ?>" class="inline-form">
                                        <?= Auth::csrfField(); ?>
                                        <button class="btn btn-mini" type="submit">Approve</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($canManage && $property['status'] !== 'rejected'): ?>
                                    <form method="post" action="<?= URL_ROOT; ?>/property/reject/<?= (int) $property['property_id']; ?>" class="inline-form">
                                        <?= Auth::csrfField(); ?>
                                        <button class="btn btn-mini btn-secondary" type="submit">Reject</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" action="<?= URL_ROOT; ?>/property/delete/<?= (int) $property['property_id']; ?>" class="inline-form">
                                    <?= Auth::csrfField(); ?>
                                    <button class="btn btn-mini btn-danger" type="submit" data-confirm="Delete this property? Delete is blocked if bookings are linked.">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
