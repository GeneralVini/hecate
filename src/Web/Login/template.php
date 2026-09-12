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
            <label class="sr-only" for="username">Usuário</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"/>
                    </svg>
                </span>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    placeholder="Usuário institucional"
                    aria-label="Usuário"
                >
            </div>
        </div>

        <div class="login-field">
            <label class="sr-only" for="password">Senha</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                        <path d="M7 10V8a5 5 0 0 1 10 0v2M6 10h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z"/>
                    </svg>
                </span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Senha"
                    aria-label="Senha"
                >
            </div>
        </div>
    </div>

    <a
        class="button button-primary login-submit"
        href="<?= Html::encode($urlGenerator->generate('home')) ?>"
    >
        Entrar
    </a>
</section>
