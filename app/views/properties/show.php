<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<?php
    $imagePath = PUBLIC_PATH . '/uploads/properties/' . ($property['image'] ?? '');
    $hasImage = !empty($property['image']) && is_file($imagePath);
    $locationText = trim(($property['area'] ? $property['area'] . ', ' : '') . $property['city']);
    $bookable = ($property['status'] ?? '') === 'approved' && ($property['availability'] ?? '') === 'available';
    $interiorGallery = [
        [
            'label' => 'Bedroom',
            'filename' => 'interior-bedroom.jpg',
            'alt' => 'Bedroom interior',
        ],
        [
            'label' => 'Bathroom',
            'filename' => 'interior-bathroom.jpg',
            'alt' => 'Bathroom interior',
        ],
        [
            'label' => 'Living Area',
            'filename' => 'interior-living-area.jpg',
            'alt' => 'Living area interior',
        ],
    ];
?>
<section class="container section property-detail-page">
    <div class="property-detail-layout">
        <div>
            <?php if ($hasImage): ?>
                <img class="property-detail-image" src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <div class="property-detail-image property-placeholder"><span><?= htmlspecialchars(substr($property['title'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span></div>
            <?php endif; ?>

            <div class="card property-description-card">
                <h2>Description</h2>
                <p><?= nl2br(htmlspecialchars($property['description'] ?? 'No description added.', ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
        </div>

        <aside class="card property-detail-panel">
            <div class="property-meta-row">
                <span class="badge"><?= htmlspecialchars($property['category'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="badge <?= ($property['availability'] ?? '') === 'available' ? 'badge-green' : 'badge-gray'; ?>">
                    <?= htmlspecialchars(ucfirst($property['availability'] ?? 'Unavailable'), ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <span class="badge badge-status-<?= htmlspecialchars($property['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars(ucfirst($property['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>

            <h1><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="muted property-location">Location: <?= htmlspecialchars($locationText, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if (!empty($property['address'])): ?>
                <p class="muted"><?= htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <strong class="price detail-price">DKK <?= number_format((float) $property['price'], 2); ?> / night</strong>

            <div class="detail-facts">
                <div>
                    <span>Type</span>
                    <strong><?= htmlspecialchars($property['property_type'] ?? $property['category'] ?? 'Property', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div>
                    <span>Bedrooms</span>
                    <strong><?= (int) ($property['bedrooms'] ?? 1); ?></strong>
                </div>
                <div>
                    <span>Bathrooms</span>
                    <strong><?= number_format((float) ($property['bathrooms'] ?? 1), 1); ?></strong>
                </div>
                <div>
                    <span>Host</span>
                    <strong><?= htmlspecialchars($property['company_name'] ?? 'StayNest Host', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <?php if ($bookable): ?>
                <a class="btn detail-book-btn" href="<?= URL_ROOT; ?>/booking/create/<?= (int) $property['property_id']; ?>">Book This Property</a>
            <?php else: ?>
                <button class="btn detail-book-btn btn-secondary" type="button" disabled>Not Available for Booking</button>
            <?php endif; ?>

            <a class="btn btn-secondary detail-back-btn" href="<?= URL_ROOT; ?>/property">Back to Properties</a>
        </aside>
    </div>

    <section class="property-gallery-section" aria-label="Interior gallery">
        <h2>Interior Gallery</h2>
        <div class="property-gallery">
            <?php foreach ($interiorGallery as $galleryImage): ?>
                <?php $galleryImagePath = PUBLIC_PATH . '/uploads/properties/' . $galleryImage['filename']; ?>
                <figure class="property-gallery-item">
                    <?php if (is_file($galleryImagePath)): ?>
                        <img src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($galleryImage['filename'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($galleryImage['alt'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <div class="property-gallery-fallback">Image unavailable</div>
                    <?php endif; ?>
                    <figcaption><?= htmlspecialchars($galleryImage['label'], ENT_QUOTES, 'UTF-8'); ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
