<?php
/** @var \RC\Portal\Http\RouteContext $context */

declare(strict_types=1);

use RC\Portal\Http\PortalRouter;

$redirect = isset($_GET['redirect_to'])
    ? sanitize_text_field(wp_unslash((string) $_GET['redirect_to']))
    : PortalRouter::homeUrl();
?>
<div class="rc-login-shell">
    <div class="rc-login-stage">
        <aside class="rc-login-aside">
            <div class="rc-login-aside__brand">
                <span class="rc-login-mark" aria-hidden="true">RC</span>
                <div>
                    <strong><?php esc_html_e('Robotique Concept', 'rc-portal-theme'); ?></strong>
                    <span><?php esc_html_e('Plateforme métier', 'rc-portal-theme'); ?></span>
                </div>
            </div>

            <div class="rc-login-aside__content">
                <span class="rc-login-kicker"><?php esc_html_e('Espace privé', 'rc-portal-theme'); ?></span>
                <h2><?php esc_html_e('Un point d’accès unique à votre environnement Robotique Concept.', 'rc-portal-theme'); ?></h2>
                <p><?php esc_html_e('Les outils et données disponibles sont adaptés automatiquement à votre profil et à vos autorisations.', 'rc-portal-theme'); ?></p>
            </div>

            <div class="rc-login-aside__meta">
                <span aria-hidden="true"></span>
                <?php esc_html_e('Accès sécurisé', 'rc-portal-theme'); ?>
            </div>
        </aside>

        <main class="rc-login-card">
            <div class="rc-login-brand rc-login-brand--compact">
                <span class="rc-login-mark" aria-hidden="true">RC</span>
                <div>
                    <strong><?php esc_html_e('Robotique Concept', 'rc-portal-theme'); ?></strong>
                    <span><?php esc_html_e('Plateforme métier', 'rc-portal-theme'); ?></span>
                </div>
            </div>

            <div class="rc-login-heading">
                <span class="rc-eyebrow"><?php esc_html_e('Authentification', 'rc-portal-theme'); ?></span>
                <h1><?php esc_html_e('Bon retour parmi nous', 'rc-portal-theme'); ?></h1>
                <p class="rc-muted"><?php esc_html_e('Connectez-vous pour accéder à votre espace.', 'rc-portal-theme'); ?></p>
            </div>

            <?php if ($context->message !== null) : ?>
                <div class="rc-portal-alert rc-portal-alert--error" role="alert"><?php echo esc_html($context->message); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(PortalRouter::loginUrl()); ?>" class="rc-login-form">
                <input type="hidden" name="rc_portal_action" value="login">
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect); ?>">
                <?php wp_nonce_field('rc_portal_login', 'rc_portal_login_nonce'); ?>

                <label>
                    <span><?php esc_html_e('Identifiant ou e-mail', 'rc-portal-theme'); ?></span>
                    <input type="text" name="log" autocomplete="username" required autofocus>
                </label>

                <label>
                    <span><?php esc_html_e('Mot de passe', 'rc-portal-theme'); ?></span>
                    <input type="password" name="pwd" autocomplete="current-password" required>
                </label>

                <div class="rc-login-options">
                    <label class="rc-check">
                        <input type="checkbox" name="rememberme" value="1">
                        <span><?php esc_html_e('Rester connecté', 'rc-portal-theme'); ?></span>
                    </label>
                    <a class="rc-login-help" href="<?php echo esc_url(wp_lostpassword_url(PortalRouter::homeUrl())); ?>"><?php esc_html_e('Mot de passe oublié ?', 'rc-portal-theme'); ?></a>
                </div>

                <div class="rc-portal__turnstile">
                    <?php do_action('rc_portal_login_form'); ?>
                </div>

                <button class="rc-button rc-button--primary rc-button--wide" type="submit"><?php esc_html_e('Se connecter', 'rc-portal-theme'); ?></button>
            </form>
        </main>
    </div>
</div>
