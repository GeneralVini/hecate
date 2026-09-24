<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var array<string,string> $errors */
/** @var string $username */
/** @var string|null $csrf */

$this->setTitle('Entrar — HECATE');
?>
<form class="login-panel" method="post" novalidate aria-labelledby="login-title">
    <h1 id="login-title" class="sr-only">Acesso ao HECATE</h1>
    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">

    <?php if ($errors !== []) : ?>
        <p class="login-error-summary" role="alert">Confira os campos obrigatórios para continuar.</p>
    <?php endif; ?>

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
                value="<?= Html::encode($username) ?>"
                required
                aria-invalid="<?= isset($errors['username']) ? 'true' : 'false' ?>"
                <?= isset($errors['username']) ? 'aria-describedby="login-username-error"' : '' ?>
            >
        </div>
        <?php if (isset($errors['username'])) : ?>
            <span id="login-username-error" class="login-error" role="alert"><?= Html::encode($errors['username']) ?></span>
        <?php endif; ?>
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
                required
                aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>"
                <?= isset($errors['password']) ? 'aria-describedby="login-password-error"' : '' ?>
            >
        </div>
        <?php if (isset($errors['password'])) : ?>
            <span id="login-password-error" class="login-error" role="alert"><?= Html::encode($errors['password']) ?></span>
        <?php endif; ?>
    </div>

    <button class="button button-primary login-submit" type="submit">Entrar</button>
    <a class="login-manual" href="/manual/index.html" target="_blank" rel="noopener noreferrer" aria-label="Abrir manual do HECATE em nova aba">
        <span class="login-manual__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                <path d="M7 4.5c-1.9 0-3 1.1-3 3v9c0-1.9 1.1-3 3-3h11V4.5H7Z"/>
                <path d="M18 4.5h1a1 1 0 0 1 1 1v11h-1"/>
                <path d="M7 13.5c-1.9 0-3 1.1-3 3"/>
                <path d="M8.5 8h6M8.5 10.5h4.5"/>
            </svg>
        </span>
        <span>Manual</span>
    </a>
</form>
