<?php
$this->title = 'Dashboard';
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><span>Impressoras</span><strong><?= (int)$metrics['printers'] ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Online</span><strong><?= (int)$metrics['printersOnline'] ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Divisões</span><strong><?= (int)$metrics['divisions'] ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Cotas ativas</span><strong><?= (int)$metrics['quotaRows'] ?></strong></div></div>
</div>
<div class="row g-3">
    <div class="col-lg-6"><div class="panel-card"><h5>Fila de impressão</h5><p class="text-muted">Jobs pendentes, liberados, concluídos e falhos serão exibidos aqui a partir do accounting do SavaPage.</p></div></div>
    <div class="col-lg-6"><div class="panel-card"><h5>Componentes do stack</h5><ul class="stack-list"><li>SavaPage <span class="badge text-bg-secondary">POC</span></li><li>CUPS <span class="badge text-bg-secondary">POC</span></li><li>PostgreSQL <span class="badge text-bg-success">Configurado</span></li><li>Keycloak <span class="badge text-bg-secondary">Planejado</span></li><li>Catálogo MB <span class="badge text-bg-secondary">Planejado</span></li><li>hecate-agent <span class="badge text-bg-secondary">Planejado</span></li></ul></div></div>
    <div class="col-lg-6"><div class="panel-card"><h5>Consumo de suprimentos</h5><p class="text-muted">SNMP → IPP → EWS/API → parser específico. Falha de telemetria nunca bloqueará impressão.</p></div></div>
    <div class="col-lg-6"><div class="panel-card"><h5>Cotas e contratos</h5><p class="text-muted">Controle independente de P&B e colorida, incluindo reservado, consumido e disponível por DCTIM-xx.</p></div></div>
</div>
