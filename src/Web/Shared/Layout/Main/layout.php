<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
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
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<header class="topbar">
    <div class="shell topbar-inner">
        <a class="brand" href="<?= Html::encode($urlGenerator->generate('home')) ?>">
            <span class="brand-mark">H</span>
            <span><strong>HECATE</strong><small>Controle e Governança de Impressão</small></span>
        </a>
        <nav aria-label="Navegação principal">
            <a href="<?= Html::encode($urlGenerator->generate('home')) ?>">Painel</a>
            <a href="<?= Html::encode($urlGenerator->generate('printer/index')) ?>">Impressoras</a>
        </nav>
    </div>
</header>
<main class="shell main-content">
    <?= $content ?>
</main>
<footer class="footer">
    <div class="shell">HECATE — Plataforma Institucional de Governança e Controle de Impressão</div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
