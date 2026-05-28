<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section two-column">
    <div class="form-card">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="muted">Add listing information, connect the property with a host and location, and upload one main image.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" enctype="multipart/form-data" novalidate>
            <?= Auth::csrfField(); ?>

            <?php if ($isManager): ?>
                <label>Host</label>
                <select name="host_id" required>
                    <option value="">Select host</option>
                    <?php foreach ($hosts as $host): ?>
                        <option value="<?= (int) $host['host_id']; ?>" <?= (int) ($property['host_id'] ?? 0) === (int) $host['host_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($host['company_name'] . ' — ' . $host['email'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['host_id'])): ?><small class="field-error"><?= htmlspecialchars($errors['host_id'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
            <?php else: ?>
                <input type="hidden" name="host_id" value="<?= (int) ($property['host_id'] ?? 0); ?>">
            <?php endif; ?>

            <label>Location</label>
            <select name="location_id" required>
                <option value="">Select location</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= (int) $location['location_id']; ?>" <?= (int) ($property['location_id'] ?? 0) === (int) $location['location_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($location['city'] . (!empty($location['area']) ? ' — ' . $location['area'] : '') . ' (' . $location['country'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['location_id'])): ?><small class="field-error"><?= htmlspecialchars($errors['location_id'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Property Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($property['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Modern Studio Apartment" required>
            <?php if (!empty($errors['title'])): ?><small class="field-error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Description</label>
            <textarea name="description" rows="5" placeholder="Describe the property, room, nearby facilities, and target tenant."><?= htmlspecialchars($property['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($errors['description'])): ?><small class="field-error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Category</label>
            <select name="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?= ($property['category'] ?? '') === $category ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['category'])): ?><small class="field-error"><?= htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Price / Night or Month</label>
            <input type="number" step="0.01" min="1" name="price" value="<?= htmlspecialchars((string) ($property['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="750.00" required>
            <?php if (!empty($errors['price'])): ?><small class="field-error"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Availability</label>
            <select name="availability" required>
                <option value="available" <?= ($property['availability'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="unavailable" <?= ($property['availability'] ?? '') === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
            </select>
            <?php if (!empty($errors['availability'])): ?><small class="field-error"><?= htmlspecialchars($errors['availability'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <?php if ($isManager): ?>
                <label>Moderation Status</label>
                <select name="status" required>
                    <option value="pending" <?= ($property['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?= ($property['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?= ($property['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <?php if (!empty($errors['status'])): ?><small class="field-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
            <?php else: ?>
                <input type="hidden" name="status" value="pending">
            <?php endif; ?>

            <label>Main Property Image</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <small class="muted">Allowed: JPG, PNG, WEBP. Maximum size: 2MB.</small>
            <?php if (!empty($errors['image'])): ?><small class="field-error"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <div class="form-actions">
                <button class="btn" type="submit"><?= ($mode ?? 'create') === 'create' ? 'Save Property' : 'Update Property'; ?></button>
                <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/property">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card profile-summary">
        <h2>Preview / Rules</h2>
        <?php
            $imagePath = PUBLIC_PATH . '/uploads/properties/' . ($property['image'] ?? '');
            $hasImage = !empty($property['image']) && is_file($imagePath);
        ?>
        <?php if ($hasImage): ?>
            <img class="preview-image" src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Current property image">
        <?php else: ?>
            <div class="property-image property-placeholder preview-placeholder"><span>SN</span></div>
        <?php endif; ?>
        <p>Host-submitted properties are saved as <strong>Pending</strong>. Only Main Admin or Booking & Property Admin can approve or reject them.</p>
        <p>Delete is blocked when a booking exists, because deleting linked business records would damage the system.</p>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
