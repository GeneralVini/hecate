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

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

$routeUrl = static function (string $route) use ($urlGenerator, $basePath): string {
    $url = $urlGenerator->generate($route);

    if ($basePath !== '' && str_starts_with($url, '/') && !str_starts_with($url, $basePath . '/')) {
        return $basePath . $url;
    }

    return $url;
};

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
    <style>
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-icon {
            flex: 0 0 22px;
            width: 22px;
            color: var(--hecate-gold-soft);
            font-size: 1rem;
            line-height: 1;
            text-align: center;
            opacity: .92;
        }
        .nav-label { min-width: 0; }
        .nav-section {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .nav-section-icon {
            color: rgba(210, 173, 82, .72);
            font-size: .76rem;
        }
        .sidebar-nav .nav-section-gold,
        .sidebar-nav .nav-section-gold .nav-section-icon {
            color: var(--hecate-gold-strong);
        }
        .sidebar-nav .nav-section-gold {
            padding-top: 5px;
            border-top: 1px solid rgba(210, 173, 82, .12);
        }
        .app-shell[data-sidebar-state="collapsed"] .sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .app-shell[data-sidebar-state="collapsed"] .sidebar-header img {
            display: none;
        }
        .app-shell[data-sidebar-state="collapsed"] .sidebar-nav a .nav-icon {
            opacity: 1;
            pointer-events: auto;
        }
        .app-shell[data-sidebar-state="collapsed"] .sidebar-nav a {
            justify-content: center;
        }
    </style>
</head>
<body class="app-page">
<?php $this->beginBody() ?>
<div class="app-shell" data-sidebar-state="expanded">
    <header class="app-topbar">
        <a class="app-brand" href="<?= Html::encode($routeUrl('home')) ?>">
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
<<<<<<< HEAD
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Recolher menu" aria-expanded="true">☰</button>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= Html::encode($routeUrl('home')) ?>">
                <span class="nav-icon" aria-hidden="true">⌂</span><span class="nav-label">Dashboard</span>
            </a>

            <p class="nav-section"><span class="nav-section-icon" aria-hidden="true">▣</span><span>Impressão</span></p>
            <a href="<?= Html::encode($routeUrl('printer/index')) ?>"><span class="nav-icon" aria-hidden="true">▤</span><span class="nav-label">Impressoras</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">≡</span><span class="nav-label">Filas</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">◷</span><span class="nav-label">Jobs pendentes</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">✓</span><span class="nav-label">Liberação</span></a>

            <p class="nav-section nav-section-gold"><span class="nav-section-icon" aria-hidden="true">◇</span><span>Organização</span></p>
            <a href="#"><span class="nav-icon" aria-hidden="true">▦</span><span class="nav-label">Divisões</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">♙</span><span class="nav-label">Usuários</span></a>

            <p class="nav-section"><span class="nav-section-icon" aria-hidden="true">◆</span><span>Governança</span></p>
            <a href="#"><span class="nav-icon" aria-hidden="true">§</span><span class="nav-label">Políticas</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">♜</span><span class="nav-label">Papéis e aprovações</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">◫</span><span class="nav-label">Cotas</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">▧</span><span class="nav-label">Contratos</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">⌁</span><span class="nav-label">Indicadores</span></a>

            <p class="nav-section"><span class="nav-section-icon" aria-hidden="true">◉</span><span>Operação</span></p>
            <a href="#"><span class="nav-icon" aria-hidden="true">◎</span><span class="nav-label">Monitoramento</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">≣</span><span class="nav-label">Auditoria</span></a>

            <p class="nav-section"><span class="nav-section-icon" aria-hidden="true">⚙</span><span>Sistema</span></p>
            <a href="#"><span class="nav-icon" aria-hidden="true">↔</span><span class="nav-label">Integrações</span></a>
            <a href="#"><span class="nav-icon" aria-hidden="true">⚙</span><span class="nav-label">Configurações</span></a>
=======
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Recolher menu" aria-controls="sidebar-navigation" aria-expanded="true">☰</button>
        </div>

        <nav class="sidebar-nav" id="sidebar-navigation">
            <a href="<?= Html::encode($urlGenerator->generate('home')) ?>" title="Dashboard" aria-label="Dashboard"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10 12 3l9 7v11h-6v-7H9v7H3Z"/></svg><span>Dashboard</span></a>

            <p>Impressão</p>
            <a href="<?= Html::encode($urlGenerator->generate('printer/index')) ?>" title="Impressoras" aria-label="Impressoras"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 17H3V9h18v8h-3M6 14h12v7H6Z"/></svg><span>Impressoras</span></a>
            <a aria-disabled="true" title="Filas — Em breve" aria-label="Filas"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/></svg><span>Filas</span></a>
            <a aria-disabled="true" title="Jobs pendentes — Em breve" aria-label="Jobs pendentes"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l4 4v14H6ZM14 3v5h5M9 12h7M9 16h7"/></svg><span>Jobs pendentes</span></a>
            <a aria-disabled="true" title="Liberação — Em breve" aria-label="Liberação"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 10V7a5 5 0 0 1 10 0M5 10h14v11H5Z"/></svg><span>Liberação</span></a>

            <p>Organização</p>
            <a aria-disabled="true" title="Divisões — Em breve" aria-label="Divisões"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6v5H9ZM3 16h6v5H3ZM15 16h6v5h-6ZM12 8v4M6 16v-4h12v4"/></svg><span>Divisões</span></a>
            <a aria-disabled="true" title="Usuários — Em breve" aria-label="Usuários"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-3a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v3M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8M17 4a4 4 0 0 1 0 8M22 21v-3a4 4 0 0 0-3-4"/></svg><span>Usuários</span></a>

            <p>Governança</p>
            <a aria-disabled="true" title="Políticas — Em breve" aria-label="Políticas"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 3 7v6c0 5 9 9 9 9s9-4 9-9V7ZM8 12l3 3 5-6"/></svg><span>Políticas</span></a>
            <a aria-disabled="true" title="Papéis e aprovações — Em breve" aria-label="Papéis e aprovações"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h14v18H5ZM8 8l2 2 4-4M8 15h8"/></svg><span>Papéis e aprovações</span></a>
            <a aria-disabled="true" title="Cotas — Em breve" aria-label="Cotas"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h18v14H3ZM7 3h10v4M7 12h10M7 16h5"/></svg><span>Cotas</span></a>
            <a aria-disabled="true" title="Contratos — Em breve" aria-label="Contratos"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h14v18H5ZM8 7h8M8 11h8M8 15h5"/></svg><span>Contratos</span></a>
            <a aria-disabled="true" title="Indicadores — Em breve" aria-label="Indicadores"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V11h4v10M10 21V3h4v18M16 21V7h4v14"/></svg><span>Indicadores</span></a>

            <p>Operação</p>
            <a aria-disabled="true" title="Monitoramento — Em breve" aria-label="Monitoramento"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12h5l3-8 4 16 3-8h5"/></svg><span>Monitoramento</span></a>
            <a aria-disabled="true" title="Auditoria — Em breve" aria-label="Auditoria"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 3h12v18H4ZM8 7h4M8 11h4M16 14l6 6M18 10a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/></svg><span>Auditoria</span></a>

            <p>Sistema</p>
            <a aria-disabled="true" title="Integrações — Em breve" aria-label="Integrações"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v5M16 3v5M5 8h14v3a7 7 0 0 1-14 0ZM12 18v4"/></svg><span>Integrações</span></a>
            <a aria-disabled="true" title="Configurações — Em breve" aria-label="Configurações"><svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16M8 3v6M16 9v6M10 15v6"/></svg><span>Configurações</span></a>
>>>>>>> 7a2b66d (ok)
        </nav>
    </aside>

    <section class="app-workspace">
        <div class="workspace-navbar">
            <div>
                <span class="breadcrumb-root">HECATE</span>
                <span class="breadcrumb-separator">/</span>
                <span><?= Html::encode($this->getTitle()) ?></span>
            </div>
            <a class="workspace-login-link" href="<?= Html::encode($routeUrl('login')) ?>">Sair</a>
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
