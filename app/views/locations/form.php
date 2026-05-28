<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section two-column">
    <div class="form-card">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="muted">Locations are used later when adding property listings and filtering property searches.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" novalidate>
            <?= Auth::csrfField(); ?>

            <label>City</label>
            <input type="text" name="city" value="<?= htmlspecialchars($location['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Copenhagen" required>
            <?php if (!empty($errors['city'])): ?><small class="field-error"><?= htmlspecialchars($errors['city'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Area</label>
            <input type="text" name="area" value="<?= htmlspecialchars($location['area'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nørrebro">

            <label>Country</label>
            <input type="text" name="country" value="<?= htmlspecialchars($location['country'] ?? 'Denmark', ENT_QUOTES, 'UTF-8'); ?>" required>
            <?php if (!empty($errors['country'])): ?><small class="field-error"><?= htmlspecialchars($errors['country'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Postal Code</label>
            <input type="text" name="postal_code" value="<?= htmlspecialchars($location['postal_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="2200">
            <?php if (!empty($errors['postal_code'])): ?><small class="field-error"><?= htmlspecialchars($errors['postal_code'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <div class="form-actions">
                <button class="btn" type="submit"><?= ($mode ?? 'create') === 'edit' ? 'Update Location' : 'Save Location'; ?></button>
                <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/location">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card profile-summary">
        <h2>Testing Notes</h2>
        <p>Use this page to add city/area records before adding properties in the next phase.</p>
        <p>Delete is blocked when the location is already linked with a property. That avoids broken database relationships.</p>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
