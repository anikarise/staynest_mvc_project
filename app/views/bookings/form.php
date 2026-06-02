<?php
/*
|--------------------------------------------------------------------------
| Booking Form View
|--------------------------------------------------------------------------
| Collects reservation details while controller/model logic validates dates,
| availability, overlap rules, and total price.
|
*/
require APP_ROOT . '/app/views/layouts/header.php';
?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="muted">Select property, dates, and booking status. Total price is calculated from nights × property price.</p>
        </div>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Back to Bookings</a>
    </div>

    <div class="form-card wide-form">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>" class="booking-form">
            <?= Auth::csrfField(); ?>

            <?php if ($isManager): ?>
                <label>Customer / User</label>
                <select name="user_id" required>
                    <option value="">Select user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int) $user['user_id']; ?>" <?= (int) ($booking['user_id'] ?? 0) === (int) $user['user_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($user['name'] . ' — ' . $user['email'] . ' (' . Auth::roleLabel($user['role']) . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['user_id'])): ?><div class="field-error"><?= htmlspecialchars($errors['user_id'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <?php else: ?>
                <input type="hidden" name="user_id" value="<?= (int) Auth::userId(); ?>">
            <?php endif; ?>

            <label>Property</label>
            <select name="property_id" required>
                <option value="">Select property</option>
                <?php foreach ($properties as $property): ?>
                    <?php $locationText = trim(($property['area'] ? $property['area'] . ', ' : '') . $property['city']); ?>
                    <option value="<?= (int) $property['property_id']; ?>" data-price="<?= htmlspecialchars((string) $property['price'], ENT_QUOTES, 'UTF-8'); ?>" <?= (int) ($booking['property_id'] ?? 0) === (int) $property['property_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($property['title'] . ' — ' . $locationText . ' — DKK ' . number_format((float) $property['price'], 2) . '/night', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['property_id'])): ?><div class="field-error"><?= htmlspecialchars($errors['property_id'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

            <div class="form-row-two">
                <div>
                    <label>Check-in Date</label>
                    <input type="date" name="check_in_date" value="<?= htmlspecialchars($booking['check_in_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" min="<?= date('Y-m-d'); ?>" required>
                    <?php if (!empty($errors['check_in_date'])): ?><div class="field-error"><?= htmlspecialchars($errors['check_in_date'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                </div>
                <div>
                    <label>Check-out Date</label>
                    <input type="date" name="check_out_date" value="<?= htmlspecialchars($booking['check_out_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" min="<?= date('Y-m-d', strtotime('+1 day')); ?>" required>
                    <?php if (!empty($errors['check_out_date'])): ?><div class="field-error"><?= htmlspecialchars($errors['check_out_date'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                </div>
            </div>

            <?php if ($isManager): ?>
                <label>Booking Status</label>
                <select name="booking_status">
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?= ($booking['booking_status'] ?? '') === $statusOption ? 'selected' : ''; ?>>
                            <?= htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['booking_status'])): ?><div class="field-error"><?= htmlspecialchars($errors['booking_status'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <?php else: ?>
                <input type="hidden" name="booking_status" value="pending">
            <?php endif; ?>

            <div class="booking-price-preview card">
                <span class="muted">Estimated total</span>
                <strong id="bookingTotalPreview">DKK <?= number_format((float) ($booking['total_price'] ?? 0), 2); ?></strong>
                <small class="muted">Final total is recalculated securely on the server.</small>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit"><?= $mode === 'edit' ? 'Update Booking' : 'Create Booking'; ?></button>
                <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Cancel</a>
            </div>
        </form>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
