<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container auth-page">
    <div class="form-card">
        <div class="auth-logo">
            <img class="logo-img logo-img-large" src="<?= URL_ROOT; ?>/assets/images/staynest-logo.png" alt="StayNest logo">
        </div>
        <h1>Login</h1>
        <p class="muted">Login with your demo account or a newly registered account.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= URL_ROOT; ?>/auth/login" novalidate>
            <?= Auth::csrfField(); ?>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="admin@staynest.test" required>
            <?php if (!empty($errors['email'])): ?><small class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Password</label>
            <input type="password" name="password" placeholder="password123" required>
            <?php if (!empty($errors['password'])): ?><small class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <button class="btn" type="submit">Login</button>
        </form>

        <p class="auth-switch muted">
            New to StayNest?
            <a href="<?= URL_ROOT; ?>/auth/register">Create a customer, staff, host, or admin account</a>.
        </p>

        <div class="demo-box">
            <strong>Demo login accounts</strong>
            <p class="muted">Password for all demo accounts: <b>password123</b></p>
            <ul>
                <li>Main Admin: admin@staynest.test</li>
                <li>Booking & Property Admin: bookingadmin@staynest.test</li>
                <li>Host & Location Admin: hostadmin@staynest.test</li>
                <li>Host: host@staynest.test</li>
                <li>Customer: customer@staynest.test</li>
                <li>Staff: staff@staynest.test</li>
            </ul>
        </div>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
