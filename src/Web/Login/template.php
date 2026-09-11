<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Entrar — HECATE');
?>
<section class="login-panel" aria-labelledby="login-title">
    <img class="login-logo" src="/branding/logo-horizontal.png" alt="HECATE">

    <h1 id="login-title" class="sr-only">Acesso ao HECATE</h1>

    <div class="login-fields" aria-label="Credenciais institucionais">
        <label for="username">Usuário</label>
        <input
            id="username"
            name="username"
            type="text"
            autocomplete="username"
            placeholder="Usuário institucional"
        >

        <label for="password">Senha</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            placeholder="Senha"
        >
    </div>

    <a class="button button-primary button-block login-submit" href="<?= Html::encode($urlGenerator->generate('home')) ?>">
        Entrar
    </a>
</section>
