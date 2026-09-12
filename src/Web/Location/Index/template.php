<?php

declare(strict_types=1);

use App\Organization\Query\LocationListItem;
use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var list<LocationListItem> $locations */
/** @var array<string,string> $errors */
/** @var array{type: string, message: string}|null $flash */
/** @var array{code: string, name: string, description: string, active: string} $filters */
/** @var int $page */
/** @var int $pageCount */
/** @var int $pageSize */
/** @var int $total */
/** @var string|null $csrf */

$this->setTitle('Locais — HECATE');

$pageUrl = static function (int $targetPage) use ($filters): string {
    $query = array_filter(
        [...$filters, 'page' => $targetPage],
        static fn (string|int $value): bool => $value !== '',
    );

    return '?' . http_build_query($query);
};
?>
<section class="page-header">
    <p class="eyebrow">Organização</p>
    <h1>Locais</h1>
    <p>Cadastre os locais físicos utilizados para vincular impressoras sem depender de texto livre.</p>
</section>

<?php if ($flash !== null) : ?>
    <div class="flash-message flash-message-<?= Html::encode($flash['type']) ?>" role="status" data-flash-message>
        <svg viewBox="0 0 16 16" aria-hidden="true">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M7 11.5a.5.5 0 0 0 1 0V7a.5.5 0 0 0-1 0zm.5-6.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5"/>
        </svg>
        <span><?= Html::encode($flash['message']) ?></span>
    </div>
<?php endif; ?>

<?php if (isset($errors['form'])) : ?>
    <div class="flash-message flash-message-danger" role="alert" data-flash-message>
        <svg viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
        </svg>
        <span><?= Html::encode($errors['form']) ?></span>
    </div>
<?php endif; ?>

<div class="page-toolbar">
    <button class="button button-with-icon button-bs-warning" type="button" data-location-modal-create>
        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
        Cadastrar
    </button>
</div>

