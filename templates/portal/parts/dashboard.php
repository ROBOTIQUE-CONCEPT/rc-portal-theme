<?php
/**
 * @var array<string, array{
 *     id: string, label: string, icon: string, description: string,
 *     version: string, url: string,
 *     children: list<array{route: string, label: string}>
 * }> $navigation
 */

declare(strict_types=1);

$user = wp_get_current_user();
$displayName = trim((string) $user->first_name) !== ''
    ? (string) $user->first_name
    : (trim((string) $user->display_name) !== '' ? (string) $user->display_name : (string) $user->user_login);
?>
<section class="rc-dashboard-hero">
    <div>
        <span class="rc-eyebrow"><?php esc_html_e('Votre espace', 'rc-portal-theme'); ?></span>
        <h1><?php echo esc_html(sprintf(__('Bonjour %s', 'rc-portal-theme'), $displayName)); ?></h1>
        <p><?php esc_html_e('Retrouvez vos outils, données et opérations depuis un espace unique.', 'rc-portal-theme'); ?></p>
    </div>
    <div class="rc-dashboard-date" aria-label="<?php esc_attr_e('Date du jour', 'rc-portal-theme'); ?>">
        <span><?php echo esc_html(wp_date('l')); ?></span>
        <strong><?php echo esc_html(wp_date('j F')); ?></strong>
    </div>
</section>

<section aria-labelledby="rc-dashboard-applications">
    <div class="rc-section-heading">
        <div>
            <span class="rc-eyebrow"><?php esc_html_e('Applications', 'rc-portal-theme'); ?></span>
            <h2 id="rc-dashboard-applications"><?php esc_html_e('Votre espace de travail', 'rc-portal-theme'); ?></h2>
        </div>
    </div>

    <div class="rc-module-grid">
        <?php foreach ($navigation as $entry) : ?>
            <?php $icon = trim((string) $entry['icon']) !== '' ? (string) $entry['icon'] : strtoupper(substr($entry['label'], 0, 2)); ?>
            <a class="rc-module-card" href="<?php echo esc_url($entry['url']); ?>">
                <div class="rc-module-card__top">
                    <span class="rc-module-card__icon" aria-hidden="true"><?php echo esc_html($icon); ?></span>
                    <span class="rc-module-card__arrow" aria-hidden="true">↗</span>
                </div>
                <div class="rc-module-card__body">
                    <span class="rc-module-card__eyebrow"><?php esc_html_e('Module', 'rc-portal-theme'); ?></span>
                    <strong><?php echo esc_html($entry['label']); ?></strong>
                    <p><?php echo esc_html($entry['description']); ?></p>
                </div>
                <div class="rc-module-card__footer">
                    <?php echo esc_html(sprintf(__('Version %s', 'rc-portal-theme'), $entry['version'])); ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
