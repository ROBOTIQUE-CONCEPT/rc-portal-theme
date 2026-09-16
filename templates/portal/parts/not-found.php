<?php declare(strict_types=1); ?>
<section class="rc-error">
    <span class="rc-error__badge">404</span>
    <h1><?php esc_html_e('Page introuvable', 'rc-portal-theme'); ?></h1>
    <p><?php esc_html_e('Cette adresse ne correspond à aucune application disponible dans votre espace.', 'rc-portal-theme'); ?></p>
    <a class="rc-button rc-button--primary" href="<?php echo esc_url(\RC\Portal\Http\PortalRouter::homeUrl()); ?>"><?php esc_html_e('Retour au tableau de bord', 'rc-portal-theme'); ?></a>
</section>
