<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container auth-page">
    <div class="form-card">
        <div class="auth-logo">
            <img class="logo-img logo-img-large" src="<?= URL_ROOT; ?>/assets/images/staynest-logo.png" alt="StayNest logo">
        </div>
        <h1>Create Account</h1>
        <p class="muted">Create a Customer, Staff, Host, or Admin account.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= URL_ROOT; ?>/auth/register" novalidate>
            <?= Auth::csrfField(); ?>

            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <?php if (!empty($errors['name'])): ?><small class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <?php if (!empty($errors['email'])): ?><small class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <label>Account Type</label>
            <select name="role" id="roleSelect">
                <option value="customer" <?= ($role ?? 'customer') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                <option value="staff" <?= ($role ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="host" <?= ($role ?? '') === 'host' ? 'selected' : ''; ?>>Host</option>
                <option value="main_admin" <?= ($role ?? '') === 'main_admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            <?php if (!empty($errors['role'])): ?><small class="field-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <div id="companyField" class="conditional-field">
                <label>Company / Host Name</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($company_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Required only for host accounts">
                <?php if (!empty($errors['company_name'])): ?><small class="field-error"><?= htmlspecialchars($errors['company_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
            </div>

            <label>Password</label>
            <input type="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><small class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <?php if (!empty($errors['confirm_password'])): ?><small class="field-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <button class="btn" type="submit">Create Account</button>
        </form>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
