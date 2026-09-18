<?php
/**
 * Generic renderer for a page resolved through RC Core's UI Registry
 * (a module dashboard or one of its declared child pages).
 *
 * No `<header class="rc-page-header">` is rendered here: the page markup
 * below (produced by the owning module's own renderer) already provides
 * its own `.rc-page-header` — as a `<header>` element — with the real
 * title/eyebrow/description. Rendering a second, generic one here used to
 * duplicate the page title; the module's own header is now the only one.
 *
 * @var \RC\Portal\Http\RouteContext $context
 */

declare(strict_types=1);
?>
<section class="rc-module-surface">
    <?php
    // The page markup is produced by the owning module's own renderer
    // (registered through `rc_register_ui_page()`); the theme only frames it.
    echo $context->pageHtml;
    ?>
</section>
