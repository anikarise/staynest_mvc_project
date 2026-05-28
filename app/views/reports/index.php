<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<?php
if (!function_exists('staynest_report_rows')) {
    function staynest_report_rows(array $rows, string $labelKey = 'label', string $valueKey = 'total'): void
    {
        if (empty($rows)) {
            echo '<p class="muted">No data available.</p>';
            return;
        }
        echo '<div class="report-table-mini">';
        foreach ($rows as $row) {
            $label = htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($row[$labelKey] ?? 'Unknown'))), ENT_QUOTES, 'UTF-8');
            $value = number_format((float) ($row[$valueKey] ?? 0), 0);
            echo '<div><span>' . $label . '</span><strong>' . $value . '</strong></div>';
        }
        echo '</div>';
    }
}
?>
<section class="container section dashboard-hero print-hide">
    <div>
        <p class="eyebrow">Management Reports</p>
        <h1><?= $isHost ? 'Host Performance Report' : 'StayNest System Report'; ?></h1>
        <p class="muted">A presentation-ready overview of users, hosts, locations, properties, bookings, revenue, and recent system activity.</p>
    </div>
    <div class="quick-actions compact-actions">
        <a class="btn" href="<?= URL_ROOT; ?>/dashboard">Back to Dashboard</a>
        <a class="btn btn-secondary" href="<?= URL_ROOT; ?>/report/export">Export CSV</a>
        <button class="btn btn-secondary" onclick="window.print()">Print Report</button>
    </div>
</section>

<section class="container section section-tight report-print-area">
    <div class="report-header-print">
        <h1>StayNest Report</h1>
        <p>Generated on <?= date('F j, Y, H:i'); ?></p>
    </div>

    <div class="cards-grid dashboard-grid phase6-stats">
        <?php foreach (($summary ?? []) as $key => $value): ?>
            <?php $isMoney = strpos((string) $key, 'revenue') !== false; ?>
            <div class="card stat-card stat-card-modern">
                <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $key)), ENT_QUOTES, 'UTF-8'); ?></span>
                <strong><?= $isMoney ? 'DKK ' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="report-grid-3">
        <div class="card report-panel">
            <h3>Booking Status</h3>
            <?php staynest_report_rows($bookingStatus ?? []); ?>
        </div>
        <div class="card report-panel">
            <h3>Property Status</h3>
            <?php staynest_report_rows($propertyStatus ?? []); ?>
        </div>
        <div class="card report-panel">
            <h3>Property Availability</h3>
            <?php staynest_report_rows($propertyAvailability ?? []); ?>
        </div>
    </div>

    <?php if (!$isHost): ?>
        <div class="report-grid-3">
            <div class="card report-panel">
                <h3>Property Categories</h3>
                <?php staynest_report_rows($propertyCategories ?? []); ?>
            </div>
            <div class="card report-panel">
                <h3>Top Locations</h3>
                <?php if (empty($topLocations ?? [])): ?>
                    <p class="muted">No data available.</p>
                <?php else: ?>
                    <div class="report-table-mini">
                        <?php foreach ($topLocations as $row): ?>
                            <div><span><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></span><strong><?= (int) $row['properties']; ?> properties</strong></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card report-panel">
                <h3>Top Hosts</h3>
                <?php if (empty($topHosts ?? [])): ?>
                    <p class="muted">No data available.</p>
                <?php else: ?>
                    <div class="report-table-mini">
                        <?php foreach ($topHosts as $row): ?>
                            <div><span><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></span><strong><?= (int) $row['properties']; ?> properties</strong></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="report-two-column">
        <div class="card report-panel">
            <h3>Recent Bookings</h3>
            <?php if (empty($recentBookings ?? [])): ?>
                <p class="muted">No recent bookings yet.</p>
            <?php else: ?>
                <div class="table-card flat-table">
                    <table>
                        <thead><tr><th>Customer</th><th>Property</th><th>Dates</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($booking['property_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(($booking['check_in_date'] ?? '') . ' → ' . ($booking['check_out_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge badge-booking-<?= htmlspecialchars($booking['booking_status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords($booking['booking_status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td>DKK <?= number_format((float) ($booking['total_price'] ?? 0), 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card report-panel">
            <h3>Recent Properties</h3>
            <?php if (empty($recentProperties ?? [])): ?>
                <p class="muted">No recent properties yet.</p>
            <?php else: ?>
                <div class="table-card flat-table">
                    <table>
                        <thead><tr><th>Title</th><th>Location</th><th>Status</th><th>Price</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentProperties as $property): ?>
                                <tr>
                                    <td><?= htmlspecialchars($property['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(($property['city'] ?? '') . ' ' . ($property['area'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge badge-status-<?= htmlspecialchars($property['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucwords($property['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td>DKK <?= number_format((float) ($property['price'] ?? 0), 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
