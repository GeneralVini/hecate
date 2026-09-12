<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var array<string,string> $errors */
/** @var array{name:string,host:string,location_id:string} $values */
/** @var array<int,string> $locations */
/** @var string|null $csrf */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Cadastrar impressora — HECATE');

$indexUrl = $urlGenerator->generate('printer/index');
$baseUrl = $_ENV['HECATE_BASE_URL'] ?? '';
if (is_string($baseUrl) && $baseUrl !== '' && str_starts_with($indexUrl, '/')) {
    $indexUrl = rtrim($baseUrl, '/') . $indexUrl;
}
?>
<section class="page-header">
    <p class="eyebrow">Inventário</p>
    <h1>Cadastrar impressora</h1>
    <p>Cadastre apenas os dados mínimos. A descoberta técnica será realizada pelo hecate-agent.</p>
</section>

<form class="form-card" method="post">
    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">

    <?php if (isset($errors['form'])) : ?>
        <p class="field-error" role="alert"><?= Html::encode($errors['form']) ?></p>
    <?php endif; ?>

    <label for="name">Nome lógico</label>
    <input id="name" name="name" maxlength="160" required value="<?= Html::encode($values['name']) ?>">
    <?php if (isset($errors['name'])) : ?>
        <p class="field-error"><?= Html::encode($errors['name']) ?></p>
    <?php endif; ?>

    <label for="host">IP ou FQDN</label>
    <input id="host" name="host" maxlength="160" required value="<?= Html::encode($values['host']) ?>">
    <?php if (isset($errors['host'])) : ?>
        <p class="field-error"><?= Html::encode($errors['host']) ?></p>
    <?php endif; ?>

    <label for="location_id">Local</label>
    <select id="location_id" name="location_id">
        <option value="">Sem local definido</option>
        <?php foreach ($locations as $id => $label) : ?>
            <option value="<?= $id ?>"<?= $values['location_id'] === (string) $id ? ' selected' : '' ?>>
                <?= Html::encode($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['location_id'])) : ?>
        <p class="field-error"><?= Html::encode($errors['location_id']) ?></p>
    <?php endif; ?>

    <?php if ($locations === []) : ?>
        <p class="field-hint">Nenhum local ativo cadastrado. Cadastre um local em Organização → Locais.</p>
    <?php endif; ?>

    <div class="form-actions">
        <button class="button" type="submit">Salvar</button>
        <a class="button button-secondary" href="<?= Html::encode($indexUrl) ?>">Cancelar</a>
    </div>
</form>
