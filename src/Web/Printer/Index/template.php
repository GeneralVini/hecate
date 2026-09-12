<?php

declare(strict_types=1);

use App\Printing\Query\PrinterListItem;
use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var list<PrinterListItem> $printers */
/** @var array<int,string> $locations */
/** @var array<string,string> $errors */
/** @var array{type: string, message: string}|null $flash */
/** @var array{name: string, host: string, location: string, active: string} $filters */
/** @var string $sort */
/** @var string $direction */
/** @var int $page */
/** @var int $pageCount */
/** @var int $pageSize */
/** @var int $total */
/** @var string|null $csrf */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Impressoras — HECATE');

$queryBase = [...$filters, 'sort' => $sort, 'direction' => $direction];
$pageUrl = static function (int $targetPage) use ($queryBase): string {
    $query = array_filter([...$queryBase, 'page' => $targetPage], static fn (string|int $value): bool => $value !== '');
    return '?' . http_build_query($query);
};
$sortUrl = static function (string $column) use ($filters, $sort, $direction): string {
    $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    $query = array_filter([...$filters, 'sort' => $column, 'direction' => $nextDirection], static fn (string $value): bool => $value !== '');
    return '?' . http_build_query($query);
};
$sortMark = static fn (string $column): string => $sort === $column ? ($direction === 'asc' ? '↑' : '↓') : '↕';
?>
<section class="page-header">
    <p class="eyebrow">Impressão</p>
    <h1>Impressoras</h1>
    <p>Equipamentos gerenciados pelo fluxo controlado do HECATE.</p>
</section>

<?php if ($flash !== null) : ?>
    <div class="flash-message flash-message-<?= Html::encode($flash['type']) ?>" role="status" data-flash-message>
        <span><?= Html::encode($flash['message']) ?></span>
    </div>
<?php endif; ?>

<?php if (isset($errors['form'])) : ?>
    <div class="flash-message flash-message-danger" role="alert" data-flash-message>
        <span><?= Html::encode($errors['form']) ?></span>
    </div>
<?php endif; ?>

<div class="page-toolbar">
    <button class="button button-with-icon button-bs-warning" type="button" data-printer-modal-create>
        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
        Cadastrar
    </button>
</div>

