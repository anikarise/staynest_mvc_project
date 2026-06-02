<?php
/*
|--------------------------------------------------------------------------
| Host Form View
|--------------------------------------------------------------------------
| Collects structured host contact details while controller logic validates
| linked users, email format, and country-specific phone rules.
|
*/
require APP_ROOT . '/app/views/layouts/header.php';
?>
<section class="container section two-column">
    <div class="form-card">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="muted">A host profile stores the company/owner information used by property listings.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" novalidate>
            <?= Auth::csrfField(); ?>

            <?php if (($mode ?? '') === 'create'): ?>
                <label>Linked Host User</label>
                <select name="user_id" required>
                    <option value="">Select a host user account</option>
                    <?php foreach ($availableUsers as $user): ?>
                        <option value="<?= (int) $user['user_id']; ?>" <?= (string) ($host['user_id'] ?? '') === (string) $user['user_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($user['name'] . ' — ' . $user['email'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['user_id'])): ?><small class="field-error"><?= htmlspecialchars($errors['user_id'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                <?php if (empty($availableUsers)): ?>
                    <small class="muted">No available host users. Register a Host account first, then return here.</small>
                <?php endif; ?>
            <?php else: ?>
                <label>Linked Host User</label>
                <input type="text" value="<?= htmlspecialchars(($host['name'] ?? 'Host') . ' — ' . ($host['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" disabled>
                <input type="hidden" name="user_id" value="<?= (int) ($host['user_id'] ?? 0); ?>">
            <?php endif; ?>

            <label>Company / Host Name</label>
            <input type="text" name="company_name" value="<?= htmlspecialchars($host['company_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <?php if (!empty($errors['company_name'])): ?><small class="field-error"><?= htmlspecialchars($errors['company_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Company Description</label>
            <textarea name="company_description" rows="5" placeholder="Short description about the host or company"><?= htmlspecialchars($host['company_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label>Contact Email</label>
            <input id="hostContactEmailInput" type="email" name="contact_email" value="<?= htmlspecialchars($host['contact_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="host@example.com" autocomplete="email" required>
            <small class="field-error phone-js-error" id="hostContactEmailError" hidden></small>
            <?php if (!empty($errors['contact_email'])): ?><small class="field-error"><?= htmlspecialchars($errors['contact_email'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <label>Phone Number</label>
            <div class="phone-field host-phone-field">
                <select name="country_code" id="hostContactCountryCodeSelect" aria-label="Country code">
                    <?php foreach (($phoneCountries ?? []) as $code => $country): ?>
                        <option
                            value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                            data-min="<?= (int) $country['min']; ?>"
                            data-max="<?= (int) $country['max']; ?>"
                            data-placeholder="<?= htmlspecialchars($country['placeholder'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?= ($host['country_code'] ?? '+45') === $code ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($country['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input id="hostContactPhoneInput" type="tel" name="contact_phone" value="<?= htmlspecialchars($host['contact_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" inputmode="numeric" autocomplete="tel-national" placeholder="12 34 56 78" required>
            </div>
            <small class="muted phone-hint" id="hostContactPhoneHint">Numbers only.</small>
            <small class="field-error phone-js-error" id="hostContactPhoneError" hidden></small>
            <?php if (!empty($errors['contact_phone'])): ?><small class="field-error"><?= htmlspecialchars($errors['contact_phone'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>

            <div class="form-actions">
                <button class="btn" type="submit"><?= ($mode ?? 'create') === 'create' ? 'Save Host Profile' : 'Update Host Profile'; ?></button>
                <a class="btn btn-secondary" href="<?= ($mode ?? '') === 'host-profile' ? URL_ROOT . '/dashboard' : URL_ROOT . '/host'; ?>">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card profile-summary">
        <h2>Important</h2>
        <p>Host profiles must be linked with users whose role is <strong>Host</strong>.</p>
        <p>Deleting a host profile is blocked when that host owns properties. This prevents accidental loss of property and booking records.</p>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
