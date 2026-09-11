<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$assetManager->register(MainAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="/branding/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/branding/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="/branding/favicon-48x48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/branding/favicon-180x180.png">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body class="app-page">
<?php $this->beginBody() ?>
<div class="app-shell" data-sidebar-state="expanded">
    <header class="app-topbar">
        <a class="app-brand" href="<?= Html::encode($urlGenerator->generate('home')) ?>">
            <img src="/branding/logo-horizontal.png" alt="HECATE">
        </a>
        <div class="topbar-context" aria-label="Contexto operacional">
            <span><strong>OM</strong> não selecionada</span>
            <span><strong>Jobs</strong> 0</span>
            <span><strong>Governança</strong> POC</span>
        </div>
        <div class="topbar-user">
            <span class="status-dot" aria-hidden="true"></span>
            <span>Desenvolvimento</span>
        </div>
    </header>

    <aside class="app-sidebar" aria-label="Menu principal">
        <div class="sidebar-header">
            <img src="/branding/symbol.png" alt="" aria-hidden="true">
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Recolher menu">☰</button>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= Html::encode($urlGenerator->generate('home')) ?>"><span>Dashboard</span></a>

            <p>Impressão</p>
            <a href="<?= Html::encode($urlGenerator->generate('printer/index')) ?>"><span>Impressoras</span></a>
            <a href="#"><span>Filas</span></a>
            <a href="#"><span>Jobs pendentes</span></a>
            <a href="#"><span>Liberação</span></a>

            <p>Organização</p>
            <a href="#"><span>Divisões</span></a>
            <a href="#"><span>Usuários</span></a>

            <p>Governança</p>
            <a href="#"><span>Políticas</span></a>
            <a href="#"><span>Papéis e aprovações</span></a>
            <a href="#"><span>Cotas</span></a>
            <a href="#"><span>Contratos</span></a>
            <a href="#"><span>Indicadores</span></a>

            <p>Operação</p>
            <a href="#"><span>Monitoramento</span></a>
            <a href="#"><span>Auditoria</span></a>

            <p>Sistema</p>
            <a href="#"><span>Integrações</span></a>
            <a href="#"><span>Configurações</span></a>
        </nav>
    </aside>

    <section class="app-workspace">
        <div class="workspace-navbar">
            <div>
                <span class="breadcrumb-root">HECATE</span>
                <span class="breadcrumb-separator">/</span>
                <span><?= Html::encode($this->getTitle()) ?></span>
            </div>
            <a class="workspace-login-link" href="<?= Html::encode($urlGenerator->generate('login')) ?>">Sair</a>
        </div>

        <main class="app-content">
            <?= $content ?>
        </main>
    </section>

    <footer class="app-footerbar">
        <span class="app-footerbar-year">CTIM - <?= date('Y') ?></span>
        <span class="app-footerbar-slogan">
            <span class="app-footerbar-star" aria-hidden="true">✦</span>
            Plataforma Institucional de Governança e Controle de Impressão
        </span>
        <span class="app-footerbar-credit">CC(EN) HONORATO</span>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