<section class="table-card" aria-label="Impressoras cadastradas">
    <form class="data-grid-filters" method="get">
        <input type="hidden" name="sort" value="<?= Html::encode($sort) ?>">
        <input type="hidden" name="direction" value="<?= Html::encode($direction) ?>">
        <div class="data-grid-filter-field">
            <label for="printer-filter-name">Nome</label>
            <input id="printer-filter-name" name="name" value="<?= Html::encode($filters['name']) ?>" placeholder="Buscar nome">
        </div>
        <div class="data-grid-filter-field">
            <label for="printer-filter-host">Host</label>
            <input id="printer-filter-host" name="host" value="<?= Html::encode($filters['host']) ?>" placeholder="Buscar IP/FQDN">
        </div>
        <div class="data-grid-filter-field">
            <label for="printer-filter-location">Local</label>
            <input id="printer-filter-location" name="location" value="<?= Html::encode($filters['location']) ?>" placeholder="Buscar local">
        </div>
        <div class="data-grid-filter-field data-grid-filter-field--status">
            <label for="printer-filter-active">Ativo</label>
            <select id="printer-filter-active" name="active">
                <option value="">Todos</option>
                <option value="1"<?= $filters['active'] === '1' ? ' selected' : '' ?>>Ativos</option>
                <option value="0"<?= $filters['active'] === '0' ? ' selected' : '' ?>>Inativos</option>
            </select>
        </div>
        <div class="data-grid-filter-actions">
            <button class="button button-bs-primary" type="submit" hidden>Filtrar</button>
            <a class="button button-with-icon button-bs-secondary" href="?" title="Limpar filtros">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2.5 2.5a.5.5 0 0 1 .707 0L8 7.293 12.793 2.5a.5.5 0 1 1 .707.707L8.707 8l4.793 4.793a.5.5 0 0 1-.707.707L8 8.707 3.207 13.5a.5.5 0 0 1-.707-.707L7.293 8 2.5 3.207a.5.5 0 0 1 0-.707"/></svg>
                Limpar
            </a>
        </div>
    </form>

    <table class="data-grid">
        <thead>
        <tr>
            <th><a class="data-grid-sort" href="<?= Html::encode($sortUrl('name')) ?>">Nome <span><?= $sortMark('name') ?></span></a></th>
            <th><a class="data-grid-sort" href="<?= Html::encode($sortUrl('host')) ?>">Host <span><?= $sortMark('host') ?></span></a></th>
            <th><a class="data-grid-sort" href="<?= Html::encode($sortUrl('location')) ?>">Local <span><?= $sortMark('location') ?></span></a></th>
            <th><a class="data-grid-sort" href="<?= Html::encode($sortUrl('active')) ?>">Ativo <span><?= $sortMark('active') ?></span></a></th>
            <th><a class="data-grid-sort" href="<?= Html::encode($sortUrl('last_seen_at')) ?>">Telemetria <span><?= $sortMark('last_seen_at') ?></span></a></th>
            <th class="grid-actions">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($printers as $printer) : ?>
            <tr>
                <td><?= Html::encode($printer->name) ?></td>
                <td><?= Html::encode($printer->host) ?></td>
                <td><?= Html::encode($printer->location ?? '—') ?></td>
                <td>
                    <span class="status-badge <?= $printer->active ? 'status-badge--active' : 'status-badge--inactive' ?>">
                        <?= $printer->active ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td><?= Html::encode($printer->lastSeenAt ?? 'Não detectada') ?></td>
                <td class="grid-actions">
                    <div class="grid-action-group">
                        <form method="post" action="<?= Html::encode($urlGenerator->generate('printer/detect')) ?>">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                            <input type="hidden" name="id" value="<?= $printer->id ?>">
                            <button class="icon-button icon-button-secondary" type="submit" title="Detectar" aria-label="Detectar impressora <?= Html::encode($printer->name) ?>">
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1a7 7 0 1 0 7 7A7 7 0 0 0 8 1m0 1a6 6 0 1 1-6 6 6 6 0 0 1 6-6m0 2.5A3.5 3.5 0 1 0 11.5 8 3.5 3.5 0 0 0 8 4.5m0 1A2.5 2.5 0 1 1 5.5 8 2.5 2.5 0 0 1 8 5.5m0 1.5A1 1 0 1 0 9 8a1 1 0 0 0-1-1"/></svg>
                            </button>
                        </form>
                        <button
                            class="icon-button icon-button-primary"
                            type="button"
                            title="Editar"
                            aria-label="Editar impressora <?= Html::encode($printer->name) ?>"
                            data-printer-modal-edit
                            data-printer-id="<?= $printer->id ?>"
                            data-printer-name="<?= Html::encode($printer->name) ?>"
                            data-printer-host="<?= Html::encode($printer->host) ?>"
                            data-printer-location-id="<?= $printer->locationId ?? '' ?>"
                            data-printer-active="<?= $printer->active ? '1' : '0' ?>"
                        >
                            <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1-2-2 1-1a.5.5 0 0 1 .707 0zM13.5 4.207l-2-2L4.939 8.768a2 2 0 0 0-.497.832l-.94 3.132a.5.5 0 0 0 .621.621l3.132-.94a2 2 0 0 0 .832-.497zM1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                        </button>
                        <form method="post" onsubmit="return confirm('Remover esta impressora?');">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id" value="<?= $printer->id ?>">
                            <button class="icon-button icon-button-danger" type="submit" title="Remover" aria-label="Remover impressora <?= Html::encode($printer->name) ?>">
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H6V1a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h3.5a1 1 0 0 1 1 1M4 4v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4zm3-2h2V1H7z"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($printers === []) : ?>
            <tr><td colspan="6">Nenhuma impressora encontrada para os filtros informados.</td></tr>
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
            <nav class="data-grid-pagination" aria-label="Paginação de impressoras">
                <a class="data-grid-page<?= $page === 1 ? ' is-disabled' : '' ?>" href="<?= $page === 1 ? '#' : Html::encode($pageUrl($page - 1)) ?>">‹</a>
                <?php for ($number = 1; $number <= $pageCount; $number++) : ?>
                    <a class="data-grid-page<?= $number === $page ? ' is-current' : '' ?>" href="<?= Html::encode($pageUrl($number)) ?>"<?= $number === $page ? ' aria-current="page"' : '' ?>><?= $number ?></a>
                <?php endfor; ?>
                <a class="data-grid-page<?= $page === $pageCount ? ' is-disabled' : '' ?>" href="<?= $page === $pageCount ? '#' : Html::encode($pageUrl($page + 1)) ?>">›</a>
            </nav>
        <?php endif; ?>
    </footer>
</section>

<div class="modal-backdrop" data-printer-modal aria-hidden="true">
    <section class="app-modal" role="dialog" aria-modal="true" aria-labelledby="printer-modal-title">
        <header class="app-modal__header">
            <h2 id="printer-modal-title" data-printer-modal-title>Cadastrar impressora</h2>
            <button class="icon-button icon-button-danger" type="button" data-printer-modal-close title="Fechar" aria-label="Fechar">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>
            </button>
        </header>
        <div class="app-modal__body">
            <form method="post" class="form-card-compact" data-printer-form>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf ?? '') ?>">
                <input type="hidden" name="operation" value="create" data-printer-operation>
                <input type="hidden" name="id" value="" data-printer-id>
                <div class="form-grid form-grid-2">
                    <div class="form-field">
                        <label for="printer-modal-name">Nome lógico</label>
                        <input id="printer-modal-name" name="name" maxlength="160" required data-printer-name>
                    </div>
                    <div class="form-field">
                        <label for="printer-modal-host">IP ou FQDN</label>
                        <input id="printer-modal-host" name="host" maxlength="160" required data-printer-host>
                    </div>
                    <div class="form-field">
                        <label for="printer-modal-location">Local</label>
                        <select id="printer-modal-location" name="location_id" data-printer-location>
                            <option value="">Sem local definido</option>
                            <?php foreach ($locations as $id => $label) : ?>
                                <option value="<?= $id ?>"><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <label class="form-switch" data-printer-active-field hidden>
                        <input type="checkbox" name="active" value="1" data-printer-active>
                        <span class="form-switch__control" aria-hidden="true"></span>
                        <span>Ativo</span>
                    </label>
                </div>
                <?php if ($locations === []) : ?>
                    <p class="field-hint">Nenhum local ativo cadastrado. Cadastre um local em Organização → Locais.</p>
                <?php endif; ?>
                <div class="app-modal__footer">
                    <button class="button button-bs-secondary" type="button" data-printer-modal-close>Cancelar</button>
                    <button class="button button-with-icon button-bs-warning" type="submit">
                        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093z"/></svg>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