<section class="table-card" aria-label="Locais cadastrados">
    <form class="data-grid-filters" method="get">
        <div class="data-grid-filter-field">
            <label for="filter-code">Código</label>
            <input id="filter-code" name="code" value="<?= Html::encode($filters['code']) ?>" placeholder="Buscar código">
        </div>
        <div class="data-grid-filter-field">
            <label for="filter-name">Nome</label>
            <input id="filter-name" name="name" value="<?= Html::encode($filters['name']) ?>" placeholder="Buscar nome">
        </div>
        <div class="data-grid-filter-field">
            <label for="filter-description">Descrição</label>
            <input id="filter-description" name="description" value="<?= Html::encode($filters['description']) ?>" placeholder="Buscar descrição">
        </div>
        <div class="data-grid-filter-field data-grid-filter-field--status">
            <label for="filter-active">Ativo</label>
            <select id="filter-active" name="active">
                <option value="">Todos</option>
                <option value="1"<?= $filters['active'] === '1' ? ' selected' : '' ?>>Ativos</option>
                <option value="0"<?= $filters['active'] === '0' ? ' selected' : '' ?>>Inativos</option>
            </select>
        </div>
        <div class="data-grid-filter-actions">
            <button class="button button-with-icon button-bs-primary" type="submit" title="Filtrar">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .4.8L10 7.667V13.5a.5.5 0 0 1-.757.429l-3-1.8A.5.5 0 0 1 6 11.7V7.667L1.6 1.8a.5.5 0 0 1-.1-.3"/></svg>
                Filtrar
            </button>
            <a class="button button-with-icon button-bs-secondary" href="?" title="Limpar filtros">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2.5 2.5a.5.5 0 0 1 .707 0L8 7.293 12.793 2.5a.5.5 0 1 1 .707.707L8.707 8l4.793 4.793a.5.5 0 0 1-.707.707L8 8.707 3.207 13.5a.5.5 0 0 1-.707-.707L7.293 8 2.5 3.207a.5.5 0 0 1 0-.707"/></svg>
                Limpar
            </a>
        </div>
    </form>

    <table class="data-grid">
        <thead>
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Ativo</th>
            <th class="grid-actions">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $location) : ?>
            <tr>
                <td><?= Html::encode($location->code) ?></td>
                <td><?= Html::encode($location->name) ?></td>
                <td><?= Html::encode($location->description ?? '—') ?></td>
                <td><?= $location->active ? 'Sim' : 'Não' ?></td>
                <td class="grid-actions">
                    <div class="grid-action-group">
                        <button
                            class="icon-button icon-button-primary"
                            type="button"
                            title="Editar"
                            aria-label="Editar local <?= Html::encode($location->name) ?>"
                            data-location-modal-edit
                            data-location-id="<?= $location->id ?>"
                            data-location-name="<?= Html::encode($location->name) ?>"
                            data-location-description="<?= Html::encode($location->description ?? '') ?>"
                            data-location-active="<?= $location->active ? '1' : '0' ?>"
                        >
                            <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1-2-2 1-1a.5.5 0 0 1 .707 0zM13.5 4.207l-2-2L4.939 8.768a2 2 0 0 0-.497.832l-.94 3.132a.5.5 0 0 0 .621.621l3.132-.94a2 2 0 0 0 .832-.497zM1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                        </button>

                        <form method="post" onsubmit="return confirm('Remover este local?');">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id" value="<?= $location->id ?>">
                            <button
                                class="icon-button icon-button-danger"
                                type="submit"
                                title="Remover"
                                aria-label="Remover local <?= Html::encode($location->name) ?>"
                            >
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H6V1a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h3.5a1 1 0 0 1 1 1M4 4v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4zm3-2h2V1H7z"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($locations === []) : ?>
            <tr><td colspan="5">Nenhum local encontrado para os filtros informados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <footer class="data-grid-footer">
        <div class="data-grid-result-count">
            <?php if ($total === 0) : ?>
                Nenhum resultado
            <?php else : ?>
                <?php $first = (($page - 1) * $pageSize) + 1; ?>
                <?php $last = min($page * $pageSize, $total); ?>
                Exibindo <?= $first ?>–<?= $last ?> de <strong><?= $total ?></strong> resultado<?= $total === 1 ? '' : 's' ?>
            <?php endif; ?>
        </div>

        <?php if ($pageCount > 1) : ?>
            <nav class="data-grid-pagination" aria-label="Paginação de locais">
                <a class="data-grid-page<?= $page === 1 ? ' is-disabled' : '' ?>" href="<?= $page === 1 ? '#' : Html::encode($pageUrl($page - 1)) ?>" aria-label="Página anterior">‹</a>
                <?php for ($number = 1; $number <= $pageCount; $number++) : ?>
                    <a
                        class="data-grid-page<?= $number === $page ? ' is-current' : '' ?>"
                        href="<?= Html::encode($pageUrl($number)) ?>"
                        <?= $number === $page ? 'aria-current="page"' : '' ?>
                    ><?= $number ?></a>
                <?php endfor; ?>
                <a class="data-grid-page<?= $page === $pageCount ? ' is-disabled' : '' ?>" href="<?= $page === $pageCount ? '#' : Html::encode($pageUrl($page + 1)) ?>" aria-label="Próxima página">›</a>
            </nav>
        <?php endif; ?>
    </footer>
</section>

<div class="modal-backdrop" data-location-modal aria-hidden="true">
    <section class="app-modal" role="dialog" aria-modal="true" aria-labelledby="location-modal-title">
        <header class="app-modal__header">
            <h2 id="location-modal-title" data-location-modal-title>Cadastrar local</h2>
            <button class="icon-button icon-button-danger" type="button" data-location-modal-close title="Fechar" aria-label="Fechar">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>
            </button>
        </header>

        <div class="app-modal__body">
            <form method="post" class="form-card-compact" data-location-form>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                <input type="hidden" name="operation" value="create" data-location-operation>
                <input type="hidden" name="id" value="" data-location-id>

                <div class="form-grid form-grid-2">
                    <div class="form-field">
                        <label for="location-modal-name">Nome</label>
                        <input id="location-modal-name" name="name" maxlength="160" required data-location-name>
                    </div>

                    <div class="form-field">
                        <label for="location-modal-description">Descrição</label>
                        <input id="location-modal-description" name="description" maxlength="255" data-location-description>
                    </div>

                    <label class="form-switch" data-location-active-field hidden>
                        <input type="checkbox" name="active" value="1" data-location-active>
                        <span class="form-switch__control" aria-hidden="true"></span>
                        <span>Ativo</span>
                    </label>
                </div>

                <div class="app-modal__footer">
                    <button class="button button-bs-secondary" type="button" data-location-modal-close>Cancelar</button>
                    <button class="button button-with-icon button-bs-warning" type="submit">
                        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093z"/></svg>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
