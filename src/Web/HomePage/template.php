<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/** @var Yiisoft\View\WebView $this */
/** @var App\Monitoring\Query\InventoryMetrics $metrics */

$this->setTitle('HECATE — Controle e Governança de Impressão');
?>
<section class="page-header">
    <div>
        <p class="eyebrow">HECATE</p>
        <h1>Controle e Governança de Impressão</h1>
        <p>Visão operacional do ambiente de impressão institucional.</p>
    </div>
</section>

<section class="metric-grid" aria-label="Indicadores">
    <article class="metric-card">
        <span>Impressoras</span>
        <strong><?= Html::encode((string) $metrics->printers) ?></strong>
    </article>
    <article class="metric-card">
        <span>Com telemetria</span>
        <strong><?= Html::encode((string) $metrics->withTelemetry) ?></strong>
    </article>
    <article class="metric-card">
        <span>Divisões</span>
        <strong><?= Html::encode((string) $metrics->divisions) ?></strong>
    </article>
    <article class="metric-card">
        <span>Registros de quota</span>
        <strong><?= Html::encode((string) $metrics->quotaRows) ?></strong>
    </article>
</section>
