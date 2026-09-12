<?php

declare(strict_types=1);

use App\Organization\Query\LocationListItem;
use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var list<LocationListItem> $locations */
/** @var array<string,string> $errors */
/** @var string|null $csrf */

$this->setTitle('Locais — HECATE');
?>
<section class="page-header">
    <p class="eyebrow">Organização</p>
    <h1>Locais</h1>
    <p>Cadastre os locais físicos utilizados para vincular impressoras sem depender de texto livre.</p>
</section>

<?php if (isset($errors['form'])) : ?>
    <p class="field-error" role="alert"><?= Html::encode($errors['form']) ?></p>
<?php endif; ?>

<form class="form-card form-card-compact" method="post">
    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
    <input type="hidden" name="operation" value="create">

    <div class="form-grid form-grid-2">
        <div class="form-field">
            <label for="location-name">Nome</label>
            <input id="location-name" name="name" maxlength="160" required>
        </div>

        <div class="form-field">
            <label for="location-description">Descrição</label>
            <input id="location-description" name="description" maxlength="255">
        </div>
    </div>

    <div class="form-actions form-actions-compact">
        <button class="button" type="submit">Adicionar local</button>
    </div>
</form>

<section class="table-card" aria-label="Locais cadastrados">
    <table>
        <thead>
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Ativo</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $location) : ?>
            <tr>
                <td><?= Html::encode($location->code) ?></td>
                <td colspan="4">
                    <form method="post" class="location-inline-form">
                        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                        <input type="hidden" name="operation" value="update">
                        <input type="hidden" name="id" value="<?= $location->id ?>">
                        <div class="location-inline-grid location-inline-grid-compact">
                            <input name="name" maxlength="160" required value="<?= Html::encode($location->name) ?>" aria-label="Nome">
                            <input name="description" maxlength="255" value="<?= Html::encode($location->description ?? '') ?>" aria-label="Descrição">
                            <label class="inline-checkbox">
                                <input type="checkbox" name="active" value="1"<?= $location->active ? ' checked' : '' ?>>
                                Ativo
                            </label>
                            <div class="form-actions form-actions-compact">
                                <button class="button button-secondary" type="submit">Salvar</button>
                                <button
                                    class="button button-secondary"
                                    type="submit"
                                    name="operation"
                                    value="delete"
                                    onclick="return confirm('Remover este local?');"
                                >Remover</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($locations === []) : ?>
            <tr><td colspan="5">Nenhum local cadastrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
