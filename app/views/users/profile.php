<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section two-column">
    <div class="form-card">
        <h1>My Profile</h1>
        <p class="muted">Manage your basic account details and password.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= URL_ROOT; ?>/user/profile" class="account-validation-form" novalidate>
            <?= Auth::csrfField(); ?>

            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30" autocomplete="name" required>
            <small class="field-error name-js-error" hidden></small>
            <?php if (!empty($errors['name'])): ?><small class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
            <small class="muted">Email changing is locked in Phase 2 to avoid account conflicts.</small>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <label>New Password</label>
            <input type="password" name="new_password" maxlength="50" autocomplete="new-password" placeholder="Leave blank if you do not want to change it">
            <small class="field-error password-js-error" hidden></small>
            <?php if (!empty($errors['new_password'])): ?><small class="field-error"><?= htmlspecialchars($errors['new_password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" maxlength="50" autocomplete="new-password">
            <?php if (!empty($errors['confirm_password'])): ?><small class="field-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <button class="btn" type="submit">Update Profile</button>
        </form>
    </div>

    <div class="card profile-summary">
        <h2>Account Summary</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Role:</strong> <span class="badge"><?= Auth::roleLabel($user['role']); ?></span></p>
        <p><strong>Status:</strong> <span class="badge badge-green"><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></p>
        <p><strong>Created:</strong> <?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
