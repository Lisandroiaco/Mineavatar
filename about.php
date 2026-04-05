<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

render_header('about', 'About - SkinForge');
?>
<section class="static-page reveal">
    <div class="card static-card">
        <div class="eyebrow">About us</div>
        <h1>We build skin discovery platforms with real product logic</h1>
        <p>
            SkinForge was structured to show a production-ready understanding of Minecraft skin ecosystems:
            nickname lookup, API enrichment, profile-skin relationships, categorized content and performance-conscious front-end layout.
        </p>
        <p>
            In a real version, the search pipeline would query Mojang, persist normalized data in MySQL and instantly update home,
            detail, category and related-profile pages from one consistent source.
        </p>
    </div>
</section>
<?php render_footer(); ?>
