<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<main class="home-page">
    <section class="hero home-hero">
        <div class="container hero-content">
            <span class="hero-kicker">StayNest Copenhagen</span>
            <h1>StayNest</h1>
            <p>Find your place, feel at home.</p>
            <form class="search-box" action="<?= URL_ROOT; ?>/property" method="get">
                <input type="text" name="search" placeholder="Search by city, area, or property name">
                <button type="submit">Search</button>
            </form>
            <div class="hero-actions">
                <a class="btn" href="<?= URL_ROOT; ?>/property">Properties</a>
                <a class="btn btn-outline" href="<?= URL_ROOT; ?>/auth/register">Register</a>
            </div>
        </div>
    </section>

    <section class="company-card-section">
        <div class="container">
            <h2>StayNest Company Card</h2>
            <div class="business-card-frame">
                <img src="<?= URL_ROOT; ?>/assets/images/business-card.png" alt="StayNest company business card">
            </div>
        </div>
    </section>

    <section class="container section featured-home-section">
        <div class="section-heading">
            <div>
                <span class="hero-kicker">Curated Stays</span>
                <h2>Featured Property Section</h2>
            </div>
            <a class="btn btn-small" href="<?= URL_ROOT; ?>/property">View All</a>
        </div>
        <div class="cards-grid">
            <?php foreach (($featuredProperties ?? []) as $property): ?>
                <?php
                    $imagePath = PUBLIC_PATH . '/uploads/properties/' . ($property['image'] ?? '');
                    $hasImage = !empty($property['image']) && is_file($imagePath);
                    $locationText = trim(($property['area'] ? $property['area'] . ', ' : '') . $property['city']);
                ?>
                <div class="card property-card">
                    <?php if ($hasImage): ?>
                        <img class="property-image" src="<?= URL_ROOT; ?>/uploads/properties/<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <div class="property-image property-placeholder"><span><?= htmlspecialchars(substr($property['title'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?= htmlspecialchars($locationText, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="price">DKK <?= number_format((float) $property['price'], 2); ?></p>
                    <a class="btn btn-small" href="<?= URL_ROOT; ?>/property/show/<?= (int) $property['property_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
