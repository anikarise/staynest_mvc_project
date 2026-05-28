<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1><?= $mode === 'manager' ? 'Booking Management' : ($mode === 'host' ? 'Property Booking Overview' : 'My Bookings'); ?></h1>
            <p class="muted">
                <?php if ($mode === 'manager'): ?>
                    Manage booking requests, confirm or reject bookings, update dates, and monitor revenue.
                <?php elseif ($mode === 'host'): ?>
                    View booking activity for your own properties. Approval is handled by booking/property admins.
                <?php else: ?>
                    Track your booking requests, edit pending requests, and cancel active bookings.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($mode !== 'host'): ?>
            <a class="btn" href="<?= URL_ROOT; ?>/booking/create">Add Booking</a>
        <?php endif; ?>
    </div>

    <?php if ($canManage): ?>
        <div class="cards-grid dashboard-grid booking-stats">
            <div class="card stat-card"><span>Total Bookings</span><strong><?= (int) ($stats['total'] ?? 0); ?></strong></div>
            <div class="card stat-card"><span>Pending</span><strong><?= (int) ($stats['pending'] ?? 0); ?></strong></div>
            <div class="card stat-card"><span>Confirmed</span><strong><?= (int) ($stats['confirmed'] ?? 0); ?></strong></div>
            <div class="card stat-card"><span>Confirmed Revenue</span><strong>DKK <?= number_format((float) ($stats['revenue'] ?? 0), 0); ?></strong></div>
        </div>
    <?php endif; ?>

    <form class="toolbar booking-toolbar" method="get" action="<?= URL_ROOT; ?>/booking">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search customer, property, host, city, area">
        <select name="status">
            <option value="">Any status</option>
            <?php foreach ($statuses as $statusOption): ?>
                <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?= ($status ?? '') === $statusOption ? 'selected' : ''; ?>>
                    <?= htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
        <?php if (!empty($search) || !empty($status)): ?>
            <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/booking">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($bookings)): ?>
        <div class="card center">
            <h2>No bookings found</h2>
            <p class="muted">No booking records match your current view or filters.</p>
        </div>
    <?php else: ?>
        <div class="table-card booking-table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                            $locationText = trim(($booking['area'] ? $booking['area'] . ', ' : '') . $booking['city']);
                            $statusClass = 'badge-booking-' . $booking['booking_status'];
                            $isOwner = (int) $booking['user_id'] === Auth::userId();
                            $canCustomerEdit = $mode === 'customer' && $isOwner && $booking['booking_status'] === 'pending';
                            $canCancel = ($canManage || $isOwner) && !in_array($booking['booking_status'], ['rejected', 'cancelled'], true);
                        ?>
                        <tr>
                            <td>#<?= (int) $booking['booking_id']; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small class="muted"><?= htmlspecialchars($booking['customer_email'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($booking['property_title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small class="muted"><?= htmlspecialchars($booking['company_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?= htmlspecialchars($locationText, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?= htmlspecialchars($booking['check_in_date'], ENT_QUOTES, 'UTF-8'); ?><br>
                                <small class="muted">to <?= htmlspecialchars($booking['check_out_date'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>DKK <?= number_format((float) $booking['total_price'], 2); ?></td>
                            <td><span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucfirst($booking['booking_status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td>
                                <div class="row-actions booking-actions">
                                    <?php if ($canManage || $canCustomerEdit): ?>
                                        <a class="btn btn-mini" href="<?= URL_ROOT; ?>/booking/edit/<?= (int) $booking['booking_id']; ?>">Edit</a>
                                    <?php endif; ?>

                                    <?php if ($canManage && $booking['booking_status'] !== 'confirmed'): ?>
                                        <form method="post" action="<?= URL_ROOT; ?>/booking/confirm/<?= (int) $booking['booking_id']; ?>" class="inline-form">
                                            <?= Auth::csrfField(); ?>
                                            <button class="btn btn-mini" type="submit">Confirm</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($canManage && $booking['booking_status'] !== 'rejected'): ?>
                                        <form method="post" action="<?= URL_ROOT; ?>/booking/reject/<?= (int) $booking['booking_id']; ?>" class="inline-form">
                                            <?= Auth::csrfField(); ?>
                                            <button class="btn btn-mini btn-secondary" type="submit">Reject</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($canCancel): ?>
                                        <form method="post" action="<?= URL_ROOT; ?>/booking/cancel/<?= (int) $booking['booking_id']; ?>" class="inline-form">
                                            <?= Auth::csrfField(); ?>
                                            <button class="btn btn-mini btn-warning" type="submit" data-confirm="Cancel this booking?">Cancel</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($canManage): ?>
                                        <form method="post" action="<?= URL_ROOT; ?>/booking/delete/<?= (int) $booking['booking_id']; ?>" class="inline-form">
                                            <?= Auth::csrfField(); ?>
                                            <button class="btn btn-mini btn-danger" type="submit" data-confirm="Permanently delete this booking record?">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
