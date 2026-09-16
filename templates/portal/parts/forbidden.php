<?php declare(strict_types=1); ?>
<section class="rc-error">
    <span class="rc-error__badge">403</span>
    <h1><?php esc_html_e('Accès non autorisé', 'rc-portal-theme'); ?></h1>
    <p><?php esc_html_e('Votre profil ne dispose pas des autorisations nécessaires pour accéder à cette application.', 'rc-portal-theme'); ?></p>
    <a class="rc-button rc-button--primary" href="<?php echo esc_url(\RC\Portal\Http\PortalRouter::homeUrl()); ?>"><?php esc_html_e('Retour au tableau de bord', 'rc-portal-theme'); ?></a>
</section>
