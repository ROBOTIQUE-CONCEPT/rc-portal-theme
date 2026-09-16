<?php
/** @var \RC\Portal\Module\EmbeddedModuleInterface $module */

declare(strict_types=1);

$descriptor = $module->descriptor();
?>
<header class="rc-page-header">
    <div>
        <h1><?php echo esc_html($descriptor->label); ?></h1>
    </div>
</header>

<section class="rc-module-surface">
    <div class="rc-empty">
        <span class="rc-empty__icon" aria-hidden="true"><?php echo esc_html($descriptor->icon); ?></span>
        <strong><?php echo esc_html($descriptor->label); ?></strong>
        <p><?php echo esc_html($descriptor->description); ?></p>
    </div>
</section>
