<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Entrar — HECATE');
?>
<section class="login-panel" aria-labelledby="login-title">
    <img class="login-logo" src="/branding/logo-horizontal.png" alt="HECATE">
    <p class="eyebrow">Acesso institucional</p>
    <h1 id="login-title">Controle e Governança de Impressão</h1>
    <p class="login-copy">
        O acesso ao HECATE será realizado por autenticação institucional via Keycloak e LDAP/AD.
    </p>

    <div class="login-status" role="status">
        <strong>POC Yii3</strong>
        <span>Integração Keycloak/OIDC ainda não habilitada nesta etapa.</span>
    </div>

    <a class="button button-primary button-block" href="<?= Html::encode($urlGenerator->generate('home')) ?>">
        Acessar ambiente de validação
    </a>
</section>
