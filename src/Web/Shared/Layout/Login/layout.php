<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
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
    <link rel="icon" type="image/png" sizes="32x32" href="/branding/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/branding/favicon-180x180.png">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body class="login-page">
<?php $this->beginBody() ?>
<main class="login-shell">
    <div class="login-art" aria-hidden="true"></div>
    <div class="login-form-area">
        <?= $content ?>
    </div>
    <footer class="login-footerbar">
        <div class="login-footerbar-slogan" aria-label="Plataforma Institucional de Governança e Controle de Impressão">
            <span class="login-footerbar-star" aria-hidden="true">✦</span>
            <span>Plataforma Institucional de Governança e Controle de Impressão</span>
        </div>
        <span class="login-footerbar-year">CTIM - <?= date('Y') ?></span>
        <span class="login-footerbar-credit">CC(EN) HONORATO</span>
    </footer>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
