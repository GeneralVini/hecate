<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Entrar — HECATE');
?>
<section class="login-panel" aria-labelledby="login-title">
    <h1 id="login-title" class="sr-only">Acesso ao HECATE</h1>

    <div class="login-fields" aria-label="Credenciais institucionais">
        <div class="login-field">
            <label for="username">Usuário</label>
            <input
                id="username"
                name="username"
                type="text"
                autocomplete="username"
                placeholder="Usuário institucional"
            >
        </div>

        <div class="login-field">
            <label for="password">Senha</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                placeholder="Senha"
            >
        </div>
    </div>

    <a
        class="button button-primary login-submit"
        href="<?= Html::encode($urlGenerator->generate('home')) ?>"
    >
        Entrar
    </a>
</section>
