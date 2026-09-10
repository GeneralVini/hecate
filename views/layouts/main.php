<?php

use app\\assets\\AppAsset;
use yii\\helpers\\Html;
use yii\\helpers\\Url;

AppAsset::register($this);
$this->registerCsrfMetaTags();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?: 'HECATE') ?></title>
    <?php $this->head() ?>
</head>
<body class="hecate-body">
<?php $this->beginBody() ?>
<div class="hecate-shell">
    <aside class="hecate-sidebar">
        <div class="brand">HECATE</div>
        <div class="brand-subtitle">Governança de Impressão</div>
        <nav class="nav flex-column mt-4">
            <a class="nav-link" href="<?= Url::to(['/site/index']) ?>">Dashboard</a>
            <a class="nav-link" href="<?= Url::to(['/printer/index']) ?>">Impressoras</a>
            <a class="nav-link disabled" href="#">Filas e Jobs</a>
            <a class="nav-link disabled" href="#">Divisões</a>
            <a class="nav-link disabled" href="#">Cotas</a>
            <a class="nav-link disabled" href="#">Contratos</a>
            <a class="nav-link disabled" href="#">Autorizações</a>
            <a class="nav-link disabled" href="#">Monitoramento</a>
            <a class="nav-link disabled" href="#">Auditoria</a>
            <a class="nav-link disabled" href="#">Sistema</a>
        </nav>
    </aside>
    <div class="hecate-main">
        <header class="hecate-topbar">
            <div><strong>HECATE</strong> <span class="text-muted">| OM local</span></div>
            <div class="small">Stack: <span class="status-ok">OPERACIONAL</span></div>
        </header>
        <div class="hecate-navbar">
            <span><?= Html::encode($this->title ?: 'Dashboard') ?></span>
        </div>
        <main class="hecate-content container-fluid py-4">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <div class="alert alert-<?= Html::encode($type) ?>"><?= Html::encode($message) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
        <footer class="hecate-footer">HECATE MVP · SavaPage · CUPS · PostgreSQL · Keycloak · Catálogo MB</footer>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
