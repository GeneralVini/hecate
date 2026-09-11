<?php

declare(strict_types=1);

use App\Model\Printer;
use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var list<Printer> $printers */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */

$this->setTitle('Impressoras — HECATE');
?>
<section class="page-header page-header-row">
    <div>
        <p class="eyebrow">Inventário</p>
        <h1>Impressoras</h1>
        <p>Equipamentos gerenciados pelo fluxo controlado do HECATE.</p>
    </div>
    <a class="button" href="<?= Html::encode($urlGenerator->generate('printer/create')) ?>">Cadastrar impressora</a>
</section>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Nome</th>
            <th>Host</th>
            <th>Localização</th>
            <th>Telemetria</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($printers as $printer): ?>
            <tr>
                <td><?= Html::encode($printer->name) ?></td>
                <td><?= Html::encode($printer->host) ?></td>
                <td><?= Html::encode($printer->location ?? '—') ?></td>
                <td><?= Html::encode($printer->last_seen_at ?? 'Não detectada') ?></td>
                <td>
                    <form method="post" action="<?= Html::encode($urlGenerator->generate('printer/detect')) ?>">
                        <input type="hidden" name="id" value="<?= Html::encode((string) $printer->id) ?>">
                        <button class="button button-secondary" type="submit">Detectar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($printers === []): ?>
            <tr><td colspan="5">Nenhuma impressora cadastrada.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
