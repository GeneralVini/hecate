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

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
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
