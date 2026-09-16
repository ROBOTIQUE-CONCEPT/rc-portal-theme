<?php
/**
 * Generic renderer for a page resolved through RC Core's UI Registry
 * (a module dashboard or one of its declared child pages).
 *
 * @var \RC\Portal\Http\RouteContext $context
 */

declare(strict_types=1);

$label = (string) $context->pageLabel;
?>
<header class="rc-page-header">
    <div>
        <h1><?php echo esc_html($label); ?></h1>
    </div>
</header>

<section class="rc-module-surface">
    <?php
    // The page markup is produced by the owning module's own renderer
    // (registered through `rc_register_ui_page()`); the theme only frames it.
    echo $context->pageHtml;
    ?>
</section>
