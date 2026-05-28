<?php
$statLabels = [
    'users' => 'Total Users',
    'properties' => 'Total Properties',
    'approved_properties' => 'Approved Properties',
    'pending_properties' => 'Pending Properties',
    'bookings' => 'Total Bookings',
    'pending_bookings' => 'Pending Bookings',
    'confirmed_bookings' => 'Confirmed Bookings',
    'hosts' => 'Total Hosts',
    'locations' => 'Total Locations',
    'confirmed_revenue' => 'Confirmed Revenue',
    'my_bookings' => 'My Bookings',
    'my_properties' => 'My Properties',
    'property_bookings' => 'Property Bookings',
    'available_properties' => 'Available Properties',
];
?>
<div class="cards-grid dashboard-grid phase6-stats">
    <?php foreach (($stats ?? []) as $key => $value): ?>
        <?php
            $label = $statLabels[$key] ?? ucwords(str_replace('_', ' ', (string) $key));
            $isMoney = strpos((string) $key, 'revenue') !== false || strpos((string) $key, 'price') !== false;
            $display = $isMoney ? 'DKK ' . number_format((float) $value, 2) : number_format((float) $value, 0);
        ?>
        <div class="card stat-card stat-card-modern">
            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
            <strong><?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    <?php endforeach; ?>
</div>
