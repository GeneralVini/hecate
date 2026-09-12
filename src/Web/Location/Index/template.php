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

<form class="form-card" method="post">
    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
    <input type="hidden" name="operation" value="create">

    <label for="location-code">Código</label>
    <input id="location-code" name="code" maxlength="32" required>

    <label for="location-name">Nome</label>
    <input id="location-name" name="name" maxlength="160" required>

    <label for="location-description">Descrição</label>
    <input id="location-description" name="description" maxlength="255">

    <div class="form-actions">
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
                <td colspan="5">
                    <form method="post" class="location-inline-form">
                        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                        <input type="hidden" name="operation" value="update">
                        <input type="hidden" name="id" value="<?= $location->id ?>">
                        <div class="location-inline-grid">
                            <input name="code" maxlength="32" required value="<?= Html::encode($location->code) ?>" aria-label="Código">
                            <input name="name" maxlength="160" required value="<?= Html::encode($location->name) ?>" aria-label="Nome">
                            <input name="description" maxlength="255" value="<?= Html::encode($location->description ?? '') ?>" aria-label="Descrição">
                            <label>
                                <input type="checkbox" name="active" value="1"<?= $location->active ? ' checked' : '' ?>>
                                Ativo
                            </label>
                            <div class="form-actions">
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
