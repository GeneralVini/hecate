<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Branding\BrandingAsset;
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

/** @var Closure(string): string $brandingUrl */
$brandingUrl = static fn (string $file): string => $assetManager->getUrl(BrandingAsset::class, $file);
$loginBrandingStyle = "--hecate-login-background: url('" . $brandingUrl('login-background.jpg') . "');";

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= Html::encode($brandingUrl('favicon-16x16.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= Html::encode($brandingUrl('favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?= Html::encode($brandingUrl('favicon-48x48.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Html::encode($brandingUrl('favicon-180x180.png')) ?>">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body class="login-page" style="<?= Html::encode($loginBrandingStyle) ?>">
<?php $this->beginBody() ?>
<main class="login-shell">
    <section class="login-stage" aria-label="Identidade visual HECATE">
        <div class="login-art" aria-hidden="true"></div>
        <div class="login-form-area">
            <?= $content ?>
        </div>
    </section>

    <div class="login-footer-strip">
        <?php require dirname(__DIR__, 2) . '/Partial/institutional-footer.php'; ?>
    </div>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
