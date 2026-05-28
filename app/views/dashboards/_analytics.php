<?php
if (!function_exists('staynest_render_bar_panel')) {
    function staynest_render_bar_panel(string $title, array $items, string $labelKey = 'label', string $valueKey = 'total'): void
    {
        $max = 0;
        foreach ($items as $item) {
            $max = max($max, (float) ($item[$valueKey] ?? 0));
        }
        ?>
        <div class="card report-panel">
            <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
            <?php if (empty($items)): ?>
                <p class="muted">No data available yet.</p>
            <?php else: ?>
                <div class="bar-list">
                    <?php foreach ($items as $item): ?>
                        <?php
                            $value = (float) ($item[$valueKey] ?? 0);
                            $width = $max > 0 ? max(6, ($value / $max) * 100) : 0;
                        ?>
                        <div class="bar-row">
                            <div class="bar-meta">
                                <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($item[$labelKey] ?? 'Unknown'))), ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?= number_format($value, 0); ?></strong>
                            </div>
                            <div class="bar-track"><div class="bar-fill" style="width: <?= $width; ?>%;"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>
<div class="analytics-grid">
    <?php staynest_render_bar_panel('Booking Status', $analytics['bookingStatus'] ?? []); ?>
    <?php if (!empty($analytics['propertyStatus'])): ?>
        <?php staynest_render_bar_panel('Property Status', $analytics['propertyStatus']); ?>
    <?php endif; ?>
    <?php if (!empty($analytics['propertyAvailability'])): ?>
        <?php staynest_render_bar_panel('Property Availability', $analytics['propertyAvailability']); ?>
    <?php endif; ?>
    <?php if (!empty($analytics['propertyCategories'])): ?>
        <?php staynest_render_bar_panel('Property Categories', $analytics['propertyCategories']); ?>
    <?php endif; ?>
    <?php if (!empty($analytics['userRoles'])): ?>
        <?php staynest_render_bar_panel('User Roles', $analytics['userRoles']); ?>
    <?php endif; ?>
    <?php if (!empty($analytics['monthlyRevenue'])): ?>
        <div class="card report-panel">
            <h3>Monthly Confirmed Revenue</h3>
            <div class="bar-list">
                <?php
                    $maxRevenue = 0;
                    foreach ($analytics['monthlyRevenue'] as $month) {
                        $maxRevenue = max($maxRevenue, (float) ($month['revenue'] ?? 0));
                    }
                ?>
                <?php foreach ($analytics['monthlyRevenue'] as $month): ?>
                    <?php
                        $revenue = (float) ($month['revenue'] ?? 0);
                        $width = $maxRevenue > 0 ? max(6, ($revenue / $maxRevenue) * 100) : 0;
                    ?>
                    <div class="bar-row">
                        <div class="bar-meta">
                            <span><?= htmlspecialchars((string) ($month['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong>DKK <?= number_format($revenue, 2); ?></strong>
                        </div>
                        <div class="bar-track"><div class="bar-fill" style="width: <?= $width; ?>%;"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="report-two-column">
    <div class="card report-panel">
        <h3>Recent Bookings</h3>
        <?php if (empty($analytics['recentBookings'] ?? [])): ?>
            <p class="muted">No recent bookings yet.</p>
        <?php else: ?>
            <div class="compact-list">
                <?php foreach ($analytics['recentBookings'] as $booking): ?>
                    <div class="compact-item">
                        <div>
                            <strong><?= htmlspecialchars($booking['property_title'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?= htmlspecialchars($booking['customer_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($booking['check_in_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?> to <?= htmlspecialchars($booking['check_out_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <span class="badge badge-booking-<?= htmlspecialchars($booking['booking_status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords($booking['booking_status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card report-panel">
        <h3>Recent Properties</h3>
        <?php if (empty($analytics['recentProperties'] ?? [])): ?>
            <p class="muted">No recent properties yet.</p>
        <?php else: ?>
            <div class="compact-list">
                <?php foreach ($analytics['recentProperties'] as $property): ?>
                    <div class="compact-item">
                        <?php
                            $imagePath = PUBLIC_PATH . '/uploads/properties/' . ($property['image'] ?? '');
                            $hasImage = !empty($property['image']) && is_file($imagePath);
                        ?>
                        <?php if ($hasImage): ?>
                            <img class="compact-thumb" src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($property['title'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="compact-thumb compact-thumb-fallback"><?= htmlspecialchars(substr($property['title'] ?? 'SN', 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($property['title'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?= htmlspecialchars(($property['city'] ?? '') . ' ' . ($property['area'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> · DKK <?= number_format((float) ($property['price'] ?? 0), 2); ?></span>
                        </div>
                        <span class="badge badge-status-<?= htmlspecialchars($property['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords($property['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
