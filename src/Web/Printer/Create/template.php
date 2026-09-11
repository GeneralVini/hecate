<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var array<string,string> $errors */
/** @var array{name:string,host:string,location:string} $values */
/** @var string|null $csrf */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Cadastrar impressora — HECATE');
?>
<section class="page-header">
    <p class="eyebrow">Inventário</p>
    <h1>Cadastrar impressora</h1>
    <p>Cadastre apenas os dados mínimos. A descoberta técnica será realizada pelo hecate-agent.</p>
</section>

<form class="form-card" method="post" action="<?= Html::encode($urlGenerator->generate('printer/create')) ?>">
    <?= $csrf ?? '' ?>

    <label for="name">Nome lógico</label>
    <input id="name" name="name" maxlength="160" required value="<?= Html::encode($values['name']) ?>">
    <?php if (isset($errors['name'])): ?><p class="field-error"><?= Html::encode($errors['name']) ?></p><?php endif; ?>

    <label for="host">IP ou FQDN</label>
    <input id="host" name="host" maxlength="160" required value="<?= Html::encode($values['host']) ?>">
    <?php if (isset($errors['host'])): ?><p class="field-error"><?= Html::encode($errors['host']) ?></p><?php endif; ?>

    <label for="location">Localização</label>
    <input id="location" name="location" maxlength="160" value="<?= Html::encode($values['location']) ?>">

    <div class="form-actions">
        <button class="button" type="submit">Salvar</button>
        <a class="button button-secondary" href="<?= Html::encode($urlGenerator->generate('printer/index')) ?>">Cancelar</a>
    </div>
</form>
