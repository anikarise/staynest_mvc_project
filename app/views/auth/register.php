<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container auth-page">
    <div class="form-card">
        <div class="auth-logo">
            <img class="logo-img logo-img-large" src="<?= URL_ROOT; ?>/assets/images/staynest-logo.png" alt="StayNest logo">
        </div>
        <h1>Create Account</h1>
        <p class="muted">Customers can start right away. Staff and Host accounts require Main Admin approval.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= URL_ROOT; ?>/auth/register" class="account-validation-form" novalidate>
            <?= Auth::csrfField(); ?>

            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30" autocomplete="name" required>
            <small class="field-error name-js-error" hidden></small>
            <?php if (!empty($errors['name'])): ?><small class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <?php if (!empty($errors['email'])): ?><small class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Phone</label>
            <div class="phone-field">
                <select name="country_code" id="countryCodeSelect" aria-label="Country code">
                    <?php foreach (($phoneCountries ?? []) as $code => $country): ?>
                        <option
                            value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                            data-min="<?= (int) $country['min']; ?>"
                            data-max="<?= (int) $country['max']; ?>"
                            data-placeholder="<?= htmlspecialchars($country['placeholder'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?= ($country_code ?? '+45') === $code ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($country['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input id="phoneInput" type="tel" name="phone" value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>" inputmode="numeric" autocomplete="tel-national" placeholder="12 34 56 78" required>
            </div>
            <small class="muted phone-hint" id="phoneHint">Numbers only.</small>
            <small class="field-error phone-js-error" id="phoneError" hidden></small>
            <?php if (!empty($errors['phone'])): ?><small class="field-error"><?= htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Account Type</label>
            <select name="role" id="roleSelect">
                <option value="customer" <?= ($role ?? 'customer') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                <option value="staff" <?= ($role ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="host" <?= ($role ?? '') === 'host' ? 'selected' : ''; ?>>Host</option>
            </select>
            <?php if (!empty($errors['role'])): ?><small class="field-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <div id="companyField" class="conditional-field">
                <label>Company / Host Name</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($company_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Required only for host accounts">
                <?php if (!empty($errors['company_name'])): ?><small class="field-error"><?= htmlspecialchars($errors['company_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
            </div>

            <label>Password</label>
            <input type="password" name="password" maxlength="50" autocomplete="new-password" required>
            <small class="field-error password-js-error" hidden></small>
            <?php if (!empty($errors['password'])): ?><small class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" maxlength="50" autocomplete="new-password" required>
            <?php if (!empty($errors['confirm_password'])): ?><small class="field-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <button class="btn" type="submit">Create Account</button>
        </form>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
