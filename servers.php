<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

render_header('servers', 'Servers - SkinForge');
?>
<section class="section reveal">
    <div class="section-heading center">
        <div class="eyebrow">Servers</div>
        <h1>Featured Minecraft servers</h1>
        <p>Server listing block that can later connect to live status APIs and pagination.</p>
    </div>
    <div class="server-list">
        <?php foreach ($servers as $server): ?>
            <article class="server-card card">
                <div>
                    <h3><?= e($server['name']) ?></h3>
                    <p><?= e($server['version']) ?></p>
                </div>
                <div class="server-tags">
                    <?php foreach ($server['tags'] as $tag): ?>
                        <span class="tag-chip"><?= e($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="server-status">
                    <strong><?= e($server['players']) ?></strong>
                    <span><?= e($server['status']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
