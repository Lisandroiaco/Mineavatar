<?php

declare(strict_types=1);

require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/layout.php';

render_header('contact', 'Contact - SkinForge');
?>
<section class="static-page reveal">
    <div class="card static-card">
        <div class="eyebrow">Contact</div>
        <h1>Let us shape your production-ready skin platform</h1>
        <p>Use this page as the contact hub for custom builds, backend integrations, admin tooling and premium Minecraft profile features.</p>
        <form class="contact-form">
            <label>
                Name
                <input type="text" placeholder="Your name">
            </label>
            <label>
                Email
                <input type="email" placeholder="you@example.com">
            </label>
            <label>
                Message
                <textarea rows="5" placeholder="Tell us what you need"></textarea>
            </label>
            <button class="btn btn--primary" type="button">Send message</button>
        </form>
    </div>
</section>
<?php render_footer(); ?>
